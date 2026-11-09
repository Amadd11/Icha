<?php

namespace App\Services\Admin;

use App\Models\AbstractSubmission;
use App\Models\Conference;
use App\Models\ReviewRound;
use App\Models\User;
use App\Services\Admin\ReviewAssignmentService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use ZipArchive;

class AbstractReviewService
{
    public function __construct(
        protected ReviewAssignmentService $assignmentService
    ) {}

    /**
     * Get abstract submissions filtered by status with pagination.
     */
    public function getAbstracts(?string $status = null, int $perPage = 15, ?string $search = null): LengthAwarePaginator
    {
        // Auto-assign any pending or under_review abstracts to category reviewers
        $activeAbstracts = AbstractSubmission::whereIn('status', ['pending', 'under_review'])
            ->whereNotNull('category_id')
            ->get();
        foreach ($activeAbstracts as $abs) {
            $this->assignmentService->autoAssignCategoryReviewers($abs);
        }

        // Auto-decide any submissions that reached required completed reviews
        $undecidedRounds = ReviewRound::where('submission_type', 'abstract')
            ->whereIn('status', ['open', 'locked'])
            ->get();
        foreach ($undecidedRounds as $round) {
            $round->applyAutoDecision();
        }

        $confId = request()->query('conference_id') ?? session('admin_conference_id') ?? Conference::where('is_active', true)->first()?->id;

        $query = AbstractSubmission::with([
            'user.profile',
            'category',
            'conference',
            'reviewRounds.assignments.reviewer',
            'reviewRounds.assignments.review',
        ])
            ->when($confId, fn($q) => $q->where('conference_id', $confId))
            ->when($search, function ($q, $search) {
                $search = trim($search);
                $q->where(function ($sub) use ($search) {
                    $sub->where('abstract_code', 'like', "%{$search}%")
                        ->orWhere('title', 'like', "%{$search}%")
                        ->orWhere('keywords', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($uq) use ($search) {
                            $uq->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhereHas('profile', function ($pq) use ($search) {
                                    $pq->where('institution', 'like', "%{$search}%")
                                        ->orWhere('phone', 'like', "%{$search}%");
                                });
                        })
                        ->orWhereHas('category', function ($cq) use ($search) {
                            $cq->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->latest();

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Process review decision for an abstract.
     */
    public function reviewAbstract(AbstractSubmission $abstract, User $reviewer, array $data): bool
    {
        $round = $this->assertFinalDecisionAllowed($abstract, $data['status'], $data['presentation_type'] ?? null);

        $updateData = [
            'status'       => $data['status'],
            'review_notes' => $data['review_notes'] ?? null,
            'reviewed_by'  => $reviewer->id,
            'reviewed_at'  => now(),
        ];

        if ($data['status'] === 'accepted') {
            $presentationType = !empty($data['presentation_type'])
                ? strtolower($data['presentation_type'])
                : ($round ? strtolower((string) $round->recommendedPresentationType()) : 'oral');
            $updateData['presentation_type'] = in_array($presentationType, ['oral', 'poster'], true) ? $presentationType : 'oral';
        }

        $updated = $abstract->update($updateData);

        if ($round && in_array($round->status, ['locked', 'open'], true)) {
            $round->update(['status' => 'completed']);
        }

        return $updated;
    }

    private function assertFinalDecisionAllowed(AbstractSubmission $abstract, string $status, ?string $presentationType): ?ReviewRound
    {
        if (!in_array($status, ['accepted', 'revision_required', 'rejected'], true)) {
            return null;
        }

        $round = ReviewRound::where('submission_type', 'abstract')
            ->where('submission_id', $abstract->id)
            ->latest('round_number')
            ->first();

        if (!$round) {
            throw ValidationException::withMessages([
                'status' => 'Tidak ditemukan tahap penilaian (review round) untuk naskah ini.',
            ]);
        }

        $totalAssignments = $round->assignments()->count();
        $completedAssignments = $round->assignments()->where('status', 'completed')->count();

        // Admin has full right to override / change an already decided submission
        $isModifyingExistingDecision = in_array($abstract->status, ['accepted', 'revision_required', 'rejected'], true);

        // Require 3 reviewers to complete review before final decision (unless desk rejected by admin or modifying existing decision)
        if (!$isModifyingExistingDecision && $status !== 'rejected') {
            if ($round->round_number === 1) {
                $expectedReviews = min(3, max(1, $totalAssignments));
                if ($completedAssignments < $expectedReviews) {
                    throw ValidationException::withMessages([
                        'status' => "Final decision memerlukan 3 reviewer menyelesaikan telaah (saat ini baru {$completedAssignments}/{$expectedReviews} reviewer yang selesai).",
                    ]);
                }
            } else {
                if ($totalAssignments === 0 || $completedAssignments < $totalAssignments) {
                    throw ValidationException::withMessages([
                        'status' => "Final decision memerlukan seluruh reviewer pada tahap revisi ini menyelesaikan telaah (saat ini: {$completedAssignments}/{$totalAssignments}).",
                    ]);
                }
            }
        }

        // Auto-lock if all reviews are completed and round is still open
        if ($round->status === 'open' && $completedAssignments >= ($round->round_number === 1 ? 3 : $totalAssignments)) {
            $round->update(['status' => 'locked']);
        }

        return $round;
    }

    /**
     * Generate a ZIP archive containing submitted abstracts and an index CSV file.
     *
     * @param int|null $conferenceId
     * @param string|null $status
     * @return array{path: string, filename: string, count: int}
     */
    public function exportZipArchive(?int $conferenceId = null, ?string $status = null): array
    {
        $confId = $conferenceId ?? request()->query('conference_id') ?? session('admin_conference_id') ?? Conference::where('is_active', true)->first()?->id;
        $statusFilter = $status ?? request()->query('status');

        $conference = $confId ? Conference::find($confId) : null;
        $confSlug = $conference ? Str::slug($conference->title ?? 'ICHA') : 'all-conferences';
        $statusSlug = ($statusFilter && $statusFilter !== 'all') ? '_' . Str::slug($statusFilter) : '';
        $timestamp = now()->format('Y-m-d_His');
        $zipFilename = "Abstracts_{$confSlug}{$statusSlug}_{$timestamp}.zip";

        $tempDir = storage_path('app/temp');
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
        $zipPath = $tempDir . '/' . $zipFilename;

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Gagal membuat file ZIP archive.');
        }

        $abstracts = AbstractSubmission::with(['user.profile', 'category', 'conference'])
            ->when($confId, fn($q) => $q->where('conference_id', $confId))
            ->when($statusFilter && $statusFilter !== 'all', fn($q) => $q->where('status', $statusFilter))
            ->orderBy('abstract_code')
            ->get();

        // If conference filter yielded 0, and confId was provided, fallback to all abstracts
        if ($abstracts->isEmpty() && $confId) {
            $abstracts = AbstractSubmission::with(['user.profile', 'category', 'conference'])
                ->orderBy('abstract_code')
                ->get();
        }

        // Prepare CSV Index
        $csvRows = [];
        $csvRows[] = ['No', 'Kode Abstrak', 'Judul Abstrak', 'Penulis / Author', 'Email', 'Institusi', 'No. HP / WA', 'Kategori / Track', 'Jenis Presentasi', 'Status', 'Nama File di ZIP'];

        $index = 1;
        $addedFilesCount = 0;

        foreach ($abstracts as $abs) {
            $code = $abs->abstract_code ?? ('ABS-' . str_pad((string)$abs->id, 3, '0', STR_PAD_LEFT));
            $authorName = $abs->user?->name ?? $abs->author_name ?? 'Unknown Author';
            $email = $abs->user?->email ?? '-';
            $institution = $abs->user?->profile?->institution ?? '-';
            $phone = $abs->user?->profile?->phone ?? '-';
            $category = $abs->category?->name ?? 'General';
            $type = strtoupper($abs->presentation_type ?? 'oral');
            $status = ucfirst(str_replace('_', ' ', $abs->status ?? 'pending'));

            $safeCode = preg_replace('/[^A-Za-z0-9_-]/', '', $code);
            $safeAuthor = Str::slug($authorName, '_');
            $safeTitle = Str::limit(Str::slug($abs->title ?: 'untitled', '_'), 50, '');

            $realFilePath = null;
            if ($abs->file_path) {
                if (Storage::disk('public')->exists($abs->file_path)) {
                    $realFilePath = Storage::disk('public')->path($abs->file_path);
                } elseif (file_exists(public_path('storage/' . $abs->file_path))) {
                    $realFilePath = public_path('storage/' . $abs->file_path);
                }
            }

            if ($realFilePath && file_exists($realFilePath)) {
                $ext = pathinfo($realFilePath, PATHINFO_EXTENSION) ?: 'pdf';
                $fileNameInZip = "{$safeCode}_{$safeAuthor}_{$safeTitle}.{$ext}";
                $zip->addFile($realFilePath, "Files/{$fileNameInZip}");
                $addedFilesCount++;
            } else {
                $fileNameInZip = "{$safeCode}_{$safeAuthor}_{$safeTitle}.txt";
                $textSnippet = "============================================================\r\n";
                $textSnippet .= "KODE ABSTRAK       : {$code}\r\n";
                $textSnippet .= "JUDUL              : {$abs->title}\r\n";
                $textSnippet .= "PENULIS / AUTHOR   : {$authorName} ({$email})\r\n";
                $textSnippet .= "INSTITUSI          : {$institution}\r\n";
                $textSnippet .= "NO. TELEPON / WA   : {$phone}\r\n";
                $textSnippet .= "KATEGORI / TRACK   : {$category}\r\n";
                $textSnippet .= "JENIS PRESENTASI   : {$type}\r\n";
                $textSnippet .= "STATUS             : {$status}\r\n";
                $textSnippet .= "KEYWORDS           : " . ($abs->keywords ?? '-') . "\r\n";
                $textSnippet .= "============================================================\r\n\r\n";
                $textSnippet .= "ISI ABSTRAK:\r\n\r\n";
                $textSnippet .= ($abs->abstract_text ?: "(Teks naskah fisik tidak tersedia di server)");

                $zip->addFromString("Files/{$fileNameInZip}", $textSnippet);
                $addedFilesCount++;
            }

            $csvRows[] = [
                $index++,
                $code,
                $abs->title,
                $authorName,
                $email,
                $institution,
                $phone,
                $category,
                $type,
                $status,
                $fileNameInZip,
            ];
        }

        // Add Index CSV to root of ZIP
        $csvHandle = fopen('php://temp', 'r+');
        foreach ($csvRows as $row) {
            fputcsv($csvHandle, $row);
        }
        rewind($csvHandle);
        $csvContent = stream_get_contents($csvHandle);
        fclose($csvHandle);

        $zip->addFromString("DAFTAR_SELURUH_ABSTRAK.csv", $csvContent);

        $zip->close();

        return [
            'path'     => $zipPath,
            'filename' => $zipFilename,
            'count'    => $addedFilesCount,
        ];
    }
}

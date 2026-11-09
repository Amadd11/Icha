<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Submission\AdminAbstractResource;
use App\Models\AbstractSubmission;
use App\Services\Admin\AbstractReviewService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AbstractController extends Controller
{
    public function __construct(
        protected AbstractReviewService $abstractReviewService
    ) {}

    public function exportZip(Request $request): BinaryFileResponse
    {
        $confId = $request->query('conference_id');
        $status = $request->query('status');
        $result = $this->abstractReviewService->exportZipArchive($confId ? (int) $confId : null, $status);

        return response()->download($result['path'], $result['filename'])->deleteFileAfterSend(true);
    }

    public function index(Request $request): Response
    {
        $status = $request->input('status', 'all');
        $perPage = (int) $request->input('per_page', 15);
        if ($perPage < 5 || $perPage > 200) {
            $perPage = 15;
        }
        $search = trim((string) $request->input('search', ''));
        $abstracts = $this->abstractReviewService->getAbstracts($status, $perPage, $search ?: null);

        $transformedAbstracts = $abstracts->through(function ($abstract) {
            return (new AdminAbstractResource($abstract))->resolve();
        });

        return Inertia::render('Admin/Abstracts/Index', [
            'abstracts' => $transformedAbstracts,
            'filters'   => [
                'status'   => $status,
                'per_page' => $perPage,
                'search'   => $search,
            ],
        ]);
    }

    public function review(Request $request, AbstractSubmission $abstract): RedirectResponse
    {
        $data = $request->validate([
            'status'            => ['required', 'in:pending,under_review,revision_required,accepted,rejected'],
            'presentation_type' => ['nullable', 'required_if:status,accepted', 'in:oral,poster'],
            'review_notes'      => ['nullable', 'string', 'max:5000'],
        ]);

        $this->abstractReviewService->reviewAbstract(
            $abstract,
            $request->user(),
            $data
        );

        return redirect()->back()->with('success', 'Abstract decision updated successfully.');
    }

    public function destroy(AbstractSubmission $abstract): RedirectResponse
    {
        if ($abstract->file_path) {
            Storage::disk('public')->delete($abstract->file_path);
        }

        $abstract->delete();

        return redirect()->back()->with('success', 'Abstract deleted successfully.');
    }
}

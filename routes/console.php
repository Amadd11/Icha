<?php

use App\Models\AbstractSubmission;
use App\Models\Certificate;
use App\Models\Conference;
use App\Models\FullPaper;
use App\Models\Payment;
use App\Models\Speaker;
use App\Models\Sponsor;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('storage:prune-orphans {--force : Actually delete the orphaned files}', function () {
    $disks = [
        'public' => Storage::disk('public'),
        'local'  => Storage::disk('local'),
    ];

    // Collect all referenced file paths from the database
    $referencedFiles = collect();

    // 1. Abstracts
    $referencedFiles = $referencedFiles->merge(
        AbstractSubmission::withTrashed()->whereNotNull('file_path')->pluck('file_path')
    );

    // 2. Full Papers
    $referencedFiles = $referencedFiles->merge(
        FullPaper::withTrashed()->whereNotNull('file_path')->pluck('file_path')
    );

    // 3. Payments
    $referencedFiles = $referencedFiles->merge(
        Payment::withTrashed()->whereNotNull('proof_file')->pluck('proof_file')
    );

    // 4. Certificates
    $referencedFiles = $referencedFiles->merge(
        Certificate::withTrashed()->whereNotNull('file_path')->pluck('file_path')
    );

    // 5. Conferences
    Conference::withTrashed()->get()->each(function ($conf) use (&$referencedFiles) {
        if ($conf->poster) $referencedFiles->push($conf->poster);
        if ($conf->abstract_template) $referencedFiles->push($conf->abstract_template);
        if ($conf->paper_template) $referencedFiles->push($conf->paper_template);
        if (is_array($conf->hero_images)) {
            foreach ($conf->hero_images as $img) {
                if ($img) $referencedFiles->push($img);
            }
        }
    });

    // 6. Sponsors
    $referencedFiles = $referencedFiles->merge(
        Sponsor::withTrashed()->whereNotNull('logo')->pluck('logo')
    );

    // 7. Speakers (if photo column exists)
    if (Schema::hasTable('speakers') && Schema::hasColumn('speakers', 'photo')) {
        $referencedFiles = $referencedFiles->merge(
            Speaker::withTrashed()->whereNotNull('photo')->pluck('photo')
        );
    }

    $referencedSet = $referencedFiles
        ->map(fn($f) => str_replace('\\', '/', $f))
        ->filter()
        ->unique()
        ->flip();

    $orphans = [];
    $savedBytes = 0;

    foreach ($disks as $diskName => $disk) {
        try {
            $allFiles = $disk->allFiles();
        } catch (\Throwable $e) {
            continue;
        }

        foreach ($allFiles as $file) {
            $normalized = str_replace('\\', '/', $file);
            if ($normalized === '.gitignore') {
                continue;
            }

            if (!$referencedSet->has($normalized)) {
                $size = $disk->size($file);
                $orphans[] = ['disk' => $diskName, 'path' => $file, 'size' => $size];
                $savedBytes += $size;
            }
        }
    }

    if (empty($orphans)) {
        $this->info('No orphaned files found across storage disks.');
        return;
    }

    $this->warn('Found ' . count($orphans) . ' orphaned file(s) (' . round($savedBytes / 1024 / 1024, 2) . ' MB):');
    foreach ($orphans as $orphan) {
        $this->line(" - [{$orphan['disk']}] {$orphan['path']} (" . round($orphan['size'] / 1024, 1) . ' KB)');
    }

    if ($this->option('force')) {
        foreach ($orphans as $orphan) {
            Storage::disk($orphan['disk'])->delete($orphan['path']);
        }
        $this->info('Successfully deleted ' . count($orphans) . ' orphaned file(s). Freed ' . round($savedBytes / 1024 / 1024, 2) . ' MB.');
    } else {
        $this->newLine();
        $this->comment('To delete these files, run with --force:');
        $this->line('php artisan storage:prune-orphans --force');
    }
})->purpose('Scan and prune orphaned files in storage/app/public and storage/app/private that are not referenced in the database');

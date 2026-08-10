<?php

namespace App\Http\Controllers\Participant;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Services\CertificateService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CertificateController extends Controller
{
    public function __construct(
        protected CertificateService $certificateService
    ) {}

    public function index(Request $request): Response
    {
        $data = $this->certificateService->getUserCertificates($request->user());

        return Inertia::render('Participant/Certificate/Index', $data);
    }

    public function download(Certificate $certificate)
    {
        // Ensure user owns certificate or is admin
        $user = auth()->user();
        if ($certificate->user_id !== $user->id && !in_array($user->role, ['admin', 'super_admin'])) {
            abort(403, 'Unauthorized access to certificate.');
        }

        $certificate->load(['user', 'conference']);

        return view('certificates.template', [
            'certificate' => $certificate,
        ]);
    }
}

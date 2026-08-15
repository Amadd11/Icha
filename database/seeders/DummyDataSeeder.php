<?php

namespace Database\Seeders;

use App\Models\AbstractSubmission;
use App\Models\Category;
use App\Models\Conference;
use App\Models\Payment;
use App\Models\Profile;
use App\Models\Registration;
use App\Models\RegistrationFee;
use App\Models\Review;
use App\Models\ReviewAssignment;
use App\Models\ReviewRound;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DummyDataSeeder extends Seeder
{
    /**
     * Run the database seeds for testing pagination and workflows.
     */
    public function run(): void
    {
        $conference = Conference::where('is_active', true)->first() ?? Conference::first();
        if (!$conference) {
            $this->command->error('Please run ConferenceSeeder first!');
            return;
        }

        $presenterFees = RegistrationFee::where('conference_id', $conference->id)
            ->where('type', 'presenter')
            ->get();
        $nonPresenterFees = RegistrationFee::where('conference_id', $conference->id)
            ->where('type', 'non_presenter')
            ->get();
        $categories = Category::where('conference_id', $conference->id)->get();
        $reviewers = User::where('role', 'reviewer')->get();
        $admin = User::whereIn('role', ['super_admin', 'admin'])->first();

        $this->command->info('1. Generating 40 dummy participants with profiles...');
        
        $participants = User::factory()->count(40)->create();

        $this->command->info('2. Generating registrations and payments...');

        $registeredCount = 0;
        foreach ($participants as $index => $participant) {
            $isPresenter = ($index % 2 === 0);
            $fee = $isPresenter 
                ? $presenterFees->random() 
                : ($nonPresenterFees->isNotEmpty() ? $nonPresenterFees->random() : $presenterFees->random());

            $status = match ($index % 4) {
                0       => 'paid',
                1       => 'waiting_verification',
                2       => 'pending',
                default => 'paid',
            };

            $invNumber = 'INV-' . date('Y-m') . '-' . str_pad(Registration::count() + 1000 + $index, 4, '0', STR_PAD_LEFT);
            while (Registration::where('invoice_number', $invNumber)->exists()) {
                $invNumber = 'INV-' . date('Y-m') . '-' . rand(1000, 9999);
            }

            $registration = Registration::create([
                'invoice_number'      => $invNumber,
                'user_id'             => $participant->id,
                'conference_id'       => $conference->id,
                'registration_fee_id' => $fee->id,
                'is_early_bird'       => ($index % 3 === 0),
                'currency'            => $fee->currency ?? 'IDR',
                'amount'              => $fee->price,
                'status'              => $status,
                'notes'               => 'Dummy registration for testing',
            ]);

            // Create Payment record if not strictly unpaid pending
            if ($status === 'paid' || $status === 'waiting_verification') {
                Payment::create([
                    'registration_id'  => $registration->id,
                    'amount'           => $registration->amount,
                    'currency'         => $registration->currency,
                    'payment_method'   => ($index % 2 === 0) ? 'Bank Transfer (Mandiri)' : 'Bank Transfer (BCA)',
                    'proof_file'       => 'payments/sample_proof.png',
                    'status'           => ($status === 'paid') ? 'verified' : 'pending',
                    'rejection_reason' => null,
                    'paid_at'          => now()->subDays(rand(1, 10)),
                    'verified_at'      => ($status === 'paid') ? now() : null,
                    'verified_by'      => ($status === 'paid' && $admin) ? $admin->id : null,
                ]);
            }

            // If Presenter and Paid, create Abstract Submission
            if ($isPresenter && $status === 'paid' && $categories->isNotEmpty()) {
                $category = $categories->random();
                $absStatus = match ($registeredCount % 4) {
                    0       => 'accepted',
                    1       => 'under_review',
                    2       => 'revision_required',
                    default => 'accepted',
                };
                $presType = ($registeredCount % 2 === 0) ? 'oral' : 'poster';
                
                $absCode = 'ABS-' . str_pad(AbstractSubmission::count() + 100 + $registeredCount, 3, '0', STR_PAD_LEFT);
                while (AbstractSubmission::where('abstract_code', $absCode)->exists()) {
                    $absCode = 'ABS-' . rand(100, 999);
                }

                $abstract = AbstractSubmission::create([
                    'abstract_code'     => $absCode,
                    'user_id'           => $participant->id,
                    'conference_id'     => $conference->id,
                    'category_id'       => $category->id,
                    'title'             => 'Advancing ' . $category->name . ' in Modern Healthcare Ecosystems (Case Study #' . ($registeredCount + 1) . ')',
                    'abstract_text'     => 'Background: Digital transformation in healthcare requires comprehensive governance. Methods: A multi-center observational study was conducted across regional hospitals. Results: Implementation of integrated workflows improved delivery efficiency by 34%. Conclusion: Systematic administrative frameworks provide sustainable healthcare outcomes.',
                    'keywords'          => 'Healthcare, Policy, Hospital Management, Innovation',
                    'presentation_type' => $presType,
                    'file_path'         => 'abstracts/sample_abstract.pdf',
                    'status'            => $absStatus,
                    'review_notes'      => ($absStatus === 'revision_required') ? 'Please provide additional data on sample size in the methods section.' : null,
                    'reviewed_by'       => ($absStatus === 'accepted') ? $admin?->id : null,
                    'reviewed_at'       => ($absStatus === 'accepted') ? now() : null,
                ]);

                // Create Review Round
                $round = ReviewRound::create([
                    'submission_type' => 'abstract',
                    'submission_id'   => $abstract->id,
                    'round_number'    => 1,
                    'status'          => ($absStatus === 'accepted') ? 'completed' : 'pending',
                ]);

                // Assign 3 reviewers and generate reviews
                if ($reviewers->isNotEmpty()) {
                    foreach ($reviewers->take(3) as $reviewer) {
                        $isCompleted = ($absStatus === 'accepted');
                        $assignment = ReviewAssignment::create([
                            'review_round_id' => $round->id,
                            'reviewer_id'     => $reviewer->id,
                            'status'          => $isCompleted ? 'completed' : 'assigned',
                        ]);

                        if ($isCompleted) {
                            $c1 = rand(3, 5);
                            $c2 = rand(3, 5);
                            Review::create([
                                'review_assignment_id' => $assignment->id,
                                'score_criteria_1'     => $c1,
                                'score_criteria_2'     => $c2,
                                'total_score'          => $c1 + $c2,
                                'recommendation'       => ($c1 + $c2 >= 5) ? 'ORAL' : 'POSTER',
                                'summary'              => 'Solid methodological approach with clear relevance to health policy and hospital administration.',
                            ]);
                        }
                    }
                }

                $registeredCount++;
            }
        }

        $this->command->info("✓ DummyDataSeeder completed successfully! Generated 40 users, multiple registrations, payments, and abstracts.");
    }
}

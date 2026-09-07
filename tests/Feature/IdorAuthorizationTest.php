<?php

namespace Tests\Feature;

use App\Mail\InvoiceMail;
use App\Mail\PaymentApprovedMail;
use App\Mail\PaymentRejectedMail;
use App\Models\AbstractSubmission;
use App\Models\Category;
use App\Models\Certificate;
use App\Models\Conference;
use App\Models\Payment;
use App\Models\Registration;
use App\Models\RegistrationFee;
use App\Models\ReviewAssignment;
use App\Models\ReviewRound;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class IdorAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected Conference $activeConference;
    protected Conference $otherConference;
    protected User $userA;
    protected User $userB;
    protected User $adminUser;
    protected RegistrationFee $activeFee;
    protected RegistrationFee $otherFee;
    protected Category $activeCategory;
    protected Category $otherCategory;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Setup Conferences
        $this->activeConference = Conference::where('is_active', true)->first()
            ?? Conference::create([
                'title'      => 'ICHA Active Conf',
                'year'       => 2026,
                'slug'       => 'icha-active-test',
                'start_date' => '2026-10-01',
                'end_date'   => '2026-10-03',
                'is_active'  => true,
            ]);

        $this->otherConference = Conference::where('id', '!=', $this->activeConference->id)->first()
            ?? Conference::create([
                'title'      => 'ICHA Past Conf',
                'year'       => 2025,
                'slug'       => 'icha-past-test',
                'start_date' => '2025-10-01',
                'end_date'   => '2025-10-03',
                'is_active'  => false,
            ]);

        // 2. Setup Fees
        $this->activeFee = RegistrationFee::where('conference_id', $this->activeConference->id)->first()
            ?? RegistrationFee::create([
                'conference_id' => $this->activeConference->id,
                'name'          => 'Active Presenter',
                'type'          => 'presenter',
                'currency'      => 'IDR',
                'price'         => 1500000,
            ]);

        $this->otherFee = RegistrationFee::where('conference_id', $this->otherConference->id)->first()
            ?? RegistrationFee::create([
                'conference_id' => $this->otherConference->id,
                'name'          => 'Past Presenter',
                'type'          => 'presenter',
                'currency'      => 'IDR',
                'price'         => 1000000,
            ]);

        // 3. Setup Categories
        $this->activeCategory = Category::where('conference_id', $this->activeConference->id)->first()
            ?? Category::create([
                'conference_id' => $this->activeConference->id,
                'name'          => 'Active Medical Track',
            ]);

        $this->otherCategory = Category::where('conference_id', $this->otherConference->id)->first()
            ?? Category::create([
                'conference_id' => $this->otherConference->id,
                'name'          => 'Other Track',
            ]);

        // 4. Setup Users
        $this->userA = User::firstOrCreate(
            ['email' => 'author_a@test.com'],
            ['name' => 'Author A', 'password' => bcrypt('password'), 'role' => 'participant']
        );

        $this->userB = User::firstOrCreate(
            ['email' => 'author_b@test.com'],
            ['name' => 'Author B', 'password' => bcrypt('password'), 'role' => 'participant']
        );

        $this->adminUser = User::firstOrCreate(
            ['email' => 'admin_test@test.com'],
            ['name' => 'Admin Security Test', 'password' => bcrypt('password'), 'role' => 'admin']
        );
    }

    /**
     * IDOR Test 1: User B cannot download User A's certificate.
     */
    public function test_user_cannot_download_another_users_certificate(): void
    {
        $cert = Certificate::create([
            'user_id'            => $this->userA->id,
            'conference_id'      => $this->activeConference->id,
            'certificate_number' => 'CERT-TEST-001',
            'type'               => 'participant',
            'role_title'         => 'Presenter',
            'file_path'          => null,
        ]);

        // User B attempts to access User A's certificate
        $response = $this->actingAs($this->userB)->get("/certificate/{$cert->id}/download");
        $response->assertStatus(403);
    }

    /**
     * IDOR Test 2: Admin can download any user's certificate.
     */
    public function test_admin_can_download_any_users_certificate(): void
    {
        $cert = Certificate::create([
            'user_id'            => $this->userA->id,
            'conference_id'      => $this->activeConference->id,
            'certificate_number' => 'CERT-TEST-ADMIN',
            'type'               => 'participant',
            'role_title'         => 'Presenter',
            'file_path'          => null,
        ]);

        $response = $this->actingAs($this->adminUser)->get("/certificate/{$cert->id}/download");
        $response->assertStatus(200);
    }

    /**
     * IDOR Test 3: User A can download their own certificate.
     */
    public function test_owner_can_download_own_certificate(): void
    {
        $cert = Certificate::create([
            'user_id'            => $this->userA->id,
            'conference_id'      => $this->activeConference->id,
            'certificate_number' => 'CERT-TEST-OWNER',
            'type'               => 'participant',
            'role_title'         => 'Presenter',
            'file_path'          => null,
        ]);

        $response = $this->actingAs($this->userA)->get("/certificate/{$cert->id}/download");
        $response->assertStatus(200);
    }

    /**
     * IDOR Test 4: User B cannot submit paper for User A's abstract.
     */
    public function test_user_cannot_submit_paper_with_another_users_abstract_id(): void
    {
        Storage::fake('public');

        // Create accepted abstract belonging to User A
        $abstractA = AbstractSubmission::create([
            'user_id'           => $this->userA->id,
            'conference_id'     => $this->activeConference->id,
            'category_id'       => $this->activeCategory->id,
            'abstract_code'     => 'ABS-TEST-AAA',
            'title'             => 'User A Abstract Title',
            'presentation_type' => 'oral',
            'status'            => 'accepted',
        ]);

        // User B sets up paid presenter registration
        $regB = Registration::create([
            'user_id'             => $this->userB->id,
            'conference_id'       => $this->activeConference->id,
            'registration_fee_id' => $this->activeFee->id,
            'invoice_number'      => 'INV-B-001',
            'status'              => 'paid',
            'amount'              => 1500000,
        ]);
        Payment::create([
            'registration_id' => $regB->id,
            'amount'          => 1500000,
            'status'          => 'verified',
        ]);

        $payload = [
            'abstract_id' => $abstractA->id, // User A's abstract ID
            'title'       => 'User B Impersonating Paper Title',
            'file'        => UploadedFile::fake()->create('paper.docx', 500, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'),
        ];

        // User B attempts to submit full paper referencing User A's abstract
        $response = $this->actingAs($this->userB)->post('/my/paper', $payload);
        $response->assertSessionHasErrors(['abstract_id']);
    }

    /**
     * Integrity Test 5: User cannot register using registration_fee_id from another conference.
     */
    public function test_user_cannot_register_with_mismatched_conference_registration_fee(): void
    {
        $payload = [
            'registration_fee_id' => $this->otherFee->id, // Fee from another conference
            'notes'               => 'Attempted spoofing ticket',
        ];

        $response = $this->actingAs($this->userA)->post('/my/registration', $payload);
        $response->assertSessionHasErrors(['registration_fee_id']);
    }

    /**
     * Integrity Test 6: User cannot submit abstract with category_id from another conference.
     */
    public function test_user_cannot_submit_abstract_with_category_from_another_conference(): void
    {
        Storage::fake('public');

        // User A has valid paid presenter registration
        $regA = Registration::firstOrCreate(
            ['user_id' => $this->userA->id, 'conference_id' => $this->activeConference->id],
            [
                'registration_fee_id' => $this->activeFee->id,
                'invoice_number'      => 'INV-A-100',
                'status'              => 'paid',
                'amount'              => 1500000,
            ]
        );
        Payment::firstOrCreate(
            ['registration_id' => $regA->id],
            ['amount' => 1500000, 'status' => 'verified']
        );

        $payload = [
            'title'             => 'Cross Conference Category Test',
            'category_id'       => $this->otherCategory->id, // Category from another conference
            'file'              => UploadedFile::fake()->create('abstract.pdf', 300, 'application/pdf'),
            'presentation_type' => 'oral',
        ];

        $response = $this->actingAs($this->userA)->post('/my/abstract', $payload);
        $response->assertSessionHasErrors(['category_id']);
    }

    /**
     * COI Test 7: Reviewer cannot review their own submission (Anti-Conflict of Interest).
     */
    public function test_reviewer_cannot_review_own_submission_due_to_conflict_of_interest(): void
    {
        // Author who is also a reviewer
        $authorReviewer = User::firstOrCreate(
            ['email' => 'dual_role@test.com'],
            ['name' => 'Dual Role User', 'password' => bcrypt('password'), 'role' => 'reviewer']
        );

        $abstract = AbstractSubmission::create([
            'user_id'           => $authorReviewer->id,
            'conference_id'     => $this->activeConference->id,
            'category_id'       => $this->activeCategory->id,
            'abstract_code'     => 'ABS-COI-001',
            'title'             => 'Self Review COI Test',
            'presentation_type' => 'oral',
            'status'            => 'under_review',
        ]);

        $round = ReviewRound::create([
            'conference_id'   => $this->activeConference->id,
            'submission_type' => 'abstract',
            'submission_id'   => $abstract->id,
            'round_number'    => 1,
            'status'          => 'in_review',
        ]);

        $assignment = ReviewAssignment::create([
            'review_round_id' => $round->id,
            'reviewer_id'     => $authorReviewer->id,
            'assigned_by'     => $this->adminUser->id,
            'status'          => 'pending',
        ]);

        $reviewPayload = [
            'originality_score'   => 5,
            'methodology_score'   => 5,
            'clarity_score'       => 5,
            'relevance_score'     => 5,
            'total_score'         => 20,
            'recommendation'      => 'accept',
            'comments_to_author'  => 'Self review should be blocked.',
        ];

        $response = $this->actingAs($authorReviewer)->post("/reviewer/submissions/{$assignment->id}/review", $reviewPayload);
        $response->assertStatus(403);
    }

    /**
     * Mail Test 8: Invoice mail is dispatched upon participant registration.
     */
    public function test_invoice_email_is_sent_upon_registration(): void
    {
        Mail::fake();

        $freshUser = User::create([
            'name'     => 'Fresh Participant',
            'email'    => 'fresh_user_' . uniqid() . '@test.com',
            'password' => bcrypt('password'),
            'role'     => 'participant',
        ]);

        $payload = [
            'registration_fee_id' => $this->activeFee->id,
            'notes'               => 'Check instant mail dispatch',
        ];

        $response = $this->actingAs($freshUser)->post('/my/registration', $payload);
        $response->assertRedirect('/my/registration');

        Mail::assertSent(InvoiceMail::class, function ($mail) use ($freshUser) {
            return $mail->registration->user_id === $freshUser->id;
        });
    }
}

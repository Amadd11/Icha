<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Conference;
use App\Models\Payment;
use App\Models\Registration;
use App\Models\RegistrationFee;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ParticipantWorkflowTest extends TestCase
{
    protected Conference $conference;
    protected RegistrationFee $presenterFee;
    protected RegistrationFee $nonPresenterFee;
    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->conference = Conference::where('is_active', true)->first() ?? Conference::first();
        $this->presenterFee = RegistrationFee::where('conference_id', $this->conference->id)
            ->where('type', 'presenter')
            ->first() ?? RegistrationFee::first();
        $this->nonPresenterFee = RegistrationFee::where('conference_id', $this->conference->id)
            ->where('type', 'non_presenter')
            ->first() ?? RegistrationFee::latest()->first();
        $this->category = Category::where('conference_id', $this->conference->id)->first() ?? Category::first();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');

        $response = $this->get('/my/registration');
        $response->assertRedirect('/login');
    }

    public function test_unregistered_participant_dashboard_loads(): void
    {
        $user = User::where('email', 'participant@icha.com')->first();

        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertStatus(200);
    }

    public function test_participant_can_view_registration_form(): void
    {
        $user = User::where('email', 'participant@icha.com')->first();

        $response = $this->actingAs($user)->get('/my/registration');
        $response->assertStatus(200);
    }

    public function test_participant_can_access_submission_portal_without_prior_registration_or_payment(): void
    {
        $user = User::where('email', 'participant@icha.com')->first();

        // Ensure user has no registration
        Registration::where('user_id', $user->id)->delete();

        $response = $this->actingAs($user)->get('/my/submission');
        $response->assertStatus(200);
    }

    public function test_participant_can_submit_abstract_without_prior_payment(): void
    {
        Storage::fake('public');
        $user = User::where('email', 'participant@icha.com')->first();

        // Clean up previous abstracts
        \App\Models\AbstractSubmission::where('user_id', $user->id)->forceDelete();

        $file = UploadedFile::fake()->create('test_abstract.docx', 500, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

        $payload = [
            'title'       => 'Healthcare Quality and AI Leadership in Modern Hospitals',
            'category_id' => $this->category->id,
            'keywords'    => 'Quality, AI, Leadership, Hospital Administration',
            'abstract'    => 'This study explores the impact of AI-driven workflow optimization in tertiary hospital administrations across Southeast Asia, highlighting key performance indicators and patient satisfaction metrics.',
            'authors'     => [
                [
                    'name'         => $user->name,
                    'email'        => $user->email,
                    'affiliation'  => 'Universitas Muhammadiyah Surabaya',
                    'country'      => 'Indonesia',
                    'is_presenter' => true,
                ]
            ],
            'file'        => $file,
        ];

        $response = $this->actingAs($user)->post('/my/submission/abstract', $payload);
        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseHas('abstracts', [
            'user_id' => $user->id,
            'title'   => 'Healthcare Quality and AI Leadership in Modern Hospitals',
            'status'  => 'under_review',
        ]);
    }

    public function test_non_presenter_can_register_and_create_invoice(): void
    {
        $user = User::where('email', 'participant@icha.com')->first();

        // Clean up previous registration for clean idempotent test
        Registration::where('user_id', $user->id)->forceDelete();

        $payload = [
            'registration_fee_id' => $this->nonPresenterFee->id,
            'notes'               => 'Non presenter test registration',
        ];

        $response = $this->actingAs($user)->post('/my/registration', $payload);
        $response->assertRedirect('/my/registration');

        $this->assertDatabaseHas('registrations', [
            'user_id'             => $user->id,
            'conference_id'       => $this->conference->id,
            'registration_fee_id' => $this->nonPresenterFee->id,
            'status'              => 'pending',
        ]);
    }

    public function test_participant_can_upload_payment_proof(): void
    {
        Storage::fake('public');
        $user = User::where('email', 'participant@icha.com')->first();
        $registration = Registration::where('user_id', $user->id)->first();

        $file = UploadedFile::fake()->image('payment_receipt.jpg');

        $payload = [
            'registration_id' => $registration->id,
            'payment_method'  => 'Bank Transfer (Bank JATIM)',
            'proof_file'      => $file,
        ];

        $response = $this->actingAs($user)->post('/my/payment', $payload);
        $response->assertRedirect('/my/registration');

        $this->assertDatabaseHas('payments', [
            'registration_id' => $registration->id,
            'status'          => 'pending',
        ]);
    }

    public function test_presenter_fee_registration_requires_accepted_abstract(): void
    {
        $user = User::where('email', 'participant@icha.com')->first();
        Registration::where('user_id', $user->id)->forceDelete();

        // Ensure user has abstract in review (not accepted)
        \App\Models\AbstractSubmission::where('user_id', $user->id)->update(['status' => 'under_review']);

        $payload = [
            'registration_fee_id' => $this->presenterFee->id,
            'notes'               => 'Premature presenter registration',
        ];

        $response = $this->actingAs($user)->post('/my/registration', $payload);
        $response->assertStatus(422);

        // Now simulate abstract accepted by committee
        \App\Models\AbstractSubmission::where('user_id', $user->id)->update(['status' => 'accepted']);

        $response = $this->actingAs($user)->post('/my/registration', $payload);
        $response->assertRedirect('/my/registration');

        $this->assertDatabaseHas('registrations', [
            'user_id'             => $user->id,
            'registration_fee_id' => $this->presenterFee->id,
            'status'              => 'pending',
        ]);
    }

    public function test_paid_non_presenter_cannot_access_submission(): void
    {
        $user = User::where('email', 'participant@icha.com')->first();
        $registration = Registration::where('user_id', $user->id)->first();
        $registration->update([
            'registration_fee_id' => $this->nonPresenterFee->id,
            'status'              => 'paid',
        ]);
        Payment::where('registration_id', $registration->id)->update(['status' => 'verified']);

        $response = $this->actingAs($user)->get('/my/submission');
        $response->assertRedirect('/dashboard');
    }

    public function test_participant_can_view_and_update_profile(): void
    {
        $user = User::where('email', 'participant@icha.com')->first();

        $response = $this->actingAs($user)->get('/my/profile');
        $response->assertStatus(200);

        $payload = [
            'name'                 => 'Test Participant Updated',
            'phone'                => '081234567890',
            'institution'          => 'UM Surabaya Hospital',
            'country'              => 'Indonesia',
            'city'                 => 'Surabaya',
            'participant_category' => 'non_student',
            'gender'               => 'male',
        ];

        $response = $this->actingAs($user)->put('/my/profile', $payload);
        $response->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id'   => $user->id,
            'name' => 'Test Participant Updated',
        ]);

        $this->assertDatabaseHas('profiles', [
            'user_id'     => $user->id,
            'phone'       => '081234567890',
            'institution' => 'UM Surabaya Hospital',
        ]);

        // Revert name for consistency
        $user->update(['name' => 'Test Participant']);
    }

    public function test_participant_can_view_certificate_page(): void
    {
        $user = User::where('email', 'participant@icha.com')->first();

        $response = $this->actingAs($user)->get('/my/certificate');
        $response->assertStatus(200);
    }
}

<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Frontend\Models\ContactInquiry;
use Tests\TestCase;

class ContactInquiryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A verified admin user that can reach the admin panel.
     */
    private function admin(): User
    {
        return User::factory()->create(['email_verified_at' => now()]);
    }

    public function test_guest_is_redirected_away_from_the_admin_page(): void
    {
        // Guests are pushed away by the auth + verified middleware. The target
        // matches the rest of this app (see /faqs, /packages) which redirect to '/'.
        $this->get('/contact-inquiries')->assertStatus(302);
    }

    public function test_index_page_is_displayed(): void
    {
        $this->actingAs($this->admin())
            ->get('/contact-inquiries')
            ->assertOk()
            ->assertSee('Contact Inquiries')
            ->assertSee('inquiryTable')
            ->assertSee('contact-inquiries.css');
    }

    public function test_datatable_returns_json(): void
    {
        ContactInquiry::create(['name' => 'Karim', 'phone' => '01712345678']);

        $response = $this->actingAs($this->admin())
            ->getJson('/dataTable/contact-inquiries?draw=1&start=0&length=10');

        $response->assertOk()
            ->assertJsonPath('recordsTotal', 1)
            ->assertJsonStructure(['data' => [['id', 'name', 'phone', 'status', 'created_at', 'action']]]);

        // Name / phone / status are rendered as HTML, and the row exposes the
        // view + edit + delete actions used by the page.
        $row = $response->json('data.0');

        $this->assertStringContainsString('tel:01712345678', $row['phone']);
        $this->assertStringContainsString('New', $row['status']);
        $this->assertStringContainsString('inquiryView', $row['action']);
        $this->assertStringContainsString('inquiryEdit', $row['action']);
        $this->assertStringContainsString('inquiryDelete', $row['action']);
    }

    public function test_datatable_filters_by_status(): void
    {
        ContactInquiry::create(['name' => 'A', 'phone' => '01712345678']);
        $resolved = ContactInquiry::create(['name' => 'B', 'phone' => '01712345679', 'status' => 'resolved']);

        // Name is rendered as HTML by the service, so compare on id instead.
        $this->actingAs($this->admin())
            ->getJson('/dataTable/contact-inquiries?draw=1&start=0&length=10&status=resolved')
            ->assertOk()
            ->assertJsonPath('recordsTotal', 1)
            ->assertJsonPath('data.0.id', $resolved->id)
            ->assertJsonPath('data.0.status', fn ($value) => str_contains($value, 'Resolved'));
    }

    public function test_inquiry_can_be_stored_from_the_admin_panel(): void
    {
        $response = $this->actingAs($this->admin())->postJson('/contact-inquiries', [
            'name' => 'Walk In Guest',
            'phone' => '+880 1712-345678',
            'email' => 'guest@example.com',
            'message' => 'Asked about Umrah',
        ]);

        $response->assertOk()->assertJsonPath('status', 'success');

        $this->assertDatabaseHas('contact_inquiries', [
            'name' => 'Walk In Guest',
            'status' => 'new',
        ]);
    }

    public function test_store_validation_rejects_bad_payload(): void
    {
        $this->actingAs($this->admin())
            ->postJson('/contact-inquiries', ['name' => '', 'phone' => 'abc!!'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'phone']);
    }

    public function test_inquiry_can_be_shown(): void
    {
        $inquiry = ContactInquiry::create(['name' => 'Karim', 'phone' => '01712345678']);

        $this->actingAs($this->admin())
            ->getJson('/contact-inquiries/'.$inquiry->id)
            ->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('inquiry.name', 'Karim');
    }

    public function test_inquiry_status_can_be_updated_and_tracks_the_user(): void
    {
        $admin = $this->admin();
        $inquiry = ContactInquiry::create(['name' => 'Karim', 'phone' => '01712345678']);

        $this->actingAs($admin)
            ->putJson('/contact-inquiries/'.$inquiry->id, [
                'name' => 'Karim',
                'phone' => '01712345678',
                'status' => 'contacted',
            ])
            ->assertOk()
            ->assertJsonPath('inquiry.status', 'contacted');

        $this->assertDatabaseHas('contact_inquiries', [
            'id' => $inquiry->id,
            'status' => 'contacted',
            'updated_by' => $admin->id,
        ]);
    }

    public function test_update_validation_rejects_unknown_status(): void
    {
        $inquiry = ContactInquiry::create(['name' => 'Karim', 'phone' => '01712345678']);

        $this->actingAs($this->admin())
            ->putJson('/contact-inquiries/'.$inquiry->id, [
                'name' => 'Karim',
                'phone' => '01712345678',
                'status' => 'pending',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    public function test_inquiry_can_be_deleted(): void
    {
        $inquiry = ContactInquiry::create(['name' => 'Karim', 'phone' => '01712345678']);

        $this->actingAs($this->admin())
            ->deleteJson('/contact-inquiries/'.$inquiry->id)
            ->assertOk()
            ->assertJsonPath('status', 'success');

        $this->assertDatabaseMissing('contact_inquiries', ['id' => $inquiry->id]);
    }

    public function test_public_api_accepts_a_contact_form_submission(): void
    {
        $this->postJson('/api/contact-inquiries', [
            'name' => 'Web Visitor',
            'phone' => '+8801911223344',
            'message' => 'Frontend form submission',
        ])
            ->assertStatus(201)
            ->assertJsonPath('status', 'success');

        $this->assertDatabaseHas('contact_inquiries', ['name' => 'Web Visitor', 'status' => 'new']);
    }

    public function test_public_api_validation_returns_422(): void
    {
        $this->postJson('/api/contact-inquiries', ['name' => '', 'phone' => ''])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'phone']);
    }
}

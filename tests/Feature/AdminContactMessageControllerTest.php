<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminContactMessageControllerTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    private function makeMessage(array $override = []): ContactMessage
    {
        $data = array_merge([
            'first_name' => 'Layla',
            'last_name' => 'Khatib',
            'email' => 'layla@example.com',
            'phone' => '0791111111',
            'subject' => 'general',
            'subject_label' => ContactMessage::SUBJECT_LABELS['general'],
            'message' => 'رسالة اختبارية للتأكد من ظهورها في لوحة التحكم.',
            'status' => ContactMessage::STATUS_PENDING,
            'meta' => [
                'source' => 'contact-page',
            ],
        ], $override);

        return ContactMessage::create($data);
    }

    public function test_admin_can_view_contact_messages_list(): void
    {
        $admin = $this->createAdmin();
        $message = $this->makeMessage();

        $response = $this->actingAs($admin)->get(route('admin.contact-messages.index', ['message' => $message->id]));

        $response->assertOk();
        $response->assertSee($message->full_name);
        $response->assertSee($message->subject_label);
    }

    public function test_admin_can_update_contact_message_status(): void
    {
        $admin = $this->createAdmin();
        $message = $this->makeMessage();

        $response = $this->actingAs($admin)->patch(
            route('admin.contact-messages.update-status', $message),
            ['status' => ContactMessage::STATUS_REVIEWED]
        );

        $response->assertRedirect();
        $this->assertSame(ContactMessage::STATUS_REVIEWED, $message->fresh()->status);
    }
}

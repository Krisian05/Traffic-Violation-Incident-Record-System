<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupportChatTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_access_chat_support()
    {
        $response = $this->postJson(route('chat-support.query'), [
            'message' => 'How to setup SMS?'
        ]);

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_fetch_faqs()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson(route('chat-support.faqs'));

        $response->assertStatus(200)
                 ->assertJsonStructure(['success', 'faqs']);
    }

    public function test_quick_faq_query_returns_instant_answer_without_api()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('chat-support.query'), [
            'message' => 'SMS Gateway setup',
            'faq_id'  => 'faq_sms_setup'
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'source'  => 'quick_faq',
                 ]);
    }
}

<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SupportChatTest extends TestCase
{
    use DatabaseTransactions;

    public function test_unauthenticated_user_cannot_access_chat_support()
    {
        $response = $this->postJson(route('chat-support.query'), [
            'message' => 'How to setup SMS?'
        ]);

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_fetch_faqs_and_role_persona()
    {
        $user = User::factory()->make([
            'name' => 'Balamban Cashier',
            'role' => 'cashier',
        ]);

        $response = $this->actingAs($user)->getJson(route('chat-support.faqs'));

        $response->assertStatus(200)
                 ->assertJsonStructure(['success', 'persona', 'faqs'])
                 ->assertJson([
                     'persona' => [
                         'role_key'       => 'cashier',
                         'assistant_name' => 'TVIRS Cashier Assistant',
                         'badge'          => 'Official Cashier Guide',
                     ]
                 ]);

        // First FAQ for cashier should be Cashier Settlement
        $faqs = $response->json('faqs');
        $this->assertEquals('faq_settle_ticket', $faqs[0]['id']);
    }

    public function test_quick_faq_query_returns_instant_answer_without_api()
    {
        $user = User::factory()->make();

        $response = $this->actingAs($user)->postJson(route('chat-support.query'), [
            'message' => 'SMS Gateway setup',
            'faq_id'  => 'faq_sms_setup',
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'source'  => 'quick_faq',
                 ]);
    }
}

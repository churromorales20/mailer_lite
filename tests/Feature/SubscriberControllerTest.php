<?php

namespace Tests\Feature;

use App\Models\Subscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SubscriberControllerTest extends TestCase
{
    use WithFaker;

    protected $subscriber_id;
    protected $subscriber_email;
    private $initial_response;

    public function testSubscriberCanBeSaved()
    {
        $subscriber_email = $this->faker->unique()->safeEmail();

        // Create a new subscriber and set the subscriber ID
        $subscriber_data = [
            'email' => $subscriber_email,
            'name' => $this->faker->name(),
            'country' => $this->faker->country()
        ];

        $response = $this->postJson('/subscribers/new', $subscriber_data);
        $res = json_decode($response->getContent());
        $response->assertStatus(200, 'Response status should be 200');
        $this->assertStringContainsString('"status":"success"', $response->getContent());
    }

    public function testSubscriberDuplicated()
    {
        $subscriber_data = [
            'email' => 'cecilio.dev@gmail.com',
            'name' => $this->faker->name(),
            'country' => $this->faker->country()
        ];
        $response = $this->postJson('/subscribers/new', $subscriber_data);
        $response->assertStatus(200, 'Response status should be 200');
        $this->assertStringContainsString('"status":"error","code":400', $response->getContent());
    }

    public function testSubscriberInvalid()
    {
        $subscriber_data = [
            'email' => 'cecilio.devsaail.com',
            'name' => $this->faker->name(),
            'country' => $this->faker->country()
        ];
        
        $response = $this->postJson('/subscribers/new', $subscriber_data);
        $response->assertStatus(200, 'Response status should be 200');
        $this->assertStringContainsString('"status":"error","code":422', $response->getContent());
    }

    public function testSubscriberUpdate() //--filter testSubscriberUpdateDelete:84189732411541036
    {

        // Create a new subscriber and set the subscriber ID
        $subscriber_data = [
            'email' => $this->faker->unique()->safeEmail(),
            'name' => $this->faker->name(),
            'country' => $this->faker->country()
        ];

        $response = $this->postJson('/subscribers/new', $subscriber_data);
        $res = json_decode($response->getContent());
        $response->assertStatus(200, 'Response status should be 200');
        $this->assertStringContainsString('"status":"success"', $response->getContent());
        $subscriber_data = [
            'id' => $res->subscriber_id,
            'name' => $this->faker->name(),
            'country' => $this->faker->country()
        ];
        $response = $this->postJson('/subscribers/update', $subscriber_data);
        $response->assertStatus(200, 'Response status should be 200');
        $this->assertStringContainsString('"status":"success"', $response->getContent());
    }

    public function testSubscriberDelete() //--filter testSubscriberUpdateDelete:84189732411541036
    {
        $subscriber_data = [
            'email' => $this->faker->unique()->safeEmail(),
            'name' => $this->faker->name(),
            'country' => $this->faker->country()
        ];

        $response = $this->postJson('/subscribers/new', $subscriber_data);
        $res = json_decode($response->getContent());
        $response->assertStatus(200, 'Response status should be 200');
        $this->assertStringContainsString('"status":"success"', $response->getContent());

        $subscriber_data = [
            'id' => $res->subscriber_id,
        ];
        $response = $this->postJson('/subscribers/delete', $subscriber_data);
        $response->assertStatus(200, 'Response status should be 200');
        $this->assertStringContainsString('"status":"success"', $response->getContent());
    }

    public function testSubscriberUpdate404() //--filter testSubscriberUpdateDelete:84189732411541036
    {
        $subscriber_data = [
            'id' => 9399993999,
            'name' => $this->faker->name(),
            'country' => $this->faker->country()
        ];
        $response = $this->postJson('/subscribers/update', $subscriber_data);
        $response->assertStatus(200, 'Response status should be 200');
        $this->assertStringContainsString('"status":"error","code":404', $response->getContent());
    }

    public function testSubscriberDelete404() //--filter testSubscriberUpdateDelete:84189732411541036
    {
        $subscriber_data = [
            'id' => 3994884899,
        ];
        $response = $this->postJson('/subscribers/delete', $subscriber_data);
        $response->assertStatus(200, 'Response status should be 200');
        $this->assertStringContainsString('"status":"error","code":404', $response->getContent());
    }
}
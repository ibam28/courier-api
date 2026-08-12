<?php

namespace Tests\Feature;

use App\Models\Courier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourierApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_couriers(): void
    {
        Courier::factory()->count(3)->create();

        $response = $this->actingAsUser()->getJson('/api/couriers');

        $response
            ->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_can_search_couriers_by_name(): void
    {
        Courier::factory()->create(['name' => 'Bambang Saputra']);
        Courier::factory()->create(['name' => 'Andi Wijaya']);

        $response = $this->actingAsUser()->getJson('/api/couriers?search=Bambang');

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Bambang Saputra');
    }

    public function test_can_filter_couriers_by_level(): void
    {
        Courier::factory()->create(['level' => 2]);
        Courier::factory()->create(['level' => 3]);
        Courier::factory()->create(['level' => 5]);

        $response = $this->actingAsUser()->getJson('/api/couriers?level=2,3');

        $response
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_can_create_courier(): void
    {
        $payload = [
            'code' => 'KRR001',
            'name' => 'Bambang Saputra',
            'phone' => '08123456789',
            'email' => 'bambang@example.com',
            'level' => 3,
            'status' => 'active',
        ];

        $response = $this->actingAsUser()->postJson('/api/couriers', $payload);

        $response
            ->assertCreated()
            ->assertJsonPath('data.code', 'KRR001')
            ->assertJsonPath('data.name', 'Bambang Saputra');

        $this->assertDatabaseHas('couriers', [
            'code' => 'KRR001',
            'name' => 'Bambang Saputra',
        ]);
    }

    public function test_courier_name_and_level_are_required(): void
    {
        $response = $this->actingAsUser()->postJson('/api/couriers', []);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'level']);
    }

    public function test_courier_code_must_be_unique(): void
    {
        Courier::factory()->create([
            'code' => 'KRR001',
        ]);

        $response = $this->actingAsUser()->postJson('/api/couriers', [
            'code' => 'KRR001',
            'name' => 'Another Courier',
            'level' => 2,
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['code']);
    }

    public function test_can_show_courier(): void
    {
        $courier = Courier::factory()->create();

        $response = $this->actingAsUser()->getJson("/api/couriers/{$courier->id}");

        $response
            ->assertOk()
            ->assertJsonPath('data.id', $courier->id);
    }

    public function test_returns_404_for_unknown_courier(): void
    {
        $response = $this->actingAsUser()->getJson('/api/couriers/999999');

        $response->assertNotFound();
    }

    public function test_can_update_courier(): void
    {
        $courier = Courier::factory()->create([
            'name' => 'Old Name',
            'level' => 2,
        ]);

        $response = $this->actingAsUser()->putJson("/api/couriers/{$courier->id}", [
            'name' => 'New Name',
            'level' => 4,
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('data.name', 'New Name')
            ->assertJsonPath('data.level', 4);

        $this->assertDatabaseHas('couriers', [
            'id' => $courier->id,
            'name' => 'New Name',
            'level' => 4,
        ]);
    }

    public function test_can_delete_courier(): void
    {
        $courier = Courier::factory()->create();

        $response = $this->actingAsUser()->deleteJson("/api/couriers/{$courier->id}");

        $response
            ->assertOk()
            ->assertJsonPath('message', 'Courier deleted');

        $this->assertDatabaseMissing('couriers', [
            'id' => $courier->id,
        ]);
    }

    public function test_unauthenticated_request_returns_401(): void
    {
        Courier::factory()->count(2)->create();

        $response = $this->getJson('/api/couriers');

        $response->assertUnauthorized();
    }

    public function test_unauthenticated_cannot_create_courier(): void
    {
        $response = $this->postJson('/api/couriers', [
            'name' => 'Bambang Saputra',
            'level' => 3,
        ]);

        $response->assertUnauthorized();

        $this->assertDatabaseCount('couriers', 0);
    }

    public function test_unauthenticated_cannot_update_courier(): void
    {
        $courier = Courier::factory()->create();

        $response = $this->putJson("/api/couriers/{$courier->id}", [
            'name' => 'Hacked Name',
            'level' => 5,
        ]);

        $response->assertUnauthorized();

        $this->assertDatabaseHas('couriers', [
            'id' => $courier->id,
            'name' => $courier->name,
        ]);
    }

    public function test_unauthenticated_cannot_delete_courier(): void
    {
        $courier = Courier::factory()->create();

        $response = $this->deleteJson("/api/couriers/{$courier->id}");

        $response->assertUnauthorized();

        $this->assertDatabaseHas('couriers', [
            'id' => $courier->id,
        ]);
    }
}

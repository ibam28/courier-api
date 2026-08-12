<?php

namespace Tests\Feature;

use App\Models\Courier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourierApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_couriers_with_pagination(): void
    {
        Courier::create(['code' => 'KRR001', 'name' => 'Andi Saputra', 'level' => 2]);
        Courier::create(['code' => 'KRR002', 'name' => 'Budi Santoso', 'level' => 3]);

        $response = $this->getJson('/api/couriers?per_page=1');

        $response->assertOk()
            ->assertJsonPath('per_page', 1)
            ->assertJsonCount(1, 'data');
    }

    public function test_it_searches_and_filters_couriers(): void
    {
        Courier::create(['code' => 'KRR001', 'name' => 'Andi Saputra', 'level' => 2]);
        Courier::create(['code' => 'KRR002', 'name' => 'Budi Santoso', 'level' => 3]);

        $response = $this->getJson('/api/couriers?search=Andi&level=2');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Andi Saputra');
    }

    public function test_it_sorts_couriers(): void
    {
        Courier::create(['code' => 'KRR001', 'name' => 'Andi Saputra', 'level' => 2]);
        Courier::create(['code' => 'KRR002', 'name' => 'Budi Santoso', 'level' => 3]);

        $response = $this->getJson('/api/couriers?sort=name&order=desc');

        $response->assertOk()
            ->assertJsonPath('data.0.name', 'Budi Santoso');
    }

    public function test_it_creates_a_courier(): void
    {
        $response = $this->postJson('/api/couriers', [
            'code' => 'KRR001',
            'name' => 'Andi Saputra',
            'phone' => '08123456789',
            'email' => 'andi@example.com',
            'level' => 2,
            'status' => 'active',
        ]);

        $response->assertCreated()
            ->assertJsonPath('message', 'Courier created')
            ->assertJsonPath('data.code', 'KRR001')
            ->assertJsonPath('data.name', 'Andi Saputra');

        $this->assertDatabaseHas('couriers', [
            'code' => 'KRR001',
            'name' => 'Andi Saputra',
        ]);
    }

    public function test_it_validates_required_and_constrained_fields(): void
    {
        $response = $this->postJson('/api/couriers', [
            'name' => '',
            'email' => 'not-an-email',
            'level' => 9,
            'status' => 'unknown',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'level', 'status']);
    }

    public function test_it_rejects_duplicate_codes(): void
    {
        Courier::create(['code' => 'KRR001', 'name' => 'Existing Courier', 'level' => 1]);

        $response = $this->postJson('/api/couriers', [
            'code' => 'KRR001',
            'name' => 'Another Courier',
            'level' => 2,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['code']);
    }

    public function test_it_shows_updates_and_deletes_a_courier(): void
    {
        $courier = Courier::create([
            'code' => 'KRR001',
            'name' => 'Andi Saputra',
            'level' => 2,
        ]);

        $this->getJson("/api/couriers/{$courier->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $courier->id)
            ->assertJsonPath('data.name', 'Andi Saputra');

        $this->putJson("/api/couriers/{$courier->id}", [
            'code' => 'KRR001',
            'name' => 'Andi Updated',
            'level' => 4,
        ])
            ->assertOk()
            ->assertJsonPath('data.name', 'Andi Updated')
            ->assertJsonPath('data.level', 4);

        $this->deleteJson("/api/couriers/{$courier->id}")
            ->assertOk()
            ->assertJsonPath('message', 'Courier deleted')
            ->assertJsonPath('still_in_db', 'no');

        $this->assertDatabaseMissing('couriers', ['id' => $courier->id]);
    }

    public function test_it_returns_not_found_for_a_missing_courier(): void
    {
        $this->getJson('/api/couriers/999999')
            ->assertNotFound();
    }
}

<?php

namespace Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Tests\TestCase;

class ExpenseTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['role' => 'owner']);
    }

    public function test_expense_list_page_loads()
    {
        $response = $this->actingAs($this->user)->get('/expenses');

        $response->assertStatus(200);
    }

    public function test_create_expense()
    {
        $response = $this->actingAs($this->user)->post('/expenses', [
            'description' => 'Beli sabun mandi',
            'amount' => 50000,
            'payment_method' => 'cash',
            'expense_date' => Carbon::today()->format('Y-m-d'),
        ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('expenses', [
            'description' => 'Beli sabun mandi',
            'amount' => 50000,
        ]);
    }
}

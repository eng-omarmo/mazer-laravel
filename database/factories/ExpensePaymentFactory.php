<?php

namespace Database\Factories;

use App\Models\Expense;
use App\Models\ExpensePayment;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExpensePaymentFactory extends Factory
{
    protected $model = ExpensePayment::class;

    public function definition(): array
    {
        return [
            'expense_id' => Expense::factory(),
            'amount' => $this->faker->randomFloat(2, 5, 500),
            'paid_at' => now(),
            'paid_by' => null,
            'note' => 'Test payment',
            'status' => 'pending',
        ];
    }
}

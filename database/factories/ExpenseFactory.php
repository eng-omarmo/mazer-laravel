<?php

namespace Database\Factories;

use App\Models\Expense;
use App\Models\Organization;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExpenseFactory extends Factory
{
    protected $model = Expense::class;

    public function definition(): array
    {
        return [
            'supplier_id' => Supplier::factory(),
            'organization_id' => Organization::factory(),
            'type' => $this->faker->word(),
            'amount' => $this->faker->randomFloat(2, 10, 1000),
            'document_path' => null,
            'status' => 'approved',
            'payment_status' => 'pending',
        ];
    }
}

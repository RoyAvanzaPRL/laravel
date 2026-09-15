<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\Loan;
use App\Models\Member;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Loan>
 */
class LoanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $loanedAt = fake()->dateTimeBetween('-6 months', '-1 day');
        return [
            'member_id' => Member::factory(),
            'book_id' => Book::factory(),
            'loaned_at' => $loanedAt,
            'due_at' => fake()->dateTimeBetween($loanedAt, '+1 month'),
            'returned_at' => null,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'returned_at' => null,
        ]);
    }

    public function returned(): static
    {
        return $this->state(function (array $attributes) {
            $loanedAt = $attributes['loaned_at'];
            
            return [
                'returned_at' => fake()->dateTimeBetween($loanedAt, 'now'),
            ];
        });
    }
}

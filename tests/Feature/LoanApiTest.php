<?php

namespace Tests\Feature;

use App\Models\Loan;
use App\Models\Member;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoanApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_loans(): void
    {
        Loan::factory()->count(2)->active()->create();
        Loan::factory()->returned()->create();

        $this->getJson('/api/loans')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'member_id',
                        'book_id',
                        'loaned_at',
                        'due_at',
                        'returned_at',
                        'member',
                        'book',
                    ],
                ],
            ]);
    }

    public function test_can_filter_active_loans(): void
    {
        $active = Loan::factory()->active()->create();
        Loan::factory()->returned()->create();

        $this->getJson('/api/loans?active=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $active->id)
            ->assertJsonPath('data.0.returned_at', null);
    }

    public function test_can_filter_loans_by_member(): void
    {
        $member = Member::factory()->create();
        $loan = Loan::factory()->for($member)->active()->create();
        Loan::factory()->active()->create();

        $this->getJson("/api/loans?member_id={$member->id}")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $loan->id)
            ->assertJsonPath('data.0.member_id', $member->id);
    }

    public function test_can_return_a_loan(): void
    {
        $loan = Loan::factory()->active()->create();

        $this->postJson("/api/loans/{$loan->id}/return")
            ->assertOk()
            ->assertJsonPath('data.id', $loan->id);

        $this->assertNotNull($loan->fresh()->returned_at);
    }

    public function test_cannot_return_an_already_returned_loan(): void
    {
        $loan = Loan::factory()->returned()->create();

        $this->postJson("/api/loans/{$loan->id}/return")
            ->assertUnprocessable()
            ->assertJsonPath('message', 'This loan is already returned.');

        $this->assertNotNull($loan->fresh()->returned_at);
    }
}
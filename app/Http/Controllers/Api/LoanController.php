<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Loan;
use App\Http\Resources\LoanResource;
use App\Http\Requests\Api\IndexLoanRequest;

class LoanController extends Controller
{
    public function index(IndexLoanRequest $request)
    {

        $sort = $request->validated('sort', 'loaned_at');
        $direction = $request->validated('direction', 'desc');

        $loans = Loan::query()
            ->with(['member', 'book'])
            ->when($request->boolean('active'), fn ($q) => $q->whereNull('returned_at'))
            ->when(
                $request->filled('member_id'),
                fn ($q) => $q->where('member_id', $request->integer('member_id'))
            )
            ->orderBy($sort, $direction)
            ->paginate($request->integer('per_page', 15));
        return LoanResource::collection($loans);

    }

    public function returnLoan(Loan $loan)
    {
        if ($loan->returned_at !== null) {
            return response()->json([
                'message' => 'This loan is already returned.',
            ], 422);
        }

        $loan->update([
            'returned_at' => now(),
        ]);

        $loan->load(['member', 'book']);

        return new LoanResource($loan);
    }
}
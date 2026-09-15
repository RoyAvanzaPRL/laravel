<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LoanResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'member_id' => $this->member_id,
            'book_id' => $this->book_id,
            'loaned_at' => $this->loaned_at,
            'due_at' => $this->due_at,
            'returned_at' => $this->returned_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'member' => new MemberResource($this->whenLoaded('member')),
            'book' => new BookResource($this->whenLoaded('book')),
        ];
    }
}

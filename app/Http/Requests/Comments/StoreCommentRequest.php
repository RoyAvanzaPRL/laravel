<?php

namespace App\Http\Requests\Comments;

use Illuminate\Foundation\Http\FormRequest;

class StoreCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('comment', $this->route('ticket'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'body' => ['required', 'string'],
            'attachment' => [
                'sometimes',
                'file',
                'max:10240', // 10 MB
                'mimes:pdf,jpg,jpeg,png,txt,doc,docx',
            ],
        ];
    }
}
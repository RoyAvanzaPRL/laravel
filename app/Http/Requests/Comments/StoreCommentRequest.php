<?php

namespace App\Http\Requests\Comments;

use Illuminate\Foundation\Http\FormRequest;

class StoreCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ticket = $this->route('ticket');
    
        if (! $this->user()->can('comment', $ticket)) {
            return false;
        }
    
        if ($this->hasFile('attachment')) {
            return $this->user()->can('attach', $ticket);
        }
    
        return true;
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
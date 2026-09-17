<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Comments\StoreCommentRequest;
use App\Http\Resources\CommentResource;
use App\Models\Attachment;
use App\Models\Comment;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;

class CommentController extends Controller
{
    public function store(StoreCommentRequest $request, Ticket $ticket): JsonResponse
    {
        try {
            $comment = Comment::query()->create([
                'ticket_id' => $ticket->id,
                'user_id' => $request->user()->id,
                'body' => $request->validated('body'),
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $path = $file->store('attachments/'.$ticket->id, 'local');

            Attachment::query()->create([
                'ticket_id' => $ticket->id,
                'comment_id' => $comment->id,
                'user_id' => $request->user()->id,
                'original_name' => $file->getClientOriginalName(),
                'path' => $path,
                'mime' => $file->getClientMimeType(),
                'size' => $file->getSize(),
            ]);
        }

        $comment->load(['user', 'attachments']);

        return (new CommentResource($comment))
            ->response()
            ->setStatusCode(201);
    }
}
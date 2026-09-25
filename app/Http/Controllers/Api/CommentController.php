<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Comments\StoreCommentRequest;
use App\Http\Resources\CommentResource;
use App\Models\Ticket;
use App\Services\Comments\CreateCommentService;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;

class CommentController extends Controller
{
    public function store(
        StoreCommentRequest $request,
        Ticket $ticket,
        CreateCommentService $createComment,
    ): JsonResponse {
        try {
            $comment = $createComment->handle(
                $ticket,
                $request->user(),
                $request->validated('body'),
                $request->file('attachment'),
            );
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return (new CommentResource($comment))
            ->response()
            ->setStatusCode(201);
    }
}

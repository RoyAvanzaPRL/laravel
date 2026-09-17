<?php

namespace App\Http\Controllers\Api;

use App\Enums\TicketStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tickets\AssignTicketRequest;
use App\Http\Requests\Tickets\StoreTicketRequest;
use App\Http\Requests\Tickets\TransitionTicketRequest;
use App\Http\Requests\Tickets\UpdateTicketRequest;
use App\Http\Resources\TicketResource;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use InvalidArgumentException;

class TicketController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Ticket::class);

        $tickets = Ticket::query()
            ->with(['creator', 'assignee'])
            ->latest('last_activity_at')
            ->paginate(15);

        return TicketResource::collection($tickets);
    }

    public function store(StoreTicketRequest $request): JsonResponse
    {
        $ticket = Ticket::query()->create([
            'creator_id' => $request->user()->id,
            'title' => $request->validated('title'),
            'body' => $request->validated('body'),
            'status' => TicketStatus::Open,
            'last_activity_at' => now(),
        ]);

        $ticket->load(['creator', 'assignee']);

        return (new TicketResource($ticket))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Ticket $ticket): TicketResource
    {
        $this->authorize('view', $ticket);
        $ticket->load([
            'creator',
            'assignee',
            'comments.user',
            'comments.attachments',
        ]);

        return new TicketResource($ticket);
    }

    public function update(UpdateTicketRequest $request, Ticket $ticket): TicketResource
    {
        $ticket->fill($request->validated());
        $ticket->last_activity_at = now();
        $ticket->save();

        $ticket->load(['creator', 'assignee']);

        return new TicketResource($ticket);
    }

    public function assign(AssignTicketRequest $request, Ticket $ticket): TicketResource|JsonResponse
    {
        $assignee = User::query()->findOrFail($request->validated('assignee_id'));

        try {
            $ticket->assignTo($assignee);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $ticket->load(['creator', 'assignee']);

        return new TicketResource($ticket);
    }

    public function transition(TransitionTicketRequest $request, Ticket $ticket): TicketResource|JsonResponse
    {
        $next = TicketStatus::from($request->validated('status'));

        try {
            $ticket->transitionTo($next);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $ticket->load(['creator', 'assignee']);

        return new TicketResource($ticket);
    }
}
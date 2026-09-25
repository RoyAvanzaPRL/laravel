<?php

namespace App\Http\Controllers\Api;

use App\Enums\TicketStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tickets\AssignTicketRequest;
use App\Http\Requests\Tickets\CloseTicketRequest;
use App\Http\Requests\Tickets\StoreTicketRequest;
use App\Http\Requests\Tickets\TransitionTicketRequest;
use App\Http\Requests\Tickets\UpdateTicketRequest;
use App\Http\Resources\TicketResource;
use App\Models\Ticket;
use App\Repositories\Tickets\TicketRepository;
use App\Repositories\Users\UserRepository;
use App\Services\Tickets\AssignTicketService;
use App\Services\Tickets\CloseTicketService;
use App\Services\Tickets\CreateTicketService;
use App\Services\Tickets\TransitionTicketService;
use App\Services\Tickets\UpdateTicketService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use InvalidArgumentException;

class TicketController extends Controller
{
    public function index(Request $request, TicketRepository $tickets): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Ticket::class);

        $paginated = $tickets->paginateVisibleTo($request->user());

        return TicketResource::collection($paginated);
    }

    public function store(StoreTicketRequest $request, CreateTicketService $createTicket): JsonResponse
    {
        $ticket = $createTicket->handle(
            $request->user(),
            $request->validated('title'),
            $request->validated('body'),
        );

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

    public function update(
        UpdateTicketRequest $request,
        Ticket $ticket,
        UpdateTicketService $updateTicket,
    ): TicketResource {
        $ticket = $updateTicket->handle($ticket, $request->validated());

        return new TicketResource($ticket);
    }

    public function assign(
        AssignTicketRequest $request,
        Ticket $ticket,
        AssignTicketService $assignTicket,
        UserRepository $users,
    ): TicketResource|JsonResponse {
        $assignee = $users->findByIdOrFail($request->validated('assignee_id'));

        try {
            $ticket = $assignTicket->handle($ticket, $assignee);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return new TicketResource($ticket);
    }

    public function transition(
        TransitionTicketRequest $request,
        Ticket $ticket,
        TransitionTicketService $transitionTicket,
        CloseTicketService $closeTicket,
    ): TicketResource|JsonResponse {
        $next = TicketStatus::from($request->validated('status'));

        try {
            $ticket = $next === TicketStatus::Closed
                ? $closeTicket->handle($ticket)
                : $transitionTicket->handle($ticket, $next);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return new TicketResource($ticket);
    }

    public function close(
        CloseTicketRequest $request,
        Ticket $ticket,
        CloseTicketService $closeTicket,
    ): TicketResource|JsonResponse {
        try {
            $ticket = $closeTicket->handle($ticket);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return new TicketResource($ticket);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TicketController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = $request->user()->tickets()->with(['order', 'messages.author']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $tickets = $query->orderBy('updated_at', 'desc')
            ->paginate($request->get('per_page', 20));

        return response()->json($tickets);
    }

    public function show(string $id): JsonResponse
    {
        $ticket = $request->user()->tickets()
            ->with(['order', 'messages.author'])
            ->findOrFail($id);

        return response()->json(['ticket' => $ticket]);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'subject' => 'required|string|max:255',
            'order_id' => 'nullable|uuid|exists:orders,id',
            'priority' => 'nullable|in:low,normal,high,urgent',
            'message' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $ticket = Ticket::create([
            'user_id' => $request->user()->id,
            'subject' => $request->subject,
            'order_id' => $request->order_id,
            'priority' => $request->priority ?? 'normal',
            'status' => Ticket::STATUS_OPEN,
        ]);

        $ticket->messages()->create([
            'author_id' => $request->user()->id,
            'is_admin' => false,
            'body' => $request->message,
        ]);

        return response()->json([
            'message' => 'Ticket created',
            'ticket' => $ticket->load('messages.author'),
        ], 201);
    }

    public function reply(string $id, Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $ticket = $request->user()->tickets()->findOrFail($id);

        if (in_array($ticket->status, [Ticket::STATUS_RESOLVED, Ticket::STATUS_CLOSED])) {
            return response()->json([
                'message' => 'Cannot reply to a closed ticket.',
            ], 400);
        }

        $ticket->messages()->create([
            'author_id' => $request->user()->id,
            'is_admin' => false,
            'body' => $request->message,
        ]);

        $ticket->update(['status' => Ticket::STATUS_PENDING]);

        return response()->json([
            'message' => 'Reply sent',
            'ticket' => $ticket->load('messages.author'),
        ]);
    }
}

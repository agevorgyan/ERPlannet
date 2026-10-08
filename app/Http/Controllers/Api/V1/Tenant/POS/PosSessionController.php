<?php

namespace App\Http\Controllers\Api\V1\Tenant\POS;

use App\Domain\POS\Actions\ClosePosSessionAction;
use App\Domain\POS\Actions\OpenPosSessionAction;
use App\Domain\POS\Actions\RecordPosCashMovementAction;
use App\Domain\POS\Models\PosSession;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PosSessionController extends Controller
{
    public function open(Request $request, OpenPosSessionAction $action): JsonResponse
    {
        $validated = $request->validate([
            'pos_terminal_id' => ['required', 'uuid', 'exists:pos_terminals,id'],
            'opening_cash' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $session = $action->execute(
            posTerminalId: $validated['pos_terminal_id'],
            cashierId: $request->user()->id,
            openingCash: (float) ($validated['opening_cash'] ?? 0.0),
            notes: $validated['notes'] ?? null
        );

        return response()->json([
            'success' => true,
            'message' => 'POS shift session opened successfully.',
            'data' => $session->load(['terminal', 'cashier']),
        ], 201);
    }

    public function current(Request $request): JsonResponse
    {
        $query = PosSession::with(['terminal.branch', 'cashier', 'cashMovements', 'paymentTransactions'])
            ->where('status', 'open');

        if ($request->filled('pos_terminal_id')) {
            $query->where('pos_terminal_id', $request->query('pos_terminal_id'));
        } else {
            $query->where('cashier_id', $request->user()->id);
        }

        $session = $query->latest('opened_at')->first();

        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => 'No active open session found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $session,
        ]);
    }

    public function cashMovement(Request $request, RecordPosCashMovementAction $action): JsonResponse
    {
        $validated = $request->validate([
            'pos_session_id' => ['required', 'uuid', 'exists:pos_sessions,id'],
            'type' => ['required', 'string', 'in:cash_in,cash_out'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'reason' => ['required', 'string', 'max:255'],
        ]);

        $movement = $action->execute(
            posSessionId: $validated['pos_session_id'],
            userId: $request->user()->id,
            type: $validated['type'],
            amount: (float) $validated['amount'],
            reason: $validated['reason']
        );

        return response()->json([
            'success' => true,
            'message' => 'Cash movement recorded successfully.',
            'data' => $movement,
        ], 201);
    }

    public function close(Request $request, ClosePosSessionAction $action): JsonResponse
    {
        $validated = $request->validate([
            'pos_session_id' => ['required', 'uuid', 'exists:pos_sessions,id'],
            'closing_cash_declared' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $session = $action->execute(
            posSessionId: $validated['pos_session_id'],
            closingCashDeclared: (float) $validated['closing_cash_declared'],
            notes: $validated['notes'] ?? null
        );

        return response()->json([
            'success' => true,
            'message' => 'POS shift session closed.',
            'data' => $session->load(['terminal', 'cashier']),
        ]);
    }
}

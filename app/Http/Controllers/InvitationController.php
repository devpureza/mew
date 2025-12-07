<?php

namespace App\Http\Controllers;

use App\Enums\GuestStatus;
use App\Models\Guest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InvitationController extends Controller
{
    public function showForm(): View
    {
        return view('confirm');
    }

    public function lookup(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'identifier' => ['required', 'string'],
        ]);

        $resolved = $this->resolveByIdentifier($validated['identifier']);

        if ($resolved['mode'] === 'cpf') {
            return response()->json(
                $resolved['head']->load(['children', 'wedding:id,title,event_date'])
            );
        }

        // codigo de convite: apenas o proprio convidado
        return response()->json(
            $resolved['head']->load(['wedding:id,title,event_date'])
        );
    }

    public function confirm(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'identifier' => ['required', 'string'],
            'updates' => ['required', 'array'],
            'updates.*.guest_id' => ['required', 'integer', 'exists:guests,id'],
            'updates.*.status' => ['required', 'string', 'in:accepted,rejected,pending'],
        ]);

        $resolved = $this->resolveByIdentifier($validated['identifier']);
        $allowedGuestIds = $resolved['allowed_ids'];

        foreach ($validated['updates'] as $update) {
            $guestId = (int) $update['guest_id'];
            if (in_array($guestId, $allowedGuestIds, true)) {
                Guest::where('id', $update['guest_id'])->update([
                    'status' => GuestStatus::from($update['status']),
                ]);
            }
        }

        return response()->json([
            'message' => 'Presenca registrada com sucesso.',
            'guest' => $resolved['head']->fresh()->load('children'),
        ]);
    }

    public function confirmWeb(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'identifier' => ['required', 'string'],
            'status' => ['required', 'array'],
        ]);

        $resolved = $this->resolveByIdentifier($validated['identifier']);
        $head = $resolved['head'];
        $allowedGuestIds = $resolved['allowed_ids'];
        $allowedStatuses = collect(GuestStatus::cases())->pluck('value')->all();

        foreach ($validated['status'] as $guestId => $status) {
            $guestId = (int) $guestId;

            if (! in_array($guestId, $allowedGuestIds, true)) {
                continue;
            }

            if (! in_array($status, $allowedStatuses, true)) {
                continue;
            }

            Guest::where('id', $guestId)->update(['status' => GuestStatus::from($status)]);
        }

        return redirect()
            ->back()
            ->with('status', "Presenca de {$head->name} e familia atualizada.");
    }

    /**
     * CPF (apenas digitos, 11 chars) -> retorna familia completa.
     * Codigo de convite -> retorna somente aquele convidado.
     *
     * @return array{mode: string, head: Guest, allowed_ids: array<int>}
     */
    private function resolveByIdentifier(string $identifier): array
    {
        $onlyDigits = preg_replace('/\D/', '', $identifier);
        $isCpf = strlen($onlyDigits) === 11;

        if ($isCpf) {
            $guest = Guest::query()
                ->where('cpf', $onlyDigits)
                ->with(['children', 'parentGuest', 'wedding'])
                ->first();

            abort_unless($guest, 404, 'Convidado nao encontrado.');

            $head = $guest->parentGuest ?: $guest;
            $allowedIds = collect([$head->id])->merge($head->children->pluck('id'))->all();

            return [
                'mode' => 'cpf',
                'head' => $head,
                'allowed_ids' => $allowedIds,
            ];
        }

        $guest = Guest::query()
            ->where('invitation_code', $identifier)
            ->with(['parentGuest', 'wedding'])
            ->first();

        abort_unless($guest, 404, 'Convidado nao encontrado.');

        return [
            'mode' => 'code',
            'head' => $guest,
            'allowed_ids' => [$guest->id],
        ];
    }
}

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

        $guest = $this->findGuestByIdentifier($validated['identifier']);

        $head = $guest->parentGuest ?: $guest;

        return response()->json(
            $head->load(['children', 'wedding:id,title,event_date'])
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

        $guest = $this->findGuestByIdentifier($validated['identifier'])->load('children', 'parentGuest');
        $head = $guest->parentGuest ?: $guest;

        $allowedGuestIds = collect([$head->id])->merge($head->children->pluck('id'))->all();

        foreach ($validated['updates'] as $update) {
            $guestId = (int) $update['guest_id'];
            if (in_array($guestId, $allowedGuestIds, true)) {
                Guest::where('id', $update['guest_id'])->update([
                    'status' => GuestStatus::from($update['status']),
                ]);
            }
        }

        return response()->json([
            'message' => 'Presença registrada com sucesso.',
            'guest' => $head->fresh()->load('children'),
        ]);
    }

    public function confirmWeb(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'identifier' => ['required', 'string'],
            'status' => ['required', 'array'],
        ]);

        $guest = $this->findGuestByIdentifier($validated['identifier'])->load('children', 'parentGuest');
        $head = $guest->parentGuest ?: $guest;

        $allowedGuestIds = collect([$head->id])->merge($head->children->pluck('id'))->all();
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
            ->with('status', "Presença de {$head->name} e família atualizada.");
    }

    private function findGuestByIdentifier(string $identifier): Guest
    {
        $guest = Guest::query()
            ->where('cpf', $identifier)
            ->orWhere('invitation_code', $identifier)
            ->with(['children', 'parentGuest'])
            ->first();

        abort_unless($guest, 404, 'Convidado não encontrado.');

        return $guest;
    }
}

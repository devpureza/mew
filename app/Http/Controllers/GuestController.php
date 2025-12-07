<?php

namespace App\Http\Controllers;

use App\Enums\GuestStatus;
use App\Models\Guest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GuestController extends Controller
{
    public function index(): JsonResponse
    {
        $user = auth()->user();
        $role = optional(optional($user)->role)->value ?? $user->role ?? null;
        $query = Guest::with(['wedding:id,title,event_date', 'parentGuest:id,name', 'children:id,name,parent_guest_id'])
            ->latest();

        if ($role === 'couple' && $user) {
            $allowedWeddingIds = $user->weddings()->pluck('wedding_id');
            $query->whereIn('wedding_id', $allowedWeddingIds);
        }

        $guests = $role === 'couple'
            ? $query->get()
            : $query->paginate(25);

        return response()->json($guests);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validatedPayload($request);
        $this->ensureCoupleCanTouchWedding($data['wedding_id']);

        if ($data['parent_guest_id'] ?? false) {
            $this->assertSameWedding($data['parent_guest_id'], $data['wedding_id']);
            $data['is_head_of_family'] = false;
        } else {
            $data['is_head_of_family'] = $data['is_head_of_family'] ?? true;
            $dependents = $request->input('dependents', []);
            $data['party_size'] = max(1, count($dependents) + 1);
        }

        $guest = Guest::create($data);

        if (! empty($dependents ?? [])) {
            foreach ($dependents as $dependent) {
                if (empty($dependent['name'])) {
                    continue;
                }

                Guest::create([
                    'wedding_id' => $guest->wedding_id,
                    'parent_guest_id' => $guest->id,
                    'invited_by_user_id' => $guest->invited_by_user_id,
                    'name' => $dependent['name'],
                    'status' => $dependent['status'] ?? GuestStatus::Pending,
                    'is_head_of_family' => false,
                ]);
            }
        }

        return response()->json($guest->load('children:id,name,parent_guest_id'), 201);
    }

    public function show(Guest $guest): JsonResponse
    {
        $this->ensureCoupleCanTouchWedding($guest->wedding_id);

        return response()->json(
            $guest->load(['wedding:id,title,event_date', 'parentGuest:id,name', 'children'])
        );
    }

    public function update(Request $request, Guest $guest): JsonResponse
    {
        $user = auth()->user();
        $role = optional(optional($user)->role)->value ?? $user->role ?? null;
        if ($role === 'couple') {
            abort(403, 'Couple não pode editar convidados.');
        }

        $data = $this->validatedPayload($request, $guest);

        if ($data['parent_guest_id'] ?? false) {
            $this->assertSameWedding($data['parent_guest_id'], $guest->wedding_id);
            $data['is_head_of_family'] = false;
        } elseif (isset($data['parent_guest_id']) && $data['parent_guest_id'] === null) {
            $data['is_head_of_family'] = true;
        }

        $guest->update($data);

        return response()->json($guest->fresh()->load('children'));
    }

    public function destroy(Guest $guest): JsonResponse
    {
        $user = auth()->user();
        $role = optional(optional($user)->role)->value ?? $user->role ?? null;
        if ($role === 'couple') {
            abort(403, 'Couple não pode apagar convidados.');
        }

        $guest->delete();

        return response()->json(status: 204);
    }

    private function validatedPayload(Request $request, ?Guest $guest = null): array
    {
        $statusValues = collect(GuestStatus::cases())->pluck('value')->toArray();

        return $request->validate([
            'wedding_id' => ['required', 'integer', Rule::exists('weddings', 'id')],
            'invited_by_user_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'parent_guest_id' => ['nullable', 'integer', Rule::exists('guests', 'id')],
            'name' => ['required', 'string', 'max:255'],
            'cpf' => ['nullable', 'string', 'size:11', Rule::unique('guests')->ignore($guest?->id)],
            'invitation_code' => ['nullable', 'string', 'max:255', Rule::unique('guests')->ignore($guest?->id)],
            'email' => ['nullable', 'email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'status' => ['nullable', Rule::in($statusValues)],
            'is_head_of_family' => ['sometimes', 'boolean'],
            'party_size' => ['nullable', 'integer', 'min:1'],
            'notes' => ['nullable', 'string'],
            'dependents' => ['nullable', 'array'],
            'dependents.*.name' => ['required_with:dependents', 'string', 'max:255'],
            'dependents.*.status' => ['nullable', Rule::in($statusValues)],
        ]);
    }

    private function assertSameWedding(int $parentGuestId, int $weddingId): void
    {
        $belongsToWedding = Guest::where('id', $parentGuestId)
            ->where('wedding_id', $weddingId)
            ->exists();

        abort_unless($belongsToWedding, 422, 'O convidado pai precisa estar no mesmo casamento.');
    }

    private function ensureCoupleCanTouchWedding(int $weddingId): void
    {
        $user = auth()->user();
        $role = optional(optional($user)->role)->value ?? $user->role ?? null;
        if ($role !== 'couple' || ! $user) {
            return;
        }

        $allowed = $user->weddings()->wherePivot('wedding_id', $weddingId)->exists();
        abort_unless($allowed, 403, 'Você só pode adicionar convidados ao seu casamento.');
    }
}

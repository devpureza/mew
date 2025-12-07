<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Wedding;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WeddingController extends Controller
{
    public function index(): JsonResponse
    {
        $user = auth()->user();
        $role = optional(optional($user)->role)->value ?? $user->role ?? null;
        $query = Wedding::with(['couples:id,name', 'guests:id,wedding_id,status'])
            ->latest('event_date');

        if ($role === 'couple' && $user) {
            $query->whereHas('couples', function ($q) {
                $q->where('user_id', auth()->id());
            });
        }

        $weddings = $role === 'couple'
            ? $query->get()
            : $query->paginate(15);

        return response()->json($weddings);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validatedPayload($request);

        $this->ensureCouplesAvailable($request->input('couple_ids', []));

        $wedding = Wedding::create($data);
        $this->syncCouples($wedding, $request->input('couple_ids', []));

        return response()->json($wedding->load('couples:id,name'), 201);
    }

    public function show(Wedding $wedding): JsonResponse
    {
        $user = auth()->user();
        $role = optional(optional($user)->role)->value ?? $user->role ?? null;
        if ($role === 'couple' && ! $wedding->couples()->where('user_id', auth()->id())->exists()) {
            abort(403);
        }

        return response()->json(
            $wedding->load([
                'couples:id,name,email',
                'guests' => function ($query) {
                    $query->latest()->take(50);
                },
            ])
        );
    }

    public function update(Request $request, Wedding $wedding): JsonResponse
    {
        $data = $this->validatedPayload($request, $wedding);
        $wedding->update($data);

        if ($request->has('couple_ids')) {
            $this->ensureCouplesAvailable($request->input('couple_ids', []), $wedding->id);
            $this->syncCouples($wedding, $request->input('couple_ids', []));
        }

        return response()->json($wedding->load('couples:id,name'));
    }

    public function destroy(Wedding $wedding): JsonResponse
    {
        $wedding->delete();

        return response()->json(status: 204);
    }

    private function validatedPayload(Request $request, ?Wedding $wedding = null): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'event_date' => ['required', 'date'],
            'location' => ['nullable', 'string', 'max:255'],
            'location_details' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'couple_ids' => ['sometimes', 'array'],
            'couple_ids.*' => [
                'integer',
                Rule::exists(User::class, 'id'),
            ],
        ]);
    }

    private function syncCouples(Wedding $wedding, array $coupleIds): void
    {
        $syncData = [];
        foreach ($coupleIds as $index => $id) {
            $syncData[$id] = [
                'role' => 'couple',
                'is_primary' => $index === 0,
            ];
        }

        if (! empty($syncData)) {
            $wedding->couples()->sync($syncData);
        }
    }

    private function ensureCouplesAvailable(array $coupleIds, ?int $currentWeddingId = null): void
    {
        if (empty($coupleIds)) {
            return;
        }

        $inUse = \DB::table('wedding_user')
            ->whereIn('user_id', $coupleIds)
            ->when($currentWeddingId, fn ($q) => $q->where('wedding_id', '!=', $currentWeddingId))
            ->pluck('user_id')
            ->all();

        if (! empty($inUse)) {
            abort(422, 'Um ou mais usuários já estão vinculados a outro casamento.');
        }
    }
}

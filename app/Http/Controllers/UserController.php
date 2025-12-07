<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(): JsonResponse
    {
        $users = User::query()
            ->with('weddings:id,title')
            ->latest()
            ->paginate(20);

        return response()->json($users);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validatedPayload($request);
        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);

        return response()->json($user->fresh(), 201);
    }

    public function show(User $user): JsonResponse
    {
        return response()->json(
            $user->load(['weddings:id,title', 'invitedGuests:id,name,wedding_id,status'])
        );
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $data = $this->validatedPayload($request, $user);

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);

        return response()->json($user->fresh());
    }

    public function destroy(User $user): JsonResponse
    {
        $user->delete();

        return response()->json(status: 204);
    }

    private function validatedPayload(Request $request, ?User $user = null): array
    {
        $roleValues = collect(UserRole::cases())->pluck('value')->toArray();
        $isSuperadmin = $this->isSuperadmin($request);
        $allowedRoles = $isSuperadmin ? $roleValues : [UserRole::Couple->value];

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user?->id),
            ],
            'cpf' => [
                'required',
                'string',
                'size:11',
                Rule::unique('users')->ignore($user?->id),
            ],
            'role' => ['required', Rule::in($allowedRoles)],
            'birth_date' => ['nullable', 'date'],
            'address_line' => ['nullable', 'string', 'max:255'],
            'address_line_two' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:150'],
            'state' => ['nullable', 'string', 'max:150'],
            'postal_code' => ['nullable', 'string', 'max:25'],
            'country' => ['nullable', 'string', 'max:2'],
            'photo_path' => ['nullable', 'string', 'max:255'],
            'password' => [$user ? 'sometimes' : 'required', 'string', 'min:8'],
        ]);

        if (! $isSuperadmin) {
            $data['role'] = UserRole::Couple->value;
        }

        return $data;
    }

    private function isSuperadmin(Request $request): bool
    {
        $role = $request->user()?->role;

        if ($role instanceof UserRole) {
            return $role === UserRole::SuperAdmin;
        }

        return $role === UserRole::SuperAdmin->value;
    }
}

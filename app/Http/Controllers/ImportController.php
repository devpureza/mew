<?php

namespace App\Http\Controllers;

use App\Enums\GodparentRole;
use App\Enums\GuestRelationship;
use App\Enums\GuestStatus;
use App\Models\Guest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ImportController extends Controller
{
    /**
     * Importa uma lista de convidados em lote.
     * Espera dados já parseados do frontend.
     */
    public function import(Request $request): JsonResponse
    {
        $user = auth()->user();
        if (!$user) {
            abort(401, 'Não autenticado.');
        }

        $data = $request->validate([
            'wedding_id' => ['required', 'integer', Rule::exists('weddings', 'id')],
            'guests' => ['required', 'array', 'min:1'],
            'guests.*.name' => ['required', 'string', 'max:255'],
            'guests.*.relationship' => ['nullable', 'string'],
            'guests.*.godparent_role' => ['nullable', 'string'],
            'guests.*.belongs_to_user_id' => ['nullable', 'integer'],
            'guests.*.cpf' => ['nullable', 'string', 'max:14'],
            'guests.*.email' => ['nullable', 'email', 'max:255'],
            'guests.*.phone' => ['nullable', 'string', 'max:30'],
        ]);

        $weddingId = $data['wedding_id'];

        // Verifica se o couple tem acesso a este casamento
        $role = optional(optional($user)->role)->value ?? $user->role ?? null;
        if ($role === 'couple') {
            $allowed = $user->weddings()->wherePivot('wedding_id', $weddingId)->exists();
            abort_unless($allowed, 403, 'Você só pode importar convidados para o seu casamento.');
        }

        $godparentValues = collect(GodparentRole::cases())->pluck('value')->toArray();
        $relationshipValues = collect(GuestRelationship::cases())->pluck('value')->toArray();

        $imported = 0;
        $errors = [];

        DB::beginTransaction();

        try {
            foreach ($data['guests'] as $index => $guestData) {
                // Limpa e valida CPF
                $cpf = null;
                if (!empty($guestData['cpf'])) {
                    $cpf = preg_replace('/\D/', '', $guestData['cpf']);
                    if (strlen($cpf) !== 11) {
                        $cpf = null; // CPF inválido, ignora
                    }
                }

                // Valida e normaliza relationship
                $relationship = null;
                if (!empty($guestData['relationship'])) {
                    $relationshipLower = $this->normalizeRelationship($guestData['relationship']);
                    if (in_array($relationshipLower, $relationshipValues)) {
                        $relationship = $relationshipLower;
                    }
                }

                // Valida e normaliza godparent_role
                $godparentRole = null;
                if (!empty($guestData['godparent_role'])) {
                    $godparentLower = $this->normalizeGodparent($guestData['godparent_role']);
                    if (in_array($godparentLower, $godparentValues)) {
                        $godparentRole = $godparentLower;
                    }
                }

                try {
                    Guest::create([
                        'wedding_id' => $weddingId,
                        'invited_by_user_id' => $user->id,
                        'name' => trim($guestData['name']),
                        'cpf' => $cpf,
                        'email' => $guestData['email'] ?? null,
                        'phone' => $guestData['phone'] ?? null,
                        'status' => GuestStatus::Pending,
                        'is_head_of_family' => true,
                        'party_size' => 1,
                        'relationship' => $relationship,
                        'godparent_role' => $godparentRole,
                        'is_godparent' => $godparentRole !== null,
                        'belongs_to_user_id' => $guestData['belongs_to_user_id'] ?? null,
                    ]);
                    $imported++;
                } catch (\Exception $e) {
                    $errors[] = [
                        'row' => $index + 1,
                        'name' => $guestData['name'],
                        'error' => $e->getMessage(),
                    ];
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'imported' => $imported,
                'errors' => $errors,
                'message' => $imported > 0
                    ? "{$imported} convidado(s) importado(s) com sucesso."
                    : "Nenhum convidado foi importado.",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'imported' => 0,
                'errors' => [['error' => $e->getMessage()]],
                'message' => 'Erro ao importar convidados.',
            ], 500);
        }
    }

    /**
     * Normaliza valores de relacionamento para o enum.
     */
    private function normalizeRelationship(string $value): string
    {
        $value = mb_strtolower(trim($value));
        
        $map = [
            'mãe' => 'mae',
            'mae' => 'mae',
            'mom' => 'mae',
            'mother' => 'mae',
            'pai' => 'pai',
            'dad' => 'pai',
            'father' => 'pai',
            'família' => 'familia',
            'familia' => 'familia',
            'family' => 'familia',
            'parente' => 'familia',
            'parentes' => 'familia',
            'tio' => 'familia',
            'tia' => 'familia',
            'primo' => 'familia',
            'prima' => 'familia',
            'avô' => 'familia',
            'avó' => 'familia',
            'avo' => 'familia',
            'amigo' => 'amigos',
            'amiga' => 'amigos',
            'amigos' => 'amigos',
            'friend' => 'amigos',
            'friends' => 'amigos',
            'trabalho' => 'trabalho',
            'work' => 'trabalho',
            'colega' => 'trabalho',
            'colegas' => 'trabalho',
            'outro' => 'outros',
            'outros' => 'outros',
            'other' => 'outros',
        ];

        return $map[$value] ?? 'outros';
    }

    /**
     * Normaliza valores de padrinho/madrinha para o enum.
     */
    private function normalizeGodparent(string $value): ?string
    {
        $value = mb_strtolower(trim($value));
        
        $map = [
            'padrinho' => 'padrinho',
            'godfather' => 'padrinho',
            'madrinha' => 'madrinha',
            'godmother' => 'madrinha',
            'sim' => null, // precisa de mais contexto
            'não' => null,
            'nao' => null,
            'no' => null,
            'yes' => null,
        ];

        return $map[$value] ?? null;
    }
}

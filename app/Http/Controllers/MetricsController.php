<?php

namespace App\Http\Controllers;

use App\Enums\GuestStatus;
use App\Models\Guest;
use App\Models\User;
use App\Models\Wedding;
use Illuminate\Http\JsonResponse;

class MetricsController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $weddings = Wedding::withCount([
            'guests',
            'guests as guests_accepted_count' => function ($q) {
                $q->where('status', GuestStatus::Accepted);
            },
        ])->get(['id', 'title', 'event_date']);

        return response()->json([
            'counts' => [
                'users' => User::count(),
                'weddings' => $weddings->count(),
                'guests' => Guest::count(),
                'guests_accepted' => Guest::where('status', GuestStatus::Accepted)->count(),
            ],
            'weddings' => $weddings,
        ]);
    }
}

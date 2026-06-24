<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceSubscriber;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MaintenanceController extends Controller
{
    public function subscribe(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email|max:255',
        ]);

        $email = strtolower(trim($validated['email']));

        $existing = MaintenanceSubscriber::where('email', $email)->first();
        if ($existing) {
            return response()->json([
                'message' => 'Cet email est déjà enregistré.',
                'already' => true,
            ], 409);
        }

        MaintenanceSubscriber::create([
            'email' => $email,
            'subscribed_at' => now(),
        ]);

        return response()->json([
            'message' => 'Merci, vous êtes inscrit.',
        ], 201);
    }
}

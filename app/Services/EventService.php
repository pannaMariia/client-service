<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EventService
{
    protected $worldServiceUrl;
    protected $apiKey;

    public function __construct()
    {
        $this->worldServiceUrl = config('services.world.url', 'http://localhost:8001');
        $this->apiKey = config('services.world.api_key');
    }

    /**
     * Отправить событие о создании пользователя в World Service
     */
    public function userCreated($userId, $location, $timestamp = null)
    {
        $timestamp = $timestamp ?? now()->toISOString();

        $payload = [
            'event' => 'UserCreated',
            'user_id' => $userId,
            'location' => $location,
            'timestamp' => $timestamp,
        ];

        try {
            $response = Http::timeout(5)
                ->withHeaders([
                    'X-API-Key' => $this->apiKey,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->post($this->worldServiceUrl . '/api/webhooks/user-created', $payload);

            if ($response->successful()) {
                Log::info('UserCreated event sent successfully', ['user_id' => $userId]);
                return true;
            } else {
                Log::warning('Failed to send UserCreated event', [
                    'user_id' => $userId,
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Error sending UserCreated event', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}

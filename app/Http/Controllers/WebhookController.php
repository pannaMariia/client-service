<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function userCreated(Request $request)
    {
        Log::info('WEBHOOK RECEIVED');
        Log::info('Event: UserCreated', $request->all());

        return response()->json([
            'status' => 'ok',
            'message' => 'Event received successfully',
            'data' => $request->all()
        ]);
    }
}

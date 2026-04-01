<?php

namespace App\Http\Controllers\API;
use OpenApi\Attributes as OA;
use App\Services\EventService;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Регистрация нового пользователя
     */
    #[OA\Post(
        path: "/register",
        summary: "Регистрация нового пользователя",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name", "email", "password", "password_confirmation"],
                properties: [
                    new OA\Property(property: "name", type: "string", example: "John Doe"),
                    new OA\Property(property: "email", type: "string", format: "email", example: "user@example.com"),
                    new OA\Property(property: "password", type: "string", format: "password", example: "password123"),
                    new OA\Property(property: "password_confirmation", type: "string", format: "password", example: "password123"),
                    new OA\Property(property: "phone", type: "string", example: "+79161234567", nullable: true),
                    new OA\Property(property: "location", type: "string", example: "Moscow", nullable: true),
                    new OA\Property(property: "birth_date", type: "string", format: "date", example: "1990-01-01", nullable: true)
                ]
            )
        ),
        tags: ["Auth"],
        responses: [
            new OA\Response(
                response: 201,
                description: "Пользователь успешно зарегистрирован",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "User registered successfully"),
                        new OA\Property(property: "user", ref: "#/components/schemas/User"),
                        new OA\Property(property: "token", type: "string", example: "1|abcdefghijklmnopqrstuvwxyz")
                    ]
                )
            ),
            new OA\Response(response: 422, description: "Ошибка валидации")
        ]
    )]

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'phone' => 'nullable|string|max:20',
            'location' => 'nullable|string|max:255',
            'birth_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'location' => $request->location,
            'birth_date' => $request->birth_date,
        ]);
        //отправка ивента в world - пока не работает


        try {
            $eventService = new EventService();
            $eventService->userCreated(
                $user->id,
                $user->location,
                now()->toISOString()
            );
        } catch (\Exception $e) {
            Log::error('Failed to send UserCreated event after registration', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user,
            'token' => $token
        ], 201);
    }

    /**
     * Авторизация пользователя
     */
    #[OA\Post(
        path: "/login",
        summary: "Авторизация пользователя",
        tags: ["Auth"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["email", "password"],
                properties: [
                    new OA\Property(property: "email", type: "string", format: "email", example: "user@example.com"),
                    new OA\Property(property: "password", type: "string", format: "password", example: "password123")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Успешный вход",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Login successful"),
                        new OA\Property(property: "user", ref: "#/components/schemas/User"),
                        new OA\Property(property: "token", type: "string", example: "1|abcdefghijklmnopqrstuvwxyz")
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: "Неверные учетные данные",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Invalid credentials"),
                        new OA\Property(property: "errors", type: "object",
                            properties: [
                                new OA\Property(property: "email", type: "array", items: new OA\Items(type: "string"), example: ["The provided credentials are incorrect."])
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: "Ошибка валидации"
            )
        ]
    )]
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials',
                'errors' => [
                    'email' => ['The provided credentials are incorrect.']
                ]
            ], 401);
        }

        // Удаляем старые токены
        $user->tokens()->delete();

        // Создаем новый токен
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'user' => $user,
            'token' => $token
        ]);
    }

    /**
     * Получить профиль текущего пользователя
     */
    #[OA\Get(
        path: "/profile",
        summary: "Получить профиль текущего пользователя",
        security: [["bearerAuth" => []]],
        tags: ["Profile"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Профиль пользователя",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "user", ref: "#/components/schemas/User")
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Не авторизован")
        ]
    )]
    public function profile(Request $request)
    {
        return response()->json([
            'user' => $request->user()
        ]);
    }

    /**
     * Обновить профиль пользователя
     */
    #[OA\Put(
        path: "/profile",
        summary: "Обновить профиль пользователя",
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "name", type: "string", example: "Updated Name"),
                    new OA\Property(property: "phone", type: "string", example: "+79161234567", nullable: true),
                    new OA\Property(property: "location", type: "string", example: "Saint Petersburg", nullable: true),
                    new OA\Property(property: "birth_date", type: "string", format: "date", example: "1990-01-01", nullable: true)
                ]
            )
        ),
        tags: ["Profile"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Профиль обновлен",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Profile updated successfully"),
                        new OA\Property(property: "user", ref: "#/components/schemas/User")
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Не авторизован"),
            new OA\Response(response: 422, description: "Ошибка валидации")
        ]
    )]
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'phone' => 'nullable|string|max:20',
            'location' => 'nullable|string|max:255',
            'birth_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user->update($request->only([
            'name', 'phone', 'location', 'birth_date'
        ]));

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user
        ]);
    }

    /**
     * Выход (удаление текущего токена)
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }

    /**
     * Получить все токены пользователя (для отладки)
     */
    public function tokens(Request $request)
    {
        return response()->json([
            'tokens' => $request->user()->tokens
        ]);
    }
}

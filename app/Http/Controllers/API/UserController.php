<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "User",
    properties: [
        new OA\Property(property: "id", type: "string", format: "uuid", example: "550e8400-e29b-41d4-a716-446655440000"),
        new OA\Property(property: "name", type: "string", example: "John Doe"),
        new OA\Property(property: "email", type: "string", format: "email", example: "john@example.com"),
        new OA\Property(property: "phone", type: "string", example: "+1234567890", nullable: true),
        new OA\Property(property: "location", type: "string", example: "Moscow", nullable: true),
        new OA\Property(property: "birth_date", type: "string", format: "date", nullable: true),
        new OA\Property(property: "created_at", type: "string", format: "date-time"),
        new OA\Property(property: "updated_at", type: "string", format: "date-time")
    ]
)]
class UserController extends Controller
{
    #[OA\Get(
        path: "/users",
        summary: "Получить список всех пользователей",
        tags: ["Users"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Успешный ответ",
                content: new OA\JsonContent(
                    type: "array",
                    items: new OA\Items(ref: "#/components/schemas/User")
                )
            )
        ]
    )]
    public function index(): JsonResponse
    {
        $users = User::all();
        return response()->json($users);
    }

    #[OA\Post(
        path: "/users",
        summary: "Создать нового пользователя",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name", "email"],
                properties: [
                    new OA\Property(property: "name", type: "string", example: "John Doe"),
                    new OA\Property(property: "email", type: "string", format: "email", example: "john@example.com"),
                    new OA\Property(property: "phone", type: "string", example: "+1234567890", nullable: true),
                    new OA\Property(property: "location", type: "string", example: "Moscow", nullable: true),
                    new OA\Property(property: "birth_date", type: "string", format: "date", nullable: true)
                ]
            )
        ),
        tags: ["Users"],
        responses: [
            new OA\Response(
                response: 201,
                description: "Пользователь создан",
                content: new OA\JsonContent(ref: "#/components/schemas/User")
            ),
            new OA\Response(
                response: 422,
                description: "Ошибка валидации"
            )
        ]
    )]
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'phone' => 'nullable|string|max:20',
            'location' => 'nullable|string|max:255',
            'birth_date' => 'nullable|date',
        ]);

        $user = User::create($validated);
        return response()->json($user, 201);
    }

    #[OA\Get(
        path: "/users/{id}",
        summary: "Получить пользователя по ID",
        tags: ["Users"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "string", format: "uuid")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Успешный ответ",
                content: new OA\JsonContent(ref: "#/components/schemas/User")
            ),
            new OA\Response(
                response: 404,
                description: "Пользователь не найден"
            )
        ]
    )]
    public function show(string $id): JsonResponse
    {
        $user = User::findOrFail($id);
        return response()->json($user);
    }

    #[OA\Put(
        path: "/users/{id}",
        summary: "Обновить пользователя",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "name", type: "string", example: "John Doe Updated"),
                    new OA\Property(property: "email", type: "string", format: "email", example: "john.updated@example.com"),
                    new OA\Property(property: "phone", type: "string", example: "+1234567890"),
                    new OA\Property(property: "location", type: "string", example: "Saint Petersburg"),
                    new OA\Property(property: "birth_date", type: "string", format: "date", example: "1990-01-01")
                ]
            )
        ),
        tags: ["Users"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "string", format: "uuid")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Пользователь обновлен",
                content: new OA\JsonContent(ref: "#/components/schemas/User")
            ),
            new OA\Response(
                response: 404,
                description: "Пользователь не найден"
            )
        ]
    )]
    public function update(Request $request, string $id): JsonResponse
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'location' => 'nullable|string|max:255',
            'birth_date' => 'nullable|date',
        ]);

        $user->update($validated);
        return response()->json($user);
    }

    #[OA\Delete(
        path: "/users/{id}",
        summary: "Удалить пользователя",
        tags: ["Users"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "string", format: "uuid")
            )
        ],
        responses: [
            new OA\Response(
                response: 204,
                description: "Пользователь удален"
            ),
            new OA\Response(
                response: 404,
                description: "Пользователь не найден"
            )
        ]
    )]
    public function destroy(string $id): JsonResponse
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json(null, 204);
    }
}

<?php

declare(strict_types=1);

namespace App\Swagger;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: "1.0.0",
    description: "API для управления клиентским сервисом",
    title: "Client Service",
    contact: new OA\Contact(
        name: "Mariia",
        email: "mariia@email.com"
    )
)]
#[OA\Server(
    url: "http://localhost:8000/api/v1",
    description: "Local development server"
)]
#[OA\Server(
    url: "https://api.example.com/api/v1",
    description: "Production server"
)]
#[OA\Tag(
    name: "Users",
    description: "Управление пользователями"
)]
#[OA\Tag(
    name: "Auth",
    description: "Аутентификация и регистрация"
)]
#[OA\Tag(
    name: "Profile",
    description: "Управление профилем пользователя"
)]
#[OA\SecurityScheme(
    securityScheme: "bearerAuth",
    type: "http",
    bearerFormat: "JWT",
    scheme: "bearer"
)]

class SwaggerAnnotations
{
}

<?php

declare(strict_types=1);

namespace App\Http\Api\Controllers;

use App\Core\Session;

class ApiBaseController
{
    public function __construct()
    {
    }

    protected function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    protected function jsonResponse(int $statusCode, array $data): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    protected function getJsonInput(): array
    {
        $input = file_get_contents('php://input');
        $decoded = json_decode($input ?? '{}', true);
        return is_array($decoded) ? $decoded : [];
    }

    protected function requireAuth(): void
    {
        if (Session::get('user_id') === null) {
            $this->jsonResponse(401, ['message' => 'No autenticado.']);
            exit;
        }
    }

    protected function getAuthUserId(): ?int
    {
        return Session::get('user_id');
    }

    protected function getAuthUser(): ?\App\Models\User
    {
        $userId = $this->getAuthUserId();
        if ($userId === null) {
            return null;
        }
        return \App\Models\User::find($userId);
    }
}

<?php

namespace App\Core;

class Controller
{
    protected function view(string $view, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        require __DIR__ . '/../views/layout/header.php';
        require __DIR__ . '/../views/' . $view . '.php';
        require __DIR__ . '/../views/layout/footer.php';
    }

    protected function redirect(string $path): void
    {
        header('Location: ' . BASE_URL . $path);
        exit;
    }

    protected function json(array $payload, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($payload);
        exit;
    }

    protected function requireLogin(): void
    {
        if (empty($_SESSION['user'])) {
            $_SESSION['flash'] = 'Please log in first.';
            $this->redirect('?page=login');
        }
    }

    protected function requireRole(string $role): void
    {
        $this->requireLogin();
        if (($_SESSION['user']['role'] ?? '') !== $role) {
            http_response_code(403);
            echo 'Forbidden';
            exit;
        }
    }

    protected function csrf(): string
    {
        if (empty($_SESSION['csrf'])) {
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf'];
    }

    protected function verifyCsrf(): void
    {
        $token = $_POST['csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!hash_equals($_SESSION['csrf'] ?? '', (string) $token)) {
            $this->json(['ok' => false, 'error' => 'Invalid CSRF token.'], 419);
        }
    }
}

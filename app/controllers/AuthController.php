<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;

class AuthController extends Controller
{
    public function __construct(private User $users)
    {
    }

    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->verifyCsrf();
            $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
            $password = (string) ($_POST['password'] ?? '');
            $user = $email ? $this->users->findByEmail($email) : null;

            if (!$user || !password_verify($password, $user['password_hash'])) {
                $_SESSION['flash'] = 'Invalid email or password.';
            } else {
                $_SESSION['user'] = ['id' => (int) $user['id'], 'name' => $user['name'], 'role' => $user['role']];
                if (!empty($_POST['remember'])) {
                    $token = bin2hex(random_bytes(32));
                    $this->users->setRememberToken((int) $user['id'], hash('sha256', $token));
                    setcookie('remember_token', $token, time() + 60 * 60 * 24 * 30, '', '', false, true);
                }
                $this->redirect('?page=home');
            }
        }

        $this->view('login', ['title' => 'Login', 'activePage' => 'login', 'csrf' => $this->csrf()]);
    }

    public function register(): void
    {
        $errors = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->verifyCsrf();
            $data = $this->validatedUserInput($errors);
            if ($data && $this->users->findByEmail($data['email'])) {
                $errors['email'] = 'Email already exists.';
            }
            if (!$errors && $data) {
                $data['password_hash'] = password_hash((string) $_POST['password'], PASSWORD_DEFAULT);
                $this->users->create($data);
                $_SESSION['flash'] = 'Registration complete. Please log in.';
                $this->redirect('?page=login');
            }
        }

        $this->view('register', ['title' => 'Register', 'activePage' => 'register', 'csrf' => $this->csrf(), 'errors' => $errors]);
    }

    private function validatedUserInput(array &$errors): ?array
    {
        $name = trim((string) ($_POST['name'] ?? ''));
        $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
        $password = (string) ($_POST['password'] ?? '');
        $role = in_array($_POST['role'] ?? '', ['admin', 'customer'], true) ? $_POST['role'] : 'customer';
        $address = trim((string) ($_POST['address'] ?? ''));
        $phone = trim((string) ($_POST['phone'] ?? ''));

        if ($name === '') {
            $errors['name'] = 'Name is required.';
        }
        if (!$email) {
            $errors['email'] = 'Valid email is required.';
        }
        if (strlen($password) < 8) {
            $errors['password'] = 'Password must be at least 8 characters.';
        }
        if ($address === '') {
            $errors['address'] = 'Address is required.';
        }
        if ($phone === '') {
            $errors['phone'] = 'Phone is required.';
        }

        return $errors ? null : compact('name', 'email', 'role', 'address', 'phone');
    }

}

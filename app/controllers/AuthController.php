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

    public function profile(): void
    {
        $this->requireLogin();
        $user = $this->users->find((int) $_SESSION['user']['id']);
        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->verifyCsrf();
            $name = trim((string) ($_POST['name'] ?? ''));
            $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
            $address = trim((string) ($_POST['address'] ?? ''));
            $phone = trim((string) ($_POST['phone'] ?? ''));
            if ($name === '') {
                $errors['name'] = 'Name is required.';
            }
            if (!$email) {
                $errors['email'] = 'Valid email is required.';
            }
            if ($address === '') {
                $errors['address'] = 'Address is required.';
            }
            if (!$this->validPhone($phone)) {
                $errors['phone'] = 'Phone must be 11 digits and start with 013, 014, 015, 016, 017, 018, or 019.';
            }

            $picture = $user['profile_picture'] ?? null;
            if (!empty($_FILES['profile_picture']['tmp_name'])) {
                $picture = $this->uploadImage('profile_picture', 'profiles', $errors);
            }

            if (!empty($_POST['new_password'])) {
                if (!password_verify((string) ($_POST['current_password'] ?? ''), $user['password_hash'])) {
                    $errors['current_password'] = 'Current password is incorrect.';
                } elseif (strlen((string) $_POST['new_password']) < 8) {
                    $errors['new_password'] = 'New password must be at least 8 characters.';
                } else {
                    $this->users->updatePassword((int) $user['id'], password_hash((string) $_POST['new_password'], PASSWORD_DEFAULT));
                }
            }

            if (!$errors) {
                $this->users->updateProfile((int) $user['id'], compact('name', 'email', 'address', 'phone') + ['profile_picture' => $picture]);
                $_SESSION['user']['name'] = $name;
                $_SESSION['flash'] = 'Profile updated.';
                $this->redirect('?page=profile');
            }
        }

        $this->view('profile', ['title' => 'Profile', 'activePage' => 'profile', 'csrf' => $this->csrf(), 'user' => $user, 'errors' => $errors]);
    }

    public function logout(): void
    {
        if (!empty($_SESSION['user'])) {
            $this->users->setRememberToken((int) $_SESSION['user']['id'], null);
        }
        setcookie('remember_token', '', time() - 3600, '', '', false, true);
        session_destroy();
        $this->redirect('?page=login');
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
        if (!$this->validPhone($phone)) {
            $errors['phone'] = 'Phone must be 11 digits and start with 013, 014, 015, 016, 017, 018, or 019.';
        }

        return $errors ? null : compact('name', 'email', 'role', 'address', 'phone');
    }

    private function validPhone(string $phone): bool
    {
        return (bool) preg_match('/^01[3-9][0-9]{8}$/', $phone);
    }

    private function uploadImage(string $field, string $folder, array &$errors): ?string
    {
        $file = $_FILES[$field];
        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png'];
        $mime = mime_content_type($file['tmp_name']);
        if (!isset($allowed[$mime]) || $file['size'] > 2 * 1024 * 1024) {
            $errors[$field] = 'Upload a JPEG or PNG image under 2MB.';
            return null;
        }
        $dir = __DIR__ . '/../../public/uploads/' . $folder;
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $name = bin2hex(random_bytes(12)) . '.' . $allowed[$mime];
        move_uploaded_file($file['tmp_name'], $dir . '/' . $name);
        return 'uploads/' . $folder . '/' . $name;
    }
}

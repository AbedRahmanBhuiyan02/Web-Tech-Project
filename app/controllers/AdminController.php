<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Category;
use App\Models\Medicine;
use App\Models\Order;
use App\Models\User;

class AdminController extends Controller
{
    public function __construct(private Medicine $medicines, private Category $categories, private Order $orders, private User $users)
    {
    }

    public function dashboard(): void
    {
        $this->requireRole('admin');
        $this->view('admin/dashboard', [
            'title' => 'Admin Dashboard',
            'activePage' => 'admin',
            'medicinesCount' => $this->medicines->count(),
            'categoriesCount' => $this->categories->count(),
            'customersCount' => $this->users->customerCount(),
            'pendingCount' => $this->orders->pendingCount(),
            'orders' => array_slice($this->orders->all(), 0, 8),
        ]);
    }

    public function medicines(): void
    {
        $this->requireRole('admin');
        $errors = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->verifyCsrf();
            $action = $_POST['action'] ?? 'save';
            if ($action === 'delete') {
                if (!$this->medicines->delete((int) ($_POST['id'] ?? 0))) {
                    $_SESSION['flash'] = 'Cannot delete a medicine attached to a pending order.';
                }
                $this->redirect('?page=admin-medicines');
            }
            $data = $this->medicineInput($errors);
            if (!$errors && $data) {
                $id = (int) ($_POST['id'] ?? 0);
                $id > 0 ? $this->medicines->update($id, $data) : $this->medicines->create($data);
                $_SESSION['flash'] = 'Medicine saved.';
                $this->redirect('?page=admin-medicines');
            }
        }

        $this->view('admin/medicines', [
            'title' => 'Medicine Management',
            'activePage' => 'admin-medicines',
            'csrf' => $this->csrf(),
            'medicines' => $this->medicines->all(),
            'categories' => $this->categories->all(),
            'edit' => isset($_GET['edit']) ? $this->medicines->find((int) $_GET['edit']) : null,
            'errors' => $errors,
        ]);
    }

    public function categories(): void
    {
        $this->requireRole('admin');
        $errors = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->verifyCsrf();
            if (($_POST['action'] ?? '') === 'delete') {
                if (!$this->categories->delete((int) ($_POST['id'] ?? 0))) {
                    $_SESSION['flash'] = 'Cannot delete a category that still has medicines.';
                }
                $this->redirect('?page=admin-categories');
            }
            $name = trim((string) ($_POST['name'] ?? ''));
            $type = in_array($_POST['category_type'] ?? '', ['liquid', 'solid'], true) ? $_POST['category_type'] : '';
            if ($name === '') {
                $errors['name'] = 'Category name is required.';
            }
            if ($type === '') {
                $errors['category_type'] = 'Choose liquid or solid.';
            }
            if (!$errors) {
                $id = (int) ($_POST['id'] ?? 0);
                $id > 0 ? $this->categories->update($id, $name, $type) : $this->categories->create($name, $type);
                $_SESSION['flash'] = 'Category saved.';
                $this->redirect('?page=admin-categories');
            }
        }

        $this->view('admin/categories', [
            'title' => 'Category Management',
            'activePage' => 'admin-categories',
            'csrf' => $this->csrf(),
            'categories' => $this->categories->all(),
            'edit' => isset($_GET['edit']) ? $this->categories->find((int) $_GET['edit']) : null,
            'errors' => $errors,
        ]);
    }

    public function customers(): void
    {
        $this->requireRole('admin');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->verifyCsrf();
            $this->users->deleteCustomer((int) ($_POST['id'] ?? 0));
            $_SESSION['flash'] = 'Customer deleted.';
            $this->redirect('?page=admin-customers');
        }

        $this->view('admin/customers', [
            'title' => 'Customers',
            'activePage' => 'admin-customers',
            'csrf' => $this->csrf(),
            'customers' => $this->users->customers(),
        ]);
    }

    public function requests(): void
    {
        $this->requireRole('admin');
        $this->view('admin/requests', [
            'title' => 'Purchase Requests',
            'activePage' => 'admin-requests',
            'csrf' => $this->csrf(),
            'orders' => $this->orders->all(),
        ]);
    }

    public function history(): void
    {
        $this->requireRole('admin');
        $this->view('admin/history', [
            'title' => 'Purchase History',
            'activePage' => 'admin-history',
            'orders' => $this->orders->acceptedHistory(),
        ]);
    }

    private function medicineInput(array &$errors): ?array
    {
        $name = trim((string) ($_POST['name'] ?? ''));
        $categoryId = (int) ($_POST['category_id'] ?? 0);
        $vendor = trim((string) ($_POST['vendor_name'] ?? ''));
        $price = (float) ($_POST['price'] ?? 0);
        $availability = (int) ($_POST['availability'] ?? 0);
        $description = trim((string) ($_POST['description'] ?? ''));
        $imagePath = trim((string) ($_POST['existing_image'] ?? ''));

        if ($name === '') {
            $errors['name'] = 'Medicine name is required.';
        }
        if ($categoryId <= 0) {
            $errors['category_id'] = 'Choose a category.';
        }
        if ($vendor === '') {
            $errors['vendor_name'] = 'Vendor name is required.';
        }
        if ($price <= 0) {
            $errors['price'] = 'Price must be greater than zero.';
        }
        if ($availability < 0) {
            $errors['availability'] = 'Stock cannot be negative.';
        }
        if ($description === '') {
            $errors['description'] = 'Description is required.';
        }
        if (!empty($_FILES['image']['tmp_name'])) {
            $imagePath = $this->uploadMedicineImage($errors);
        }

        return $errors ? null : [
            'name' => $name,
            'category_id' => $categoryId,
            'vendor_name' => $vendor,
            'price' => $price,
            'availability' => $availability,
            'description' => $description,
            'image_path' => $imagePath ?: null,
        ];
    }

    private function uploadMedicineImage(array &$errors): ?string
    {
        $file = $_FILES['image'];
        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png'];
        $mime = mime_content_type($file['tmp_name']);
        if (!isset($allowed[$mime]) || $file['size'] > 2 * 1024 * 1024) {
            $errors['image'] = 'Upload a JPEG or PNG image under 2MB.';
            return null;
        }
        $dir = __DIR__ . '/../../public/uploads/medicines';
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $name = bin2hex(random_bytes(12)) . '.' . $allowed[$mime];
        move_uploaded_file($file['tmp_name'], $dir . '/' . $name);
        return 'uploads/medicines/' . $name;
    }
}

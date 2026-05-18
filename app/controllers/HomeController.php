<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Category;
use App\Models\Medicine;

class HomeController extends Controller
{
    public function __construct(private Medicine $medicines, private Category $categories)
    {
    }

    public function index(): void
    {
        $filters = [
            'q' => trim((string) ($_GET['q'] ?? '')),
            'vendor' => trim((string) ($_GET['vendor'] ?? '')),
            'category_id' => (int) ($_GET['category_id'] ?? 0),
            'genre' => trim((string) ($_GET['genre'] ?? '')),
            'type' => in_array($_GET['type'] ?? '', ['liquid', 'solid'], true) ? $_GET['type'] : '',
        ];

        $this->view('home', [
            'title' => 'Browse Medicines',
            'activePage' => 'home',
            'csrf' => $this->csrf(),
            'medicines' => $this->medicines->all($filters),
            'categories' => $this->categories->all(),
            'vendors' => $this->medicines->vendors(),
            'filters' => $filters,
        ]);
    }
}

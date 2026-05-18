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
            'genre' => trim((string) ($_GET['genre'] ?? '')),
            'type' => trim((string) ($_GET['type'] ?? '')),
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

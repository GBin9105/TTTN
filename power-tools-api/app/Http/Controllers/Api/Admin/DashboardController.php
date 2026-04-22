<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Models\Banner;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Contact;
use App\Models\Order;
use App\Models\Post;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\User;

class DashboardController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $data = [
            'products_count'   => Product::count(),
            'categories_count' => Category::count(),
            'brands_count'     => Brand::count(),
            'posts_count'      => Post::count(),
            'users_count'      => User::where('role', 'customer')->count(),
            'orders_count'     => Order::count(),
            'contacts_count'   => Contact::count(),
            'banners_count'    => Banner::count(),
            'promotions_count' => Promotion::count(),
        ];

        return $this->success($data, 'Dashboard loaded successfully.');
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use App\Models\User;

class AdminController extends Controller
{
    public function dashboard()
    {
        $articles = Article::with(['category', 'author'])->latest()->get();
        $articleCount = Article::count();
        $categoryCount = Category::count();
        $userCount = User::count();

        return view('admin.dashboard', compact('articles', 'articleCount', 'categoryCount', 'userCount'));
    }
}

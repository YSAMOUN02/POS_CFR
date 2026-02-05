<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
     // Return all categories for select
    public function getCategories(Request $request)
    {
        // Optional: you can filter active categories only
        $categories = Category::query()
            ->when($request->filled('active'), function ($q) use ($request) {
                $q->where('status', $request->active); // assuming 'status' column
            })
            ->orderBy('name') // sort alphabetically
            ->get(['id', 'name']); // only return fields needed

        return response()->json($categories);
    }
}

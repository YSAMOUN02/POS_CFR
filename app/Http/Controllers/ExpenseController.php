<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
public function latest()
{
    $expenses = Expense::orderBy('id', 'desc')

        ->get();

    return response()->json([
        'status' => true,
        'data' => $expenses
    ]);
}
}

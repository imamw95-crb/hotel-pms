<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    /**
     * Display a listing of expenses.
     */
    public function index(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::today()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::today()->format('Y-m-d'));

        $expenses = Expense::with('createdBy')
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->orderBy('expense_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        $totalExpenses = $expenses->sum('amount');
        $byMethod = $expenses->groupBy('payment_method')->map->sum('amount');

        return view('expenses.index', compact(
            'startDate', 'endDate', 'expenses', 'totalExpenses', 'byMethod'
        ));
    }

    /**
     * Show the form for creating a new expense.
     */
    public function create()
    {
        return view('expenses.create');
    }

    /**
     * Store a newly created expense in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'description'    => 'required|string|max:255',
            'amount'         => 'required|numeric|min:0',
            'payment_method' => 'required|string|max:20',
            'expense_date'   => 'required|date',
            'notes'          => 'nullable|string|max:1000',
        ]);

        $validated['created_by'] = auth()->id();

        Expense::create($validated);

        return redirect()->route('expenses.index', ['start_date' => $validated['expense_date'], 'end_date' => $validated['expense_date']])
            ->with('success', 'Pengeluaran berhasil dicatat.');
    }
}

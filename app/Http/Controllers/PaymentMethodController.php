<?php

namespace App\Http\Controllers;

use App\Models\PaymentMethod;
use Illuminate\Http\Request;

class PaymentMethodController extends Controller
{
    /**
     * Tampilkan daftar metode pembayaran.
     */
    public function index()
    {
        $paymentMethods = PaymentMethod::orderBy('name')->get();
        return view('admin.payment-methods.index', compact('paymentMethods'));
    }

    /**
     * Form tambah metode pembayaran.
     */
    public function create()
    {
        return view('admin.payment-methods.create');
    }

    /**
     * Simpan metode pembayaran baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:payment_methods,name',
        ]);

        PaymentMethod::create([
            'name' => $validated['name'],
            'slug' => \Illuminate\Support\Str::slug($validated['name']),
            'is_active' => true,
        ]);

        return redirect()->route('admin.payment-methods.index')
            ->with('success', 'Metode pembayaran berhasil ditambahkan.');
    }

    /**
     * Form edit metode pembayaran.
     */
    public function edit(PaymentMethod $paymentMethod)
    {
        return view('admin.payment-methods.edit', compact('paymentMethod'));
    }

    /**
     * Update metode pembayaran.
     */
    public function update(Request $request, PaymentMethod $paymentMethod)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:payment_methods,name,' . $paymentMethod->id,
            'is_active' => 'boolean',
        ]);

        $paymentMethod->update([
            'name' => $validated['name'],
            'slug' => \Illuminate\Support\Str::slug($validated['name']),
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.payment-methods.index')
            ->with('success', 'Metode pembayaran berhasil diperbarui.');
    }

    /**
     * Hapus metode pembayaran.
     */
    public function destroy(PaymentMethod $paymentMethod)
    {
        $paymentMethod->delete();
        return redirect()->route('admin.payment-methods.index')
            ->with('success', 'Metode pembayaran berhasil dihapus.');
    }
}

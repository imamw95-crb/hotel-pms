<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Kode Akun Default (Chart of Accounts)
    |--------------------------------------------------------------------------
    | Dipakai sebagai fallback kalau sebuah transaksi belum punya pemetaan akun
    | di tabel account_mappings.
    */
    'default_codes' => [
        'cash' => '1-1100',
        'bank' => '1-1200',
        'guest_deposit' => '2-1100',
        'room_revenue' => '4-1000',
        'resto_revenue' => '4-1100',
        'other_revenue' => '4-1200',
        'contra_revenue' => '4-9000',
        'default_expense' => '6-9000',
    ],

    /*
    |--------------------------------------------------------------------------
    | Pemetaan Pendapatan per Tipe Transaksi Reservasi
    |--------------------------------------------------------------------------
    */
    'revenue_by_transaction_type' => [
        'refund' => 'contra_revenue',
        'koreksi' => 'contra_revenue',
    ],

    /*
    |--------------------------------------------------------------------------
    | Tipe Transaksi yang Berarti UANG KELUAR
    |--------------------------------------------------------------------------
    */
    'outflow_transaction_types' => ['refund', 'koreksi'],

    /*
    |--------------------------------------------------------------------------
    | Kategori Pengeluaran (Expense Category) → Akun Beban
    |--------------------------------------------------------------------------
    | Dipakai form pengeluaran & pemetaan otomatis ke akun beban.
    */
    'expense_categories' => [
        'gaji' => 'Gaji & Upah',
        'amenities' => 'Amenities & Supplies',
        'laundry' => 'Laundry & Linen',
        'fnb_bahan' => 'Bahan Makanan & Minuman',
        'utilitas' => 'Listrik, Air & Internet',
        'atk' => 'ATK & Administrasi',
        'perawatan' => 'Perawatan & Perbaikan',
        'sewa' => 'Sewa',
        'pemasaran' => 'Pemasaran & Komisi OTA',
        'pajak' => 'Pajak & Perizinan',
        'lain_lain' => 'Lain-lain',
    ],

    'expense_category_accounts' => [
        'gaji' => '6-1100',
        'amenities' => '5-1200',
        'laundry' => '5-1300',
        'fnb_bahan' => '5-2100',
        'utilitas' => '6-1200',
        'atk' => '6-1400',
        'perawatan' => '6-1500',
        'sewa' => '6-1600',
        'pemasaran' => '6-1800',
        'pajak' => '6-1900',
        'lain_lain' => '6-9000',
    ],
];

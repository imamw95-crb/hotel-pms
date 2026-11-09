@extends('layouts.app')

@section('title', 'Jurnal Manual')
@section('header', 'Akunting — Jurnal Manual')

@section('content')
@include('accounting.partials.nav')

<form method="POST" action="{{ route('accounting.journals.store') }}">
    @csrf

    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">Tanggal <span class="text-red-500">*</span></label>
                <input type="date" name="date" value="{{ old('date', date('Y-m-d')) }}" required
                       class="w-full border rounded px-3 py-2 @error('date') border-red-500 @enderror">
                @error('date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="md:col-span-2">
                <label class="block text-gray-700 text-sm font-bold mb-2">Keterangan</label>
                <input type="text" name="memo" value="{{ old('memo') }}" maxlength="255"
                       class="w-full border rounded px-3 py-2" placeholder="cth: Saldo awal kas per 1 Januari">
            </div>
        </div>
        @if($errors->has('lines'))
            <p class="text-red-500 text-sm mt-3">{{ $errors->first('lines') }}</p>
        @endif
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden mb-4">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-100">
                    <tr class="text-left text-gray-600">
                        <th class="px-3 py-2 w-2/5">Akun</th>
                        <th class="px-3 py-2 w-1/6 text-right">Debit</th>
                        <th class="px-3 py-2 w-1/6 text-right">Kredit</th>
                        <th class="px-3 py-2">Memo Baris</th>
                        <th class="px-3 py-2 w-10"></th>
                    </tr>
                </thead>
                <tbody id="linesBody"></tbody>
                <tfoot>
                    <tr class="bg-gray-50 font-bold">
                        <td class="px-3 py-2 text-right">TOTAL</td>
                        <td class="px-3 py-2 text-right" id="totalDebit">0</td>
                        <td class="px-3 py-2 text-right" id="totalCredit">0</td>
                        <td colspan="2" class="px-3 py-2 text-xs font-normal">
                            <span id="balanceInfo" class="text-gray-500">Belum seimbang</span>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <div class="flex flex-wrap gap-3 mb-6">
        <button type="button" onclick="addLine()" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-slate-700">
            <i class="fas fa-plus mr-1"></i> Tambah Baris
        </button>
        <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 font-semibold">
            <i class="fas fa-save mr-2"></i> Posting Jurnal
        </button>
        <a href="{{ route('accounting.journals.index') }}" class="bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600 font-semibold">
            <i class="fas fa-arrow-left mr-2"></i> Kembali
        </a>
    </div>
</form>

<template id="rowTemplate">
    <tr>
        <td class="px-3 py-2">
            <select name="lines[__INDEX__][account_id]" class="w-full border rounded px-2 py-1">
                <option value="">— Pilih Akun —</option>
                @foreach($accounts as $typeKey => $group)
                    <optgroup label="{{ \App\Models\Account::TYPES[$typeKey] ?? ucfirst($typeKey) }}">
                        @foreach($group as $account)
                            <option value="{{ $account->id }}">{{ $account->code }} — {{ $account->name }}</option>
                        @endforeach
                    </optgroup>
                @endforeach
            </select>
        </td>
        <td class="px-3 py-2">
            <input type="number" name="lines[__INDEX__][debit]" step="0.01" min="0" class="line-debit w-full border rounded px-2 py-1 text-right" placeholder="0">
        </td>
        <td class="px-3 py-2">
            <input type="number" name="lines[__INDEX__][credit]" step="0.01" min="0" class="line-credit w-full border rounded px-2 py-1 text-right" placeholder="0">
        </td>
        <td class="px-3 py-2">
            <input type="text" name="lines[__INDEX__][memo]" maxlength="255" class="w-full border rounded px-2 py-1">
        </td>
        <td class="px-3 py-2 text-center">
            <button type="button" class="text-red-600 hover:text-red-800" onclick="removeLine(this)" title="Hapus baris">
                <i class="fas fa-times"></i>
            </button>
        </td>
    </tr>
</template>

<script>
    let lineIndex = 0;

    function addLine(values) {
        const template = document.getElementById('rowTemplate').innerHTML.replace(/__INDEX__/g, lineIndex++);
        const wrapper = document.createElement('tbody');
        wrapper.innerHTML = template.trim();
        const row = wrapper.firstElementChild;

        if (values) {
            row.querySelector('select').value = values.account_id ?? '';
            row.querySelector('.line-debit').value = values.debit ?? '';
            row.querySelector('.line-credit').value = values.credit ?? '';
            row.querySelector('input[type=text]').value = values.memo ?? '';
        }

        document.getElementById('linesBody').appendChild(row);
        recalc();
    }

    function removeLine(button) {
        const rows = document.querySelectorAll('#linesBody tr');
        if (rows.length > 2) {
            button.closest('tr').remove();
            recalc();
        }
    }

    function formatRupiah(value) {
        return new Intl.NumberFormat('id-ID').format(Math.round(value));
    }

    function recalc() {
        let debit = 0;
        let credit = 0;

        document.querySelectorAll('#linesBody .line-debit').forEach((el) => {
            debit += parseFloat(el.value) || 0;
        });
        document.querySelectorAll('#linesBody .line-credit').forEach((el) => {
            credit += parseFloat(el.value) || 0;
        });

        document.getElementById('totalDebit').textContent = formatRupiah(debit);
        document.getElementById('totalCredit').textContent = formatRupiah(credit);

        const info = document.getElementById('balanceInfo');
        const diff = Math.round((debit - credit) * 100) / 100;

        if (diff === 0 && debit > 0) {
            info.textContent = 'Seimbang';
            info.className = 'text-green-600 font-semibold';
        } else {
            info.textContent = 'Selisih: ' + formatRupiah(Math.abs(diff));
            info.className = 'text-red-600 font-semibold';
        }
    }

    document.addEventListener('input', function (event) {
        if (event.target.classList.contains('line-debit') || event.target.classList.contains('line-credit')) {
            recalc();
        }
    });

    window.addEventListener('DOMContentLoaded', function () {
        addLine();
        addLine();
        @if(old('lines'))
            document.getElementById('linesBody').innerHTML = '';
            lineIndex = 0;
            @foreach(old('lines', []) as $line)
                addLine(@json($line));
            @endforeach
        @endif
    });
</script>
@endsection

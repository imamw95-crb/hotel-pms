<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Deposit;
use App\Models\Expense;
use App\Models\Guest;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\Reservation;
use App\Models\Transaction;
use App\Models\User;
use App\Services\AccountingReportService;
use Carbon\Carbon;
use Database\Seeders\AccountingSeeder;
use Tests\TestCase;

class AccountingTest extends TestCase
{
    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(AccountingSeeder::class);

        $this->owner = User::factory()->create(['role' => 'owner']);
        $this->actingAs($this->owner);
    }

    public function test_seeder_creates_chart_of_accounts()
    {
        $this->assertGreaterThan(30, Account::count());
        $this->assertNotNull(Account::byCode('1-1100'));
        $this->assertNotNull(Account::byCode('4-1000'));
        $this->assertTrue((bool) Account::byCode('1-1100')->is_cash);
    }

    public function test_expense_posts_balanced_journal()
    {
        $expense = Expense::create([
            'description' => 'Bayar listrik',
            'expense_category' => 'utilitas',
            'amount' => 250000,
            'payment_method' => 'cash',
            'expense_date' => Carbon::today()->toDateString(),
        ]);

        $entry = JournalEntry::where('source_type', 'expense')->where('source_id', $expense->id)->first();

        $this->assertNotNull($entry);
        $this->assertEquals(250000, (float) $entry->total_debit);
        $this->assertEquals((float) $entry->lines->sum('debit'), (float) $entry->lines->sum('credit'));

        $this->assertDatabaseHas('journal_entry_lines', [
            'journal_entry_id' => $entry->id,
            'account_id' => Account::byCode('6-1200')->id,
            'debit' => 250000,
        ]);
        $this->assertDatabaseHas('journal_entry_lines', [
            'journal_entry_id' => $entry->id,
            'account_id' => Account::byCode('1-1100')->id,
            'credit' => 250000,
        ]);
    }

    public function test_reservation_payment_credits_room_revenue()
    {
        $reservation = Reservation::factory()->create();

        $transaction = Transaction::create([
            'reservation_id' => $reservation->id,
            'type' => 'dp',
            'amount' => 500000,
            'payment_method' => 'transfer_bca',
        ]);

        $entry = JournalEntry::where('source_type', 'transaction')->where('source_id', $transaction->id)->first();

        $this->assertNotNull($entry);
        $this->assertDatabaseHas('journal_entry_lines', [
            'journal_entry_id' => $entry->id,
            'account_id' => Account::byCode('1-1200')->id,
            'debit' => 500000,
        ]);
        $this->assertDatabaseHas('journal_entry_lines', [
            'journal_entry_id' => $entry->id,
            'account_id' => Account::byCode('4-1000')->id,
            'credit' => 500000,
        ]);
    }

    public function test_refund_debits_contra_revenue()
    {
        $reservation = Reservation::factory()->create();

        $refund = Transaction::create([
            'reservation_id' => $reservation->id,
            'type' => 'refund',
            'amount' => 100000,
            'payment_method' => 'cash',
        ]);

        $entry = JournalEntry::where('source_type', 'transaction')->where('source_id', $refund->id)->first();

        $this->assertDatabaseHas('journal_entry_lines', [
            'journal_entry_id' => $entry->id,
            'account_id' => Account::byCode('4-9000')->id,
            'debit' => 100000,
        ]);
    }

    public function test_deposit_return_keeps_receipt_and_adds_reversal()
    {
        $deposit = Deposit::create([
            'guest_id' => Guest::factory()->create()->id,
            'number_of_cards' => 1,
            'nominal_per_card' => 500000,
            'payment_method' => 'cash',
            'status' => 'active',
        ]);

        $this->assertNotNull(JournalEntry::where('source_type', 'deposit')->where('source_id', $deposit->id)->first());

        $deposit->update(['status' => 'returned']);

        $this->assertNotNull(JournalEntry::where('source_type', 'deposit')->where('source_id', $deposit->id)->first());
        $this->assertNotNull(JournalEntry::where('source_type', 'deposit_return')->where('source_id', $deposit->id)->first());

        $liability = Account::byCode('2-1100');
        $credit = JournalEntryLine::where('account_id', $liability->id)
            ->whereHas('journalEntry', fn ($q) => $q->where('status', JournalEntry::STATUS_POSTED))
            ->sum('credit');
        $debit = JournalEntryLine::where('account_id', $liability->id)
            ->whereHas('journalEntry', fn ($q) => $q->where('status', JournalEntry::STATUS_POSTED))
            ->sum('debit');

        $this->assertEquals(0.0, round((float) $credit - (float) $debit, 2));
    }

    public function test_manual_journal_rejects_unbalanced_entry()
    {
        $cash = Account::byCode('1-1100');
        $revenue = Account::byCode('4-1000');

        $response = $this->post(route('accounting.journals.store'), [
            'date' => Carbon::today()->toDateString(),
            'memo' => 'Jurnal tidak seimbang',
            'lines' => [
                ['account_id' => $cash->id, 'debit' => 100000, 'credit' => null],
                ['account_id' => $revenue->id, 'debit' => null, 'credit' => 90000],
            ],
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseCount('journal_entries', 0);
    }

    public function test_manual_journal_posts_balanced_entry()
    {
        $cash = Account::byCode('1-1100');
        $revenue = Account::byCode('4-1220');

        $response = $this->post(route('accounting.journals.store'), [
            'date' => Carbon::today()->toDateString(),
            'memo' => 'Saldo awal kas',
            'lines' => [
                ['account_id' => $cash->id, 'debit' => 1000000, 'credit' => null],
                ['account_id' => $revenue->id, 'debit' => null, 'credit' => 1000000],
            ],
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseCount('journal_entries', 1);
        $this->assertEquals(2, JournalEntry::first()->lines()->count());
    }

    public function test_income_statement_and_balance_sheet()
    {
        $reservation = Reservation::factory()->create();

        Transaction::create([
            'reservation_id' => $reservation->id,
            'type' => 'pelunasan',
            'amount' => 800000,
            'payment_method' => 'cash',
        ]);

        Expense::create([
            'description' => 'Beli ATK',
            'expense_category' => 'atk',
            'amount' => 300000,
            'payment_method' => 'cash',
            'expense_date' => Carbon::today()->toDateString(),
        ]);

        $reports = app(AccountingReportService::class);
        $start = Carbon::today()->toDateString();

        $income = $reports->incomeStatement($start, $start);
        $this->assertEquals(800000.0, $income['total_revenue']);
        $this->assertEquals(300000.0, $income['total_expense']);
        $this->assertEquals(500000.0, $income['net_income']);

        $balance = $reports->balanceSheet($start);
        $this->assertTrue($balance['balanced']);
        $this->assertEquals(500000.0, $balance['total_assets']);

        $trial = $reports->trialBalance($start, $start);
        $this->assertEquals($trial['totals']['ending_debit'], $trial['totals']['ending_credit']);

        $cashFlow = $reports->cashFlow($start, $start);
        $this->assertEquals(800000.0, $cashFlow['totals']['receipts']);
        $this->assertEquals(300000.0, $cashFlow['totals']['payments']);
        $this->assertEquals(500000.0, $cashFlow['totals']['closing']);
    }

    public function test_accounting_pages_load()
    {
        $pages = [
            '/accounting',
            '/accounting/accounts',
            '/accounting/accounts/create',
            '/accounting/journals',
            '/accounting/journals/create',
            '/accounting/ledger',
            '/accounting/reports/trial-balance',
            '/accounting/reports/income-statement',
            '/accounting/reports/balance-sheet',
            '/accounting/reports/cash-flow',
            '/accounting/settings',
            '/accounting/periods',
        ];

        foreach ($pages as $page) {
            $this->get($page)->assertStatus(200);
        }
    }

    public function test_closing_period_blocks_new_journal()
    {
        $this->post(route('accounting.periods.close', Carbon::today()->format('Y-m')))
            ->assertSessionHas('success');

        $cash = Account::byCode('1-1100');
        $revenue = Account::byCode('4-1220');

        $response = $this->post(route('accounting.journals.store'), [
            'date' => Carbon::today()->toDateString(),
            'memo' => 'Setelah tutup buku',
            'lines' => [
                ['account_id' => $cash->id, 'debit' => 1000, 'credit' => null],
                ['account_id' => $revenue->id, 'debit' => null, 'credit' => 1000],
            ],
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseCount('journal_entries', 0);
    }
}

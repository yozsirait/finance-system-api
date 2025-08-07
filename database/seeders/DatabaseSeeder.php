<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\{User, Member, Account, Category, BudgetCategory, Transaction, SavingTarget};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        User::truncate();
        Member::truncate();
        Account::truncate();
        Category::truncate();
        BudgetCategory::truncate();
        Transaction::truncate();
        SavingTarget::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $user = User::create([
            'name' => "YJ's Family",
            'email' => 'yjs_finance@gmail.com',
            'password' => Hash::make('finance123'),
        ]);

        $members = collect([
            'Yosua' => 'Ayah',
            'Juliana' => 'Istri',
            'Paima' => 'Anak',
        ])->map(function ($role, $name) use ($user) {
            return Member::create(['name' => $name, 'user_id' => $user->id]);
        });

        $account = Account::create([
            'user_id' => $user->id,
            'member_id' => $members['Yosua']->id,
            'name' => 'BCA',
            'balance' => 5000000,
        ]);

        $income = Category::create([
            'user_id' => $user->id,
            'name' => 'Gaji',
            'type' => 'income'
        ]);

        $expense = Category::create([
            'user_id' => $user->id,
            'name' => 'Belanja Bulanan',
            'type' => 'expense'
        ]);

        BudgetCategory::create([
            'user_id' => $user->id,
            'category_id' => $expense->id,
            'amount' => 3000000
        ]);

        Transaction::create([
            'user_id' => $user->id,
            'member_id' => $members['Yosua']->id,
            'account_id' => $account->id,
            'category_id' => $income->id,
            'amount' => 10000000,
            'type' => 'income',
            'date' => now()->subDays(3),
            'description' => 'Gaji Bulanan'
        ]);

        Transaction::create([
            'user_id' => $user->id,
            'member_id' => $members['Juliana']->id,
            'account_id' => $account->id,
            'category_id' => $expense->id,
            'amount' => 2500000,
            'type' => 'expense',
            'date' => now()->subDays(1),
            'description' => 'Belanja kebutuhan rumah'
        ]);

        SavingTarget::create([
            'user_id' => $user->id,
            'name' => 'Liburan Akhir Tahun',
            'target_amount' => 10000000,
            'saved_amount' => 2000000,
            'due_date' => now()->addMonths(4)
        ]);
    }
}
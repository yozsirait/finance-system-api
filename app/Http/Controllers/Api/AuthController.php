<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use App\Models\{RecurringTransaction, Transaction};
use Carbon\Carbon;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        return response()->json([
            'message' => 'Registrasi berhasil',
            'user' => $user,
        ], 201);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah'],
            ]);
        }

        // Hapus baris dd($token)
        $token = $user->createToken('auth_token')->plainTextToken;

        // 🟩 Jalankan recurring setelah login
        $recurringExecuted = $this->runRecurringForUser($user);

        return response()->json([
            'message' => 'Login berhasil',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
            'recurring_executed' => $recurringExecuted,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout berhasil']);
    }

    public function user(Request $request)
    {
        return response()->json($request->user());
    }

    private function runRecurringForUser(User $user)
    {
        $today = now()->toDateString();
        $executed = [];

        $recurrings = RecurringTransaction::where('user_id', $user->id)
            ->where('next_date', '<=', $today)
            ->get();

        foreach ($recurrings as $recurring) {
            $transaction = Transaction::create([
                'user_id'     => $recurring->user_id,
                'member_id'   => $recurring->member_id,
                'account_id'  => $recurring->account_id,
                'category_id' => $recurring->category_id,
                'amount'      => $recurring->amount,
                'description' => '[Recurring] ' . $recurring->description,
                'type'        => $recurring->type,
                'date'        => $today,
            ]);

            // Update next_date sesuai recurring_type
            switch ($recurring->recurring_type) {
                case 'daily':
                    $recurring->next_date = now()->addDay();
                    break;
                case 'weekly':
                    $recurring->next_date = now()->addWeek();
                    break;
                case 'monthly':
                    $recurring->next_date = now()->addMonth();
                    break;
            }

            $recurring->save();

            // Tambahkan ke list yang akan dikembalikan
            $executed[] = [
                'category' => $recurring->category->name,
                'amount' => $recurring->amount,
                'type' => $recurring->type,
                'date' => $today,
            ];
        }

        return $executed;
    }
}

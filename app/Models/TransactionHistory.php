<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionHistory extends Model
{
    protected $fillable = ['transaction_id', 'user_id', 'old_data', 'new_data', 'action'];

    protected $casts = [
        'old_data' => 'array',
        'new_data' => 'array',
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

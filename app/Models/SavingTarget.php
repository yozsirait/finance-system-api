<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SavingTarget extends Model
{
    //
    protected $fillable = [
        'user_id',
        'name',
        'target_amount',
        'save_amount',
        'due_date'         // opsional, jika ada
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

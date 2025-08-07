<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class RecurringTransaction extends Model
{
    //
    protected $fillable = [
        'user_id',
        'member_id',
        'account_id',
        'category_id',
        'type',
        'amount',
        'description',
        'recurring_type', // <- ini yang benar
        'next_date',
    ];

    public function calculateNextDate()
    {
        $date = Carbon::parse($this->next_date);

        return match ($this->recurring_type) {
            'daily' => $date->addDay(),
            'weekly' => $date->addWeek(),
            'monthly' => $date->addMonth(),
            default => $date,
        };
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}

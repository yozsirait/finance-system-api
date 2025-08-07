<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    //
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function budgetCategories()
    {
        return $this->hasMany(BudgetCategory::class);
    }
}

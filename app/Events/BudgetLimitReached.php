<?php
namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\BudgetCategory;

class BudgetLimitReached
{
    use Dispatchable, SerializesModels;

    public BudgetCategory $budget;
    public float $percent;

    public function __construct(BudgetCategory $budget, float $percent)
    {
        $this->budget = $budget;
        $this->percent = $percent;
    }
}

<?php
namespace App\Listeners;

use App\Events\BudgetLimitReached;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class SendBudgetAlertNotification implements ShouldQueue
{
    public function handle(BudgetLimitReached $event)
    {
        // Contoh kirim notifikasi, bisa dikembangkan ke email, push, dsb.
        Log::warning("Alert! Budget kategori {$event->budget->category->name} sudah mencapai {$event->percent}% limit.");
        // TODO: Kirim email atau notifikasi lain di sini
    }
}

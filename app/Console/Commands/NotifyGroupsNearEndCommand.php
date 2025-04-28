<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\GroupNotificationController;

class NotifyGroupsNearEndCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'groups:notify-near-end';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notify when course groups are near to end';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        app(GroupNotificationController::class)->notifyGroupsNearEnd();
    }
}

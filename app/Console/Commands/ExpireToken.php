<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Verification;
use App\Models\ForgotPassword;
use Carbon\Carbon;

class ExpireToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:expire-token';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Verification::where('is_used', false)
            ->where('updated_at', '<', Carbon::now()->subMinutes(5))
            ->update(['is_used' => true]);

        ForgotPassword::where('is_used', false)
            ->where('updated_at', '<', Carbon::now()->subMinutes(5))
            ->update(['is_used' => true]);
    }
}

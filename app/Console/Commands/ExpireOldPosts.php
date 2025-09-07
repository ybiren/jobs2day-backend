<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Post;
use App\Models\PostAvailabilitySpecificDates;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ExpireOldPosts extends Command
{
    protected $signature = 'posts:expire';
    protected $description = 'Set post status to 0 when the last availability date has passed.';

    public function handle()
    {
        $today = Carbon::now()->toDateString();

        // Find posts where the latest availability date has passed
        $expiredPosts = Post::whereIn('id', function ($query) use ($today) {
            $query->select('post_id')
                ->from('post_availability_specific_dates')
                ->groupBy('post_id')
                ->havingRaw('MAX(availability_date) < ?', [$today]);
        })->update(['status' => '0']);

        $this->info("Updated {$expiredPosts} posts as expired.");
    }
}

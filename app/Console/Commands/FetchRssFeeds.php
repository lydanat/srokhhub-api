<?php

namespace App\Console\Commands;

use App\Services\RssService;
use Illuminate\Console\Command;

class FetchRssFeeds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'feed:fetch';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch and cache RSS feeds from all external news sources';

    public function __construct(protected RssService $rssService)
    {
        parent::__construct();
    }
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Fetching RSS feeds...');

        $this->rssService->fetchAll();

        $this->info('RSS feeds fetched and cached successfully');

        return Command::SUCCESS;
    }
}

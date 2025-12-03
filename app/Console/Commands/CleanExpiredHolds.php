<?php

namespace App\Console\Commands;

use App\Services\InventoryService;
use Illuminate\Console\Command;

class CleanExpiredHolds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'holds:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';
    protected $inventory;
    public function __construct(InventoryService $inventory)
    {
        parent::__construct();
        $this->inventory = $inventory;
    }
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for expired holds...');

        $holds = Hold::where('status', 'valid')
            ->where('expires_at', '<', now())
            ->get();

        $count = 0;

        foreach ($holds as $hold) {
            $this->inventory->expireHold($hold);
            $count++;
        }

        $this->info("Expired {$count} holds and released stock.");
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RoomAssignment;

class SyncRoomOccupancies extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'room:sync-occupancies';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync room occupancies based on active room assignments';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Syncing room occupancies...');
        
        RoomAssignment::syncAllRoomOccupancies();
        
        $this->info('Room occupancies synced successfully!');
        
        return Command::SUCCESS;
    }
}

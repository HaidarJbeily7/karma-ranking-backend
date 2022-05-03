<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class FillRedis extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'redis:fill';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "fill redis with users' score";

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        ini_set('max_execution_time', '-1');
        $users = User::all();
        $bar = $this->output->createProgressBar(count($users));

        foreach ($users as $user) {
            $user->storeUserScoreToLeaderBoard();
            $user->image_url = $user->image->url;
            $data = $user->toArray();
            unset($data['image_id']);
            unset($data['image']);
            Redis::set((string)$user->id, json_encode($data));
            $bar->advance();
        }
        $bar->finish();
    }
}

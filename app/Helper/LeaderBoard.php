<?php

namespace App\Helper;

use Illuminate\Support\Facades\Redis;

class LeaderBoard{

    private $redis;
    private $set = "leader_board";

    public function __construct(){
        $this->redis = Redis::connection();
    }

    public function storeScore($score, $userID): void
    {
        $this->redis->zAdd($this->set, $score, (string)$userID);
    }

    public function getRank(int $userId): int
    {
        if (!$this->redis->exists($this->set)) {
            $msg = "LeaderBoard seems empty.Execute php artisan redis:fill to seed the data";
            throw new \Exception($msg);
        }
        return $this->redis->zRevRank($this->set, $userId);
    }

    public function getScore(int $userId): string
    {
        return $this->redis->zScore($this->set, $userId);
    }

    public function arroundUser(int $userId, bool $withScores = true)
    {
        $rank = (int)$this->getRank($userId);
        $neighbors = $this->redis->zRevRange($this->set, $rank - 2, $rank + 2, array('withscores' => $withScores));

        return collect($neighbors)->keys();
    }
}


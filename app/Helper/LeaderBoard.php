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

    public function arroundUser(int $userId, int $limit = 4 ,bool $withScores = true)
    {
        $rank = (int)$this->getRank($userId);
        $after_neighbors = $this->redis->zRevRange($this->set, $rank , $rank + $limit, array('withscores' => $withScores));
        $before_neighbors = $this->redis->zRevRange($this->set, ($rank - $limit < 0) ? 0 : $rank - $limit , ($rank - 1 < 0) ? 0 : $rank - 1, array('withscores' => $withScores));

        $neighbors = array_merge(collect($before_neighbors)->keys()->toArray(), collect($after_neighbors)->keys()->toArray());

        return [collect($neighbors)->values(), $rank + 1];
    }
}


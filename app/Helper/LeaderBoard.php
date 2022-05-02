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
            $msg = "LeaderBoard seems empty.Execute php artisan leaderboard:scores to seed the data";
            throw new \Exception($msg);
        }
        return $this->redis->zRevRank($this->set, $userId);
    }

    public function getScore(int $userId): string
    {
        return $this->redis->zScore($this->set, $userId);
    }

    public function arroundUser(int $userId, bool $withScores = true): array
    {
        $rank = (int)$this->getRank($this->user->id);
        $score = $this->getScore($this->user->id);
        $shownAboveCount = 2;
        $shownBelowcount = 2;

        //get before
        $beforeMe =  $this->redis->zRangeByScore($this->set, $score+1, "+inf",  ['WITHSCORES' => $withScores, "LIMIT" => [ "OFFSET" => 0, "COUNT" => $shownAboveCount]] );

        //get me
        $me = [];
        $me[$this->user->id] =  $score;

        //get after
        $afterMeAll = $this->redis->zRevRangeByScore($this->set, $score, 0, ['WITHSCORES' => $withScores]);

        unset($afterMeAll[$userId]);
        $i = 0;
        $afterMe = [];
        foreach ($afterMeAll as $id => $a) {
            if ($i < $shownBelowcount) {
                $afterMe[$id] = $a;
            }
            $i++;
        }
        unset($afterMeAll);
        return  $beforeMe +  $me + $afterMe;
    }
}


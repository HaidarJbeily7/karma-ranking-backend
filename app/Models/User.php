<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Helper\LeaderBoard;
class User extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'username',
        'karma_score',
        'image_id'
    ];

    public function image(){
        return $this->belongsTo(Image::class);
    }

    public function storeUserScoreToLeaderBoard(){
       $lb =  new LeaderBoard();
       $lb->storeScore($this->karma_score, $this->id);
    }
}

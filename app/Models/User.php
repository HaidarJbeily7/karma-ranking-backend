<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

}

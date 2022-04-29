<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UsersController extends Controller
{

    public function getUsersForLeaderboard($id){

        $query = User::query();
        // dd($query->min('karma'))
        // dd($query->where('karma_score', 999991809)->get());
        $user = $query->with('image')->find($id);

        if ($user == false){
            return response('User Not Found', 404);
        }


        $user_position = User::orderBy('karma_score')->where('karma_score', '>', $user->karma_score)->count() + 1;


        $users_lt = User::where('karma_score', '<', $user->karma_score)->orderByDesc('karma_score')->distinct('karma_score')->take(4)->with('image')->get();
        $users_gt = User::where('karma_score', '>', $user->karma_score)->orderBy('karma_score')->take(4)->with('image')->get();
        $data = [];

        $limit = 5;
        $r =  (int) (($limit-1) / 2) + ($limit-1) % 2;
        $reminder = $r - $users_gt->count();
        $reminder = ($reminder<0)?0:$reminder;
        $i = 1;
        foreach($users_lt as $value){
            if($i > $r + $reminder){
                break;
            }
            $value->position = $user_position + $i;
            array_push($data, $value->toArray());
            $i++;
        }
        $data = array_reverse($data);

        $user->position = $user_position;

        array_push($data, $user->toArray());

        $reminder = $r - $users_lt->count();
        $reminder = ($reminder<0)?0:$reminder;

        $i = 1;
        foreach($users_gt as $value){
            if($i > $r + $reminder){
                break;
            }
            $value->position = $user_position - $i;
            $i++;
            array_push($data, $value->toArray());
        }

        $data = array_reverse($data);

        return response()->json($data);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UsersController extends Controller
{

    public function getUsersForLeaderboard2(Request $request, $id){
        ini_set('memory_limit', '-1');
        $limit = $request->limit ?? 5;

        $users = cache('users');

        $message = 'cache';

        if(is_null($users)){
            $message = 'db';
            $users = User::with('image')->orderByDesc('karma_score')->get()->toArray();
            cache(['users' => $users], 30);
        }
        $map = $this->getMappingArray($users);

        $user_position = $map[(string) $id];

        ////////////////
        $lt_arr = [];
        $index = $user_position - 1;
        while($index >= 0 && $user_position - $index <= $limit - 1){
            array_push($lt_arr, $users[(string)$index]);
            $index--;
        }
        $data = [];

        $gt_arr = [];
        $index = $user_position + 1;
        $max = count($users);
        while($index < $max &&  $index - $user_position  <= $limit - 1){
            array_push($gt_arr, $users[(string)$index]);
            $index++;
        }

        //////////////
        $r =  (int) (($limit-1) / 2) + ($limit-1) % 2;
        $reminder = $r - count($gt_arr);
        $reminder = ($reminder<0)?0:$reminder;
        $i = 1;
        foreach($lt_arr as $value){
            if($i > $r + $reminder){
                break;
            }
            $value['position'] = $user_position + $i + 1;
            array_push($data, $value);
            $i++;
        }
        $data = array_reverse($data);

        //////////////

        $user = $users[(string) $user_position];
        $user['position'] = $user_position + 1;
        array_push($data, $user);
        ///////////////
        $reminder = $r - count($lt_arr);
        $reminder = ($reminder<0)?0:$reminder;

        $i = 1;
        foreach($gt_arr as $value){
            if($i > $r + $reminder){
                break;
            }
            $value['position'] = $user_position - $i;
            $i++;
            array_push($data, $value);
        }

        $data = array_reverse($data);


        ///////////




        return response()->json([
            'message' => $message,
            'data' => $data
        ]);
    }

    public function getMappingArray($users){
        $ranks_map = cache('mapping');
        if(is_null($ranks_map)){
            $ranks_map = [];
            foreach ($users as $key => $value) {
                $ranks_map [(string)$value['id']] = $key;
            }
            cache(['mapping' => $ranks_map], 29);
        }
        return $ranks_map;
    }

    /////////////////////////////////////////
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

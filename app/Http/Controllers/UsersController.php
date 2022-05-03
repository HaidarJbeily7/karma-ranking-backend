<?php

namespace App\Http\Controllers;

use App\Helper\LeaderBoard;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Redis;

class UsersController extends Controller
{

    public function getUsersForLeaderboard3(Request $request, $id){

        $limit = $request->limit ?? 5;

        $lb = new LeaderBoard();

        $row = $lb->arroundUser($id, $limit-1);
        $data = $row[0]->toArray();
        $rank = $row[1];
        $data = array_unique($data);
        $size = count($data);
        $new_data = [];

        foreach($data as $item){
            array_push($new_data, $item);
        }
        $data = $new_data;

        $key = array_search($id, $data);
        $start = $key;
        $end = $key;

        while($end - $start + 1 < $limit){
            if($end + 1 == $size && $start - 1 < 0)
                break;

            if($end+1 != $size)
                $end++;

            if($start - 1 >= 0)
                $start--;

        }

        $response = [];
        for ($i=$start; $i <= $end ; $i++) {
            $user_object = collect(json_decode(Redis::get((string)$data[$i])))->toArray();
            $user_object['position'] = $rank - $key + $i;
            array_push($response, $user_object);
        }

        return response()->json([
            'data' => $response
        ]);
    }

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
        // dd($query->max('karma'))
        // dd($query->where('karma_score', 999991809)->get());
        $user = $query->with('image')->find($id);

        if ($user == false){
            return response('User Not Found', 404);
        }


        $user_position = User::orderBy('karma_score')->where('karma_score', '>', $user->karma_score)->count() + 1;


        $users_lt = User::where('karma_score', '<', $user->karma_score)->orderByDesc('karma_score')->take(4)->with('image')->get();
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
            $value->image_url = $value->image->url;
            $d = $value->toArray();
            unset($d['image']);
            unset($d['image_id']);
            array_push($data, $d);
            $i++;
        }
        $data = array_reverse($data);

        $user->position = $user_position;
        $user->image_url = $user->image->url;
        $d = $user->toArray();
        unset($d['image']);
        unset($d['image_id']);
        array_push($data, $d);

        $reminder = $r - $users_lt->count();
        $reminder = ($reminder<0)?0:$reminder;

        $i = 1;
        foreach($users_gt as $value){
            if($i > $r + $reminder){
                break;
            }
            $value->position = $user_position - $i;
            $i++;
            $value->image_url = $value->image->url;
            $d = $value->toArray();
            unset($d['image']);
            unset($d['image_id']);
            array_push($data, $d);
        }

        $data = array_reverse($data);

        return response()->json($data);
    }
}

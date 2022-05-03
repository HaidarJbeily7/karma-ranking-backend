<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class GetLeaderBoardTest extends TestCase
{

    public function test_v3_api_if_is_working()
    {
        $number = rand(1,100000);
        $url = "/api/v3/user/{$number}/karma-position";
        $response = $this->get($url);

        $response->assertStatus(200);

    }

    public function test_v3_api_response_depending_on_v1_api()
    {
        $iteration = 5;
        $i = 0;
        $check = true;
        $response = null;
        while($i < $iteration)
        {
            $number = rand(1,100000);
            $url_v3 = "/api/v3/user/{$number}/karma-position";
            $url_v1 = "/api/v1/user/{$number}/karma-position";

            $response = $this->get($url_v3);
            $data1 = $response->json()['data'];

            $response = $this->get($url_v1);
            $data2 =  $response->json();

            for($i = 0; $i < count($data1); $i++){
                if($data1[$i]['id'] === $data2[$i]['id'])
                    if ($data1[$i]['position'] === $data2[$i]['position'])
                        continue;
                $check = false;
                break;
            }
        }

        return $this->assertTrue($check);
    }

    public function test_v3_api_users_position_depending_on_db_queries()
    {
        $iteration = 5;
        $i = 0;
        $check = true;
        $response = null;
        while($i < $iteration)
        {
            $number = rand(1,100000);
            $url_v3 = "/api/v3/user/{$number}/karma-position";

            $response = $this->get($url_v3);
            $data = $response->json()['data'];

            for($i = 0; $i < count($data); $i++){
                $checked_count = User::orderByDesc('karma_score')->where('karma_score', '>', $data[$i]['karma_score'])->count() + 1;
                if( $data[$i]['position'] === $checked_count)
                        continue;
                $check = false;
                break;
            }
        }

        return $this->assertTrue($check);
    }
}

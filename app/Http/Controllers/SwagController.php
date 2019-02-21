<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\User;

class SwagController extends Controller
{
    public function index()
    {
        echo phpinfo();
    }

    public function query()
    {
        $result = DB::select("select * from users");
        // var_dump($result);

        $user = new User();
        $user->name = 'Pahom';
        $user->email = 'green_elephant@gmail.com';
        $user->password = '12345';
        $user->save();

        $users = User::all();
        var_dump($users);
    }
}

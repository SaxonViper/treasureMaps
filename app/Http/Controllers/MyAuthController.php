<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\User;

class MyAuthController extends Controller
{
    //

    public function login()
    {
        $request = Request::capture();
        $email = $request->get('email');
        $password = $request->get('password');
        $remember = (bool)$request->get('remember');

        // Авторизация
        $result = Auth::attempt([
            'email' => $email,
            'password' => $password,
        ], $remember);

        // Если прошла , получим юзера
        if ($result) {
            $result = Auth::user();
        }
        return [
            'result' => $result
        ];
    }

    public function checkCurrentUser()
    {
        // Пробуем получить юзера?
        return [
            'result' => Auth::user()
        ];
    }

    public function logout()
    {
        Auth::logout();
        return [
            'result' => true
        ];
    }

    public function register()
    {
        $request = Request::capture();
        $email = $request->get('email');
        $password = $request->get('password');
        $confirm_password = $request->get('confirm_password');
        $remember = (bool)$request->get('remember');

        $this->registerValidator($request->all())->validate();
        $user = $this->create($request->all());

        if ($user) {
            Auth::login($user);
        }

        return ['result' => $user];
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function registerValidator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:5', 'confirmed'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
    }
}

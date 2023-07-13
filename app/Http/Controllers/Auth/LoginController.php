<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

     public $isLogined = false;

    // ログイン処理
    public function login(LoginRequest $request)
    {
        $status = 200;
        $credentials = $request->validated();

        $user = User::where('name', $request->name)->first();
        // 登録されているハッシュ化されたパスワードと入力したパスワードが一致しているかチェック
        $check_password = Hash::check($request->password, $user->password);
        if(!$check_password){
            $msg = ['password'=>'パスワードが間違っています'];
            return response()->json(['status_code' => $status,'errors' => $msg], 401);
        }

        if(Auth::guard('web')->attempt($credentials)){
            $request->session()->regenerate();
            return response()->json(['status_code' => $status,'message' => 'ログインしました'], 200);
        };
    }

    // ログアウト処理
    public function logout(Request $request)
    {
        Auth::guard('web')->logout();

        return response()->json([
            'status_code' => 200,
            'message' => 'ログアウトしました'
        ], 200);
    }

    // ソーシャルログイン処理
    public function getProviderOAuthURL(string $provider)
    {
        $redirectUrl = Socialite::driver($provider)->redirect()->getTargetUrl();

        return response()->json([
            'redirectUrl' => $redirectUrl
        ]);
    }

    public function handleProviderCallback(Request $request, string $provider)
    {
        $providerUser = Socialite::driver($provider)->stateless()->user();

        $user = User::where('email', $providerUser->getEmail())->first();

        if ($user) {
            Auth::guard()->login($user, true);
            return response()->json([
                'user' => $user
            ]);
        }else{
            return response()->json([
                'provider' => $provider,
                'email' => $providerUser->getEmail(),
                'token' => $providerUser->token,
            ]);
        }
    }
}

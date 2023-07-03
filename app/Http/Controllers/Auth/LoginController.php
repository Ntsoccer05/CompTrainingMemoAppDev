<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

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
    public function login(Request $request)
    {
        // フォーム入力情報のエラーチェック
        $credentials = $request->validate([
            'name' => 'required',
            'email' => 'nullable|email',
            'password' => 'nullable'
        ]);

        // フォーム入力内容のユーザ取得
        $user = User::where('name', $request->name)->first();
        if(!$user){
            return response()->json([
                'status_code' => 401,
                'message' => 'ユーザー登録されていません'
            ]);
        }

        if(Auth::attempt($credentials)){
            $request->session()->regenerate();
            return response()->json(['status_code' => 200,'message' => 'ログインしました'],200);
        } else {
            return response()->json(['status_code' => 500,'message' => '正しく入力してください'],200);
        }
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
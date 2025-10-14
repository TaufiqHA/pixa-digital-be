<?php

namespace App\Http\Controllers\authentications;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginBasic extends Controller
{
  public function index()
  {
    return view('content.authentications.auth-login-basic');
  }

  public function login(Request $request)
  {
    $credentials = $request->validate([
      'email' => 'required|email',
      'password' => 'required',
    ]);

    if (Auth::attempt($credentials)) {
      $request->session()->regenerate();

      if(!Auth::user()->is_admin)
      {
        Auth::logout();

        return back()->withErrors([
          'email' => 'You do not have admin access.',
        ])->withInput($request->only('email'));
      }

      return redirect()->intended('/');
    }

    return back()->withErrors([
      'email' => 'The provided credentials do not match our records.',
    ])->withInput($request->only('email'));
  }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class LoginController extends Controller
{
    //

    public function index(){
        return view('login'); 
    }

    public function cekLogin(Request $request){
        $request->validate([
            'username'=> 'required',
            'password'=>'required'
        ]);

        $login = [
            'username' => $request->username,
            'password' => $request->password
        ];
        // dd($login);
        if (Auth::attempt($login)) {
            $user = Auth::user();

            if ($user->role === 'admin') {
                return redirect()->route('admin.profile')->with('login success', $user->name);
            } elseif ($user->role === 'manager') {
                return redirect()->route('manager.profile')->with('login success', $user->name);
            } elseif ($user->role === 'karyawan') {
                return redirect()->route('karyawan.profile')->with('login success', $user->name);
            } else {
                return redirect()->route('login')->with('role failed', 'Role tidak valid');
            }
        } else {
            return redirect()->route('login')->with('login failed', 'username atau password salah');
        }
    }

    public function logout(){
        auth::logout();
        return redirect()->route('login')->with('logout success','Anda berhasil logout');
    }
}

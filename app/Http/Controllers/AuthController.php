<?php

namespace App\Http\Controllers;
 
use Illuminate\Http\Request;
use Validator,Redirect,Response;
Use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Session;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Contracts\Auth\Guard;
 
use App\Repositories\AuthRepository;

class AuthController extends Controller
{

    use AuthenticatesUsers;

    public function __construct()
    {
        //$this->repository = new AuthRepository();
    }

    public function index()
    {
        return view('auth.login');
    }  
 
    public function registration()
    {
        return view('registration');
    }
     
    public function postLogin(Request $request)
    {
        request()->validate([
        'loginname' => 'required',
        'senha' => 'required',
        ]);
        $credentials = ['loginname' => $request->loginname, 'password' => $request->senha];

        $ret = Auth::attempt($credentials);
        //dd($ret);
        if ($ret) {
            return redirect()->to('/notas');
        }
        return redirect()->back()->withErrors(['Usuário ou senha invalidos']);
    }
 
    public function postRegistration(Request $request)
    {  
        request()->validate([
        'name' => 'required',
        'email' => 'required|email|unique:users',
        'password' => 'required|min:6',
        ]);
         
        $data = $request->all();
 
        $check = $this->create($data);
       
        return Redirect::to("dashboard")->withSuccess('Great! You have Successfully loggedin');
    }
     
    public function dashboard()
    {
 
      if(Auth::check()){
        return view('dashboard');
      }
       return Redirect::to("login")->withSuccess('Opps! You do not have access');
    }
 
    public function create(array $data)
    {
      return User::create([
        'name' => $data['name'],
        'email' => $data['email'],
        'password' => Hash::make($data['password'])
      ]);
    }
     
    public function logout() {
        Session::flush();
        Auth::logout();
        return Redirect('login');
    }
}
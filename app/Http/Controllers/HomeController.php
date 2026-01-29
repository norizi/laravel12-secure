<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class HomeController extends Controller
{
    
    public function __construct()
    {
        $this->middleware('auth');
    } 
    public function index()
    {
        return view('home');
    }

    public function users()
    {
        $users = User::all();
        return view('users', compact('users'));
    }

    public function destroy($id)
    {
        User::findOrFail($id)->delete();

        return redirect()->back()->with('success', 'Data berjaya dipadam');
    }
}

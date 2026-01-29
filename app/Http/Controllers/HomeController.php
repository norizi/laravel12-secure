<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Validation\Rules\Password;

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

     

    public function destroyb($encryptid)
    {
        $id = decrypt($encryptid);
        User::findOrFail($id)->delete();

        Log::info('User delete triggered', [
            'user_id' => $id
        ]);

        return redirect()->back()->with('success', 'Data berjaya dipadam');
    }

    public function destroya($encryptid)
    {
        $id = decrypt($encryptid);
        User::findOrFail($id)->delete();

        Log::info('User delete triggered', [
            'user_id' => $id
        ]);

        return redirect()->back()->with('success', 'Data berjaya dipadam');
    }

    public function store(Request $request)
    {
        // 1. Validasi data yang diterima
    $request->validate([
        'name' => 'required|string|max:255',
        //'email' => 'required|string|email|max:255|unique:users',
        /* 'password' => [
        'required',
        //'confirmed',
        Password::min(8)
            //->letters()   // Mesti ada sekurang-kurangnya satu huruf
            //->mixedCase() // Mesti ada huruf besar & kecil
            ->numbers()   // Mesti ada nombor
            //->symbols()   // Mesti ada simbol (!@#$%^&*)
            //->uncompromised(), // Semak jika password pernah bocor dalam data breach!
    ],*/
    ]);

    // 2. Simpan data ke dalam table users
    User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password), // Jangan lupa encrypt password!
    ]);
         
        return redirect()->back()->with('success', 'Data berjaya dipadam');
    }


    public function update(Request $request)
    {

        // 1. Validasi data yang diterima
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255',
            ]); 
            /*
            // 2. Kemaskini data ke dalam table users
            User::where('id', $request->id)->update([
                'name' => $request->name,
                'email' => $request->email,         
            ]);

            */

            // 2. Cari Model User dahulu (PENTING)
            // Gunakan findOrFail supaya keluar error 404 jika id tiada
            $user = User::findOrFail($request->id); 

            // 3. Kemaskini menggunakan instance model
            // Cara ini akan trigger event 'updated' dan Trait Auditable akan berfungsi
            $user->update([
                'name' => $request->name,
                'email' => $request->email,        
            ]);

        return redirect()->back()->with('success', 'Data berjaya dikemaskini'); 
    }   
}

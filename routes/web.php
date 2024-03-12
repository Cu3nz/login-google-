<?php

use App\Http\Controllers\ProfileController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});


Route::get('/google-auth/redirect', function () {
    return Socialite::driver('google')->redirect();
});
 
Route::get('/google-auth/callback', function () {
    $user_google = Socialite::driver('google')->stateless()->user();

    //dd($user_google);
    //todo Busca al usuario por su correo electrónico, saca el primero que encuentre gracias a firtst
    $user = User::where('email', $user_google->email)->first();


    if ($user) {
        //? Si el usuario ya existe, actualiza su google_id y avatar si fuese necesario
        $user->update([
            'google_id' => $user_google->id,
            
        ]);
    } else {
        //? Si el usuario no existe, crea uno nuevo
        $user = User::create([
            'google_id' => $user_google->id,
            'name' => $user_google->name,
            'email' => $user_google->email,
        ]);
    }

    Auth::login($user);

    return redirect('/dashboard');
});


Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

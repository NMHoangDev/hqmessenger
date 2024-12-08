<?php

use App\Http\Controllers\MessengerController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserProfileController;
use Illuminate\Support\Facades\Route;

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

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    // Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';

Route::group(['middleware' => 'auth'], function () {
    Route::get('messenger', [MessengerController::class, 'index'])->name("home");
    Route::post('profile', [UserProfileController::class, 'update'])->name("profile.update");
    Route::get("messenger/search", [MessengerController::class, "search"])->name("messenger.search");
    // fetch id data of user
    Route::get("messenger/id-info", [MessengerController::class, "fetchIdInfo"])->name("messenger.id-info");
    // send message
    Route::post("messenger/send-message", [MessengerController::class, "sendMessage"])->name("messenger.send-message");
    //   fetch messages routes
    Route::get("messenger/fetch-message", [MessengerController::class, "fetchMessages"])->name("messenger.fetch-messages");
    //  get contact routes
    Route::get("messenger/fetch-contacts", [MessengerController::class, "getContacts"])->name("messenger.fetch-contacts");
    // make seen routes
    Route::get("messenger/make-seen", [MessengerController::class, "makeSeen"])->name("messenger.make-seen");
    // set favorite routes
    Route::post("messenger/favorite", [MessengerController::class, "favorite"])->name("messenger.favorite");
    // fetch favorite routes
    Route::get("messenger/fetch-favorite", [MessengerController::class, "fetchFavoritesList"])->name("messenger.fetch-favorite");
    Route::delete("messenger/delete-message", [MessengerController::class, "deleteMessage"])->name("messenger.delete-message");
});

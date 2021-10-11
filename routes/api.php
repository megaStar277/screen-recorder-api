<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\File;
use App\Notifications\SendFile;
use Laravel\Socialite\Facades\Socialite;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/email-file', function (Request $request) {
  $uploadedFile = $request->video;
  $file = File::firstOrCreate([
    'name' => $uploadedFile->getClientOriginalName(),
    'mime_type' => $uploadedFile->getClientMimeType(),
    'size' => $uploadedFile->getSize()
  ]);
  $file->email = $request->email;
  $file->save();
  $file->notify(new SendFile($uploadedFile));
  return response()->json($file);
});

Route::post('/upload-file-data', function (Request $request) {
  $uploadedFile = $request->video;
  $file = File::Create([
    'name' => $uploadedFile->getClientOriginalName(),
    'mime_type' => $uploadedFile->getClientMimeType(),
    'size' => $uploadedFile->getSize(),
  ]);
  return response()->json($file);
});

Route::get('/get-stats', function (Request $request) {
  $size = File::all()->sum('size');
  return response()->json($size);
});

Route::get('/login/youtube', function (Request $request) {
  return Socialite::driver('youtube')->stateless()->redirect();
});

Route::get('/callback/youtube', function (Request $request) {
  $user = Socialite::driver('youtube')->stateless()->user();
  return redirect(env('RECORDER_URL').'/#/success?token='.$user->token);
});

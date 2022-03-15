<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\File;
use App\Notifications\SendFile;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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
  $path = Storage::putFile('videos', $uploadedFile);
  $file = File::Create([
    'name' => $uploadedFile->getClientOriginalName(),
    'mime_type' => $uploadedFile->getClientMimeType(),
    'size' => $uploadedFile->getSize(),
    'path' => Storage::url($path)
  ]);
  return response()->json($file);
});

Route::get('/video/{file}', function (File $file) {
  return response()->json($file);
});

Route::get('/video', function (Request $request) {
  return response()->json(File::all());
});

Route::post('/video', function (Video $video) {
  return response()->json($video);
});

Route::get('/get-stats', function (Request $request) {
  $size = File::all()->sum('size');
  return response()->json($size);
});

Route::get('/login/youtube', function (Request $request) {
  return Socialite::driver('youtube')->scopes(['https://www.googleapis.com/auth/youtube', 'https://www.googleapis.com/auth/youtube.upload', 'https://www.googleapis.com/auth/youtube.readonly', 'https://www.googleapis.com/auth/drive', 'https://www.googleapis.com/auth/drive.metadata', 'https://www.googleapis.com/auth/drive.metadata.readonly'])->stateless()->redirect();
});

Route::get('/callback/youtube', function (Request $request) {
  $user = Socialite::driver('youtube')->stateless()->user();
  return redirect(env('RECORDER_URL').'/#/success?token='.$user->token);
});

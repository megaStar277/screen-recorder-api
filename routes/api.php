<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\File;
use App\Notifications\SendFile;
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
  $file = File::Create([
    'name' => $uploadedFile->getClientOriginalName(),
    'mime_type' => $uploadedFile->getClientMimeType(),
    'size' => $uploadedFile->getSize(),
    'email' => $request->email
  ]);
  $file->notify(new SendFile($uploadedFile));
  return response()->json($file);
});

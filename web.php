<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [\App\Http\Controllers\Admin\MainController::class, 'index'])->name('admin.home');
//Route::get('/version', [\App\Http\Controllers\Admin\MainController::class, 'version'])->name('admin.version');
Route::get('/logger', [\App\Http\Controllers\Admin\MainController::class, 'logger'])->name('admin.logger');
Route::get('/topten', [\App\Http\Controllers\Admin\MainController::class, 'topten'])->name('admin.topten');
Route::get('/city_requests', [\App\Http\Controllers\Admin\MainController::class, 'city_requests'])->name('admin.city_requests');
Route::get('/newhome', [\App\Http\Controllers\Admin\MainController::class, 'index']);

Route::group(['prefix' => '/day'], function() {

    Route::get('/', [\App\Http\Controllers\Web\DayController::class, 'index'])
        ->name('web.day.list');

    Route::get('/start', [\App\Http\Controllers\Web\DayController::class, 'start'])
        ->where('id', '[0-9]+')
        ->name('web.day.start');

    Route::get('/detail/{day}', [\App\Http\Controllers\Web\DayController::class, 'detail'])
        ->where('id', '[0-9]+')
        ->name('web.day.detail');

});

Route::any('/endpoint', [\App\Http\Controllers\Web\StatController::class, 'endpoint'])->name('web.endpoint');



Route::group(['prefix' => '/setting'], function() {

    Route::get('/', [\App\Http\Controllers\Admin\SettingController::class, 'setting'])
        ->name('admin.setting.setting');

    Route::post('/', [\App\Http\Controllers\Admin\SettingController::class, 'change_version'])
        ->name('admin.setting.change_version');
});


Route::get('/test', function(){

     \App\Model\Hit::chunk(100, function ($hits) {
        foreach ($hits as $hit) {
            if( $hit->log != null){
                $log = $hit->log;
                $log = json_decode($log, true);
                if(isset($log['lang']) && $log['lang']!= null ){

//                    dd($log['lang']);
                    $hit->lang = $log['lang'];
                    $hit->save();
                }
            }
        }
    });
    echo "end";
});


Route::get('/test_request', function(){

    \App\Http\Controllers\Admin\KonaarkaController::get_city_requests();
});

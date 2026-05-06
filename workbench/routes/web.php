<?php

use Illuminate\Support\Facades\Route;

use Workbench\App\Models\SampleRecord;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/vitals-test', function () {
    SampleRecord::create(['name' => 'a']);
    SampleRecord::create(['name' => 'b']);
    SampleRecord::create(['name' => 'c']);
    $records = SampleRecord::query()->orderBy('id')->get();
    return view('vitals-test', ['records' => $records]);
})->name('vitals-test');

<?php

use Illuminate\Support\Facades\Route;
use App\Filament\Resources\IncomeResource;

Route::get('/', function () {
    return redirect('/admin');
});
// Route::get('/template', function () {
//     return view('pdf.report');
// });
// Route::get('/test-arrears/{userId}/{category}', function ($userId, $category) {
//     // Call the arrears calculation method
//     $arrears = IncomeResource::calculateArrears($userId, $category);
//     dd($arrears);
// });

<?php

use Illuminate\Http\Request;

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

Route::middleware('api')->group(function () {
    Route::get('/book/get', 'BooksController@getBooks')->name('book.get');
    Route::post('/book/save', 'BooksController@saveBook')->name('book.save');
    Route::delete('/book/delete/{id}', 'BooksController@deleteBook')->name('book.delete');
    Route::get('/book/exportcsv', 'BooksController@exportDataToCsv')->name('book.export.csv');
    Route::get('/book/exportxml', 'BooksController@exportDataToXml')->name('book.export.xml');
});

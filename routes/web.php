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



 Route::get('notas','NotaController@index')->name('notas.index');

//enissao das notas
 Route::get('notas/emitir','NotaController@emitir')->name('notas.emitir');
 Route::post('notas/enviar','NotaController@enviar')->name('notas.enviar');


//consulta de notas emitidas e aprovadas
Route::get('notas/preconsultarnotas','NotaController@preConsultarNotas')->name('notas.pre-consultarnota');
Route::post('notas/consultarnotas','NotaController@consultarNotas')->name('notas.consultarnotas');



 Route::post('notas/destroy','NotaController@destroy')->name('notas.destroy');
 Route::get('notas/show','NotaController@enviar')->name('notas.show');
 Route::get('notas/edit','NotaController@enviar')->name('notas.edit');



 Route::get('notas/danfse','NotaController@danfse')->name('notas.danfse');
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


//autenticação
Route::get('login', 'AuthController@index')->name('login');
Route::post('post-login', 'AuthController@postLogin'); 
Route::get('registration', 'AuthController@registration');
Route::post('post-registration', 'AuthController@postRegistration'); 
Route::get('dashboard', 'AuthController@dashboard'); 
Route::get('logout', 'AuthController@logout');


Route::get('/notas','NotaController@index')->name('notas.index');

//enissao das notas
 Route::get('notas/preemitir/{idcliente}/','NotaController@preEmitir')->name('notas.preemitir');
 Route::post('notas/posemitir','NotaController@posEmitir')->name('notas.posemitir');

//consulta de notas emitidas e aprovadas
Route::get('notas/preconsultarnfse','NotaController@preConsultarNfse')->name('notas.preconsultarnfse');
Route::post('notas/posconsultarnfse','NotaController@posConsultarNfse')->name('notas.posconsultarnfse');
Route::get('notas/imprimirnfse/{numnota}/{codigo}/','NotaController@imprimirNfse')->name('notas.imprimirNfse');


Route::get('notas/preconsnotasemitidas','NotaController@preConsultarNotasEmitidas')->name('notas.preconsnotasemitidas');
Route::post('notas/posconsnotasemitidas','NotaController@posConsultarNotasEmitidas')->name('notas.posconsnotasemitidas');


Route::get('notas/cancelarnota','NotaController@cancelarNota')->name('notas.cancelarnota');


//faturamento
Route::get('faturamento','FaturamentoController@index')->name('faturamento.index');

Route::get('faturamento/preconsultarfaturamento/{tipo}/','FaturamentoController@preConsultarFaturamento')->name('notas.preconsultarfaturamento');

Route::post('faturamento/posconsultarfaturamento/{tipo}/','FaturamentoController@posConsultarFaturamento')->name('notas.posconsultarfaturamento');
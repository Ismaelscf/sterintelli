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
 Route::get('notas/preemitir/{idcliente}/','NotaController@preEmitir')->name('notas.preemitir');
 Route::post('notas/posemitir','NotaController@posEmitir')->name('notas.posemitir');


//consulta de notas emitidas e aprovadas
Route::get('notas/preconsultarnotas','NotaController@preConsultarNotas')->name('notas.pre-consultarnota');
Route::post('notas/consultarnotas','NotaController@posConsultarNotas')->name('notas.consultarnotas');

Route::get('notas/consultarnfse/{numnota}/{codigo}/','NotaController@consultarNfse')->name('notas.consultarnfse');


Route::get('notas/cancelarnota','NotaController@cancelarNota')->name('notas.cancelarnota');

Route::get('notas/danfse','NotaController@danfse')->name('notas.danfse');


//faturamento


Route::get('faturamento','FaturamentoController@index')->name('faturamento.index');

Route::get('faturamento/preconsultarfaturamento/{tipo}/','FaturamentoController@preConsultarFaturamento')->name('notas.preconsultarfaturamento');

Route::post('faturamento/posconsultarfaturamento/{tipo}/','FaturamentoController@posConsultarFaturamento')->name('notas.posconsultarfaturamento');
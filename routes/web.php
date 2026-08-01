<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BoletoController;
use App\Http\Controllers\CadastrosController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\FaturamentoController;
use App\Http\Controllers\NotaController;
use App\Http\Controllers\ItauBoletoController;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\CheckAuthentication;



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

Route::get('/', [AuthController::class, 'index']);


//autenticação
Route::get('/login', [AuthController::class, 'index'])->name('login');
Route::post('/post-login', [AuthController::class, 'postLogin']);
Route::get('/registration', [AuthController::class, 'registration']);
Route::post('/post-registration', [AuthController::class, 'postRegistration']);
Route::get('/dashboard', [AuthController::class, 'dashboard']);
Route::get('/logout', [AuthController::class, 'logout']);

Route::middleware([CheckAuthentication::class])->group(function () {

    Route::get('/notas', [NotaController::class, 'index'])->name('notas.index');

    //emissao das notas
    Route::get('/notas/preemitir/{idcliente}/', [NotaController::class, 'preEmitir'])->name('notas.preemitir');
    Route::post('/notas/posemitir', [NotaController::class, 'posEmitir'])->name('notas.posemitir');

    //consulta de notas emitidas e aprovadas
    Route::get('/notas/preconsultarnfse', [NotaController::class, 'preConsultarNfse'])->name('notas.preconsultarnfse');
    Route::post('/notas/posconsultarnfse', [NotaController::class, 'posConsultarNfse'])->name('notas.posconsultarnfse');
    Route::get('/notas/imprimirnfse/{numnota}/{codigo}/', [NotaController::class, 'imprimirNfse'])->name('notas.imprimirNfse');

    Route::get('/notas/preconsnotasemitidas', [NotaController::class, 'preConsultarNotasEmitidas'])->name('notas.preconsnotasemitidas');
    Route::post('/notas/posconsnotasemitidas', [NotaController::class, 'posConsultarNotasEmitidas'])->name('notas.posconsnotasemitidas');

    Route::get('/notas/preeditarnota/{idcliente}/{idnota}/', [NotaController::class, 'preEditarNota'])->name('notas.preeditarnota');
    Route::post('/notas/poseditarnota', [NotaController::class, 'posEditarNota'])->name('notas.poseditarnota');

    Route::get('/notas/cancelarnota', [NotaController::class, 'cancelarNota'])->name('notas.cancelarnota');

    //faturamento
    Route::get('/faturamento', [FaturamentoController::class, 'index'])->name('faturamento.index');
    Route::get('/faturamento/preconsultarfaturamento/{tipo}/', [FaturamentoController::class, 'preConsultarFaturamento'])->name('faturamento.preconsultarfaturamento');
    Route::post('/faturamento/posconsultarfaturamento/{tipo}/', [FaturamentoController::class, 'posConsultarFaturamento'])->name('faturamento.posconsultarfaturamento');
    Route::get('/faturamento/detalharfaturamento/{idcliente}/', [FaturamentoController::class, 'detalharFaturamento'])->name('faturamento.detalhefaturamento');
    Route::get('/faturamento/imprimirfaturamento/{idcliente}/', [FaturamentoController::class, 'imprimirFaturamento'])->name('faturamento.imprimirFaturamento');

    //clientes
    Route::get('/clientes', [CadastrosController::class, 'indexClientes'])->name('cadastros.indexclientes');
    Route::get('/clientes/preeditarcliente/{tipo}', [CadastrosController::class, 'preEditarCliente'])->name('cadastros.preeditarcliente');
    Route::post('/clientes/poseditarcliente/{tipo}', [CadastrosController::class, 'posEditarCliente'])->name('cadastros.poseditarcliente');
    Route::post('/clientes/posdeletarcliente', [CadastrosController::class, 'posDeletarCliente'])->name('cadastros.posdeletarcliente');

    Route::post('/encapsule', [NotaController::class, 'encapsule'])->name('nota.encapsule');

    // Route::get('/boleto/posemitir/{codcliente}/{numnota}/', [BoletoController::class, 'posEmitir'])->name('boleto.posemitir');
    // Route::get('/boleto/preconsboletosemitidos', [BoletoController::class, 'preConsultarBoletosEmitidos'])->name('boleto.preconsboletosemitidos');
    // Route::post('/boleto/posconsboletosemitidos', [BoletoController::class, 'posConsultarBoletosEmitidos'])->name('boleto.posconsboletosemitidos');

    //Boleto
    // Route::get('/boleto/{numnota}', [ItauBoletoController::class, 'index'])->name('boleto.index');
    Route::get('/boleto/{codcliente}/{numnota}/', [ItauBoletoController::class, 'index'])->name('boleto.index');
    Route::post('/boleto/gerar', [ItauBoletoController::class, 'gerarBoleto'])->name('boleto.gerar');
    Route::get('/boleto/{nossoNumero}', [ItauBoletoController::class, 'consultarBoleto'])->name('boleto.consultar');
    Route::put('/boleto/{nossoNumero}', [ItauBoletoController::class, 'instruirBoleto'])->name('boleto.instruir');
    Route::post('/boleto/posconsboletosemitidos', [ItauBoletoController::class, 'posConsultarBoletosEmitidos'])->name('boleto.posconsboletosemitidos');
    Route::post('/boleto/alterar-vencimento', [ItauBoletoController::class, 'alterarVencimento'])->name('boleto.alterarVencimento');

    Route::get('/cobranca', [NotaController::class, 'cobranca'])->name('cobranca');
    Route::get('/detalhesEmail/{codigo}', [NotaController::class, 'detalhesEmail'])->name('detalhesEmail');
});

Route::prefix('/clientes')->name('clientes.')->group(function () {
    Route::get('/', [ClienteController::class, 'index'])->name('index');
    Route::post('/login', [ClienteController::class, 'login'])->name('login');
    Route::get('/dashboard', [ClienteController::class, 'dashboard'])->name('dashboard');
    Route::get('/logout', [ClienteController::class, 'logout'])->name('logout');
    Route::get('/buscafaturas/{id}', [ClienteController::class, 'buscafaturas'])->name('buscafaturas');
});

Route::get('/boletos/ajax/{numnota}', [ClienteController::class, 'ajaxPorNota'])->name('boletos.ajaxPorNota');
Route::get('/boletos/ajax/{numnota}', [ItauBoletoController::class, 'ajaxBoletos'])->name('boletos.ajax');




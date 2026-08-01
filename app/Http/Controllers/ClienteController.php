<?php

namespace App\Http\Controllers;

use App\Model\Cliente;
use App\Repositories\NotaFiscalRepository;
use App\Services\ItauBoletoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClienteController extends Controller
{

    protected $itauBoletoService;
    protected $notaRepository;

    public function __construct(ItauBoletoService $itauBoletoService, NotaFiscalRepository $notaRepository)
    {
        $this->itauBoletoService = $itauBoletoService;
        $this->notaRepository = $notaRepository;
    }

    public function index(){
        return view('clientes.index');
    }

    public function login(Request $request){
        $cliente = Cliente::where('cnpj', $request->login)
                  ->where('senha', $request->password)
                  ->get();

        if ($cliente) {
            session()->put('cliente_login', $cliente[0]->cnpj);

            return redirect()->route('clientes.dashboard'); 
        } else {
            return back()->with('error', 'Credenciais inválidas.');
        }

    }

    public function dashboard()
    {
        if (!session()->has('cliente_login')) {
            return redirect()->route('clientes.index')->with('error', 'Faça login para acessar o sistema.');
        }

        $cnpj = session('cliente_login');

        $clientes = Cliente::where('cnpj', $cnpj)->get();

        return view('clientes.dashboard', compact('clientes'));
    }

    public function buscafaturas($id)
    {
        $cliente = Cliente::findOrFail($id);

        $faturas = DB::select("
            SELECT
                TO_CHAR(n.DATAESTE, 'MM/YYYY') AS mes_ano,
                c.nome AS razao,
                c.fantasia,
                n.clicod,
                fn_busca_nfse(n.clicod, TRUNC(n.DATAESTE, 'MM'), LAST_DAY(n.DATAESTE)) AS caminho,
                c.email,
                SUM(n.qtd) AS qtd_total,
                TO_CHAR(SUM(n.totaldesc), '99G999G990D99') AS total_desc,
                TO_CHAR(SUM(n.transporte), '99G999G990D99') AS transporte,
                TO_CHAR(SUM(n.totald), '99G999G990D99') AS total_geral
            FROM
                vie_nota_detalhe n
            INNER JOIN
                clientes c ON n.clicod = c.codigo
            WHERE
                n.clicod = :clicod
            GROUP BY
                TO_CHAR(n.DATAESTE, 'MM/YYYY'),
                TRUNC(n.DATAESTE, 'MM'),
                LAST_DAY(n.DATAESTE),
                c.nome,
                c.fantasia,
                n.clicod,
                c.email
            ORDER BY
                TO_DATE(TO_CHAR(n.DATAESTE, 'MM/YYYY'), 'MM/YYYY') DESC
        ", ['clicod' => $cliente->codigo]);
        return view('clientes.busca', compact('cliente', 'faturas'));
    }


    public function ajaxPorNota($numnota)
    {
        $boletos = $this->itauBoletoService->consultarBoletosporNF($numnota);
        return response()->json($boletos);
    }

    public function getBoletosAjax($numnota)
    {
        $dadosNota = $this->notaRepository->buscarNF($numnota);
        $boletos = $this->itauBoletoService->consultarBoletosporNF($numnota);
        $nossoNumero = $this->itauBoletoService->getLastNumero();

        // Retorna apenas a partial para AJAX
        return view('boletos.partials.lista', compact('boletos', 'dadosNota', 'nossoNumero'));
    }

    public function logout()
    {
        session()->forget(['cliente_login', 'cliente_nome', 'cliente_id']);
        
        return redirect()->route('clientes.index')->with('success', 'Logout realizado com sucesso.');
    }

}

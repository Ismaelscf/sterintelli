<?php
namespace App\Model\Boleto\Itens;

class Juros {

	private $tipo_juros;
	private $percentual_juros;



	public function getTipoJuros() {
		return $this->tipo_juros;
	}

	public function setTipoJuros($tipo_juros) {
		$this->tipo_juros = $tipo_juros;
	}

	public function getPercentualJuros() {
		return $this->percentual_juros;
	}

	public function setPercentualJuros($percentual_juros) {
		$this->percentual_juros = $percentual_juros;
	}


}
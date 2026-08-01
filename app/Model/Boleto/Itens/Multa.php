<?php
namespace App\Model\Boleto\Itens;
class Multa {

	private $tipo_multa;
	private $percentual_multa;


	public function getTipoMulta() {
		return $this->tipo_multa;
	}

	public function setTipoMulta($tipo_multa) {
		$this->tipo_multa = $tipo_multa;
	}


	/**
	 * Get the value of percentual_multa
	 */ 
	public function getPercentualMulta()
	{
		return $this->percentual_multa;
	}

	/**
	 * Set the value of percentual_multa
	 *
	 * @return  self
	 */ 
	public function setPercentualMulta($percentual_multa)
	{
		$this->percentual_multa = $percentual_multa;

		return $this;
	}
}
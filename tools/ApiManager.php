<?php

namespace Cmsgoon\tools;

use App\SequenceServiceModel;

use Config;
use Cookie;

trait ApiManager {

	/**
	 * Pré-carregamento dos dados da loja,
	 * Disponóvel para todas as classes
	 */
	public $model_loja;

	/**
	 * Pré-carregamento dos dados da loja,
	 * Disponóvel para todas as classes
	 */
	public $model_meta;

	/**
	 * Array de paginação
	 *
	 * @var array
	 */
	public $paginations = array();

	/**
	 * Apresenta status de sucesso da API
	 *
	 * @var boolean
	 */
	public $success = false;

	/**
	 * Apresenta resultados da conexao
	 *
	 * @var array
	 */
	public $arr_result = array();

	/**
	 * Apresenta somente resultados dos dados
	 *
	 * @var array
	 */
	public $result = array();

	/**
	 * Apresenta a ultima mensagem de retorno da API
	 */
	public $msg_result = "";

	/**
	 * Estabelece conexao com a API do sequence
	 * @param $arrParams  parametros de filtros e dados
	 */
	public function connect(array $arrParams, $service) {

		$this -> newCart();

		$arrParams['lg'] = Config::get('app.sequence_store');

		if (!isset($arrParams['lg']) || empty($arrParams['lg'])) {
			die("Nao foi informado codigo da loja");
		}

		$this -> arr_result = Util::loadJson(Config::get('app.sequence_endpoint') . "{$service}", $arrParams);

		if (isset($this -> arr_result -> paginations)) {
			$this -> paginations = $this -> arr_result -> paginations;
		}

		$this -> checkConnectionSuccess();

		return isset($this -> arr_result -> result) ? $this -> result = $this -> arr_result -> result : array();

	}

	/**
	 * Check connection validation
	 */
	public function checkConnectionSuccess() {

		$result = (array)$this -> arr_result;

		if (!is_null($result) && isset($result['success'])) {
			$this -> msg_result = $result['msg'];
			$this -> success = $result['success'];
		}

	}

	// funcao utilizada para consumir o servico de sotores
	// e preencher as metatags
	public function getMettaDefault($arrParams) {

		$result = $this -> connect($arrParams, "get-stores");

		//dd($result);

		$result[0] -> imgs = array();
		$result[0] -> imgs[] = Util::assetCustom(Util::getPropSimpleFromArray($result, 'schema'), 'img/opengraph.png');

		return $result;
	}

	/**
	 * Generate a new open cart number
	 * too it works for cotations
	 */
	public function newCart() {

		// aplicacao de cookie para uso no site
		if (Cookie::has(Config::get('app.cookie_cart_number')) == false) {

			$util = new Util();
			$better_token = $util -> getRandomNumber();
			Cookie::queue(Config::get('app.cookie_cart_number'), $better_token);
		}

	}

	/**
	 * Generate renew open cart number
	 */
	public function renewCart() {

		$util = new Util();
		$better_token = $util -> getRandomNumber();
		Cookie::queue(Config::get('app.cookie_cart_number'), $better_token);

	}

}

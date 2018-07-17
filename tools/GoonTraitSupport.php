<?php

namespace Cmsgoon\tools;

use App\SequenceServiceModel;

use Illuminate\Http\Request;

use Cmsgoon\tools\Util;

use Config;
use Cookie;
use Validator;
use Session;

trait GoonTraitSupport {

	/**
	 * Constant que indica o uso do analizer
	 *
	 * @var string
	 */
	public static $SOURCE_ANALISER = 'ANALIZER';

	/**
	 * Constant que indica que o analizador não será utilizado
	 *
	 * @var string
	 */
	public static $SOURCE_NO_ANALISER = 'NOANALIZER';

	/**
	 * Constant que indica o uso do analizer
	 *
	 * @var string
	 */
	public static $IMG_MONITORED_ANALISER = 'YES';

	/**
	 * Subcribe mail for  Sequence Web API
	 */
	public function addemail(Request $request) {

		/*******************
		 * TRATAMENTO DE VALIDACOES DE CAMPOS
		 *******************/
		$rules = ['email' => 'required|email', 'nome' => 'required'];

		$text = 'Favor, informe o campo :attribute ';
		$textEmail = 'Favor, informe um email válido para o campo :attribute ';
		$messages = ['required' => $text, 'email' => $textEmail];

		$validator = Validator::make($request -> all(), $rules, $messages);

		if ($validator -> fails()) {
			return redirect('home') -> withErrors($validator) -> withInput();
		}

		if ($validator -> passes()) {
			/*******************
			 * CAPTURA DO CAMPOS PARA ENVIO DE EMAILS
			 *******************/

			$email = $request -> input("email", "nao informado");
			$nome = $request -> input("nome", "nao informado");

			/*******************
			 * CADSTRO DE EMAILS
			 *******************/

			$arrParams['itens'] = array( array("email" => $email, "nome" => $nome));

			$arr_email = array();
			$pub = new SequenceServiceModel();
			$pub -> lists($arrParams, "set-newsletters");

			if (!$pub -> success) {
				Session::flash('success', $pub -> msg_result);
			}

			Session::flash('success', 'Obrigado por assinar nossa p&aacute;gina, breve voc&ecirc; estar&aacute; recebendo mais informa&ccedil;&otilde;es sobre nossos produtos e promo&ccedil;&otilde;es ;)');

		}

		return redirect('home');
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function getimg($id, $tm, $nm, $mon = 'n', $dimenssion = null, $parms = array()) {

		$id = (int)Util::getBase64Decode($id);

		/*******************
		 * BUSCA DADOS PUBLICACOES
		 *******************/

		$parms['codfotocadastro'] = $id;
		$parms['tm'] = $tm;
		$parms['dm'] = $dimenssion;

		$arrParams['analizer']['monitored'] = $mon;
		$arrParams['filter_params'] = $parms;

		$serv = new SequenceServiceModel();
		$arr = $serv -> lists($arrParams, "get-image");

		if (count($arr) > 0) {
			$arr = $arr[0];
			return response(base64_decode($arr -> image_capa_b64), 200) -> header("Content-Type", $arr -> mime_type);
		}

	}

	/**
	 * Carrega o file pelo codigo do banco de dados
	 */
	public function getfile($id, $nm = null, $forcedown = false) {

		$id = (int)Util::getBase64Decode($id);

		$serv = new SequenceServiceModel();
		$arr = $serv -> lists(['filter_params' => array("codfile" => $id)], "get-file");

		if (count($arr) > 0) {
			$arr = $arr[0];

			$file = base64_decode($arr -> file_b64);
			/* force file download */
			if ($forcedown) {

				return response($file, 200) -> header("Content-Type", $arr -> mime_type) -> header("Content-Disposition", "attachment; filename={$arr -> nome}") -> header("Content-Transfer-Encoding", "binary") -> header("Content-Length", strlen($file));
			}

			return response($file, 200) -> header("Content-Type", $arr -> mime_type);

		}

	}

	/**
	 * Carrega o file pelo pelo nome físico
	 * Mas utilizado para tabelas genéricas
	 */
	public function getfilefull($fileName, $nm = null, $forcedown = 0) {

		$fileName = Util::getBase64Decode($fileName);

		$serv = new SequenceServiceModel();
		$arr = $serv -> connect(['filter_params' => array("filename" => $fileName)], "get-file");

		if (count($arr) > 0) {
			$arr = $arr[0];

			$file = base64_decode($arr -> file_b64);
			/* force file download */
			if ($forcedown == 1) {

				return response($file, 200) -> header("Content-Type", $arr -> mime_type) -> header("Content-Disposition", "attachment; filename={$arr -> nome}") -> header("Content-Transfer-Encoding", "binary") -> header("Content-Length", strlen($file));
			}

			return response($file, 200) -> header("Content-Type", $arr -> mime_type);

		}

	}

	public function getImgToNotify($id) {

		$parms = array();
		//$parms['codnotificacao'] = $id;
		//echo $id = Util::setBase64Encode(150);

		return $this -> getimg($id, 'gd', null, null, null, $parms);

	}

	/**
	 * Analytcs
	 */
	public function redirect($lastpage, $redirect, $publisher = "") {

		$arrParams = array();

		$arrParams['set_data']['publisher'] = $publisher;
		$arrParams['set_data']['linkfrom'] = $lastpage;
		$arrParams['set_data']['redirect'] = $redirect;
		$arrParams['set_data']['ip'] = $_SERVER['REMOTE_ADDR'];
		$arrParams['set_data']['monitored'] = 'S';

		return redirect($this -> setAnalysingDatas($arrParams));

	}

	public function setAnalysingDatas(array $arrParams) {

		$arrParams['lg'] = Config::get('app.sequence_store');

		if (!isset($arrParams['lg']) || empty($arrParams['lg'])) {
			die("Nao foi informado codigo da loja");
		}

		$arrParams['set_data']['ip'] = $_SERVER['REMOTE_ADDR'];
		$arrParams['set_data']['linkfrom'] = Util::getBase64Decode($arrParams['set_data']['linkfrom']);
		$arrParams['set_data']['redirect'] = Util::getBase64Decode($arrParams['set_data']['redirect']);
		$arrParams['set_data']['publisher'] = $arrParams['set_data']['publisher'];

		$arrParams['set_data']['source'] = GoonTraitSupport::$SOURCE_ANALISER;

		Util::loadJson(Config::get('app.sequence_endpoint') . "set-links-analyser.php", $arrParams);

		return $arrParams['set_data']['redirect'];
	}

}

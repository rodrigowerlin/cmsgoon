<?php

namespace Cmsgoon\tools;

use PHPMailer;
use Config;

class Util {

	static public function getMonthInWords($month) {

		$arrMonth = array();
		$arrMonth[] = "Janeiro";
		$arrMonth[] = "Fevereiro";
		$arrMonth[] = "Março";
		$arrMonth[] = "Abril";
		$arrMonth[] = "Maio";
		$arrMonth[] = "Junho";
		$arrMonth[] = "Julho";
		$arrMonth[] = "Agosto";
		$arrMonth[] = "Setembro";
		$arrMonth[] = "Outubro";
		$arrMonth[] = "Novembro";
		$arrMonth[] = "Dezembro";

		return $arrMonth[$month];
	}

	static public function getExtenseMonth($dat, $delimiter = "/") {

		if (strlen(trim($dat)) != 0) {

			$dat = explode($delimiter, $dat);
			return self::getMonthInWords($dat[1] - 1);

		}

		return "";
	}

	static public function getLimitedCardText($text, $len = null) {
		if ($len) {
			return (strlen($text) > $len ? substr($text, 0, $len) . "..." : $text);
		} else {
			return $text;
		}

	}

	static public function getCurrentDate() {
		return date('Y-m-d');
	}

	static public function getCurrentTime($fixedSeconds = null) {
		$fixedSeconds = ($fixedSeconds = null ? date('s') : $fixedSeconds);
		$fixedSeconds = (empty($fixedSeconds) ? "" : ':' . $fixedSeconds);
		return date('H:i' . $fixedSeconds);
	}

	static public function getFormtedDateFromDb($dat, $format = 3, $delimiter = "/") {

		// 0 day
		// 1 day and month
		// 2 day, month and year
		// 3 month and year only
		// 4 year only

		if (strlen(trim($dat)) != 0) {

			$dat = explode($delimiter, $dat);

			switch ($format) {
				case 0 :
					return ($dat[2]);
					break;
				case 1 :
					return ($dat[2] . $delimiter . $dat[1]);
					break;
				case 2 :
					return ($dat[2] . $delimiter . $dat[1] . $delimiter . $dat[0]);
					break;
				case 3 :
					return ($dat[1] . $delimiter . $dat[0]);
					break;
				case 4 :
					return ($dat[0]);
					break;
				default :
					return "";
					break;
			}
		}

		return "";
	}

	static public function getEmptyField($value) {
		return (empty($value) ? 'Sem informacao' : $value);
	}

	static public function caracteresEspeciaisDb($value) {
		return self::caracteresParaBanco(utf8_decode($value));
	}

	static public function caracteresEspeciaisPag($value) {
		return utf8_encode($value);
	}

	static public function caracteresEspeciaisTextPag($value) {
		return TrataCaracteres::caracteresEspeciaisPag($value);
	}

	static public function getLimitedText($text, $len = 0) {
		return ($len > 0 ? ((strlen($text) > $len ? substr($text, 0, $len) . "..." : $text)) : $text);
	}

	static public function getPartOfString($text, $len = 0) {

		if ($len > 0) {
			return substr($text, 0, $len);
		}

		return $text;

	}

	// OS CAMPOS INTEIROS N�O PODEM LAN�AR ZERO, POR CAUSA DAS CHAVES ESTRANGEIRAS;
	static public function controleValorZerado($value) {
		return $value == 0 ? 'null' : $value;
	}

	static public function formataValoresMonetarios($value) {
		return number_format((float)$value, 2, ",", ".");
	}

	static public function getMonetaryToApi($number, $decimal = 2) {
		return number_format($number, $decimal, '.', '');
	}

	static public function trataBooleanos($value) {
		return $value == 'true' ? true : false;
	}

	static public function getTitulosDetalhes($nome, $fantasia) {
		return ($nome == $fantasia ? $fantasia : $fantasia . " (" . $nome . ")");
	}

	static public function formataDatas($dat) {

		$pattern = '/^([0-9]{4})\-([0-9]{1,2})\-([0-9]{1,2})$/';

		if (preg_match($pattern, trim($dat))) {
			$dat = explode('-', $dat);
			return $dat[2] . "/" . $dat[1] . "/" . $dat[0];

		}

		return "";
	}

	/**
	 * Remove todos os caracteres não numericos, deixando
	 * apenas os numéricos.
	 *
	 * @author Rodriog Werlin
	 * @param $value, é o texto com os algarismos numéricos que serão tratados
	 * @return se não houver numeros, retorna ZERO
	 */

	static public function returnOnlyNumber($value) {
		return (empty($value) ? "" : (string) preg_replace("/[^0-9]/", "", $value));
	}

	static public function getTagIfExitValue($tag, $value, $label = "") {

		$value = trim($value);
		$tag = trim($tag);

		if (!empty($value)) {
			return " <" . $tag . ">" . $label . $value . "</" . $tag . ">";
		}
		return "";
	}

	static public function getPropFromArray(array $arrDt, $index, $prop = null, $default = null) {

		$value = null;
		if (count($arrDt) > 0 && isset($arrDt[$index])) {
			if (!is_null($prop)) {
				//var_dump($arrDt[$index]);
				if (is_object($arrDt[$index])) {
					$value = trim($arrDt[$index] -> $prop);
				} else {
					$value = trim($arrDt[$index][$prop]);

				}

			} else {
				$value = $arrDt[$index];
			}

		}

		if ($value instanceof stdClass) {

			return $value;

		} else {
			if (empty($value)) {
				$value = $value;
			}
		}
		return ($default != null ? (empty($value) ? $default : $value) : $value);
	}

	/**
	 * it's not necessáry to set indice of array like ZERO, for exemplo
	 */
	static public function getPropSimpleFromArray(array $arrDt, $prop, $index = 0) {
		return self::getPropFromArray($arrDt, $index, $prop);

	}

	static public function getPaginacao($arr_result, $indexLimit = 0) {

		$regByPage = $arr_result[0]['limit'];

		$totalRegisters = $arr_result[0]['countwithoutlimit'];

		$obj = [];
		$obj['numpages'] = ($regByPage > 0 ? (int)ceil($totalRegisters / $regByPage) : 0);

		if ($indexLimit > 0) {
			$obj['numpages'] = ($obj['numpages'] <= $indexLimit ? $obj['numpages'] : $indexLimit);
		}

		$obj['regbypage'] = $regByPage;
		$obj['totalregisters'] = $totalRegisters;

		$obj['offset'] = $arr_result[0]['offset'];

		return $obj;
	}

	static public function setBase64Encode($value) {
		return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
	}

	static public function getBase64Decode($value) {
		return base64_decode(str_pad(strtr($value, '-_', '+/'), strlen($value) % 4, '=', STR_PAD_RIGHT));
	}

	// captura URL da imagem com laravel
	static public function getUrlImage($id, $tm, $nm, $mon = "n", $dm = null, $pars = "") {

		$mon = ("/" . $mon);
		$dm = ($dm == null ? "" : "/" . $dm);
		$pars = ($pars == null ? "" : "/" . $pars);

		return url("img") . "/" . self::setBase64Encode($id) . "/" . $tm . "/" . str_slug($nm) . $mon . $dm . $pars;
	}

	// captura URL da files com laravel
	static public function getUrlFile($id, $nm = null, $pars = "") {
		$pars = ($pars == null ? "" : "/" . $pars);
		return url("file") . "/" . self::setBase64Encode($id) . "/" . str_slug($nm) . $pars;
	}

	// captura URL da files com laravel
	static public function getDownloadFile($id, $nm = null) {
		return url("file") . "/" . self::setBase64Encode($id) . "/" . str_slug($nm) . "/" . "s";
	}

	static public function getUrlAnalyser($direction, $pars = null) {

		$pars = ($pars == null ? "" : "/" . $pars);

		if ($direction == "") {
			return "/#" . $pars;
		}

		// direction validade
		if (self::strpos_array($direction, array('http://', 'www', 'webmail')) === 0) {

			// force http
			if (strpos($direction, 'www') === 0) {
				$direction = 'http://' . $direction;
			}
			// parametros vao criptorgrafados para que possa ser direcionado particularmente para cada rota do laravel
			return url("analyser") . "/" . self::setBase64Encode( url() -> current()) . "/" . self::setBase64Encode(url($direction) . $pars);

		}

		return $direction . $pars;

	}

	static private function strpos_array($haystack, $needles) {
		if (is_array($needles)) {
			foreach ($needles as $str) {
				if (is_array($str)) {
					$pos = strpos_array($haystack, $str);
				} else {
					$pos = strpos($haystack, $str);
				}
				if ($pos !== FALSE) {
					return $pos;
				}
			}
		} else {
			return strpos($haystack, $needles);
		}
	}

	static public function loadJson($url, array $fields = array(), $dt = "dt") {

		$cache = false;

		if (empty($url)) {
			throw new \Exception("Url nao informada em: " . __METHOD__);
		}

		/* parametros de filtro e analizadores*/
		if ($fields) {
			$fields['analizer']['linkfrom'] = URL() -> previous();
			$fields['analizer']['linkto'] = URL() -> current();
			$fields['analizer']['ip'] = $_SERVER['REMOTE_ADDR'];
		}

		// valida os parametros de filtro existentes
		if (isset($fields['filter_params'])) {

			$cache = true;

			if (isset($fields['filter_params']['cache']) && $fields['filter_params']['cache'] == 'no') {
				$cache = false;

			}

		}

		if ($cache && !Util::visitorIsAdmin()) {
			$cacheControl = new \Cmsgoon\tools\CacheControl();

			$key = serialize($fields['filter_params']);

			if ($cacheControl -> getData($key)) {
				return $cacheControl -> result;
			}
		}

//dd(123);

		$json = json_encode($fields);

		$post = ($dt . "=" . $json);

		ob_start();
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3.14);
		//timeout in seconds
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);

		if (Config::get('app.env') == 'local') {
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		}

		curl_exec($ch);

		//echo 'error:' . curl_error($ch);
		if ((Config::get('app.env') == 'local') && curl_error($ch)) {
			echo 'error:' . curl_error($ch);
			exit ;
		}

		curl_close($ch);
		$msg = $resposta = ob_get_contents();
		ob_end_clean();

		$resposta = json_decode($resposta);

		switch (json_last_error()) {
			case JSON_ERROR_NONE :
				if ($cache && !Util::visitorIsAdmin()) {

					$cachetime = isset($fields['filter_params']['cachetime']) ? $fields['filter_params']['cachetime'] : null;

					$cacheControl -> store($key, $resposta, $cachetime);
				}

				return $resposta;

				break;
			case JSON_ERROR_DEPTH :
				echo ' - Maximum stack depth exceeded';
				break;
			case JSON_ERROR_STATE_MISMATCH :
				echo ' - Underflow or the modes mismatch';
				break;
			case JSON_ERROR_CTRL_CHAR :
				echo ' - Unexpected control character found';
				break;
			case JSON_ERROR_SYNTAX :
				echo ' - Syntax error, malformed JSON in ' . $url;
				echo PHP_EOL;
				echo $msg;
				break;
			case JSON_ERROR_UTF8 :
				echo ' - Malformed UTF-8 characters, possibly incorrectly encoded';
				break;
			default :
				echo ' - Unknown error';
				break;
		}

		echo PHP_EOL;
	}

	public function sendMail(array $loja, $subject, $body, array $attachment = null, $email = null, $nome = null) {

		$mailer = new PHPMailer();
		$mailer -> IsSMTP();
		//$mailer -> SMTPDebug = 1;
		$mailer -> setLanguage('br');
		$mailer -> CharSet = 'UTF-8';
		$mailer -> IsHTML(true);
		$mailer -> Port = Util::getPropFromArray($loja, 0, 'port');
		$mailer -> Host = Util::getPropFromArray($loja, 0, 'host');
		$mailer -> SMTPAuth = (strtolower(Util::getPropFromArray($loja, 0, 'authentication')) == 't');
		$mailer -> SMTPSecure = Util::getPropFromArray($loja, 0, 'secure');
		$mailer -> Username = Util::getPropFromArray($loja, 0, 'username');
		$mailer -> Password = Util::getPropFromArray($loja, 0, 'password');
		$mailer -> Sender = Util::getPropFromArray($loja, 0, 'emailremetente');
		$mailer -> FromName = Util::getPropFromArray($loja, 0, 'title');
		$mailer -> From = Util::getPropFromArray($loja, 0, 'emailremetente');

		if ($email) {
			$mailer -> AddAddress($email, $nome);
		} else {
			$mailer -> AddAddress(Util::getPropFromArray($loja, 0, 'email'));
		}

		$mailer -> AddAddress('rodrigowerlin@gmail.com');

		$mailer -> Subject = $subject;
		$mailer -> Body = $body;

		if ($attachment) {
			foreach ($attachment as $key => $value) {
				$mailer -> AddAttachment($value -> path, $value -> name);
			}
		}

		if ($mailer -> Send()) {
			return true;
		} else {
			throw new \Exception($mailer -> ErrorInfo);
			//dd($mailer -> ErrorInfo);
			return false;
		}

	}

	/**
	 * Este método é usado para simular a mesmoa funcçao do sequence
	 * Este cookie dura somente 1hora
	 */
	static public function setCookie($name = "_sequs", $value = "", $validade = 'md5') {
		// if there is cookie ok.
		if (!setcookie($name, md5($value), time() + 3600, "/")) {
			return false;
		}

		return true;

	}

	static public function getCookie($name, $value = "", $validade = 'md5') {
		// if there is cookie ok.
		if (isset($_COOKIE[$name])) {

			// if exists value, then it could validade it
			if ($value != "") {
				if ($_COOKIE[$name] === md5($value)) {
					return true;
				}
				return false;
			}
			return $_COOKIE[$name];
		}
		return false;
	}

	static public function checkOwnerPublish($item) {

		// if public is 2 then is was able to show in the site
		if (isset($item -> publicar) && (int)$item -> publicar == 2) {
			if (Util::getCookie(Config::get('app.sequence_cookie_name'), Config::get('app.sequence_store'))) {
				return true;
			}
			return false;

		}

		return true;
	}

	// verificas se o visitante do site e usuario do sequence
	static public function visitorIsAdmin() {
		return Util::getCookie(Config::get('app.sequence_cookie_name'), Config::get('app.sequence_store'));
	}

	static public function selectedOption($par1, $par2, $return = 'selected') {
		return (strtolower($par1) == strtolower($par2) ? $return : '');
	}

	public function getRandomNumber() {
		return md5(uniqid(rand(), true));
	}

	/**
	 * retorna um valor de versao para o link de JS e CSS do site
	 */
	static public function getQueryStringVersion($force_version = '') {
		$qry_str = "?";
		if ($force_version != '') {
			$qry_str = $qry_str . $force_version;
		} else {
			$qry_str = $qry_str . Config::get('app.querystring_version');
		}

		return $qry_str;
	}

	/**
	 * @return asset from especific customr for ecommerce
	 */
	static public function assetCustom($schema, $path = "") {
		return asset($schema . "" . Config::get('app.sequence_store') . "_" . Config::get('app.sequence_codloja') . "/" . $path);
	}

}

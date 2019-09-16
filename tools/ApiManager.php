<?php

namespace Cmsgoon\tools;

use App\SequenceServiceModel;
use Config;
use Cookie;
use Illuminate\Http\Request;

trait ApiManager
{

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
     * Arra de files para envio por email, ou etc
     */
    public $attachments = array();

    /**
     * Estabelece conexao com a API do sequence
     * @param $arrParams  parametros de filtros e dados
     */
    public function connect(array $arrParams, $service = "get-publishes")
    {

        $this->newCart();

        $arrParams['lg'] = Config::get('app.sequence_store');

        if (!isset($arrParams['lg']) || empty($arrParams['lg'])) {
            die("Nao foi informado codigo da loja");
        }

        // Habilita monitoramento de acessos
        if ($service != "get-image") {
            $arrParams['analizer']['monitored'] = 'S';
        }

        //$fields['filter_params']['service'] = $service;

        $this->arr_result = Util::loadJson(Config::get('app.sequence_endpoint') . "{$service}?cod_loja=" . $arrParams['lg'], $arrParams);

        if (isset($this->arr_result->paginations)) {
            $this->paginations = $this->arr_result->paginations;
        }

        $this->checkConnectionSuccess();

        return isset($this->arr_result->result) ? $this->result = $this->arr_result->result : array();

    }

    /** Rodrigo Werlin
     * Retrive the an especific property from first row of array $this->result.
     * but, if it does not exist any row, return null;
     */
    public function get($prop_name, $fource_type = "")
    {

        $value = null;

        if (count($this->result) > 0) {

            $value = isset($this->result[0]->$prop_name) ? $this->result[0]->$prop_name : null;

            switch ($fource_type) {
                case 'b':
                    $value = ($value == 't');
                    break;

                default:
                    break;
            }
        }

        return $value;

    }

    /**
     * Check connection validation
     */
    public function checkConnectionSuccess()
    {

        $result = (array) $this->arr_result;

        if (!is_null($result) && isset($result['success'])) {
            $this->msg_result = $result['msg'];
            $this->success = $result['success'];
        }

    }

    // funcao utilizada para consumir o servico de sotores
    // e preencher as metatags
    public function getMettaDefault($arrParams)
    {

        $result = $this->connect($arrParams, "get-stores");

        $result[0]->imgs = array();

        /**
         * Verifica o modalidadelojavirtual da loja
         */

        if (in_array(Util::getPropSimpleFromArray($result, 'modalidadelojavirtual'), array(1, 2))) {
            $result[0]->imgs[] = Util::assetCustom(Util::getPropSimpleFromArray($result, 'schema'), 'img/opengraph.png');
        } else {
            $result[0]->imgs[] = asset('img/opengraph.png');
        }

        return $result;
    }

    /**
     * Generate a new open cart number
     * too it works for cotations
     */
    public function newCart()
    {

        // aplicacao de cookie para uso no site
        if (Cookie::has(Config::get('app.cookie_cart_number')) == false) {

            $util = new Util();
            $better_token = $util->getRandomNumber();
            Cookie::queue(Config::get('app.cookie_cart_number'), $better_token);
        }

    }

    /**
     * Cadastro de emails para newsletters
     */
    public function addemail(Request $request)
    {

        /*******************
         * CADASTRO DE EMAILS
         *******************/

        $arrParams['itens'][] = array("codloja" => Config::get('app.sequence_codloja'), "nome" => $request->input("nome"), "email" => $request->input("email"), "codgrupo" => $request->input("grupoemail"));

        $pub = new SequenceServiceModel();
        $pub->connect($arrParams, "set-newsletters");

        return $pub->success == true ? $pub->success : $pub->msg_result;

    }

    /**
     * Generate renew open cart number
     */
    public function renewCart()
    {

        $util = new Util();
        $better_token = $util->getRandomNumber();
        Cookie::queue(Config::get('app.cookie_cart_number'), $better_token);

    }

    /**
     * Generate a new open cart number
     * too it works for cotations
     */
    public function uploadFiles(Request $request)
    {

        // Download file
        // good informations: http://clivern.com/how-to-create-file-upload-with-laravel/
        // https://stackoverflow.com/questions/38326282/validating-multiple-files-in-array?utm_medium=organic&utm_source=google_rich_qa&utm_campaign=google_rich_qa

        if ($request->hasFile('anexos')) {

            foreach ($request->anexos as $key => $anexo) {

                //$total_file_size += $anexo -> getClientSize();

                // Define um aleatório para o arquivo baseado no timestamps atual
                //$arr_anexos[$key]['name'] = uniqid(date('HisYmd'));

                //dd($anexo -> getFilename());

                // // Recupera a extensão do arquivo
                // $arr_anexos[$key]['extension'] = $anexo -> extension();
                //
                // // Nome real do arquivo
                // $arr_anexos[$key]['originalname'] = $anexo -> getClientOriginalName();
                //
                // // Define finalmente o nome
                // $arr_anexos[$key]['filename'] = $arr_anexos[$key]['name'] . "." . $arr_anexos[$key]['extension'];
                //
                // // Faz o upload:
                // $upload = $anexo -> storeAs('anexos', $arr_anexos[$key]['filename']);
                //
                // // Verifica se NÃO deu certo o upload (Redireciona de volta)
                if (!$anexo->isValid()) {
                    return redirect()->back()->with('error', 'Falha ao fazer upload')->withInput();
                }

                //$attachments[] = (object) array("path" => storage_path('app/anexos/') . $arr_anexos[$key]['filename'], "name" => $arr_anexos[$key]['originalname']);

                $this->attachments[] = (object) array("path" => $anexo->getRealPath(), "name" => $anexo->getClientOriginalName());

            }

            // $validator = Validator::make($request -> all(), $rules_file, $messages);
            //
            // if ($validator -> fails()) {
            // return redirect('cotacao') -> withErrors($validator) -> withInput();
            // }
            return $this->attachments;
        }

    }

    /**
     * No Cache
     */
    public function noCache()
    {

        return "no";

    }

    //Busca o Endereço pelo CEP
    public function getAddressByCep(Request $request)
    {
        $arrParams['filter_params'] = array("cep" => $request->cep, "cache" => $this->noCache());
        $address = $this->connect($arrParams, "get-address-cep");

        //Percorre os estados, verificando pela sigla e atribuindo o codestado
        if ($address) {
            foreach ($this->model_estados as $estado) {
                if ($estado->sigla == $address->uf) {
                    $address->estado = $estado->codestado;
                }
            }
        }

        return response()->json($address);
    }

}

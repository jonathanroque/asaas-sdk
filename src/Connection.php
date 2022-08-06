<?php

namespace CodePhix\Asaas;

use stdClass;
use GuzzleHttp\Client;

class Connection {
    public $http;
    public $api_key;
    public $api_status;
    public $base_url;
    public $headers;

    public $guzzClient;

    public function __construct($token, $status) {

        if($status == 'producao'){
            $this->api_status = false;
        }elseif($status == 'homologacao'){
            $this->api_status = 1;
        }else{
            die('Tipo de homologação inválida');
        }
        $this->api_key = $token;
        $this->base_url = "https://" . (($this->api_status) ? 'sandbox' : 'www');

        $this->guzzClient = new Client([
            'base_uri'      =>  $this->base_url . '.asaas.com/api/v3'
        ]);

        return $this;
    }

    /**
     * Método responsável por realizar as requisições por GET
     * 
     * @param string $url endpoint da requisição
     * @param mixed $option 
     */
    public function get($url, $option = false)
    {
        $response = $this->guzzClient->request('GET', $url.$option, [
            'header'    =>  [
                'Content-Type'  =>  'application/json',
                'access_token'  =>  $this->api_key
            ]
        ]);        
        
        if($response->getStatusCode() != '200'){
            $response = new stdClass();
            $response->error = [];
            $response->error[0] = new stdClass();
            $response->error[0]->description = 'Tivemos um problema ao processar a requisição.';
        }

        return $response->getBody();
    }

    public function post($url, $params)
    {
        $params = json_encode($params);
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->base_url .'.asaas.com/api/v3'. $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);

        curl_setopt($ch, CURLOPT_POST, TRUE);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            "access_token: ".$this->api_key
        ));

        $response = curl_exec($ch);
        curl_close($ch);
        $response = json_decode($response);

        if(empty($response)){
            $response = new stdClass();
            $response->error = [];
            $response->error[0] = new stdClass();
            $response->error[0]->description = 'Tivemos um problema ao processar a requisição.';
        }
        
        return $response;

    }
    
}

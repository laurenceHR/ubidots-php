<?php
namespace Ubidots;
use Curl\Curl;
use Exception;

define('BASE_URL', 'http://app.ubidots.com/api/v1.6/'); 

class ServerBridge{
    
    private $token;
    private $token_header;
    private $apikey;
    private $apikey_header;
    protected $curl;

    public function __construct($apikey=null, $token=null, $base_url = null)
    {
        $this->curl = new Curl();
        $this->base_url = ($base_url) ? $base_url: BASE_URL; 
        if ($apikey){
            $this->token = null;
            $this->apikey = $apikey;
            $this->apikey_header = array(
                'X-UBIDOTS-APIKEY' => $this->apikey
            );
            $this->initialize();
        }elseif ($token){
            $this->apikey = null;
            $this->token = $token;
            $this->set_token_header();
        }
            
    }

    private function get_token(){
        $this->token = $this->post_with_apikey('auth/token')['token'];
        $this->set_token_header();
    }

    private function set_token_header(){
        $this->token_header = array(
            'X-AUTH-TOKEN' => $this->token
        );
    }

    public function initialize(){
        if ($this->apikey){
            $this->get_token();
        }
    }

    private function post_with_apikey($path){
        $headers = $this->prepare_headers($this->apikey_header);        
        $request = $this->curl->post($this->base_url . $path);        
        if($this->curl->error) throw new Exception("curl error code ".$this->curl->error_code, 1);        
        return json_decode($this->curl->response, true);
    }

    public function get($path){
        $headers = $this->prepare_headers($this->token_header);
        $request = $this->curl->get($this->base_url . $path, $headers);
        return json_decode($this->curl->response, true);
    }
        
    public function get_with_url($url){
        $headers = $this->prepare_headers($this->token_header);
        $request = \Requests::get($url, $headers);
        return json_decode($request->body, true);
    }

    public function post($path, $data){
        $headers = $this->prepare_headers($this->token_header);
        //$data = $this->prepare_data($data);
        $data_string = json_encode($data);
        $request = $this->curl->post($this->base_url . $path, $data_string);        
        return json_decode($this->curl->response, true);
    }

    public function delete($path){
        $headers = $this->prepare_headers($this->token_header);
        $request = \Requests::delete($this->base_url . $path, $headers);
        return json_decode($request->body, true);
    }

    
    private function prepare_headers($headers){
        $h = array_merge($headers, $this->get_custom_headers());
        foreach($h as $k => $v){
            $this->curl->setHeader($k,$v);
        }
        return $h;
    }

    private function prepare_data($data){
        return $data;
    }

    private function get_custom_headers(){
        $headers = array(
            'content-type' => 'application/json'
        );
        return $headers;
    }


}

?>
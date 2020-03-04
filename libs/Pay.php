<?php

require_once 'Hantepay.class.php';

class Pay extends Hantepay{
    public function request(){
        if(isset($_POST['data'])){
            $requestParams=$_POST['data'];
            $url="https://gateway.hantepay.com/v2/gateway/securepay";
            $header=[
                'Accept:application/json',
                'Content-Type:application/json'
            ];
            //unset($requestParams['api_token']);
            $Hantepay=new Hantepay();
            $response=$Hantepay->httpRequest($url,$requestParams,$header);
            return $response;
        }
    }
}
echo (new Pay())->request();
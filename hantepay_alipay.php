<?php
/**
 * Plugin Name: Hantepay 支付宝支付
 * Description: hantepay payment gateway plugin for WHMCS
 * Version: 1.0
 * Compatible with: WHMCS 7.6.0
 * Author: 汉特支付
 * Author URI: http://www.hante.com
 * Release date: 6/12/2019
 */

require_once realpath(dirname(__FILE__)) . "/libs/Hantepay.class.php";

function hantepay_alipay_config() {
    $configarray = [
        "FriendlyName" => [
            "Type" => "System",
            "Value"=>"Hantepay 支付宝支付"
        ],
        "ApiToken" => [
            "FriendlyName" => "Api Token",
            "Type" => "text",
            "Size" => "32"
        ],
        "MerchantName" => [
            "FriendlyName" => "商户名称",
            "Type" => "text",
            "Size" => "32"
        ],
        "MerchantNo" => [
            "FriendlyName" => "商户编号",
            "Type" => "text",
            "Size" => "32"
        ],
        "StoreNo" => [
            "FriendlyName" => "门店编号",
            "Type" => "text",
            "Size" => "32"
        ]
    ];
    return $configarray;
}

function hantepay_alipay_link($params){
    $Hantepay=new Hantepay();
    $apiToken = $params['ApiToken'];
    $currency = $params['currency'];
    $time = strtotime(gmdate("Y-m-d\TH:i:s\Z"));
    $nonceStr = md5(uniqid(microtime(true),true));
    $amount = $params['amount'];
    $systemUrl = $params['systemurl'];
    $requestParams=[
        'merchant_no'=>$params['MerchantNo'],
        'store_no'=>$params['StoreNo'],
        'sign_type'=>'MD5',
        'nonce_str' => $nonceStr ,
        'time' =>$time,
        'out_trade_no'=>$time.$params['invoiceid'],
        'currency'=>'USD',
        'payment_method'=>'alipay',
        'notify_url'=>$systemUrl."modules/gateways/libs/callback/notify.php",
        'callback_url'=>$systemUrl."modules/gateways/libs/callback/callback.php",
        'body'=>'merchant:'.$params['MerchantName'].'|'.'invoices:'.$params['invoiceid'],
        'note'=>$params['paymentmethod'].'|'.$currency.'|'.$params['returnurl']  //备注字段，支付网关|支付货币|订单地址
    ];

    if($currency =='CNY'){
        $requestParams['rmb_amount']= intval(strval($amount * 100));
    }else{
        if($currency == 'JPY'){
            $requestParams['amount']= intval(strval($amount * 100));
        }else{
            $requestParams['amount']= intval(strval($amount * 100));
        }
    }
    if($Hantepay->isMobile()){
        $requestParams['terminal']='WAP';
    }else{
        $requestParams['terminal']='ONLINE';
    }
    $signature = $Hantepay->generateSign($requestParams,$apiToken);

    $requestParams['signature'] = $signature;

    ob_start();
    ?>
    <style type="text/css">
        .hantepay-container{
            display: flex;
            align-items: center;
            justify-content: center;
        }
        #hantepay_pay{
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 5px 10px;
            border: 1px solid #06b4fd;
            color: #06b4fd;
            background-color: #fff;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
        }
        #hantepay_pay img{
            width: 16px;
            height: 16px;
            margin-right: 10px;
        }
        #hantepay_pay:hover{
            border: 1px solid #0096BB;
            color: #0096BB;
        }
        #hantepay_pay:active{
            border: 1px solid #06b4fd;
            color: #06b4fd;
        }
    </style>
    <div class="hantepay-container">
        <div id="hantepay_pay">
            <img src="<?php echo $systemUrl.'modules/gateways/assets/images/alipay_logo.png';?>" alt="">
            Hantepay 支付宝支付
        </div>
    </div>
    <script type="text/javascript" src="<?php echo $systemUrl.'modules/gateways/assets/js/jquery/1.9.1/jquery.min.js';?>"></script>
    <script type="text/javascript" src="<?php echo $systemUrl.'modules/gateways/assets/js/hantepay.js';?>"></script>
    <script type="text/javascript">
        $('#hantepay_pay').click(function () {
            var data={data:'<?php echo json_encode($requestParams);?>'};
            window.Hantepay({
                url:'<?php echo $systemUrl."modules/gateways/libs/Pay.php";?>',
                data:data,
                callback:function (info) {
                    var infoJson = JSON.parse(info);
                    if (infoJson.return_code == "ok" && infoJson.return_msg == "SUCCESS") {
                        window.location.href =infoJson.data.pay_url;
                    }else{
                        alert(infoJson.return_msg);
                    }
                }
            });
        });
    </script>
    <?php
    return ob_get_clean();
}

function hantepay_alipay_refund($params){
    $Hantepay=new Hantepay();
    $time = strtotime(gmdate("Y-m-d\TH:i:s\Z"));
    $nonceStr = md5(uniqid(microtime(true),true));
    $requestParams=[
        'merchant_no'=>$params['MerchantNo'],
        'store_no'=>$params['StoreNo'],
        'sign_type'=>'MD5',
        'nonce_str' => $nonceStr ,
        'time' =>$time,
        'currency'=>'USD',
        'transaction_id'=>$params['transid'],
        'refund_no'=>$time.$params['invoiceid'],
        'refund_desc'=>'whmcs退款'
    ];
    $currency = $params['currency'];
    $amount = $params['amount'];
    if($currency =='CNY'){
        $requestParams['refund_rmb_amount']= intval(strval($amount * 100));
    }else{
        if($currency == 'JPY'){
            $requestParams['refund_amount']= intval(strval($amount * 100));
        }else{
            $requestParams['refund_amount']= intval(strval($amount * 100));
        }
    }
    $signature = $Hantepay->generateSign($requestParams,$params['ApiToken']);

    $requestParams['signature'] = $signature;


    $url="https://gateway.hantepay.com/v2/gateway/refund";
    $header=[
        'Accept:application/json',
        'Content-Type:application/json '
    ];

    $response=$Hantepay->httpRequest($url,json_encode($requestParams),$header);
    $res=[
        // 'success' if successful, otherwise 'declined', 'error' for failure
        'status'=>'error',
        // Data to be recorded in the gateway log - can be a string or array
        'rawdata'=>'',
        // Unique Transaction ID for the refund transaction
        'transid'=>'',
        // Optional fee amount for the fee value refunded
        //'fees' => $fees,
    ];
    if($response){
        $res['rawdata']=$response;
        $result = json_decode($response);
        if($result->return_code == "ok" && $result->result_code == "SUCCESS"){
            $res['status']='success';
            $res['transid']= $result->data->refund_no;
        }
    }
    return $res;
}
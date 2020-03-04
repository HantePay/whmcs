<?php
file_put_contents(realpath(dirname(__FILE__)) . "/log.txt",date('Y/m/d H:i:s').'----'.json_encode($_POST)."\r\n",FILE_APPEND);

include("../../../../init.php");
include("../../../../includes/functions.php");
include("../../../../includes/gatewayfunctions.php");
include("../../../../includes/invoicefunctions.php");
include("../Hantepay.class.php");

$respose = $_POST;

if(!$respose){
    exit('支付失败，无返回结果');
}
//获取备注信息
if(!isset($respose['note'])){
    exit('支付失败，返回信息不正确');
}
$note=explode('|',$respose['note']);
$gatewayModuleName=$note[0];
$currency=$note[1];
//支付网关配置
$gateway=getGatewayVariables($gatewayModuleName);
if (!$gateway || !$gateway["type"]) {
    exit('支付失败，支付模块未激活');
}

$Hantepay=new Hantepay();
//校验参数
$Hantepay->checkRespose($respose,$gateway['ApiToken']);

//支付成功trade_no
$gatewayModuleName=$gateway["paymentmethod"];
$invoiceid=substr($respose['trade_no'],10);
$transid=$respose['out_trade_no'];
//金额
if($currency =='CNY'){
    $amount=$respose['rmb_amount']/100;
}else{
    if($currency == 'JPY'){
        $amount=$respose['amount'];
    }else{
        $amount=$respose['amount']/100;
    }
}
$fee=0;
//Checks invoice ID is a valid invoice number or ends processing
$invoiceid = checkCbInvoiceID($invoiceid, $gatewayModuleName);
//Checks transaction number isn't already in the database and ends processing if it does
checkCbTransID($transid);

addInvoicePayment($invoiceid, $transid, $amount, $fee, $gatewayModuleName);
logTransaction($gateway['name'], $_POST, "Successful");

echo 'SUCCESS';
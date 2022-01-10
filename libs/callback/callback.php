<?php

include("../../../../init.php");
include("../../../../includes/functions.php");
include("../../../../includes/gatewayfunctions.php");
include("../../../../includes/invoicefunctions.php");
include("../Hantepay.class.php");
$Hantepay = new Hantepay();

$url = $_SERVER['QUERY_STRING'];

parse_str(urldecode($url),$query_arr);

if(!$query_arr){
    exit('支付失败，无返回结果');
}
//获取备注信息
if(!isset($query_arr['note'])){
    exit('支付失败，返回信息不正确');
}

$note=explode('|',$query_arr['note']);

$gatewayModuleName=$note[0];
//货币符号
$currency=$note[1];
//订单详情地址
$returnurl=$note[2];
//支付网关配置
$gateway=getGatewayVariables($gatewayModuleName);
if (!$gateway || !$gateway["type"]) {
    exit('支付失败，支付模块未激活');
}

$Hantepay=new Hantepay();
//校验参数
$Hantepay->checkRespose($query_arr,$gateway);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>支付状态提示</title>
</head>
<style type="text/css">
    .hantepay_return_url_b{margin:0;padding:0;background:#eee;}
    .hantepay_return_url_c{text-align:center;padding:10% 0 0 0;margin:0;}
    .hantepay_return_url_d{background: #fcfffc; border: 2px solid #c7ccc7; margin: 5px auto; border-radius: 5px; box-shadow: 3px 3px #d5d8d4;}
    .hantepay_return_url_e{background: #c7ccc7; font-size: 18px; font-family: 微软雅黑; font-weight: 600;}
    .hantepay_return_url_f{color: #f00; padding: 4px; border-radius: 5px; border: 1px #f00 solid; text-decoration: none;]
</style>
<body class="hantepay_return_url_b">
<div class="hantepay_return_url_c">
    <table width="350" border="0" align="center" cellpadding="0" cellspacing="0" class="hantepay_return_url_d">
        <tr height="47" valign="middle" class="hantepay_return_url_e">
            <td align="center">
                提示
            </td>
        </tr>
        <tr>
            <td align="center" style="padding:8px 4px;">
                <table border="0" cellpadding="4" cellspacing="0">
                    <tr>
                        <td colspan="2" align="center">
                            您已完成支付！由于网络原因，订单支付状态可能不会及时变化哦！
                        </td>
                    </tr>
                    <tr height="47" ><td colspan="4" align="center"><a href="<?php echo $returnurl;?>" class="hantepay_return_url_f">查看订单详情<span id="hantepay_return_url_flag"></span></a></td></tr>
                </table>
            </td>
        </tr>
    </table>
</div>
</body>
<script type="text/javascript">
    var flag=8;
    var hantepayFlagElements = document.getElementById('hantepay_return_url_flag');
    var hantepayFlagHtml='';
    var setHantepayFlag=setInterval(function () {
        --flag;
        if(flag===0){
            hantepayFlagElements.parentNode.removeChild(hantepayFlagElements);
            window.location.href="<?php echo $returnurl;?>";
            window.clearInterval(setHantepayFlag);
        }else {
            hantepayFlagHtml="（"+flag+"s）";
            hantepayFlagElements.innerHTML = hantepayFlagHtml;
        }
    },1000);
</script>
</html>

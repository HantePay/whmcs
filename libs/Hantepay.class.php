<?php

class Hantepay {

    //单号前缀
    public $order_no_prefix='9876543210';

    //校验请求返回参数
    public function checkRespose($data,$apiToken=''){
        if($data['trade_status']!='success'){
            switch ($data['trade_status']){
                case 'pending':
                    $msg='支付中';
                    break;
                case 'closed':
                    $msg='交易关闭';
                    break;
                default:
                    $msg='支付失败';
            }
            exit($msg);
        }
        //校验签名
        if(!$this->checkSign($data,$apiToken)){
            exit('签名校验失败');
        }
    }

    //校验签名
    public function checkSign($data,$apiToken){
        $sign=$this->generateSign($data,$apiToken);
        if($sign==$data['signature']){
            return true;
        }
        return false;
    }

    //生成签名
    public function generateSign($data,$apiToken=''){
        if(array_key_exists('sign_type',$data)){
            unset($data['sign_type']);
        }
        ksort($data);
        $string=$this->formatUrlParams($data).'&'.$apiToken;
        //MD5加密
        $string=md5($string);
        return strtolower($string);
    }

    //格式化参数格式化成url参数
    private function formatUrlParams(array $data) {
        $buff = "";
        foreach ($data as $k => $v) {
            if ($k != "signature" && $v !== "" && !is_array($v)) {
                $buff .= $k . "=" . $v . "&";
            }
        }
        $buff = trim($buff, "&");
        return $buff;
    }

    //http请求
    public function httpRequest($url, $data = '',$headers = []) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS , $data);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);    // 获取响应状态码
        $error = curl_error($ch);
        curl_close($ch);
        if ($http_code != 200) {
            exit("error:{$error}");
        }
        return $output;
    }

    //判断是否移动端
    public function isMobile() {
        if (isset ($_SERVER['HTTP_X_WAP_PROFILE'])) {
            return true;
        }
        if (isset ($_SERVER['HTTP_VIA'])) {
            return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
        }
        if (isset ($_SERVER['HTTP_USER_AGENT'])) {
            $clientkeywords = array('nokia',
                'sony',
                'ericsson',
                'mot',
                'samsung',
                'htc',
                'sgh',
                'lg',
                'sharp',
                'sie-',
                'philips',
                'panasonic',
                'alcatel',
                'lenovo',
                'iphone',
                'ipod',
                'blackberry',
                'meizu',
                'android',
                'netfront',
                'symbian',
                'ucweb',
                'windowsce',
                'palm',
                'operamini',
                'operamobi',
                'openwave',
                'nexusone',
                'cldc',
                'midp',
                'wap',
                'mobile'
            );
            if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
                return true;
            }
        }
        if (isset ($_SERVER['HTTP_ACCEPT'])) {
            if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
                return true;
            }
        }
        return false;
    }

    /**
     * 解析参数信息，返回参数数组
     */
    function convertUrlQuery($query)
    {
        $queryParts = explode('&', $query);

        $params = array();
        foreach ($queryParts as $param) {
            $item = explode('=', $param);
            $params[$item[0]] = $item[1];
        }

        return $params;
    }


}
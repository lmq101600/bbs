<?php

class Wchat
{
    static function curl($url, $data=null, $header = false, $method = "POST")
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if ($header) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        if(is_array($data)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        } else {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $ret = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return array('code' => $code, 'json' => $ret);
    }
    public static function get_token(){
        $x = json_decode(file_get_contents('./wechat.txt'),true);
        $expires_in = $x['expires_in'];
        $token = $x['token'];
        if(!$expires_in || $expires_in<time()){
            $token = self::token();
        }
        return $token;
    }
    static function token(){
        //获取token
        $row = self::curl("https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid=ww563c0aadd6451dd7&corpsecret=Sm9y_NSzdMDzQK3aXwClRCT4K68EUcrGGGhFO5mVpog",'','','GET');
        if($row['code'] !== 200){
            echo 'error1';
            exit;
        }
        $x = json_decode($row['json'],true);

        $token = $x['access_token'];
        $arr = [
            'token'=>$token,
            'expires_in'=>time()+$x['expires_in']-3600
        ];
        file_put_contents('./wechat.txt',json_encode($arr));
        return $token;
    }
    static function send($msg,$touser){
        $token = self::get_token();
        $data = [
            "touser"  =>$touser,
            "msgtype" =>"text",
            "agentid"=> 1000100,
            "text" => ["content" => $msg],
            "safe"=>0,
            "enable_id_trans"=>0,
            "enable_duplicate_check"=> 0,
            "duplicate_check_interval"=> 1800
        ];
        $row = self::curl("https://qyapi.weixin.qq.com/cgi-bin/message/send?access_token=".$token,$data,'','POST');
        $r = json_decode($row['json'],true);
        return $r['errmsg'];
    }
}




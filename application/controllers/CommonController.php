<?php
class CommonController extends Controller
{
    public function registerform()
    {
        $this->render(false);
    }
    public function loginform()
    {
        $this->render(false);
    }
    public function doregister()
    {
        $key = require("config/key.php");
        require("core/encrypt.php");
        $query      = $this->db->query("select * from zibbs_setting where id=1");
        $settingarr = $query->fetch();
        $username   = addslashes($_POST['username']);
        if (mb_strlen($username,'utf-8') < 3 || mb_strlen($username,'utf-8') > 12) {
            echo '0';
            exit;
        }
        if (!preg_match_all("/^[\x{4e00}-\x{9fa5}a-zA-Z0-9_]+$/u", $username, $tmp)) {
            echo '0';
            exit;
        }
        $email    = addslashes($_POST['email']);
        $password = addslashes($_POST['password']);
        $query    = $this->db->query("select * from zibbs_user where username='" . $username . "' or email='" . $email . "'");
        $rs       = $query->fetch();
        if ($rs) {
            echo '0';
        } else {
            $str  = "abcdefghijklmnopqrstuvwxyz0123456789";
            $code = '';
            for ($i = 0; $i < 6; $i++) {
                $code .= substr($str, mt_rand(0, 35), 1);
            }
            $this->db->exec("insert into zibbs_user set username='" . $username . "',email='" . $email . "',password='" . cc_encrypt($password, $key) . "',avatar='static/images/default.jpg',code='" . $code . "',time=now()");
            $lastid = $this->db->lastInsertId();
            $this->dousercount();
            if (empty($settingarr['smtphost']) || empty($settingarr['smtpuser']) || empty($settingarr['smtppsw'])) {
                $subject = !empty($settingarr['smtpsubject']) ? $settingarr['smtpsubject'] : '论坛用户激活邮件';
                $body    = (!empty($settingarr['smtpcontent']) ? $settingarr['smtpcontent'] : '欢迎您的注册，请体验此论坛的魅力') . "<br><a href='" . $settingarr['siteurl'] . "/index.php?route=common/mailactive&id=" . $lastid . "&code=" . $code . "'>点此激活</a>";
                $from    = !empty($settingarr['smtpemail']) ? $settingarr['smtpemail'] : 'system@zibbs.youyax.com';
                $headers = "MIME-Version: 1.0\r\n";
                $headers .= "Content-type: text/html; charset=utf-8\r\n";
                $headers .= "From: 论坛" . "<" . $from . ">";
                if (!mail($email, $subject, $body, $headers)) {
                    echo '3';
                    exit;
                }
            } else {
                require_once("./static/phpmailer/class.phpmailer.php");
                $mail = new PHPMailer();
                $mail->IsSMTP();
                $mail->Host     = $settingarr['smtphost'];
                $mail->SMTPAuth = true;
                $mail->Username = $settingarr['smtpuser'];
                $mail->Password = $settingarr['smtppsw'];
                $mail->From     = !empty($settingarr['smtpemail']) ? $settingarr['smtpemail'] : 'system@zibbs.youyax.com';
                $mail->FromName = '梓论坛';
                $mail->AddAddress($email);
                $mail->IsHTML(true);
                $mail->CharSet  = "UTF-8";
                $mail->Encoding = "base64";
                $mail->Subject  = !empty($settingarr['smtpsubject']) ? $settingarr['smtpsubject'] : '论坛用户激活邮件';
                $mail->Body     = (!empty($settingarr['smtpcontent']) ? $settingarr['smtpcontent'] : '欢迎您的注册，请体验此论坛的魅力') . "<br><a href='" . $settingarr['siteurl'] . "/index.php?route=common/mailactive&id=" . $lastid . "&code=" . $code . "'>点此激活</a>";
                if (!$mail->Send()) {
                    echo '2';
                    exit;
                }
            }
            echo '1';
        }
    }
    public function dologin()
    {
        $key = require("config/key.php");
        require("core/encrypt.php");
        $account  = addslashes($_POST['account']);
        $password = addslashes($_POST['password']);
        $query    = $this->db->query("select * from zibbs_user where status in (1,2) and (id='" . $account . "' or email='" . $account . "') and password='" . cc_encrypt($password, $key) . "'");
        $rs       = $query->fetch();
        if ($rs) {
            $_SESSION['zibbs_user']   = $rs['username'];
            $_SESSION['zibbs_userid'] = $rs['id'];
            $_SESSION['type'] = $rs['type'];
            $_SESSION['gid'] = $rs['gid'];
            $_SESSION['status'] = $rs['status'];

            $_SESSION['userinfo'] = [
                'gid' => $rs['gid'],
                'zibbs_user' => $rs['username'],
                'zibbs_userid' => $rs['id'],
                'type' => $rs['type']
            ];
            echo true;
        } else {
            echo false;
        }
    }
    public function mailactive()
    {
        $id    = intval($_GET['id']);
        $code  = addslashes($_GET['code']);
        $query = $this->db->query("select * from zibbs_user where status=0 and id=" . $id . " and code='" . $code . "'");
        $rs    = $query->fetch();
        if ($rs) {
            $this->db->exec("update zibbs_user set status=1 where id=" . $id);
            $this->assign('info', '激活成功');
            $this->assign('icon', '1');
        } else {
            $this->assign('info', '操作错误，激活失败！');
            $this->assign('icon', '2');
        }
        $this->render(false);
    }
    public function logout()
    {
        unset($_SESSION['zibbs_user']);
        unset($_SESSION['zibbs_userid']);
        unset($_SESSION['type']);
        unset($_SESSION['gid']);
        unset($_SESSION['status']);
        header("location:./");
    }
    private function dousercount()
    {
        $userscount = require("./config/users.count.php");
        $month      = date("Ym", time());
        $d          = cal_days_in_month(CAL_GREGORIAN, date("m", time()), date("Y", time()));
        $day        = date("j", time());
        if (empty($userscount[$month])) {
            $userscount[$month] = array();
            for ($i = 1; $i <= $d; $i++) {
                if ($i == $day) {
                    $userscount[$month][$day] = 1;
                } else {
                    $userscount[$month][$i] = 0;
                }
            }
        } else {
            if (empty($userscount[$month][$day])) {
                $userscount[$month][$day] = 1;
            } else {
                $userscount[$month][$day] += 1;
            }
        }
        file_put_contents("./config/users.count.php", "<?php\r\nreturn " . var_export($userscount, true) . "\r\n?>");
    }
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
    function wchat_login(){
        $token = self::token();
        $code = $_REQUEST['code'];
        $url = "https://qyapi.weixin.qq.com/cgi-bin/user/getuserinfo?access_token=$token&code=$code";
        $json = self::curl($url, $data=null, $header = false, $method = "GET");
        $user =    json_decode($json['json'],true);
        $key = require("config/key.php");
        require("core/encrypt.php");
//        $user['UserId'] = 306201;
        $UserId = $user['UserId'];

        $group = self::curl("http://10.1.9.18/com.hbky.esb.provider.rlzy.jgrsjk.hrService?module=person&opt=bycode&code=$UserId", $data=null, $header = false, $method = "GET");
        $user = json_decode($group['json'],true);
        $gid = $user[0]['a00100z']?$user[0]['a00100z']:'100';
        $name = $user[0]['a00100n']?$user[0]['a00100n']:'';
        if(!$gid || !$name){
            echo '获取个人信息失败';die;
        }
        $gname = $user[0]['a0010be']?$user[0]['a0010be']:'';
        $company = $user[0]['a0010me']?$user[0]['a0010me']:'';

        $query    = $this->db->query("select * from zibbs_user where id='" . $UserId . "' and password='" . cc_encrypt('hbky' . $UserId, $key) . "'");
        $rs = $query->fetch();
        $type = 0;
        $status = 1;
        if (!$rs) { //如果没有此用户则注册
            $str  = "abcdefghijklmnopqrstuvwxyz0123456789";
            $code = '';
            for ($i = 0; $i < 6; $i++) {
                $code .= substr($str, mt_rand(0, 35), 1);
            }
            $this->db->exec("insert into zibbs_user set gid='{$gid}',gname='{$gname}',company='{$company}',id='{$UserId}',status={$status},username='" . $name . "',email='" . $UserId . "',password='" . cc_encrypt('hbky' . $UserId, $key) . "',avatar='static/images/default.jpg',code='" . $code . "',time=now()");
            $id = $UserId;
//            $id = $this->db->lastInsertId();
        } else {
            $this->db->exec("update zibbs_user set gid='{$gid}',gname='{$gname}',company='{$company}' where id=" . $UserId);
            $id =  $rs['id'];
            $type = $rs['type'];
            $status = $rs['status'];
        }
        $_SESSION['gid'] = $gid;
        $_SESSION['zibbs_user']   = $name;
        $_SESSION['zibbs_userid'] = $id;
        $_SESSION['type'] = $type;
        $_SESSION['status'] = $status;
        header("location:./");
    }
    /*
     * 集成平台登录
     */
    function jcptlogin(){
        $SSOToken = $_REQUEST['SSOToken'];
        if(!$SSOToken){
            echo '参数错误';die;
        }
        $url = "http://10.1.19.211/sso/verifyIdentityApi";
        $data = 'Para={"req":{"code":"main","name":"main"},"account":"admin","pwd":"bPugg5f3AXA5a46SuLtmYcvvu5mLKjft","systemid":"f2209c43-9eb8-4d67-bed4-d203ca8c2279"}';
        $json = self::curl($url, $data, ['Content-Type: application/x-www-form-urlencoded'], "POST");
        $token = json_decode($json['json'],true);
        if(!$token){
            echo '获取token失败';die;
        }
        $access_token = $token['AccessToken'];
        $data = 'Para={"req":{"code":"main","name":"main"},"ssotoken":"' .$SSOToken. '"
,"accesstoken":"'.$access_token.'" }';
        $url = 'http://10.1.19.211/sso/verifySsoToken';
        $json = self::curl($url, $data, ['Content-Type: application/x-www-form-urlencoded'], "POST");
        $user = json_decode($json['json'],true);
        if(!$user){
            echo '获取用户信息失败';die;
        }

        $UserId = $user['main.CODE'];   //用户id
        $key = require("config/key.php");
        require("core/encrypt.php");

        $group = self::curl("http://10.1.9.18/com.hbky.esb.provider.rlzy.jgrsjk.hrService?module=person&opt=bycode&code=$UserId", $data=null, $header = false, $method = "GET");
        $user = json_decode($group['json'],true);
        $gid = $user[0]['a00100z']?$user[0]['a00100z']:'';
        $name = $user[0]['a00100n']?$user[0]['a00100n']:'';
        if(!$gid || !$name){
            echo '获取个人信息失败';die;
        }
        $gname = $user[0]['a0010be']?$user[0]['a0010be']:'';
        $company = $user[0]['a0010me']?$user[0]['a0010me']:'';

        $query    = $this->db->query("select * from zibbs_user where id='" . $UserId . "' and password='" . cc_encrypt('hbky' . $UserId, $key) . "'");
        $rs = $query->fetch();
        $type = 0;
        $status = 1;
        if (!$rs) { //如果没有此用户则注册
            $str  = "abcdefghijklmnopqrstuvwxyz0123456789";
            $code = '';
            for ($i = 0; $i < 6; $i++) {
                $code .= substr($str, mt_rand(0, 35), 1);
            }
            $this->db->exec("insert into zibbs_user set gid='{$gid}',gname='{$gname}',company='{$company}',id='{$UserId}',status={$status},username='" . $name . "',email='" . $UserId . "',password='" . cc_encrypt('hbky' . $UserId, $key) . "',avatar='static/images/default.jpg',code='" . $code . "',time=now()");
            $id = $UserId;
//            $id = $this->db->lastInsertId();
        } else {
            $this->db->exec("update zibbs_user set gid='{$gid}',gname='{$gname}',company='{$company}' where id=" . $UserId);
            $id =  $rs['id'];
            $type = $rs['type'];
            $status = $rs['status'];
        }
        $_SESSION['gid'] = $gid;
        $_SESSION['zibbs_user']   = $name;
        $_SESSION['zibbs_userid'] = $id;
        $_SESSION['type'] = $type;
        $_SESSION['status'] = $status;
        header("location:./");
    }
}
?>
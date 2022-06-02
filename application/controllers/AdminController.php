<?php
class AdminController extends Controller
{
    public function login()
    {
        if (self::is_admin()) {
            header("location:./index.php?route=admin/index");
          	exit;
        }
        $this->render(false);
    }
    public static function is_admin(){
        if($_SESSION['type'] == 1 && $_SESSION['gid'] && $_SESSION['zibbs_user']) {
            return true;
        }
        return false;
    }
    public function dologin()
    {
        $key = require("config/key.php");
        require("core/encrypt.php");
        $username = addslashes($_POST['username']);
        $password = addslashes($_POST['password']);
        $query    = $this->db->query("select * from zibbs_admin where username='" . $username . "' and password='" . cc_encrypt($password, $key) . "'");
        $rs       = $query->fetch();
        if ($rs) {
            $_SESSION['zibbs_admin'] = $rs['username'];
            echo true;
        } else {
            echo false;
        }
    }
    public function backendcount()
    {
        if(!self::is_admin()){
            echo '您无权限';
            exit;
        }
        $postscount = require("./config/posts.count.php");
        $userscount = require("./config/users.count.php");
        $month      = date("Ym", time());
        $jsonarr    = array();
        if (!empty($postscount[$month]) && !empty($userscount[$month])) {
        	$postsdata               = $postscount[$month];
            $jsonarr['x']            = array_keys($postsdata);
            $jsonarr['y'][0]['name'] = '主题数';
            $jsonarr['y'][0]['data'] = array_values($postsdata);
            
            $usersdata               = $userscount[$month];
            $jsonarr['x']            = array_keys($usersdata);
            $jsonarr['y'][1]['name'] = '用户数';
            $jsonarr['y'][1]['data'] = array_values($usersdata);
        }
        if (empty($postscount[$month]) && !empty($userscount[$month])) {
            $usersdata               = $userscount[$month];
            $jsonarr['x']            = array_keys($usersdata);
            $jsonarr['y'][0]['name'] = '用户数';
            $jsonarr['y'][0]['data'] = array_values($usersdata);
        }
        if (!empty($postscount[$month]) && empty($userscount[$month])) {
            $postsdata               = $postscount[$month];
            $jsonarr['x']            = array_keys($postsdata);
            $jsonarr['y'][0]['name'] = '主题数';
            $jsonarr['y'][0]['data'] = array_values($postsdata);
        }
        echo json_encode($jsonarr);
    }
    public function index()
    {
        if(!self::is_admin()){
            echo '您无权限';
            exit;
        }
        $siteurl = $this->getCfg("siteurl");
        $this->assign("siteurl", $siteurl);
        $mysqlversion = $this->db->getAttribute(PDO::ATTR_SERVER_VERSION);
        $this->assign("mysqlversion", $mysqlversion);
        $this->render();
    }
    /*
     * 退出时
     */
    public function logout()
    {
        if(!self::is_admin()){
            echo '您无权限';
            exit;
        }
        unset($_SESSION['zibbs_user']);
        unset($_SESSION['zibbs_userid']);
        unset($_SESSION['type']);
        unset($_SESSION['gid']);
        header("location:./");
//        unset($_SESSION['zibbs_admin']);
//        header("location:./index.php?route=admin/login");
    }
    /*
     * 后台tag列表
     */
    public function tags()
    {
        $gid = $_SESSION['gid'];
        if(!self::is_admin()){
            echo '您无权限';
            exit;
        }
        $siteurl = $this->getCfg("siteurl");
        $this->assign("siteurl", $siteurl);
        require('static/Fenye.class.php');
        $count = $this->db->query("select count(*) from zibbs_tags where gid='{$gid}' order by sort desc,id desc")->fetchColumn();
        $fenye = new Fenye($count, 5);
        $show  = $fenye->show();
        $sql   = $fenye->listcon("select * from zibbs_tags where gid='{$gid}' order by sort desc,id desc");
        $query = $this->db->query($sql);
        if ($query) {
            $tags = $query->fetchAll();
            $this->assign("show", $show);
            $this->assign('tags', $tags);
        }
        $this->render();
    }
    public function addtag()
    {
        if(!self::is_admin()){
            echo '您无权限';
            exit;
        }
        $this->render(false);
    }
    public function doaddtag()
    {
        if(!self::is_admin()){
            echo '您无权限';
            exit;
        }
        $tagname = addslashes(htmlspecialchars($_POST['tagname']));
        $tagdesc = addslashes(htmlspecialchars($_POST['tagdesc']));
        $tagsort = intval($_POST['tagsort']);
        if (!empty($tagname) && !empty($tagdesc)) {
            $rel = $this->db->exec("insert into zibbs_tags(name,description,sort,gid) values('" . $tagname . "','" . $tagdesc . "','" . $tagsort . "','" . $_SESSION['gid'] . "')");
            echo $rel;
        } else {
            echo false;
        }
    }
    public function edittag()
    {
        if(!self::is_admin()){
            echo '您无权限';
            exit;
        }
        $tagid = intval($_GET['tagid']);
        $query = $this->db->query("select * from zibbs_tags where id=$tagid");
        $rs    = $query->fetch();
        $this->assign("rs", $rs);
        $this->render(false);
    }
    public function doedittag()
    {
        if(!self::is_admin()){
            echo '您无权限';
            exit;
        }
        $tagname = addslashes(htmlspecialchars($_POST['tagname']));
        $tagdesc = addslashes(htmlspecialchars($_POST['tagdesc']));
        $tagsort = intval($_POST['tagsort']);
        $tagid   = intval($_POST['tagid']);
        if (!empty($tagname) && !empty($tagdesc) && !empty($tagid)) {
            $rel = $this->db->exec("
  				update zibbs_tags set 
  					name = '" . $tagname . "',
  					description = '" . $tagdesc . "',
  					sort = '" . $tagsort . "' where id=" . $tagid);
            echo $rel;
        } else {
            echo false;
        }
    }
    public function deletetag()
    {
        if(!self::is_admin()){
            echo '您无权限';
            exit;
        }
        $tagid = intval($_POST['tagid']);
        $rel   = $this->db->exec("delete from zibbs_tags where id=$tagid");
        if ($rel) {
            echo true;
        } else {
            echo false;
        }
    }
    public function users()
    {
        if(!self::is_admin()){
            echo '您无权限';
            exit;
        }
        $gid = $_SESSION['gid'];
        $siteurl = $this->getCfg("siteurl");
        $this->assign("siteurl", $siteurl);
        require('static/Fenye.class.php');
        $count = $this->db->query("select count(*) from zibbs_user where gid='{$gid}' and status in (0,1,2) order by id desc")->fetchColumn();
        $fenye = new Fenye($count, 15);
        $show  = $fenye->show();
        $sql   = $fenye->listcon("select * from zibbs_user where gid='{$gid}' and status in (0,1,2) order by id desc");
        $query = $this->db->query($sql);
        if ($query) {
            $users = $query->fetchAll();
            $this->assign("show", $show);
            $this->assign('users', $users);
        }
        $this->render();
    }
    public function posts()
    {
        if(!self::is_admin()){
            echo '您无权限';
            exit;
        }
        $gid = $_SESSION['gid'];
        $siteurl = $this->getCfg("siteurl");
        $this->assign("siteurl", $siteurl);
        require('static/Fenye.class.php');
        $count = $this->db->query("select count(*) from zibbs_posts where gid='{$gid}' order by id desc")->fetchColumn();
        $fenye = new Fenye($count, 15);
        $show  = $fenye->show();
        $sql   = $fenye->listcon("select * from zibbs_posts where gid='{$gid}' order by id desc");
        $query = $this->db->query($sql);
        if ($query) {
            $posts = $query->fetchAll();
            $this->assign("show", $show);
            $this->assign('posts', $posts);
        }
        $this->render();
    }
    public function replies()
    {
        if(!self::is_admin()){
            echo '您无权限';
            exit;
        }
        $gid = $_SESSION['gid'];
        $siteurl = $this->getCfg("siteurl");
        $this->assign("siteurl", $siteurl);
        require('static/Fenye.class.php');
        $count = $this->db->query("select count(*) from zibbs_replies where gid='{$gid}' order by rid desc")->fetchColumn();
        $fenye = new Fenye($count, 15);
        $show  = $fenye->show();
        $sql   = $fenye->listcon("select * from zibbs_replies where gid='{$gid}' order by rid desc");
        $query = $this->db->query($sql);
        if ($query) {
            $replies = $query->fetchAll();
            $this->assign("show", $show);
            $this->assign('replies', $replies);
        }
        $this->render();
    }
    public function clean()
    {
        if(!self::is_admin()){
            echo '您无权限';
            exit;
        }
        $siteurl = $this->getCfg("siteurl");
        $this->assign("siteurl", $siteurl);
        require('static/Fenye.class.php');
        $count = $this->db->query("select count(*) from zibbs_master_pmb a,zibbs_slave_pmb u where a.t_num=u.t_num and a.status='closed' and u.status='closed'")->fetchColumn();
        $fenye = new Fenye($count, 15);
        $show  = $fenye->show();
        $sql   = $fenye->listcon("select a.* from zibbs_master_pmb a,zibbs_slave_pmb u where a.t_num=u.t_num and a.status='closed' and u.status='closed' order by a.num desc");
        $query = $this->db->query($sql);
        if ($query) {
            $cleanmsg = $query->fetchAll();
            $this->assign("show", $show);
            $this->assign('cleanmsg', $cleanmsg);
        }
        $this->render();
    }
    public function deletemsg()
    {
        if(!self::is_admin()){
            echo '您无权限';
            exit;
        }
        $mid  = intval($_POST['mid']);
        $rel1 = $this->db->exec("delete from zibbs_master_pmb where t_num=" . $mid . " or r_num=" . $mid);
        $rel2 = $this->db->exec("delete from zibbs_slave_pmb where t_num=" . $mid);
        if ($rel1 && $rel2) {
            echo true;
        } else {
            echo false;
        }
    }
    public function deleteallmsg()
    {
        if(!self::is_admin()){
            echo '您无权限';
            exit;
        }
        $midarr = $_POST['mid'];
        foreach ($midarr as $mid) {
        	if(is_numeric($mid)){
            $rel1 = $this->db->exec("delete from zibbs_master_pmb where t_num=" . $mid . " or r_num=" . $mid);
            $rel2 = $this->db->exec("delete from zibbs_slave_pmb where t_num=" . $mid);
          }
        }
        if ($rel1 && $rel2) {
            echo true;
        } else {
            echo false;
        }
    }
    public function setting()
    {
        if(!self::is_admin()){
            echo '您无权限';
            exit;
        }
        $siteurl = $this->getCfg("siteurl");
        $this->assign("siteurl", $siteurl);
        $query      = $this->db->query("select * from zibbs_setting where id=1");
        $settingarr = $query->fetch();
        $this->assign("settingarr", $settingarr);
        $sitekey = require("./config/key.php");
        $this->assign("sitekey", $sitekey);
        $this->render();
    }
    public function dosetting()
    {
        if(!self::is_admin()){
            echo '您无权限';
            exit;
        }
        $oldkey = require("config/key.php");
        require("core/encrypt.php");
        $siteurl         = addslashes(htmlspecialchars($_POST['siteurl']));
        $sitetitle       = addslashes(htmlspecialchars($_POST['sitetitle']));
        $sitekeywords    = addslashes(htmlspecialchars($_POST['sitekeywords']));
        $sitedescription = addslashes(htmlspecialchars($_POST['sitedescription']));
        $smtphost        = addslashes(htmlspecialchars($_POST['smtphost']));
        $smtpuser        = addslashes(htmlspecialchars($_POST['smtpuser']));
        $smtppsw         = addslashes(htmlspecialchars($_POST['smtppsw']));
        $smtpemail       = addslashes(htmlspecialchars($_POST['smtpemail']));
        $smtpsubject     = addslashes(htmlspecialchars($_POST['smtpsubject']));
        $smtpcontent     = addslashes(htmlspecialchars($_POST['smtpcontent']));
        $sitekey         = addslashes(htmlspecialchars($_POST['sitekey']));
        $bbsmeta         = addslashes(htmlspecialchars($_POST['bbsmeta']));
        $bbslink         = addslashes(htmlspecialchars($_POST['bbslink']));
        $rel1            = file_put_contents("./config/key.php", "<?php\r\nreturn " . var_export($sitekey, true) . "\r\n?>");
        $queryuser       = $this->db->query("select * from zibbs_user");
        while ($u = $queryuser->fetch()) {
            $oldpwd = $u['password'];
            $psw    = cc_decrypt($oldpwd, $oldkey);
            $this->db->exec("update zibbs_user set password='" . cc_encrypt($psw, $sitekey) . "' where id=" . $u['id']);
        }
        $queryadm = $this->db->query("select * from zibbs_admin");
        while ($u = $queryadm->fetch()) {
            $oldpwd = $u['password'];
            $psw    = cc_decrypt($oldpwd, $oldkey);
            $this->db->exec("update zibbs_admin set password='" . cc_encrypt($psw, $sitekey) . "' where id=" . $u['id']);
        }
        $rel2 = $this->db->exec("update zibbs_setting set 
			siteurl = '" . $siteurl . "',
			sitetitle = '" . $sitetitle . "',
			sitekeywords = '" . $sitekeywords . "',
			sitedescription = '" . $sitedescription . "',
			bbsmeta = '" . $bbsmeta . "',
			bbslink = '" . $bbslink . "',
			smtphost = '" . $smtphost . "',
			smtpuser = '" . $smtpuser . "',
			smtppsw = '" . $smtppsw . "',
			smtpemail = '" . $smtpemail . "',
			smtpsubject = '" . $smtpsubject . "',
			smtpcontent = '" . $smtpcontent . "' where id = 1");
        if ($rel1 || $rel2) {
            echo true;
        } else {
            echo false;
        }
    }
    public function releaseuser()
    {
        if(!self::is_admin()){
            echo '您无权限';
            exit;
        }
        $uid = intval($_POST['uid']);
        $rel = $this->db->exec("update zibbs_user set status=1 where id=" . $uid);
        if ($rel) {
            echo true;
        } else {
            echo false;
        }
    }
    public function sealuser()
    {
        if(!self::is_admin()){
            echo '您无权限';
            exit;
        }
        $uid = intval($_POST['uid']);
        $rel = $this->db->exec("update zibbs_user set status=2 where id=" . $uid);
        if ($rel) {
            echo true;
        } else {
            echo false;
        }
    }
    public function activeuser()
    {
        if(!self::is_admin()){
            echo '您无权限';
            exit;
        }
        $uid = intval($_POST['uid']);
        $rel = $this->db->exec("update zibbs_user set status=1 where id=" . $uid);
        if ($rel) {
            echo true;
        } else {
            echo false;
        }
    }
    public function emptyuser()
    {
        if(!self::is_admin()){
            echo '您无权限';
            exit;
        }
        $uid     = intval($_POST['uid']);
        $rel     = $this->db->exec("delete from  zibbs_posts where userid=" . $uid);
        $query   = $this->db->query("select * from zibbs_replies where ruserid=" . $uid);
        $replies = $query->fetchAll();
        foreach ($replies as $reply) {
            $count = $this->db->query("select count(*) from zibbs_posts where id=" . $reply['pid'])->fetchColumn();
            if ($count > 0) {
                $this->db->exec("update zibbs_posts set answers=answers-1 where id=" . $reply['pid']);
            }
            $this->db->exec("delete from  zibbs_replies where ruserid=" . $uid);
        }
        $this->db->exec("update zibbs_master_pmb set status='closed' where createdByUserNum=" . $uid);
        $this->db->exec("update zibbs_slave_pmb set status='closed' where createdByUserNum=" . $uid);
        $this->db->exec("update zibbs_user set msgmark='0' where id=" . $uid);
        echo true;
    }
    public function deleteuser()
    {
        if(!self::is_admin()){
            echo '您无权限';
            exit;
        }
        $uid = intval($_POST['uid']);
        $rel = $this->db->exec("delete from  zibbs_user where id=" . $uid);
        if ($rel) {
            echo true;
        } else {
            echo false;
        }
    }
    public function movepost()
    {
        if(!self::is_admin()){
            echo '您无权限';
            exit;
        }
        $pid = intval($_GET['pid']);
        $this->assign('pid', $pid);
        $query  = $this->db->query("select tagid from zibbs_posts where id=" . $pid);
        $tagarr = $query->fetch();
        $this->assign("tagid", $tagarr['tagid']);
        $query = $this->db->query("select * from zibbs_tags order by sort desc,id desc");
        $rs    = $query->fetchAll();
        $this->assign("showtags", $rs);
        $this->render(false);
    }
    //审核论坛
    public function edit()
    {
        if(!self::is_admin()){
            echo '您无权限';
            exit;
        }
        if($_POST['status']){
            $status = 0;
        } else {
            $status = 1;
        }
        $pid = intval($_POST['pid']);
        $rel = $this->db->exec("update  zibbs_posts set status={$status} where id=" . $pid);
        if ($rel) {
            echo true;
        } else {
            echo false;
        }
    }
    //审核论坛
    public function edit_reply()
    {
        if(!self::is_admin()){
            echo '您无权限';
            exit;
        }
        if($_POST['status']){
            $status = 0;
        } else {
            $status = 1;
        }
        $rid = intval($_POST['rid']);
        $rel = $this->db->exec("update zibbs_replies set status={$status} where rid=" . $rid);
        if ($rel) {
            echo true;
        } else {
            echo false;
        }
    }

    public function domovepost()
    {
        if(!self::is_admin()){
            echo '您无权限';
            exit;
        }
        $tagid = intval($_POST['tagid']);
        $pid   = intval($_POST['pid']);
        $rel   = $this->db->exec("update zibbs_posts set tagid=" . $tagid . " where id=" . $pid);
        if ($rel) {
            echo true;
        } else {
            echo false;
        }
    }
    public function deletepost()
    {
        if(!self::is_admin()){
            echo '您无权限';
            exit;
        }
        $pid = intval($_POST['pid']);
        $rel = $this->db->exec("delete from zibbs_posts where id=" . $pid);
        $this->db->exec("delete from zibbs_replies where pid=" . $pid);
        if ($rel) {
            echo true;
        } else {
            echo false;
        }
    }
    public function deletereply()
    {
        if(!self::is_admin()){
            echo '您无权限';
            exit;
        }
        $rid   = intval($_POST['rid']);
        $query = $this->db->query("select pid from zibbs_replies where rid=" . $rid);
        $arr   = $query->fetch();
        $pid   = $arr['pid'];
        $rel   = $this->db->exec("delete from zibbs_replies where rid=" . $rid);
        $this->db->exec("update zibbs_posts set answers=answers-1 where id=" . $pid);
        if ($rel) {
            echo true;
        } else {
            echo false;
        }
    }
}
?>
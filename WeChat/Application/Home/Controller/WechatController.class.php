<?php
/**
 * Created by PhpStorm.
 * User: 张伟强
 * Date: 2019/3/27
 * Time: 19:44
 */

namespace Home\Controller;

use Home\Model\WechatModel;
use Think\Controller;


class WechatController extends Controller
{
    //验证接入APi
    public function index(){
        //获得参数 signature nonce token timestamp echostr
        $nonce = $_GET['nonce'];
        $token = 'zwq';
        $timestamp = $_GET['timestamp'];
        $echostr = $_GET['echostr'];
        $signature = $_GET['signature'];
        //形成数组，然后按字典序排序
        $array = array();
        $array = array($nonce, $timestamp, $token);
        sort($array);
        //拼接成字符串,sha1加密 ，然后与signature进行校验
        $str = sha1(implode($array));
        if ($str == $signature && $echostr) {
            //第一次接入weixin api接口的时候
            echo $echostr;
            exit;
        } else {
            $this->reponseMsg();
        }
    }

    // 接收事件推送并回复
    public function reponseMsg(){
        //1.获取到微信推送过来post数据（xml格式）
        $postArr = $GLOBALS['HTTP_RAW_POST_DATA'];
        //2.处理消息类型，并设置回复类型和内容
        $postObj = simplexml_load_string($postArr);
        //判断该数据包是否是订阅的事件推送
        if (strtolower($postObj->MsgType) == 'event') {
            //如果是关注 subscribe 事件
            if (strtolower($postObj->Event == 'subscribe')) {
                //回复用户消息(纯文本格式)
                $content = '欢迎关注！！';
                $wechatdao = new WechatModel();
                $wechatdao->responseText($postObj, $content);

            }
        }

        //用户发送tuwen1关键字的时候，回复一个单图文
        if (strtolower($postObj->MsgType) == 'text' && trim($postObj->Content) == 't1') {

            $arr = array(
                array(
                    'title'       => '慕课网',
                    'description' => "imooc is very cool",
                    'picUrl'      => 'https://www.imooc.com/static/img/index/logo.png',
                    'url'         => 'http://www.imooc.com',
                ),
            );
            //实例化模型
            $wechatdao = new WechatModel();
            $wechatdao->responseNews($postObj, $arr);

        }
         //用户发送tuwen2关键字的时候，回复一个多图文
        else if (strtolower($postObj->MsgType) == 'text' && trim($postObj->Content) == 't2') {
            $arr = array(
                array(
                    'title'       => 'imooc',
                    'description' => "imooc is very cool",
                    'picUrl'      => 'https://www.imooc.com/static/img/index/logo.png',
                    'url'         => 'http://www.imooc.com',
                ),
                array(
                    'title'       => 'baidu',
                    'description' => "baidu is very cool",
                    'picUrl'      => 'https://www.baidu.com/img/bdlogo.png',
                    'url'         => 'http://www.baidu.com',
                ),
                array(
                    'title'       => 'qq',
                    'description' => "qq is very cool",
                    'picUrl'      => 'https://mat1.gtimg.com/pingjs/ext2020/qqindex2018/dist/img/qq_logo_2x.png',
                    'url'         => 'http://www.qq.com',
                ),
            );
            $wechatdao = new WechatModel();
            $wechatdao->responseNews($postObj, $arr);

            //进行多图文发送时，子图文个数不能超过10个
        } //纯文本回复
        else {
            switch (trim($postObj->Content)) {
                case 1:
                    $content = '您输入的数字是1';
                    break;
                case 2:
                    $content = '您输入的数字是2';
                    break;
                case 3:
                    $content = "<a href='http://www.baidu.com'>百度</a>";
                    break;
                case 4:
                    $content ="<a href='http://zwq666.top/myWorkspace/WeChat/index.php/Home/Wechat/definedItem'>实验四</a>" ;
                    break;
                case '5':
                    $content = "<a href='http://zwq666.top/myWorkspace/WeChat/index.php/Home/Wechat/shareWX'>实验五</a>";
                    break;
            }
            $wechatdao = new WechatModel();
            $wechatdao->responseText($postObj, $content);

        }
    }


    //获取access_token
    function getWxAccessToken(){
        //1.请求url地址
        //$appid = 'wx612271828ee4de36';//公众号（我是大嘤雄）
       // $appsecret = '4ece1e7d55c0ff8a627526d0d8d0856a';//公众号（我是大嘤雄）
        
         $appid = 'wx0291ed993373aabf';//测试号
         $appsecret = '4b5b8a9580d9af60a13facec13a51fdb';//测试号
       
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid="
         . $appid . "&secret=" . $appsecret;
        if ($_SESSION['access_token_expire_time'] > time()&& $_SESSION['access_token']) {
            $access_token = $_SESSION['access_token'];    
        } else {
            // $res = $this->http_curl($url);
            $wechatdao = new WechatModel();
            $res= $wechatdao->getWx($url);

            $access_token = $res['access_token'];
            $_SESSION['access_token'] = $access_token;
            $_SESSION['access_token_expire_time'] = time() + 7000;
        }
        var_dump($access_token);
        return $access_token;
    }

    //获取jsapi_ticket
    function getJsApiTicket(){
        if ($_SESSION['jsapi_ticket_expire_time'] > time() && $_SESSION['jsapi_ticket']) {
            $jsapi_ticket = $_SESSION['jsapi_ticket'];
            
        } else {
            $access_token = $this->getWxAccessToken();
            $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=" 
            . $access_token . "&type=jsapi";
            // $res = $this->http_curl($url);
            $wechatdao = new WechatModel();
            $res= $wechatdao->getWx($url);
            $jsapi_ticket = $res['ticket'];
            $_SESSION['jsapi_ticket'] = $jsapi_ticket;
            $_SESSION['jsapi_ticket_expire_time'] = time() + 7000;
        }
        var_dump($jsapi_ticket);
        return $jsapi_ticket;
    }

    //创建自定义菜单
    function definedItem(){
        $access_token = $this->getWxAccessToken();
        $url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=".$access_token;
        $postArr =array(
            'button' =>array(
            array(
                 'name'=>urlencode('用户信息'),
                 'type'=>'view',
                 'url' =>'http://zwq666.top/myWorkspace/WeChat/index.php/Home/Wechat/getUserInfo'
            ),//第一个一级菜单
            array(
                'name'=>urlencode('菜单二'),
                'sub_button'=>array( 
                  array(
                    'name'=>urlencode('啊啊'),
                    'type'=>'click',
                    'key' =>'aa',
                  ),//第一个二级菜单
                  array(
                    'name'=>urlencode('实验四'),
                    'type'=>'view',
                    'url' =>'http://zwq666.top/myWorkspace/WeChat/index.php/Home/Wechat/definedItem',
                  ),//第二个二级菜单
                ),
            ),//第二个一级菜单
            array(
                'name'=>urlencode('实验五'),
                'type'=>'view',
                'url' =>'http://zwq666.top/myWorkspace/WeChat/index.php/Home/Wechat/shareWX',
            ),//第三个一级菜单
        ),
        );
        echo '<hr/>';
        var_dump($postArr);
        echo '<hr/>';
        echo $postJson =  urldecode(json_encode($postArr));//数组转json
        echo '<hr/>';

        $wechatdao = new WechatModel();
        $res= $wechatdao->getWx($url,'post','json',$postJson);
         var_dump($res);
    }
    // //网页授权登录
    // //获取用户信息
    // function getUserInfo(){
    //     //1.获取到code
    //     $appid="wx0291ed993373aabf";
    //     $redirect_uri=urlencode("http://zwq666.top/myWorkspace/WeChat/index.php/Home/Wechat/getUserOpenId");
    //     $url="https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$appid."&redirect_uri=".$redirect_uri."&response_type=code&scope=snsapi_userinfo&state=zwq#wechat_redirect";
    //     header('Location:'.$url);
    // }
    // //获取用户OpenId
    // function getUserOpenId(){
    //      //2.获取网页授权的access_token
    //      $appid="wx0291ed993373aabf";
    //      $appsecret="4b5b8a9580d9af60a13facec13a51fdb";
    //      $code=$_GET['code'];
    //      $url="https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$appid."&secret=".$appsecret."&code=".$code."&grant_type=authorization_code";
    //     //3.拉取用户的openid
    //     $wechatdao = new WechatModel();
    //     $res= $wechatdao->getWx($url);
    //     var_dump($res);
    //     $access_token = $res['access_token'];
    //     $openid= $res['openid'];
    //     $url="https://api.weixin.qq.com/sns/userinfo?access_token=".$access_token."&openid=".$openid."&lang=zh_CN";
    //     $wechatdao = new WechatModel();
    //     $res= $wechatdao->getWx($url);
    //     var_dump($res);

    //     $this->assign('headimgurl',$res['headimgurl']);
    //     $this->assign('nickname', $res['nickname']);
    //     $this->assign('sex',$res['sex']);
    //     $this->assign('city', $res['city']);
    //     $this->assign('province', $res['province']);
    //     $this->assign('country', $res['country']);
    //     $this->assign('headimgurl', $res['headimgurl']);
    //     $this->display('UserInfo');
    // }

    //获取用户信息
    function getUserInfo(){
      $access_token = $this->getWxAccessToken();
      $openid ="o0_kT1aYsk3RIPEhLew5YzJbfI8U";
       $url="https://api.weixin.qq.com/cgi-bin/user/info?access_token=".$access_token."&openid=".$openid."&lang=zh_CN";
       $wechatdao = new WechatModel();
       $res= $wechatdao->getWx($url);
       var_dump($res);
       $this->assign('headimgurl',$res['headimgurl']);
       $this->assign('nickname', $res['nickname']);
       $this->assign('sex',$res['sex']);
       $this->assign('city', $res['city']);
       $this->assign('province', $res['province']);
       $this->assign('country', $res['country']);
       $this->assign('headimgurl', $res['headimgurl']);
       $this->display('UserInfo');
    }

    //获取随机码
    function getRancode($num = 16)
    {
        $array = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', '0', 'l', '2', '3', '4', 'S', '6', '7', '8', '9');

        $tmpstr = '';
        $max = count($array);
        for ($i = 1; $i <= $num; $i++) {
            $key = rand(0, $max - 1);
            $tmpstr .= $array[$key];
        }
        return $tmpstr;
    }

    //jssdk接口
    function shareWX()
    {
        //获得jsapi_ticket票据
        $jsapi_ticket = $this->getJsApiTicket();
        $url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];    //获取当前完整url
        $timestamp = time();
        $noncestr = $this->getRancode();

        $signature = "jsapi_ticket=" . $jsapi_ticket . 
                     "&noncestr=" . $noncestr . 
                     "&timestamp=" . $timestamp .
                     "&url=" . $url;
        $signature = sha1($signature);

        $this->assign('timestamp', $timestamp);
        $this->assign('noncestr', $noncestr);
        $this->assign('signature', $signature);
        $this->display('index');
    }
}
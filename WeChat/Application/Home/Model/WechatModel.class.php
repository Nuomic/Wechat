<?php

namespace Home\Model;


class WechatModel
{
    //回复多图文类型的微信消息SDK
    public function responseNews($postObj, $arr)
    {
        $toUser   = $postObj->FromUserName;
        $fromUser = $postObj->ToUserName;
        $template = "<xml>
					<ToUserName><![CDATA[%s]]></ToUserName>
					<FromUserName><![CDATA[%s]]></FromUserName>
					<CreateTime>%s</CreateTime>
					<MsgType><![CDATA[%s]]></MsgType>
					<ArticleCount>" . count($arr) . "</ArticleCount>
					<Articles>";
        foreach ($arr as $k => $v) {
            $template .= "<item>
						<Title><![CDATA[" . $v['title'] . "]]></Title> 
						<Description><![CDATA[" . $v['description'] . "]]></Description>
						<PicUrl><![CDATA[" . $v['picUrl'] . "]]></PicUrl>
						<Url><![CDATA[" . $v['url'] . "]]></Url>
						</item>";
        }

        $template .= "</Articles>
					</xml> ";
        echo sprintf($template, $toUser, $fromUser, time(), 'news');
    }

    // 回复单文本
    public function responseText($postObj, $content)
    {
        $template = "<xml>
                    <ToUserName><![CDATA[%s]]></ToUserName>
                    <FromUserName><![CDATA[%s]]></FromUserName>
                    <CreateTime>%s</CreateTime>
                    <MsgType><![CDATA[%s]]></MsgType>
                    <Content><![CDATA[%s]]></Content>
                    </xml>";

        $fromUser = $postObj->ToUserName;
        $toUser   = $postObj->FromUserName;
        $time     = time();
        $msgType  = 'text';
        echo sprintf($template, $toUser, $fromUser, $time, $msgType, $content);
    }

    //回复微信用户的关注事件
    public function responseSubscribe($postObj, $arr)
    {

        $this->responseNews($postObj, $arr);
    }


   //CURL抓取
 public function getWx($url,$type='get',$res='json',$arr='')
    {
        //1.获取url
        //2初始化
        $ch = curl_init();
        //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        //3.设置参数
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if($type=='post'){
            curl_setopt($ch,CURLOPT_POST,1);
            curl_setopt($ch,CURLOPT_POSTFIELDS,$arr);
        }
        //4.调用接口
        $output = curl_exec($ch);
        //5.关闭curl
        
        if($res=='json'){
            if (curl_errno($ch)) {
                //报错
            return curl_error($ch);
        }else{
            return json_decode($output,true);
        }
        curl_close($ch);
        }
    }
}
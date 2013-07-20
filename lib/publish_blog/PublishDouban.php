<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PublishTqq
 *
 * @author yubchen
 */
class PublishDouban extends PublishBase{
    private $customer_id;
    private $platform_id;
    private $blogAccount;
    private $title;
    private $text;
    private $picUrl;
    private $product_id;
    private $is_public;
    private $redirect_uri;
    
    public function __construct($customer_id, $is_public) {
        parent::__construct();
        $this->platform_id =  sfConfig::get("app_douban_platform_id");
        $this->customer_id = $customer_id;
        $this->is_public = $is_public;
        if($is_public==1){
           $this->blogAccount = Doctrine::getTable("PublicAccount")->findOneBy("platform_id", $this->platform_id);
        }else{
           $this->blogAccount = Doctrine::getTable("BlogAccount")->findOneBy("platform_idAndcustomer_id", array($this->platform_id,  $this->customer_id));
        }
    }
    public function publish(){
        $this->logger->info(date("Y-m-d H:m:s")." [start publish Kaixin blog customer_id=".$this->customer_id."]");
        
        if(!$this->blogAccount){
            return false;
        }
        if(!$this->isTokenVaild()){
            return false;
        }
//        rec_title	false	string	推荐网址的标题
//        rec_url	false	string	推荐网址的href
//        rec_desc	false	string	推荐网址的描述
//        rec_image	false	string	推荐网址的附图url
//        $result = $this->share("", $this->title, "http://item.taobao.com/item.htm?id=".$this->product_id, $this->text, $this->picUrl);
        $params = array(   
            'rec_desc' => '',
            "rec_url" => "http://item.taobao.com/item.htm?id=".$this->product_id,
            'rec_title'=>  $this->title,
            'rec_image'=>  $this->picUrl,
            'text'=>$this->text
         );

        $this->logger->info(date("Y-m-d H:m:s")." [publish Douban blog, param=".json_encode($params).", customer_id=".$this->customer_id."]");
        $response = Http::request("https://api.douban.com/shuo/v2/statuses/", $params, "POST", false,array("Authorization: Bearer ".$this->blogAccount->getToken(),"User-Agent: 生活微分(d4cheng.com)"));
        $this->logger->info(date("Y-m-d H:m:s")." [publish Douban blog, response=".$response.", customer_id=".$this->customer_id."]");
        $result = json_decode($response,true);
        /*
         * Array ( 
         *      [category] => 
         *      [reshared_count] => 0 
         *      [layout] => 1 
         *      [entities] => Array ( 
         *              [user_mentions] => Array ( ) 
         *              [topics] => Array ( ) 
         *              [urls] => Array ( 
         *                  [0] => Array ( 
         *                      [indices] => Array ( [0] => 37 [1] => 57 ) 
         *                      [url] => http://dou.bz/195Q2w 
         *                      [expanded_url] => http://item.taobao.com?id=21287183461 ) ) ) 
         *      [title] => 推荐网址 
         *      [muted] => 
         *      [text] => 包包2013韩版新款潮包春夏 女包百搭休闲复古包单肩潮包斜挎小包 详细: http://dou.bz/195Q2w 价格:24.90元 
         *      [created_at] => 2013-07-19 20:38:10 
         *      [can_reply] => 1 
         *      [liked] => 
         *      [source] => Array ( 
         *          [href] => http://www.d4cheng.com 
         *          [title] => 生活微分 ) 
         *      [like_count] => 0 
         *      [comments_count] => 0 
         *      [user] => Array ( 
         *          [screen_name] => 生活微分 
         *          [description] => 
         *          [small_avatar] => http://img3.douban.com/icon/user_normal.jpg 
         *          [uid] => 53048269 
         *          [type] => user 
         *          [id] => 53048269 
         *          [large_avatar] => http://img3.douban.com/icon/user_large.jpg ) 
         *      [is_follow] => 
         *      [has_photo] => 
         *      [type] => 0 
         *      [id] => 1192607440 
         *      [attachments] => Array ( 
         *          [0] => Array ( 
         *              [description] => 
         *              [title] => 包包2013新款 潮 女夏 韩版包金太狼的幸福生活米小米李小璐同款 
         *              [media] => Array ( 
         *                  [0] => Array ( 
         *                      [src] => http://img3.douban.com/view/status/small/public/61f257996bef89e.jpg 
         *                      [sizes] => Array ( 
         *                          [small] => Array ( [0] => 128 [1] => 128 ) 
         *                          [raw] => Array ( [0] => 651 [1] => 651 ) 
         *                          [median] => Array ( [0] => 460 [1] => 460 ) ) 
         *                          [secret_pid] => 
         *                          [original_src] => http://img02.taobaocdn.com/bao/uploaded/i2/13942025045019474/T1Nn4AFgtbXXXXXXXX_!!0-item_pic.jpg 
         *                          [href] => http://dou.bz/2BVKRc 
         *                          [type] => image ) ) 
         *              [expaned_href] => http://item.taobao.com/item.htm?id=26328548144 
         *              [caption] => 
         *              [href] => http://dou.bz/2BVKRc 
         *              [type] => 
         *              [properties] => Array ( ) ) ) ) 
         * 真他妈的长啊！！！
         */
        if(isset($result["code"])){
            $this->logger->info(date("Y-m-d H:m:s")." [end publish Douban blog, publish fail,blog=".$this->text.", length=".  strlen($this->text).", customer_id=".$this->customer_id."]");
            return false;
        }
//
        $weiboId = $result["id"];
        if($this->is_public==1){
            Doctrine::getTable("Blog")->addBlog($this->customer_id, $this->platform_id,$this->product_id, null, $weiboId, $this->text,$this->is_public);
        }else{
            Doctrine::getTable("Blog")->addBlog($this->customer_id, $this->platform_id,$this->product_id, $this->blogAccount->getId(), $weiboId, $this->text,  $this->is_public);
        }
        
        $this->logger->info(date("Y-m-d H:m:s")." [end publish Douban blog, publish success, customer_id=".$this->customer_id."]");
        return true;
    }
    public function isTokenVaild(){
        $this->logger->info(date("Y-m-d H:m:s")." [start validate Douban token customer_id=".$this->customer_id."]");
        if($this->blogAccount->getVar1()>time()){
            $this->logger->info(date("Y-m-d H:m:s")." [Douban token is validate, customer_id=".$this->customer_id."]");
            return true;
        }else{
            $flag = $this->reflashToken($this->blogAccount);
            if($flag){
                $this->logger->info(date("Y-m-d H:m:s")." [Douban token is validate, customer_id=".$this->customer_id."]");
                return true;
            }else{
                $this->logger->info(date("Y-m-d H:m:s")." [Douban token is unvalidate, customer_id=".$this->customer_id."]");
                return false;
            }
        }
    }
    
    public function reflashToken(){
        $blogAccount = $this->blogAccount;
        $this->logger->info(date("Y-m-d H:m:s")." [start reflash Douban token customer_id=".$this->customer_id."]");
        $reflash_token = $blogAccount->getVar2();
        echo $reflash_token;
        $appkey = sfConfig::get("app_douban_app_key");
        $appsecret = sfConfig::get("app_douban_app_secret");
        
        $url = sfConfig::get("app_douban_access_token_url");
        $this->logger->info(date("Y-m-d H:m:s")." [reflash token Douban token url=".$url." , customer_id=".$this->customer_id."]");
        $response = Http::request($url,array(
            "client_id"=>$appkey,
            "client_secret"=>$appsecret,
            "redirect_uri"=>$this->redirect_uri,
            "grant_type"=>"refresh_token",
            "refresh_token"=>$reflash_token
        ),"POST");
        $param = json_decode($response,true);
        print_r($param);
        
        /* 
         * Array ( [msg] => invalid_refresh_token : 155194355_9b8c5e326d5f0378a2f0ff7edad72ef5 [code] => 119 [request] => POST /service/auth2/token ) 
         * Array ( [access_token] => 280d8018e33263ff7828804aa9972bfd [douban_user_name] => 生活微分 [douban_user_id] => 53048269 [expires_in] => 604800 [refresh_token] => 0e36c6c30615c2092aa454491241461b ) 
         */
       
        $this->logger->info(date("Y-m-d H:m:s")." [reflash token Douban token response=".$response." , customer_id=".$this->customer_id."]");
        if(isset($param['access_token'])&&isset($param["expires_in"])&&isset($param["refresh_token"])){
            if($this->is_public){
                Doctrine::getTable("PublicAccount")->addOrUpdateBlogAccount($this->customer_id, $this->platform_id, $param["access_token"],$param["douban_user_name"],0,time()+$param["expires_in"],$param["refresh_token"],$param['douban_user_id']);
            }else{
                Doctrine::getTable("BlogAccount")->addOrUpdateBlogAccount($this->customer_id, $this->platform_id, $param["access_token"],$param["douban_user_name"],0,time()+$param["expires_in"],$param["refresh_token"],$param['douban_user_id']);
            }
            $this->logger->info(date("Y-m-d H:m:s")." [reflash token Douban token success, customer_id=".$this->customer_id."]");
            $this->logger->info(date("Y-m-d H:m:s")." [End reflash Douban token customer_id=".$this->customer_id."]");
            return true;
        }else{
            $this->logger->info(date("Y-m-d H:m:s")." [reflash token Douban token fail, customer_id=".$this->customer_id."]");
            $blogAccount->delete();
            $this->logger->info(date("Y-m-d H:m:s")." [End reflash Douban token customer_id=".$this->customer_id."]");
            return false;
        }
        
    }
    public function setText($text){
        $this->text = $text;
    }
    public function setPicUrl($url){
        $this->picUrl = $url;
    }
    public function setProductId($id){
        $this->product_id = $id;
    }
    public function setRedirectUri($uri){
        $this->redirect_uri = $uri;
    }
    public function setTitle($title){
        $this->title = $title;
    }
    
    
    //以下方法未使用
    function share($text, $title, $url, $description='', $pic=''){
        $params=array(
            'text'=>$text,
            'rec_title'=>$title,
            'rec_url'=>$url,
            'rec_desc'=>$description,
            'rec_image'=>$pic
        );
        $url='https://api.douban.com/shuo/v2/statuses/';
        return $this->api($url, $params, 'POST');
    }

    function api($url, $params, $method='GET'){
        $headers[]="Authorization: Bearer ".$this->blogAccount->getToken();
        if($method=='GET'){
            $result=$this->http($url.'?'.http_build_query($params), '', 'GET',$headers);
        }else{
            $result=$this->http($url, http_build_query($params), 'POST', $headers);
        }
        return $result;
    }

    function http($url, $postfields='', $method='GET', $headers=array()){
        $ci=curl_init();
        curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ci, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ci, CURLOPT_TIMEOUT, 30);
        if($method=='POST'){
            curl_setopt($ci, CURLOPT_POST, TRUE);
            if($postfields!='')curl_setopt($ci, CURLOPT_POSTFIELDS, $postfields);
        }
        $headers[]="User-Agent: 生活微分(d4cheng.com)";
        curl_setopt($ci, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ci, CURLOPT_URL, $url);
        $response=curl_exec($ci);
        curl_close($ci);
        $json_r=array();
        if($response!='')$json_r=json_decode($response, true);
        return $json_r;
    }
    
}

?>

<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of publishRenren
 * 
 * @author yubchen
 */
class PublishRenren extends PublishBase{
    private $customer_id;
    private $platform_id;
    private $blogAccount;
    private $text;
    private $picUrl;
    private $product_id;
    private $title;
    private $is_public;
    
    public function __construct($customer_id, $is_public) {
        parent::__construct();
        $this->platform_id =  sfConfig::get("app_renren_platform_id");
        $this->customer_id = $customer_id;
        $this->is_public = $is_public;
        if($is_public==1){
            $this->blogAccount = Doctrine::getTable("PublicAccount")->findOneBy("platform_id", $this->platform_id);
        }else{
            $this->blogAccount = Doctrine::getTable("BlogAccount")->findOneBy("platform_idAndcustomer_id", array($this->platform_id,  $this->customer_id));
        }
    }
    
    public function publish() {
        $this->logger->info(date("Y-m-d H:m:s")." [start publish Renren blog, customer_id=".$this->customer_id."]");
        if(!$this->isTokenVaild()){
            return false;
        }
        
        $appid = sfConfig::get("app_renren_app_id");
        $appkey = sfConfig::get('app_renren_app_key');
        $appsecret = sfConfig::get('app_renren_app_secret_key');

        $params = array(
            'name'=> mb_substr($this->title, 0, 30, "utf8"),
            'description'=>$this->text,
            'url'=>"http://item.taobao.com/item.htm?id=".$this->product_id,
            'image'=>  $this->picUrl,
            'action_name'=>"去看看",
            'action_link'=>"http://item.taobao.com/item.htm?id=".$this->product_id,
            'message'=>  "这个挺不错的，分享一下",
            'access_token'=>  $this->blogAccount->getToken(),
            "format"=>"json",
            "v" => "1.0",
            "call_id" => $this->getCallId(),
            "method" => "feed.publishFeed"
        );
        $this->logger->info(date("Y-m-d H:m:s")." [publish Renren blog, param=".json_encode($params).", customer_id=".$this->customer_id."]");
        $response = Http::request("https://api.renren.com/restserver.do",$params,"POST");
        $this->logger->info(date("Y-m-d H:m:s")." [publish Renren blog, response=".$response.", customer_id=".$this->customer_id."]");
        $json = json_decode($response,true);
        if(isset($json["post_id"])){
            $this->logger->info(date("Y-m-d H:m:s")." [end publish Renren blog, publish success, customer_id=".$this->customer_id."]");
            if($this->is_public==1){
                Doctrine::getTable("Blog")->addBlog($this->customer_id, $this->platform_id,$this->product_id, null, $json["post_id"], $this->text,$this->is_public);
            }else{
                Doctrine::getTable("Blog")->addBlog($this->customer_id, $this->platform_id,$this->product_id, $this->blogAccount->getId(), $json["post_id"], $this->text,$this->is_public);
            }
            
            return true;
         }
         $this->logger->info(date("Y-m-d H:m:s")." [end publish Renren blog, publish fail,blog=".$this->text.", length=".  strlen($this->text).", customer_id=".$this->customer_id."]");
        return false;

        
    }
    public function isTokenVaild(){
        $this->logger->info(date("Y-m-d H:m:s")." [start validate Renren token customer_id=".$this->customer_id."]");
        if($this->blogAccount->getVar1()>time()){
            $this->logger->info(date("Y-m-d H:m:s")." [Renren token is validate, customer_id=".$this->customer_id."]");
            return true;
        }else{
            $this->logger->info(date("Y-m-d H:m:s")." [Renren token is times up, go to reflash token, customer_id=".$this->customer_id."]");
            return $this->reflashToken($this->blogAccount);
        }
    }
    
    public function reflashToken($blogAccount){
        $this->logger->info(date("Y-m-d H:m:s")." [start reflash Renren token, customer_id=".$this->customer_id."]");
        $reflash_token = $blogAccount->getVar2();
        $appid = sfConfig::get("app_renren_app_id"); 
        $app_secrect = sfConfig::get("app_renren_app_secret_key");
        $tokenUrl = sfConfig::get("app_renren_access_token_url")."?client_id=".$appid."&client_secret=".$app_secrect."&grant_type=refresh_token&refresh_token=".$reflash_token;
        $this->logger->info(date("Y-m-d H:m:s")." [reflash Renren token url=".$tokenUrl.", customer_id=".$this->customer_id."]");
        $response = Http::request($tokenUrl);
        $this->logger->info(date("Y-m-d H:m:s")." [reflash Renren token response=".$response.", customer_id=".$this->customer_id."]");
        $param = json_decode($response);
        if(isset($param["error"])){
            $this->blogAccount->delete();
            return false;
        }
/*
 * { 
 *  "access_token": "10000|5.a6b7dbd428f731035f771b8d15063f61.86400.1292922000-222209506", 
 *  "expires_in": 87063, 
 *  "refresh_token": "10000|0.385d55f8615fdfd9edb7c4b5ebdc3e39-222209506", 
 *  "scope": "read_user_album read_user_feed" }
 */
        if(isset($param['access_token'])&&isset($param["expires_in"])&&isset($param["refresh_token"])){
            $name = $this->blogAccount->getName();
            $token = $param["access_token"];
            $expires_in = 0;
            $var1 = $param["expires_in"]+time();
            $var2 = $param["refresh_token"];
            $var3 = $this->blogAccount->getVar3();
            $var4 = $this->blogAccount->getVar4();
            $var5 = "";
            $var6 = "";
            if($this->is_public){
                Doctrine::getTable("PublicAccount")->addOrUpdateBlogAccount($user_id, $platform_id, $token,$name,$expires_in,$var1,$var2,$var3,$var4,$var5,$var6);
            }else{
                Doctrine::getTable("BlogAccount")->addOrUpdateBlogAccount($user_id, $platform_id, $token,$name,$expires_in,$var1,$var2,$var3,$var4,$var5,$var6);
            }
            $this->logger->info(date("Y-m-d H:m:s")." [End reflash Renren token, Success, customer_id=".$this->customer_id."]");
            return true;
        }else{
            $this->blogAccount->delete();
            $this->logger->info(date("Y-m-d H:m:s")." [End reflash Renren token, Fail, customer_id=".$this->customer_id."]");
            return false;
        }
        
    }
    
    public function setTitle($title){
        $this->title=$title;
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
    public function getCallId(){
        $this->_call_id = str_pad(mt_rand(1, 9999999999), 10, 0, 1);
        return $this;
    }
}

?>

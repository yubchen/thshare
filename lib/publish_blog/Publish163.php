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
class Publish163 extends PublishBase{
    private $customer_id;
    private $platform_id;
    private $blogAccount;
    private $text;
    private $picUrl;
    private $product_id;
    private $is_public;
    
    public function __construct($customer_id, $is_public) {
        parent::__construct();
        $this->platform_id =  sfConfig::get("app_163_platform_id");
        $this->customer_id = $customer_id;
        $this->is_public = $is_public;
        if($is_public==1){
           $this->blogAccount = Doctrine::getTable("PublicAccount")->findOneBy("platform_id", $this->platform_id);
        }else{
           $this->blogAccount = Doctrine::getTable("BlogAccount")->findOneBy("platform_idAndcustomer_id", array($this->platform_id,  $this->customer_id));
        }
    }
    public function publish(){
        $this->logger->info(date("Y-m-d H:m:s")." [start publish 163 blog customer_id=".$this->customer_id."]");
        
        if(!$this->blogAccount){
            return false;
        }
        if(!$this->isTokenVaild()){
            return false;
        }
        
        $params = array('status' => $this->text);
        $params['access_token'] = $this->blogAccount->getToken();

        $this->logger->info(date("Y-m-d H:m:s")." [publish 163 blog, param=".json_encode($params).", customer_id=".$this->customer_id."]");
        $response = Http::request("https://api.t.163.com/statuses/update.json", $params, "POST", false);
        $this->logger->info(date("Y-m-d H:m:s")." [publish 163 blog, response=".$response.", customer_id=".$this->customer_id."]");
        $result = json_decode($response,true);
        if(isset($result["error_code"])){
            $this->logger->info(date("Y-m-d H:m:s")." [end publish 163 blog, publish fail,blog=".$this->text.", length=".  strlen($this->text).", customer_id=".$this->customer_id."]");
            return false;
        }

        $weiboId = $result["id"];
        if($this->is_public==1){
            Doctrine::getTable("Blog")->addBlog($this->customer_id, $this->platform_id,$this->product_id, null, $weiboId, $this->text,$this->is_public);
        }else{
            Doctrine::getTable("Blog")->addBlog($this->customer_id, $this->platform_id,$this->product_id, $this->blogAccount->getId(), $weiboId, $this->text,  $this->is_public);
        }
        
        $this->logger->info(date("Y-m-d H:m:s")." [end publish 163 blog, publish success, customer_id=".$this->customer_id."]");
        return true;
    }
    public function isTokenVaild(){
        $this->logger->info(date("Y-m-d H:m:s")." [start validate 163 token customer_id=".$this->customer_id."]");
        if($this->blogAccount->getVar1()>time()){
            $this->logger->info(date("Y-m-d H:m:s")." [163 token is validate, customer_id=".$this->customer_id."]");
            return true;
        }else{
            $flag = $this->reflashToken($this->blogAccount);
            if($flag){
                $this->logger->info(date("Y-m-d H:m:s")." [163 token is validate, customer_id=".$this->customer_id."]");
                return true;
            }else{
                $this->logger->info(date("Y-m-d H:m:s")." [163 token is unvalidate, customer_id=".$this->customer_id."]");
                return false;
            }
        }
    }
    
    public function reflashToken($blogAccount){
        $this->logger->info(date("Y-m-d H:m:s")." [start reflash 163 token customer_id=".$this->customer_id."]");
        $reflash_token = $blogAccount->getVar2();
        $appkey = sfConfig::get("app_163_app_key");
        $appsecret = sfConfig::get("app_163_app_secret");
        
        $url = "https://api.t.163.com/oauth2/access_token?client_id=".$appkey."&client_secret=".$appsecret."&grant_type=refresh_token&refresh_token=".$reflash_token;
        $this->logger->info(date("Y-m-d H:m:s")." [reflash token 163 token url=".$url." , customer_id=".$this->customer_id."]");
        $response = Http::request($url);
        $param = json_decode($response,true);

        $this->logger->info(date("Y-m-d H:m:s")." [reflash token 163 token response=".$response." , customer_id=".$this->customer_id."]");
        if(isset($param['access_token'])&&isset($param["expires_in"])&&isset($param["refresh_token"])){
            if($this->is_public){
                Doctrine::getTable("PublicAccount")->addOrUpdateBlogAccount($this->customer_id, $this->platform_id, $param["access_token"],"",0,time()+$param["expires_in"],$param["refresh_token"],$param["uid"]);
            }else{
                Doctrine::getTable("BlogAccount")->addOrUpdateBlogAccount($this->customer_id, $this->platform_id, $param["access_token"],"",0,time()+$param["expires_in"],$param["refresh_token"],$param["uid"]);
            }
            $this->logger->info(date("Y-m-d H:m:s")." [reflash token 163 token success, customer_id=".$this->customer_id."]");
            $this->logger->info(date("Y-m-d H:m:s")." [End reflash 163 token customer_id=".$this->customer_id."]");
            return true;
        }else{
            $this->logger->info(date("Y-m-d H:m:s")." [reflash token 163 token fail, customer_id=".$this->customer_id."]");
            $blogAccount->delete();
            $this->logger->info(date("Y-m-d H:m:s")." [End reflash 163 token customer_id=".$this->customer_id."]");
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
    
}

?>

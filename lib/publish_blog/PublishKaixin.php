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
class PublishKaixin extends PublishBase{
    private $customer_id;
    private $platform_id;
    private $blogAccount;
    private $text;
    private $picUrl;
    private $product_id;
    private $is_public;
    
    public function __construct($customer_id, $is_public) {
        parent::__construct();
        $this->platform_id =  sfConfig::get("app_kaixin_platform_id");
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
        
        $params = array(
            'content' => $this->text,
            'save_to_album'=>0,
            'picurl'=>  $this->picUrl
         );
        $params['access_token'] = $this->blogAccount->getToken();

        $this->logger->info(date("Y-m-d H:m:s")." [publish Kaixin blog, param=".json_encode($params).", customer_id=".$this->customer_id."]");
        $response = Http::request("https://api.kaixin001.com/records/add.json", $params, "POST", false);
        $this->logger->info(date("Y-m-d H:m:s")." [publish Kaixin blog, response=".$response.", customer_id=".$this->customer_id."]");
        $result = json_decode($response,true);

        if(isset($result["error_code"])){
            $this->logger->info(date("Y-m-d H:m:s")." [end publish Kaixin blog, publish fail,blog=".$this->text.", length=".  strlen($this->text).", customer_id=".$this->customer_id."]");
            return false;
        }

        $weiboId = $result["rid"];
        if($this->is_public==1){
            Doctrine::getTable("Blog")->addBlog($this->customer_id, $this->platform_id,$this->product_id, null, $weiboId, $this->text,$this->is_public);
        }else{
            Doctrine::getTable("Blog")->addBlog($this->customer_id, $this->platform_id,$this->product_id, $this->blogAccount->getId(), $weiboId, $this->text,  $this->is_public);
        }
        
        $this->logger->info(date("Y-m-d H:m:s")." [end publish Kaixin blog, publish success, customer_id=".$this->customer_id."]");
        return true;
    }
    public function isTokenVaild(){
        $this->logger->info(date("Y-m-d H:m:s")." [start validate Kaixin token customer_id=".$this->customer_id."]");
        if($this->blogAccount->getVar1()>time()){
            $this->logger->info(date("Y-m-d H:m:s")." [Kaixin token is validate, customer_id=".$this->customer_id."]");
            return true;
        }else{
            $flag = $this->reflashToken($this->blogAccount);
            if($flag){
                $this->logger->info(date("Y-m-d H:m:s")." [Kaixin token is validate, customer_id=".$this->customer_id."]");
                return true;
            }else{
                $this->logger->info(date("Y-m-d H:m:s")." [Kaixin token is unvalidate, customer_id=".$this->customer_id."]");
                return false;
            }
        }
    }
    
    public function reflashToken($blogAccount){
        $this->logger->info(date("Y-m-d H:m:s")." [start reflash Kaixin token customer_id=".$this->customer_id."]");
        $reflash_token = $blogAccount->getVar2();
        $appkey = sfConfig::get("app_kaixin_app_key");
        $appsecret = sfConfig::get("app_kaixin_app_secret");
        
        $url = sfConfig::get("app_kaixin_access_token_url")."?client_id=".$appkey."&client_secret=".$appsecret."&grant_type=refresh_token&refresh_token=".$reflash_token;
        $this->logger->info(date("Y-m-d H:m:s")." [reflash token Kaixin token url=".$url." , customer_id=".$this->customer_id."]");
        $response = Http::request($url);
        $param = json_decode($response,true);
        /*
         * Array ( [access_token] => 155194355_c5a661dfa2cf5340d9a5c86becee07c9 [expires_in] => 2592000 [scope] => basic create_records friends_records user_intro [refresh_token] => 155194355_fdf91ec6622f003baf371c16c71b40a4 ) 
         */
        $this->logger->info(date("Y-m-d H:m:s")." [reflash token Kaixin token response=".$response." , customer_id=".$this->customer_id."]");
        if(isset($param['access_token'])&&isset($param["expires_in"])&&isset($param["refresh_token"])){
            if($this->is_public){
                Doctrine::getTable("PublicAccount")->addOrUpdateBlogAccount($this->customer_id, $this->platform_id, $param["access_token"],"",0,time()+$param["expires_in"],$param["refresh_token"],$param["scope"]);
            }else{
                Doctrine::getTable("BlogAccount")->addOrUpdateBlogAccount($this->customer_id, $this->platform_id, $param["access_token"],"",0,time()+$param["expires_in"],$param["refresh_token"],$param["scope"]);
            }
            $this->logger->info(date("Y-m-d H:m:s")." [reflash token Kaixin token success, customer_id=".$this->customer_id."]");
            $this->logger->info(date("Y-m-d H:m:s")." [End reflash Kaixin token customer_id=".$this->customer_id."]");
            return true;
        }else{
            $this->logger->info(date("Y-m-d H:m:s")." [reflash token Kaixin token fail, customer_id=".$this->customer_id."]");
            $blogAccount->delete();
            $this->logger->info(date("Y-m-d H:m:s")." [End reflash Kaixin token customer_id=".$this->customer_id."]");
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

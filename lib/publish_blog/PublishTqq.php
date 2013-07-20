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
class PublishTqq extends PublishBase{
    private $customer_id;
    private $platform_id;
    private $clientip;
    private $serverip;
    private $blogAccount;
    private $text;
    private $picUrl;
    private $product_id;
    private $is_public;
    
    public function __construct($customer_id, $is_public) {
        parent::__construct();
        $this->platform_id =  sfConfig::get("app_tqq_platform_id");
        $this->customer_id = $customer_id;
        $this->is_public = $is_public;
        if($is_public==1){
           $this->blogAccount = Doctrine::getTable("PublicAccount")->findOneBy("platform_id", $this->platform_id);
        }else{
           $this->blogAccount = Doctrine::getTable("BlogAccount")->findOneBy("platform_idAndcustomer_id", array($this->platform_id,  $this->customer_id));
        }
    }
    public function publish(){
        $this->logger->info(date("Y-m-d H:m:s")." [start publish tqq blog customer_id=".$this->customer_id."]");
        
        if(!$this->blogAccount){
            return false;
        }
        if(!$this->isTokenVaild()){
            return false;
        }
        
        $params = array(
            'content' => $this->text,
            'pic_url' => $this->picUrl
        );
        $params['access_token'] = $this->blogAccount->getToken();
        $params['oauth_consumer_key'] = sfConfig::get("app_tqq_app_key");
        $params['openid'] = $this->blogAccount->getVar4();
        $params['oauth_version'] = '2.a';
        $params['clientip'] = $this->clientip;
        $params['scope'] = 'all';
        $params['appfrom'] = 'php-sdk2.0beta';
        $params['seqid'] = time();
        $params['serverip'] = $this->serverip;
        //$r = Tencent::api('t/add_pic_url', $params, 'POST');
        $this->logger->info(date("Y-m-d H:m:s")." [publish tqq blog, param=".json_encode($params).", customer_id=".$this->customer_id."]");
        $response = Http::request("https://open.t.qq.com/api/t/add_pic_url", $params, "POST", false);
        $this->logger->info(date("Y-m-d H:m:s")." [publish tqq blog, response=".$response.", customer_id=".$this->customer_id."]");
        $result = json_decode($response,true);
        if($result["errcode"]!=0){
            $this->logger->info(date("Y-m-d H:m:s")." [end publish tqq blog, publish fail,blog=".$this->text.", length=".  strlen($this->text).", customer_id=".$this->customer_id."]");
            return false;
        }

        $weiboId = $result["data"]["id"];
        if($this->is_public==1){
            Doctrine::getTable("Blog")->addBlog($this->customer_id, $this->platform_id,$this->product_id, null, $weiboId, $this->text,$this->is_public);
        }else{
            Doctrine::getTable("Blog")->addBlog($this->customer_id, $this->platform_id,$this->product_id, $this->blogAccount->getId(), $weiboId, $this->text,  $this->is_public);
        }
        
        $this->logger->info(date("Y-m-d H:m:s")." [end publish tqq blog, publish success, customer_id=".$this->customer_id."]");
        return true;
    }
    public function isTokenVaild(){
        $this->logger->info(date("Y-m-d H:m:s")." [start validate tqq token customer_id=".$this->customer_id."]");
        if($this->blogAccount->getVar1()>time()){
            $this->logger->info(date("Y-m-d H:m:s")." [tqq token is validate, customer_id=".$this->customer_id."]");
            return true;
        }else{
            $flag = $this->reflashToken($this->blogAccount);
            if($flag){
                $this->logger->info(date("Y-m-d H:m:s")." [tqq token is validate, customer_id=".$this->customer_id."]");
                return true;
            }else{
                $this->logger->info(date("Y-m-d H:m:s")." [tqq token is unvalidate, customer_id=".$this->customer_id."]");
                return false;
            }
        }
    }
    
    public function reflashToken($blogAccount){
        $this->logger->info(date("Y-m-d H:m:s")." [start reflash tqq token customer_id=".$this->customer_id."]");
        $reflash_token = $blogAccount->getVar2();
        $appkey = sfConfig::get("app_tqq_app_key");
        
        $url = "https://open.t.qq.com/cgi-bin/oauth2/access_token?client_id=".$appkey."&grant_type=refresh_token&refresh_token=".$reflash_token;
        $this->logger->info(date("Y-m-d H:m:s")." [reflash token tqq token url=".$url." , customer_id=".$this->customer_id."]");
        $response = Http::request($url);
        $param = ParamUtil::getParam($response);
        $this->logger->info(date("Y-m-d H:m:s")." [reflash token tqq token response=".$response." , customer_id=".$this->customer_id."]");
        if(isset($param['access_token'])&&isset($param["expires_in"])&&isset($param["refresh_token"])){
            if($this->is_public){
                Doctrine::getTable("PublicAccount")->addOrUpdateBlogAccount($this->customer_id, $this->platform_id, $param['access_token'],$param["name"],time()+$param["expires_in"],$param["refresh_token"],$param["nick"]);
            }else{
                Doctrine::getTable("BlogAccount")->addOrUpdateBlogAccount($this->customer_id, $this->platform_id, $param['access_token'],$param["name"],time()+$param["expires_in"],$param["refresh_token"],$param["nick"]);
            }
            $this->logger->info(date("Y-m-d H:m:s")." [reflash token tqq token success, customer_id=".$this->customer_id."]");
            $this->logger->info(date("Y-m-d H:m:s")." [End reflash tqq token customer_id=".$this->customer_id."]");
            return true;
        }else{
            $this->logger->info(date("Y-m-d H:m:s")." [reflash token tqq token fail, customer_id=".$this->customer_id."]");
            $blogAccount->delete();
            $this->logger->info(date("Y-m-d H:m:s")." [End reflash tqq token customer_id=".$this->customer_id."]");
            return false;
        }
        
    }
    public function setClientIp($ip){
        $this->clientip=$ip;
    }
    public function setServerIp($ip){
        $this->serverip = $ip;
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

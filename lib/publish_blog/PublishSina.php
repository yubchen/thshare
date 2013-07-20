<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PublishSina
 *
 * @author yubchen
 */
class PublishSina extends PublishBase{
    //put your code here
    private $customer_id;
    private $platform_id;
    private $blogAccount;
    private $text;
    private $picUrl;
    private $product_id;
    private $is_public;
    
    public function __construct($customer_id, $is_public) {
        parent::__construct();
        $this->platform_id =  sfConfig::get("app_sina_platform_id");
        $this->customer_id = $customer_id;
        $this->is_public = $is_public;
        if($is_public==1){
           $this->blogAccount = Doctrine::getTable("PublicAccount")->findOneBy("platform_id", $this->platform_id);
        }else{
           $this->blogAccount = Doctrine::getTable("BlogAccount")->findOneBy("platform_idAndcustomer_id", array($this->platform_id,  $this->customer_id));
        }
        
    }
    public function publish() {
        $this->logger->info(date("Y-m-d H:m:s")." [start publish Sina blog, customer_id=".$this->customer_id."]");
        if(!$this->blogAccount){
            return false;
        }
        if(!$this->isTokenVaild()){
            return false;
        }
        $appkey = sfConfig::get('app_sina_app_key');
        $appsecret = sfConfig::get('app_sina_app_secret');
        $sinsdk = new SaeTClientV2($appkey, $appsecret, $this->blogAccount->getToken());
        $respone = $sinsdk->upload($this->text, $this->picUrl);
        $this->logger->info(date("Y-m-d H:m:s")." [publish Sina blog, response=".json_encode($respone).", customer_id=".$this->customer_id."]");
        if(isset($respone["error"])){
            $this->logger->info(date("Y-m-d H:m:s")." [end publish Sina blog, publish fail,blog=".$this->text.", length=".  strlen($this->text).", customer_id=".$this->customer_id."]");
            return false;
        }else{
            $weibo_id = $respone["idstr"];
            $this->logger->info(date("Y-m-d H:m:s")." [end publish Renren blog, publish success, customer_id=".$this->customer_id."]");
            if($this->is_public==1){
                Doctrine::getTable("Blog")->addBlog($this->customer_id, $this->platform_id,$this->product_id, null, $weibo_id, $this->text,$this->is_public);
            }else{
                Doctrine::getTable("Blog")->addBlog($this->customer_id, $this->platform_id,$this->product_id, $this->blogAccount->getId(), $weibo_id, $this->text,$this->is_public);
            }
            return true;
        }
    }
    
    public function isTokenVaild(){
        if($this->blogAccount->getExpiresIn()>time()){
            return true;
        }else{
            $this->blogAccount->delete();
            return  false;
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

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
class PublishDiandian extends PublishBase{
    private $customer_id;
    private $platform_id;
    private $blogAccount;
    private $text;
    private $picUrl;
    private $product_id;
    private $is_public;
    private $title;
    
    public function __construct($customer_id, $is_public) {
        parent::__construct();
        $this->platform_id =  sfConfig::get("app_diandian_platform_id");
        $this->customer_id = $customer_id;
        $this->is_public = $is_public;
        if($is_public==1){
           $this->blogAccount = Doctrine::getTable("PublicAccount")->findOneBy("platform_id", $this->platform_id);     
        }else{
           $this->blogAccount = Doctrine::getTable("BlogAccount")->findOneBy("platform_idAndcustomer_id", array($this->platform_id,  $this->customer_id));
        }
    }
    public function publish(){
        $this->logger->info(date("Y-m-d H:m:s")." [start publish diandian blog customer_id=".$this->customer_id."]");
        
        if(!$this->blogAccount){
            return false;
        }
        if(!$this->isTokenVaild()){
            return false;
        }
        
        $params = array(
            'blogIdentity'=>$this->blogAccount->getVar3(),
            'access_token'=>  $this->blogAccount->getToken(),
          //  "data"=>"@".$this->picUrl,
            'type'=>'text',
            'state'=>'published',
            'markdown'=>true,
           // 'caption'=>  $this->text,
           // 'itemDesc'=>$this->text
            'title'=>  urlencode($this->title),
            'body'=>urlencode("<img src='".$this->picUrl."'><br/><p>".$this->text."</p><a href='http://item.taobao.com/item.htm?id=".$this->product_id."' target='_blank'>去看看</a>")
            );
       // $sv = new SaeTOAuthV2(0, 0);
      //  $response = $sv->post("https://api.diandian.com/v1/blog/".$this->blogAccount->getVar3()."/post", $params, true);
        /*
         * Array ( [meta] => Array ( [status] => 500 [msg] => 服务器出错了,请稍后重试^_^ [request] => Znpe0 ) ) 
         */
//        $this->logger->info(date("Y-m-d H:m:s")." [publish diandian blog, param=".json_encode($params).", customer_id=".$this->customer_id."]");
        $response = Http::request("https://api.diandian.com/v1/blog/".$this->blogAccount->getVar3()."/post", $params, "POST", false,array("Authorization: Bearer ".$this->blogAccount->getToken(),"User-Agent: 生活微分(d4cheng.com)"));
//        $this->logger->info(date("Y-m-d H:m:s")." [publish diandian blog, response=".$response.", customer_id=".$this->customer_id."]");
 //       print_r($response);
//        $result = json_decode($response,true);
//        if(isset($result["error_code"])){
//            $this->logger->info(date("Y-m-d H:m:s")." [end publish diandian blog, publish fail,blog=".$this->text.", length=".  strlen($this->text).", customer_id=".$this->customer_id."]");
//            return false;
//        }
//
//        $weiboId = $result["id"];
//        if($this->is_public==1){
//            Doctrine::getTable("Blog")->addBlog($this->customer_id, $this->platform_id,$this->product_id, null, $weiboId, $this->text,$this->is_public);
//        }else{
//            Doctrine::getTable("Blog")->addBlog($this->customer_id, $this->platform_id,$this->product_id, $this->blogAccount->getId(), $weiboId, $this->text,  $this->is_public);
//        }
//        
//        $this->logger->info(date("Y-m-d H:m:s")." [end publish diandian blog, publish success, customer_id=".$this->customer_id."]");
//        return true;
    }
    public function isTokenVaild(){
        $this->logger->info(date("Y-m-d H:m:s")." [start validate diandian token customer_id=".$this->customer_id."]");
        if($this->blogAccount->getVar1()>time()){
            $this->logger->info(date("Y-m-d H:m:s")." [diandian token is validate, customer_id=".$this->customer_id."]");
            return true;
        }else{
            $flag = $this->reflashToken($this->blogAccount);
            if($flag){
                $this->logger->info(date("Y-m-d H:m:s")." [diandian token is validate, customer_id=".$this->customer_id."]");
                return true;
            }else{
                $this->logger->info(date("Y-m-d H:m:s")." [diandian token is unvalidate, customer_id=".$this->customer_id."]");
                return false;
            }
        }
    }
    
    public function reflashToken(){
        $blogAccount = $this->blogAccount;
        $this->logger->info(date("Y-m-d H:m:s")." [start reflash diandian token customer_id=".$this->customer_id."]");
        $reflash_token = $blogAccount->getVar2();
        $appkey = sfConfig::get("app_diandian_app_key");
        $appsecret = sfConfig::get("app_diandian_app_secret");
        
        $url = "https://api.diandian.com/oauth/token?client_id=".$appkey."&client_secret=".$appsecret."&grant_type=refresh_token&refresh_token=".$reflash_token;
        $this->logger->info(date("Y-m-d H:m:s")." [reflash token diandian token url=".$url." , customer_id=".$this->customer_id."]");
        $response = Http::request($url);
        $param = json_decode($response,true);
        print_r($param);
        /*
         * Array ( [error] => invalid_grant [error_description] => Invalid refresh token: eb0804b1-6216-4cb3-aaa5-6123340cf3f8 ) 
         * Array ( [access_token] => bd86e631-cc6b-4d4d-aa98-7ebb9d9c3e00 [token_type] => bearer [refresh_token] => 68f514c3-0649-43c7-a512-81594f700e8a [expires_in] => 3599 [scope] => read,write [uid] => 16905048 ) 
         */
        $this->logger->info(date("Y-m-d H:m:s")." [reflash token diandian token response=".$response." , customer_id=".$this->customer_id."]");
        if(isset($param['access_token'])&&isset($param["expires_in"])&&isset($param["refresh_token"])){
            if($this->is_public){
                Doctrine::getTable("PublicAccount")->addOrUpdateBlogAccount($this->customer_id, $this->platform_id, $param["access_token"],"",0,time()+$param["expires_in"],$param["refresh_token"],$param['uid'],$param["token_type"],$param["scope"]);
            }else{
                Doctrine::getTable("BlogAccount")->addOrUpdateBlogAccount($this->customer_id, $this->platform_id, $param["access_token"],"",0,time()+$param["expires_in"],$param["refresh_token"],$param['uid'],$param["token_type"],$param["scope"]);
            }
           
            $this->logger->info(date("Y-m-d H:m:s")." [reflash token diandian token success, customer_id=".$this->customer_id."]");
            $this->logger->info(date("Y-m-d H:m:s")." [End reflash diandian token customer_id=".$this->customer_id."]");
            return true;
        }else{
            $this->logger->info(date("Y-m-d H:m:s")." [reflash token diandian token fail, customer_id=".$this->customer_id."]");
            $blogAccount->delete();
            $this->logger->info(date("Y-m-d H:m:s")." [End reflash diandian token customer_id=".$this->customer_id."]");
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
    public function setTitle($title){
        $this->title = $title;
    }
    
}

?>

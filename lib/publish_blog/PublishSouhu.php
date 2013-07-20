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
class PublishSouhu extends PublishBase{
    private $customer_id;
    private $platform_id;
    private $blogAccount;
    private $text;
    private $picUrl;
    private $product_id;
    private $is_public;
    private $redirect_uri;
    
    public function __construct($customer_id, $is_public) {
        parent::__construct();
        $this->platform_id =  sfConfig::get("app_souhu_platform_id");
        $this->customer_id = $customer_id;
        $this->is_public = $is_public;
        if($is_public==1){
           $this->blogAccount = Doctrine::getTable("PublicAccount")->findOneBy("platform_id", $this->platform_id);
        }else{
           $this->blogAccount = Doctrine::getTable("BlogAccount")->findOneBy("platform_idAndcustomer_id", array($this->platform_id,  $this->customer_id));
        }
    }
    public function publish(){
        $this->logger->info(date("Y-m-d H:m:s")." [start publish Souhu blog customer_id=".$this->customer_id."]");
        
        if(!$this->blogAccount){
            return false;
        }
        if(!$this->isTokenVaild()){
            return false;
        }
        
        $params = array('status' => $this->text);
        /*
        $params['access_token'] = $this->blogAccount->getToken();
        $params['pic'] = "@".$this->picUrl;
      
        $body = SaeTOAuthV2::build_http_query_multi($params);
	$headers[] = "Content-Type: multipart/form-data; boundary=" . SaeTOAuthV2::$boundary."; Authorization: OAuth2 ".$this->blogAccount->getToken();
        echo count($body,COUNT_RECURSIVE);
        $sv = new SaeTOAuthV2(0, 0);
        $response = $sv->post("https://api.t.sohu.com/statuses/upload.json", $params, true);
        $response = $sv->http("https://api.t.sohu.com/statuses/upload.json", "POST", $body, $headers);
         */
       
        $this->logger->info(date("Y-m-d H:m:s")." [publish Souhu blog, param=".json_encode($params).", customer_id=".$this->customer_id."]");
        $response = Http::request("https://api.t.sohu.com/statuses/update.json", $params, "POST",false, array("Authorization: OAuth2 ".$this->blogAccount->getToken()));
        /*
         * {"code":400,"error":"Same status is not acceptable within 5 minutes.","request":"/statuses/update.json"} 
         * {
         *      "transmit":0,
         *      "comment":0,
         *      "created_at":"Sat Jul 20 12:25:48 +0800 2013",
         *      "id":"9100781937",
         *      "text":"包包2013韩版新款潮包春夏 女包百搭休闲复古包单肩潮包斜挎小包 详细: http://item.taobao.com?id=21287183461 价格:24.90元",
         *      "source":"微博开放平台",
         *      "favorited":false,
         *      "truncated":"false",
         *      "in_reply_to_status_id":"",
         *      "in_reply_to_user_id":"",
         *      "in_reply_to_screen_name":"",
         *      "in_reply_to_status_text":"",
         *      "small_pic":"",
         *      "middle_pic":"",
         *      "original_pic":"",
         *      "user":{"id":"1689576102","screen_name":"生活微分","name":"","location":"福建省,厦门市","description":"","url":"","gender":"1","profile_image_url":"http://s4.cr.itc.cn/mblog/icon/60/c7/m_a4gzfw3970572600520.jpg","protected":true,"followers_count":2,"profile_background_color":"","profile_text_color":"","profile_link_color":"","profile_sidebar_fill_color":"","profile_sidebar_border_color":"","friends_count":41,"created_at":"Thu Jul 18 20:25:40 +0800 2013","favourites_count":0,"utc_offset":"","time_zone":"","profile_background_image_url":"","notifications":"","geo_enabled":false,"statuses_count":2,"following":true,"verified":false,"verified_reason":"","lang":"zh_cn","contributors_enabled":false}}
         */
        $this->logger->info(date("Y-m-d H:m:s")." [publish Souhu blog, response=".$response.", customer_id=".$this->customer_id."]");
        $result = json_decode($response,true);
        if(isset($result["error"])&&isset($result["code"])){
            $this->logger->info(date("Y-m-d H:m:s")." [end publish Souhu blog, publish fail,blog=".$this->text.", length=".  strlen($this->text).", customer_id=".$this->customer_id."]");
            return false;
        }
//
        $weiboId = $result["id"];
        if($this->is_public==1){
            Doctrine::getTable("Blog")->addBlog($this->customer_id, $this->platform_id,$this->product_id, null, $weiboId, $this->text,$this->is_public);
        }else{
            Doctrine::getTable("Blog")->addBlog($this->customer_id, $this->platform_id,$this->product_id, $this->blogAccount->getId(), $weiboId, $this->text,  $this->is_public);
        }
//        
        $this->logger->info(date("Y-m-d H:m:s")." [end publish Souhu blog, publish success, customer_id=".$this->customer_id."]");
        return true;
    }
    public function isTokenVaild(){
        $this->logger->info(date("Y-m-d H:m:s")." [start validate Souhu token customer_id=".$this->customer_id."]");
        if($this->blogAccount->getVar1()>time()){
            $this->logger->info(date("Y-m-d H:m:s")." [Souhu token is validate, customer_id=".$this->customer_id."]");
            return true;
        }else{
            $flag = $this->reflashToken($this->blogAccount);
            if($flag){
                $this->logger->info(date("Y-m-d H:m:s")." [Souhu token is validate, customer_id=".$this->customer_id."]");
                return true;
            }else{
                $this->logger->info(date("Y-m-d H:m:s")." [Souhu token is unvalidate, customer_id=".$this->customer_id."]");
                return false;
            }
        }
    }
    
    public function reflashToken(){
        $blogAccount = $this->blogAccount;
        $this->logger->info(date("Y-m-d H:m:s")." [start reflash Souhu token customer_id=".$this->customer_id."]");
        $refresh_token = $blogAccount->getVar2();

        $appkey = sfConfig::get("app_souhu_app_key");
        $appsecret = sfConfig::get("app_souhu_app_secret");
        $token_url = sfConfig::get("app_souhu_access_token_url");
        $this->logger->info(date("Y-m-d H:m:s")." [reflash token Souhu token url=".$token_url." , customer_id=".$this->customer_id."]");
        $response = Http::request($token_url,array(
            "client_id"=>$appkey,
            "client_secret"=>base64_encode($appsecret),
            "grant_type"=>"refresh_token",
            "redirect_uri"=>$this->redirect_uri,
            "refresh_token"=>$refresh_token
        ),"POST");
        $param = json_decode($response, true);
        /**
         * {"description":"Invalid grant_type parameter value","error":"invalid_request"} 
         * {"access_token":"312a1a7b62bb9d3eb0bfcd55e9f18","expires_in":"2592000","refresh_token":"aa6168c2ffb828a77952f03db8547f41"} 
         */
        $this->logger->info(date("Y-m-d H:m:s")." [reflash token Souhu token response=".$response." , customer_id=".$this->customer_id."]");
        if(isset($param['access_token'])&&isset($param["expires_in"])&&isset($param["refresh_token"])){
            if($this->is_public){
                Doctrine::getTable("PublicAccount")->addOrUpdateBlogAccount($this->customer_id, $this->platform_id, $param["access_token"],"",0,time()+$param["expires_in"],$param["refresh_token"]);
            }else{
                Doctrine::getTable("BlogAccount")->addOrUpdateBlogAccount($this->customer_id, $this->platform_id, $param["access_token"],"",0,time()+$param["expires_in"],$param["refresh_token"]);
            }
            $this->logger->info(date("Y-m-d H:m:s")." [reflash token Souhu token success, customer_id=".$this->customer_id."]");
            $this->logger->info(date("Y-m-d H:m:s")." [End reflash Souhu token customer_id=".$this->customer_id."]");
            return true;
        }else{
            $this->logger->info(date("Y-m-d H:m:s")." [reflash token Souhu token fail, customer_id=".$this->customer_id."]");
            $blogAccount->delete();
            $this->logger->info(date("Y-m-d H:m:s")." [End reflash Souhu token customer_id=".$this->customer_id."]");
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

    
}

?>

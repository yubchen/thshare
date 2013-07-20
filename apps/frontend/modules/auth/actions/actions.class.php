<?php

/**
 * taobao actions.
 *
 * @package    tbshare
 * @subpackage taobao
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class authActions extends sfActions
{
 /**
  * Executes index action
  *
  * @param sfRequest $request A request object
  */
  public function executeLogin(sfWebRequest $request) {
      if($request->isMethod("POST")){
          $nick = $request->getParameter("nick");
          $customers = Doctrine::getTable("Customer")->findBy("nick", $nick);
          if(count($customers)==0){
              $this->redirect("@to-auth-taobao");
          }else{
              $customer = $customers[0];
              $this->getUser()->setAttribute("sessionKey", $customer->getSessionKey());
              $this->getUser()->setAttribute("nick", $customer->getNick());
              $this->getUser()->setAttribute("user_id",$customer->getId());
              $this->getUser()->setAttribute("deadline", $customer->getDeadline());
              $this->redirect("@homepage_2");
          }
      }
  }
    
  public function executeIndex(sfWebRequest $request)
  {
      if(!$this->getUser()->isLogin()){
          $this->redirect("@to-auth-taobao");
      }
      if(!$this->getUser()->isOnService()){
          $this->redirect("@service-time-up");
      }
      
     $page = $request->getParameter("page",1);
     $showcase = $request->getParameter("showcase","false");
     $column = $request->getParameter("column","list_time:desc");
     $query = $request->getParameter("query","");
     $page_size = 120;
     
     //read product list form taobao
     $c = new TopClient;
     $c->appkey = $appkey = sfConfig::get('app_taobao_app_key');
     $c->secretKey = sfConfig::get('app_taobao_app_secriet');
     $c->format='json';
     $req = new ItemsOnsaleGetRequest;
     $req->setFields("title,cid,pic_url,list_time,price,has_discount,num_iid"); 
     $req->setPageNo($page); 
     if($showcase=="true")
         $req->setHasShowcase("true");
     if($query!="")
         $req->setQ($query);
     $req->setOrderBy($column);
     $req->setPageSize($page_size);
    // echo $this->getUser()->getAttribute("nick");
    // echo $this->getUser()->getAttribute("sessionKey");
     $resp = $c->execute($req, $this->getUser()->getAttribute("sessionKey"));

     //build page message and conditions
     $this->items = $resp->items;
     $this->total_results = $resp->total_results;
     $totalPage = ceil($resp->total_results/$page_size);
     $pager = array(
         "pageSize" => $page_size,
         "currentPage" =>  $page,
         "startDisplayPage" => $page-3>0?$page-3:1,
         "endDisplayPage" => $page+4<=$totalPage?$page+4:$totalPage,
         "totalPage" => $totalPage
     );
     $condition = array(
         "showcase"=>$showcase,
         "column"=>$column,
         "query"=>$query
     );
     $this->condition = $condition;
     $this->pager = $pager;
     
     //read blog account
     $this->platforms = Doctrine::getTable("Platform")->readPlatformList($this->getUser()->getAttribute("user_id"));


  }
  
  public function executeToPublicBinding(sfWebRequest $request){
      if(!$this->getUser()->isLogin()){
          $this->redirect("@to-auth-taobao");
      }
      if($request->hasParameter("code")&&$request->getParameter("code")==sfConfig::get("app_public_binding_code")){
          $this->getUser()->setAttribute("is_admin", true);
          $this->getUser()->setAttribute("public_binding", true);
          $this->platforms = Doctrine::getTable("Platform")->readPublicPlatformList();
      }else{
          return $this->redirect("@homepage_2");
      }
  }
  public function executeClosePublicBinding(sfWebRequest $request){
      if($this->getUser()->hasAttribute("is_admin")){
          $this->getUser()->setAttribute("is_admin", false);
          $this->getUser()->setAttribute("public_binding", false);
          $this->getUser()->shutdown();
      }
      return $this->redirect("@homepage_2");
  }
  
  public function executeServiceTimeUp(sfWebRequest $request){}
  
  public function executeToAuthTaobao(sfWebRequest $request){
    $appkey = sfConfig::get('app_taobao_app_key');
    $authUrl = sfConfig::get('app_taobao_auth_url')."?appkey=".$appkey."&encode=utf-8";
    return $this->redirect($authUrl);
  }
  
  public function executeAuthTaobao(sfWebRequest $request){
    $appkey = $request->getParameter("top_appkey");
    $parameters = $request->getParameter("top_parameters");
    $sessionKey = $request->getParameter("top_session");
    $sign = $request->getParameter("top_sign");
    
    $appSecret = sfConfig::get('app_taobao_app_secriet');
    $c = new TopClient;
    $c->appkey = $appkey;
    $c->secretKey = $appSecret;
    $c->format='json';
    $req = new UserSellerGetRequest();
    $req->setFields("user_id,nick");
    $resp = $c->execute($req, $sessionKey);
    $this->forward404Unless(!isset($resp->code));
    
    //判断该用户是否已经存在数据库
    $customer_database = Doctrine::getTable("Customer")->findOneBy("id",$resp->user->user_id );
    if($customer_database){
        $customer = $customer_database;
    }else{
        $customer = new Customer();
        $customer->setId($resp->user->user_id);
        $customer->setDeadline(date("Y-m-d H:i:s", time()+60*60*24*7));
    }
    
    $customer->setNick($resp->user->nick);
    $customer->setSessionKey($sessionKey);
    $customer->setParameters($parameters);
    $customer->setSign($sign);
    $customer->save();
    $this->getUser()->setAttribute("sessionKey", $sessionKey);
    $this->getUser()->setAttribute("nick", $resp->user->nick);
    $this->getUser()->setAttribute("user_id",$resp->user->user_id);
    $this->getUser()->setAttribute("deadline", $customer->getDeadline());
    $this->redirect('@homepage');
  }
  
  public function executeToAuthSina(sfWebRequest $request){
    if(!$this->getUser()->isLogin()){
          $this->redirect("@to-auth-taobao");
      }  
    if(!$this->getUser()->isOnService()){
        $this->redirect("@service-time-up");
    }
    $appkey = sfConfig::get('app_sina_app_key');
    $authUrl = sfConfig::get('app_sina_auth_url')."?client_id=".$appkey."&response_type=code&redirect_uri=".  urlencode($this->generateUrl("auth-sina",array(),true));
    return $this->redirect($authUrl);
  }
  

  public function executeAuthSina(sfWebRequest $request){
    try{
        $public_binding = $this->getUser()->getAttribute("public_binding",false);
        $code = $request->getParameter("code");
        $user_id = $this->getUser()->getAttribute("user_id");
        $platform_id = sfConfig::get("app_sina_platform_id");;
        $appkey = sfConfig::get('app_sina_app_key');
        $appsecret = sfConfig::get('app_sina_app_secret');
        $token_url = sfConfig::get("app_sina_access_token_url");

        $response = Http::request($token_url,array(
            "client_id"=>$appkey,
            "client_secret"=>$appsecret,
            "grant_type"=>"authorization_code",
            "redirect_uri"=>$this->generateUrl("auth-sina",array(),true),
            "code"=>$code
        ),"POST");

     //{"access_token":"2.00fzmOgBzUq13Bd9a2abde82pxMw8B","remind_in":"157679999","expires_in":157679999,"uid":"1540263871"}
        $result = json_decode($response, true);
        if($public_binding==true){
            Doctrine::getTable("PublicAccount")->addOrUpdatePublicAccount($user_id, $platform_id, $result["access_token"],"",time()+$result["expires_in"],$result["remind_in"],$result["uid"],$var3="",$var4="",$var5="",$var6="");
            $this->redirect('@to-public-binding?code='.sfConfig::get("app_public_binding_code"));    
        }else{
            Doctrine::getTable("BlogAccount")->addOrUpdateBlogAccount($user_id, $platform_id, $result["access_token"],"",time()+$result["expires_in"],$result["remind_in"],$result["uid"],$var3="",$var4="",$var5="",$var6="");
            $this->redirect('@account-list');
        }
    }catch (Exception $e){
        print_r($e);
        $this->setTemplate("bindFailSuccess");
    }
  }
  
  public function executeToAuthTqq(sfWebRequest $request){
    if(!$this->getUser()->isLogin()){
          $this->redirect("@to-auth-taobao");
      }
    if(!$this->getUser()->isOnService()){
        $this->redirect("@service-time-up");
    }
    $appkey = sfConfig::get('app_tqq_app_key');
    $authUrl = sfConfig::get('app_tqq_auth_url')."?client_id=".$appkey."&response_type=code&redirect_uri=".  urlencode($this->generateUrl("auth-tqq",array(),true));
    return $this->redirect($authUrl);
  }
  
  public function executeAuthTqq(sfWebRequest $request){
    try{
        $public_binding = $this->getUser()->getAttribute("public_binding",false);
        $code = $request->getParameter("code");
        $openid = $request->getParameter("openid");
        $openkey = $request->getParameter("openkey");
        $appkey = sfConfig::get("app_tqq_app_key");
        $appsecret = sfConfig::get("app_tqq_app_secret");
        $user_id = $this->getUser()->getAttribute("user_id");
        $platform_id = sfConfig::get("app_tqq_platform_id");
        $token_url = sfConfig::get("app_tqq_access_token_url")."?client_id=".$appkey."&client_secret=".$appsecret."&redirect_uri=".urlencode($this->generateUrl("auth-tqq",array(),true))."&grant_type=authorization_code&code=".$code;

        $response = Http::request($token_url);

        $param = ParamUtil::getParam($response);
        if($public_binding==true){
            Doctrine::getTable("PublicAccount")->addOrUpdatePublicAccount($user_id, $platform_id, $param['access_token'],$param["name"],0,time()+$param["expires_in"],$param["refresh_token"],$param["nick"],$openid,$openkey);
            $this->redirect('@to-public-binding?code='.sfConfig::get("app_public_binding_code"));    
        }else{
            Doctrine::getTable("BlogAccount")->addOrUpdateBlogAccount($user_id, $platform_id, $param['access_token'],$param["name"],0,time()+$param["expires_in"],$param["refresh_token"],$param["nick"],$openid,$openkey);
            $this->redirect('@account-list');
        }
    }catch (Exception $e){
          print_r($e);
          $this->setTemplate("bindFailSuccess");
      }
  }

  public function executeToAuthRenren(sfWebRequest $request){
     if(!$this->getUser()->isLogin()){
          $this->redirect("@to-auth-taobao");
      }
      if(!$this->getUser()->isOnService()){
          $this->redirect("@service-time-up");
      }
      $appid = sfConfig::get("app_renren_app_id");
      $appkey = sfConfig::get('app_renren_app_key');
      $appsecret = $appkey = sfConfig::get('app_renren_app_secret_key');
      
      $authUrl = sfConfig::get("app_renren_auth_url")."?client_id=".$appid."&redirect_uri=".urlencode($this->generateUrl("auth-renren",array(),true))."&response_type=code&scope=read_user_album+read_user_feed+publish_feed";

      return $this->redirect($authUrl);
  }
  
   public function executeAuthRenren(sfWebRequest $request){
      try{
        $public_binding = $this->getUser()->getAttribute("public_binding",false);
        $code = $request->getParameter("code");      
        $appid = sfConfig::get("app_renren_app_id");
        $appkey = sfConfig::get('app_renren_app_key');
        $appsecret = $appkey = sfConfig::get('app_renren_app_secret_key');
        $user_id = $this->getUser()->getAttribute("user_id");
        $platform_id = sfConfig::get("app_renren_platform_id");

        $tokenUrl = sfConfig::get("app_renren_access_token_url")."?grant_type=authorization_code&client_id=".$appid."&redirect_uri=".  urlencode($this->generateUrl("auth-renren",array(),true))."&client_secret=".$appsecret."&code=".$code;

        $response = Http::request($tokenUrl,array( ),"GET");
        //success return;
        //{"scope":"read_user_feed read_user_album publish_feed",
        //"token_type":"bearer",
        //"expires_in":2595116,
        //"refresh_token":"238305|0.gIgn8x4EVn0cSXPSQi69zQq2c6mooRmY.234074094.1373807283030",
        //"user":{"id":234074094,"name":"陈愉镔","avatar":[{"type":"avatar","url":"http://hdn.xnimg.cn/photos/hdn521/20100321/2040/h_head_aogU_507800069e0b2f76.jpg"},{"type":"tiny","url":"http://hdn.xnimg.cn/photos/hdn521/20100321/2040/h_tiny_6Xqh_507800069e0b2f76.jpg"},{"type":"main","url":"http://hdn.xnimg.cn/photos/hdn521/20100321/2040/h_main_qX1s_507800069e0b2f76.jpg"},{"type":"large","url":"http://hdn.xnimg.cn/photos/hdn521/20100321/2040/h_large_AuhZ_507800069e0b2f76.jpg"}]},
        //"access_token":"238305|6.1332ea8789a5db183f3e6b8db56beb8e.2592000.1376402400-234074094"} 
        $json = json_decode("$response",true);
        $name = $json["user"]["name"];
        $token = $json["access_token"];
        $expires_in = 0;
        $var1 = $json["expires_in"]+time();
        $var2 = $json["refresh_token"];
        $var3 = $json["token_type"];
        $var4 = $json["user"]["id"];
        $var5 = "";
        $var6 = "";
        if($public_binding==true){
            Doctrine::getTable("PublicAccount")->addOrUpdatePublicAccount($user_id, $platform_id, $token,$name,$expires_in,$var1,$var2,$var3,$var4,$var5,$var6);
            $this->redirect('@to-public-binding?code='.sfConfig::get("app_public_binding_code"));    
        }else{
            Doctrine::getTable("BlogAccount")->addOrUpdateBlogAccount($user_id, $platform_id, $token,$name,$expires_in,$var1,$var2,$var3,$var4,$var5,$var6);
            $this->redirect('@account-list');
        }
      }catch (Exception $e){
          print_r($e);
          $this->setTemplate("bindFailSuccess");
      }
  }
  
  public function executeToAuth163(sfWebRequest $request){
    if(!$this->getUser()->isLogin()){
          $this->redirect("@to-auth-taobao");
      }
    if(!$this->getUser()->isOnService()){
        $this->redirect("@service-time-up");
    }
    $appkey = sfConfig::get('app_163_app_key');
    $authUrl = sfConfig::get('app_163_auth_url')."?client_id=".$appkey."&response_type=code&redirect_uri=".urlencode($this->generateUrl("auth-163",array(),true));
    return $this->redirect($authUrl);
  }
  
  public function executeAuth163(sfWebRequest $request){
     try{
        $public_binding = $this->getUser()->getAttribute("public_binding",false);
        $code = $request->getParameter("code");
        $appkey = sfConfig::get("app_163_app_key");
        $appsecret = sfConfig::get("app_163_app_secret");
        $user_id = $this->getUser()->getAttribute("user_id");
        $platform_id = sfConfig::get("app_163_platform_id");
        $token_url = sfConfig::get("app_163_access_token_url")."?client_id=".$appkey."&client_secret=".$appsecret."&redirect_uri=".urlencode($this->generateUrl("auth-163",array(),true))."&grant_type=authorization_code&code=".$code;

        $response = Http::request($token_url);

        $param = json_decode($response);
        /*
         * stdClass Object ( 
         *      [uid] => 7191075257344350843 
         *      [expires_in] => 86400 
         *      [refresh_token] => 59acb4caad8a6cad1b826a45b0b9a430 
         *      [access_token] => ed3051a15a6225350cff919c6468fb3e ) 
         */
        if($public_binding==true){
            //$user_id, $platform_id, $token="",$name="",$expires_in=0,$var1="",$var2="",$var3="",$var4="",$var5="",$var6=""
            Doctrine::getTable("PublicAccount")->addOrUpdatePublicAccount($user_id, $platform_id, $param->access_token,"",0,time()+$param->expires_in,$param->refresh_token,$param->uid);
            $this->redirect('@to-public-binding?code='.sfConfig::get("app_public_binding_code"));    
        }else{
            Doctrine::getTable("BlogAccount")->addOrUpdateBlogAccount($user_id, $platform_id, $param->access_token,"",0,time()+$param->expires_in,$param->refresh_token,$param->uid);
            $this->redirect('@account-list');
        }
    }catch (Exception $e){
          print_r($e);
          $this->setTemplate("bindFailSuccess");
    }
  }

  public function executeToAuthSouhu(sfWebRequest $request){
    if(!$this->getUser()->isLogin()){
          $this->redirect("@to-auth-taobao");
      }
    if(!$this->getUser()->isOnService()){
        $this->redirect("@service-time-up");
    }
    $appkey = sfConfig::get('app_souhu_app_key');
    $authUrl = sfConfig::get('app_souhu_auth_url')."?client_id=".$appkey."&response_type=code&redirect_uri=".urlencode($this->generateUrl("auth-souhu",array(),true))."&scope=basic";
    return $this->redirect($authUrl);
  }
  public function executeAuthSouhu(sfWebRequest $request){
      try{
        $public_binding = $this->getUser()->getAttribute("public_binding",false);
        $code = $request->getParameter("code");
        $appkey = sfConfig::get("app_souhu_app_key");
        $appsecret = sfConfig::get("app_souhu_app_secret");
        $user_id = $this->getUser()->getAttribute("user_id");
        $platform_id = sfConfig::get("app_souhu_platform_id");
        $token_url = sfConfig::get("app_souhu_access_token_url");
        
        $response = Http::request($token_url,array(
            "client_id"=>$appkey,
            "client_secret"=>base64_encode($appsecret),
            "grant_type"=>"authorization_code",
            "redirect_uri"=>$this->generateUrl("auth-souhu",array(),true),
            "code"=>$code
        ),"POST");
        /*
         * {"description":"Missing parameters: redirect_uri","error":"invalid_request"} 
         * {"access_token":"fbccdfd745df667041a5fa3baea31","expires_in":"2592000","refresh_token":"dfc3f09a146aa9a5f0b0cfa48c368056"} 
         */
        
        $param = json_decode($response,true);
        if(isset($param["error"])){
            throw new Exception("souhu auth fail");
        }
        
        if($public_binding==true){
            //$user_id, $platform_id, $token="",$name="",$expires_in=0,$var1="",$var2="",$var3="",$var4="",$var5="",$var6=""
            Doctrine::getTable("PublicAccount")->addOrUpdatePublicAccount($user_id, $platform_id, $param["access_token"],"",0,time()+$param["expires_in"],$param["refresh_token"]);
            $this->redirect('@to-public-binding?code='.sfConfig::get("app_public_binding_code"));    
        }else{
            Doctrine::getTable("BlogAccount")->addOrUpdateBlogAccount($user_id, $platform_id, $param["access_token"],"",0,time()+$param["expires_in"],$param["refresh_token"]);
            $this->redirect('@account-list');
        }
    }catch (Exception $e){
          print_r($e);
          $this->setTemplate("bindFailSuccess");
    }
  }
  public function executeToAuthKaixin(sfWebRequest $request){
      if(!$this->getUser()->isLogin()){
          $this->redirect("@to-auth-taobao");
      }
      if(!$this->getUser()->isOnService()){
        $this->redirect("@service-time-up");
      }
      $appkey = sfConfig::get('app_kaixin_app_key');
      $authUrl = sfConfig::get('app_kaixin_auth_url')."?client_id=".$appkey."&response_type=code&redirect_uri=".urlencode($this->generateUrl("auth-kaixin",array(),true))."&scope=create_records+friends_records+user_intro";
      return $this->redirect($authUrl);
  }
  public function executeAuthKaixin(sfWebRequest $request){
       try{
        $public_binding = $this->getUser()->getAttribute("public_binding",false);
        $code = $request->getParameter("code");
        $appkey = sfConfig::get("app_kaixin_app_key");
        $appsecret = sfConfig::get("app_kaixin_app_secret");
        $user_id = $this->getUser()->getAttribute("user_id");
        $platform_id = sfConfig::get("app_kaixin_platform_id");
        $token_url = sfConfig::get("app_kaixin_access_token_url")."?client_id=".$appkey."&client_secret=".$appsecret."&grant_type=authorization_code&redirect_uri=".urlencode($this->generateUrl("auth-kaixin",array(),true))."&code=".$code;

        $response = Http::request($token_url);
   
        $param = json_decode($response,true);
        /*
         * Array ( [error_code] => 400 [request] => /oauth2/access_token [error] => 40004:Error: 应用信息错误 )
         * Array ( [access_token] => 155194355_b32c1fbdfb0d0c521510b3b8ef287d60 [expires_in] => 2592000 [scope] => basic create_records friends_records user_intro [refresh_token] => 155194355_823c332273d0692af19b12d818105b66 ) 
         */
        if(isset($param["error_code"])){
            throw new Exception("souhu auth fail");
        }
        
        if($public_binding==true){
            //$user_id, $platform_id, $token="",$name="",$expires_in=0,$var1="",$var2="",$var3="",$var4="",$var5="",$var6=""
            Doctrine::getTable("PublicAccount")->addOrUpdatePublicAccount($user_id, $platform_id, $param["access_token"],"",0,time()+$param["expires_in"],$param["refresh_token"],$param['scope']);
            $this->redirect('@to-public-binding?code='.sfConfig::get("app_public_binding_code"));    
        }else{
            Doctrine::getTable("BlogAccount")->addOrUpdateBlogAccount($user_id, $platform_id, $param["access_token"],"",0,time()+$param["expires_in"],$param["refresh_token"],$param['scope']);
            $this->redirect('@account-list');
        }
    }catch (Exception $e){
          print_r($e);
          $this->setTemplate("bindFailSuccess");
    }
  }
  
  public function executeToAuthDouban(sfWebRequest $request){
      if(!$this->getUser()->isLogin()){
          $this->redirect("@to-auth-taobao");
      }
      if(!$this->getUser()->isOnService()){
        $this->redirect("@service-time-up");
      }
      $appkey = sfConfig::get('app_douban_app_key');
      $authUrl = sfConfig::get('app_douban_auth_url')."?client_id=".$appkey."&response_type=code&redirect_uri=".urlencode($this->generateUrl("auth-douban",array(),true))."&scope=douban_basic_common,shuo_basic_r,shuo_basic_w";
      return $this->redirect($authUrl);
  }
  public function executeAuthDouban(sfWebRequest $request){
       try{
        $public_binding = $this->getUser()->getAttribute("public_binding",false);
        $code = $request->getParameter("code");
        $appkey = sfConfig::get("app_douban_app_key");
        $appsecret = sfConfig::get("app_douban_app_secret");
        $user_id = $this->getUser()->getAttribute("user_id");
        $platform_id = sfConfig::get("app_douban_platform_id");
        $token_url = sfConfig::get("app_douban_access_token_url");
        $response = Http::request($token_url,array(
            "client_id"=>$appkey,
            "client_secret"=>$appsecret,
            "redirect_uri"=>  urlencode($this->generateUrl("auth-douban",array(),true)),
            "grant_type"=>"authorization_code",
            "code"=>$code
        ),"POST");
   
        $param = json_decode($response,true);
        /*
         * Array ( [msg] => invalid_request_method: GET [code] => 101 [request] => GET /service/auth2/token )
         * Array ( [access_token] => 0ce761bb860c3bf226b625329224b3d6 [douban_user_name] => 生活微分 [douban_user_id] => 53048269 [expires_in] => 604800 [refresh_token] => fe8d8e37b4f38183fba8aaa71eda8489 )
         */
        if(isset($param["code"])&&isset($param["msg"])){
            throw new Exception("douban auth fail");
        }
        
        if($public_binding==true){
            //$user_id, $platform_id, $token="",$name="",$expires_in=0,$var1="",$var2="",$var3="",$var4="",$var5="",$var6=""
            Doctrine::getTable("PublicAccount")->addOrUpdatePublicAccount($user_id, $platform_id, $param["access_token"],$param["douban_user_name"],0,time()+$param["expires_in"],$param["refresh_token"],$param['douban_user_id']);
            $this->redirect('@to-public-binding?code='.sfConfig::get("app_public_binding_code"));    
        }else{
            Doctrine::getTable("BlogAccount")->addOrUpdateBlogAccount($user_id, $platform_id, $param["access_token"],$param["douban_user_name"],0,time()+$param["expires_in"],$param["refresh_token"],$param['douban_user_id']);
            $this->redirect('@account-list');
        }
    }catch (Exception $e){
          print_r($e->getMessage());
          $this->setTemplate("bindFailSuccess");
    }
  }
  
  public function executeToAuthDiandian(sfWebRequest $request){
      if(!$this->getUser()->isLogin()){
          $this->redirect("@to-auth-taobao");
      }
      if(!$this->getUser()->isOnService()){
        $this->redirect("@service-time-up");
      }

      $appkey = sfConfig::get('app_diandian_app_key');
      $authUrl = sfConfig::get('app_diandian_auth_url')."?client_id=".$appkey."&response_type=code&redirect_uri=".urlencode($this->generateUrl("auth-diandian",array(),true))."&scope=read,write";
      return $this->redirect($authUrl);
  }
  
  public function executeAuthDiandian(sfWebRequest $request){
    try{
        $public_binding = $this->getUser()->getAttribute("public_binding",false);
        $code = $request->getParameter("code");
        $appkey = sfConfig::get("app_diandian_app_key");
        $appsecret = sfConfig::get("app_diandian_app_secret");
        $user_id = $this->getUser()->getAttribute("user_id");
        $platform_id = sfConfig::get("app_diandian_platform_id");
        $token_url = sfConfig::get("app_diandian_access_token_url")."?client_id=".$appkey."&client_secret=".$appsecret."&grant_type=authorization_code&code=".$code."&redirect_uri=".urlencode($this->generateUrl("auth-diandian",array(),true));
        $response = Http::request($token_url);

        $param = json_decode($response,true);
        /*
         * Array ( [error] => redirect_uri_mismatch [error_description] => Redirect URI mismatch. )
         * Array ( [access_token] => 16cc2d23-e86a-4277-be53-dc1078f34057 [token_type] => bearer [refresh_token] => 82a1e200-b89e-4c2b-9b32-25cff1011536 [expires_in] => 3599 [scope] => read,write [uid] => 16905048 ) 
         */
        if(isset($param["code"])){
            throw new Exception("Diandian auth fail");
        }
        
        if($public_binding==true){
            //$user_id, $platform_id, $token="",$name="",$expires_in=0,$var1="",$var2="",$var3="",$var4="",$var5="",$var6=""
            Doctrine::getTable("PublicAccount")->addOrUpdatePublicAccount($user_id, $platform_id, $param["access_token"],"",0,time()+$param["expires_in"],$param["refresh_token"],$param['uid'],$param["token_type"],$param["scope"]);
            $this->redirect('@to-public-binding?code='.sfConfig::get("app_public_binding_code"));    
        }else{
            Doctrine::getTable("BlogAccount")->addOrUpdateBlogAccount($user_id, $platform_id, $param["access_token"],"",0,time()+$param["expires_in"],$param["refresh_token"],$param['uid'],$param["token_type"],$param["scope"]);
            $this->redirect('@account-list');
        }
    }catch (Exception $e){
          print_r($e->getMessage());
          $this->setTemplate("bindFailSuccess");
    }
  }
}

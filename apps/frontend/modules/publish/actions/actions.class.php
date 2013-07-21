<?php

/**
 * publish actions.
 *
 * @package    tbshare
 * @subpackage publish
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class publishActions extends sfActions
{
 /**
  * Executes index action
  *
  * @param sfRequest $request A request object
  */
  public function executeIndex(sfWebRequest $request)
  {
    $this->forward('default', 'module');
  }
  public function executeReadTrends(sfWebRequest $request){
      $scrop = $request->getParameter("scrop",'daily');

      $blogAccount = Doctrine::getTable("PublicAccount")->findOneBy("platform_id", sfConfig::get("app_sina_platform_id"));     
      $access_token = $blogAccount->getToken();
      $trendsUrl = "https://api.weibo.com/2/trends/".$scrop.".json";
      $trendsUrl = $trendsUrl."?access_token=".$access_token."&base_app=0";
      
      $response = Http::request($trendsUrl);
      $array = json_decode($response, TRUE);
      foreach($array['trends'] as $key=>$value){
          $list = $value;
      }
      return $this->renderText(json_encode($list));
      
  }
  public function executeToPublishBlog(sfWebRequest $request){
      if(!$this->getUser()->isLogin()){
          $this->redirect("@to-auth-taobao");
      }
      if(!$this->getUser()->isOnService()){
          $this->redirect("@service-time-up");
      }
      
      $this->product_id = $request->getParameter("product_id",0);
      $this->title = urldecode($request->getParameter("title"));
      $this->price = $request->getParameter("price");
      $this->pic_url = $request->getParameter("pic_url");
      $this->public = $request->getParameter("public",0);
      $this->forward404If($this->product_id==0 || $this->title=="");
      $this->platforms = Doctrine::getTable("Platform")->readPlatformList($this->getUser()->getAttribute("user_id"));
      
  }
  public function executeToQuickPublishBlog(sfWebRequest $request){
      if(!$this->getUser()->isLogin()){
          $this->redirect("@to-auth-taobao");
      }
      if(!$this->getUser()->isOnService()){
          $this->redirect("@service-time-up");
      }
      
      $customer_id = $this->getUser()->getAttribute("user_id");
      $customer = Doctrine::getTable("Customer")->findOneBy("id", $customer_id);
      $this->last_publish_times = $customer->countLastPublishTimes();
      $this->platforms = Doctrine::getTable("Platform")->readPlatformList($this->getUser()->getAttribute("user_id"));
      $this->errormsg = $request->getParameter("errormsg","");
  }
  
  public function executeQuickPublishBlog(sfWebRequest $request){
      if(!$this->getUser()->isLogin()){
          $this->redirect("@to-auth-taobao");
      }
      if(!$this->getUser()->isOnService()){
          $this->redirect("@service-time-up");
      }
      
      if(!$request->hasParameter("url")){
          $this->redirect("@to-quick-publish-blog");
      }
      $customer_id = $this->getUser()->getAttribute("user_id");
      $customer = Doctrine::getTable("Customer")->findOneBy("id", $customer_id);
      $last_publish_times = $customer->countLastPublishTimes();
      if($last_publish_times==0){
          return $this->redirect("@to-quick-publish-blog?errormsg=".urlencode("你今天的次数已经用完了"));
      }
      $url = $request->getParameter("url");
      $section = explode("?", $url);
      if(count($section)!=2){
          return $this->redirect("@to-quick-publish-blog?errormsg=".urlencode("您的网址似乎出错了"));
      }
      $paramStr = $section[1];
      if(!$paramStr){
          return $this->redirect("@to-quick-publish-blog?errormsg=".urlencode("您的网址似乎出错了"));
      }
    $list = ParamUtil::getParam($paramStr);
    if(!isset($list["id"])){
       return $this->redirect("@to-quick-publish-blog?errormsg=".urlencode("您的网址似乎出错了"));
    }
    
    //read product list form taobao
    $c = new TopClient;
    $c->appkey = $appkey = sfConfig::get('app_taobao_app_key');
    $c->secretKey = sfConfig::get('app_taobao_app_secriet');
    $c->format='json';
    $req = new ItemGetRequest;
    $req->setFields("title,cid,pic_url,list_time,price,has_discount,num_iid"); 
    $req->setNumIid($list["id"]);
    $resp = $c->execute($req, $this->getUser()->getAttribute("sessionKey"));
   // print_r($resp);
    //?product_id=19031522175&title=2012新款+潮+女包+雪曼正品单肩包+韩版菱格撞色绣线包包斜挎包&price=94.90&pic_url=http%3A%2F%2Fimg02.taobaocdn.com%2Fbao%2Fuploaded%2Fi2%2F13942025151683185%2FT1YktCFkhdXXXXXXXX_!!0-item_pic.jpg
    $redict_uri = "?product_id=".$resp->item->num_iid."&title=".urlencode($resp->item->title)."&price=".urlencode($resp->item->price)."&pic_url=".urlencode($resp->item->pic_url)."&public=1";
    $this->redirect("@to-publish-blog".$redict_uri);
    /*Array ( 26100812054
     * [0] => id=26100812054 
     * [1] => ) stdClass Object ( 
     *          [item] => stdClass Object ( 
     *                  [cid] => 50010815 
     *                  [has_discount] => 
     *                  [list_time] => 2013-07-11 15:27:03 
     *                  [num_iid] => 26100812054 
     *                  [pic_url] => http://img04.taobaocdn.com/bao/uploaded/i4/10651024810997061/T1x84tFf8dXXXXXXXX_!!0-item_pic.jpg 
     *                  [price] => 728.00 
     *                  [title] => Gucci Flora Gorg Gardenia EDT 古驰绚丽栀子淡香水100ML ) )   
     */
  }
  
  public function executePublishBlog(sfWebRequest $request){
      if(!$this->getUser()->isLogin()){
          return json_encode(array("error"=>1,"msg"=>"您还未登录"));
      }
      if(!$this->getUser()->isOnService()){
          return json_encode(array("error"=>2,"msg"=>"您的服务已经到期"));
      }
      
      if(!$request->isMethod("POST")){
          $this->forward404();
      }
      $customer_id = $this->getUser()->getAttribute("user_id");
      if(!$customer_id){
          $this->forward404();
      }
      set_time_limit(0);
      $content = $request->getParameter("content");
      $pic_url = $request->getParameter("pic_url");
      $title = $request->getParameter("title");
      $price = $request->getParameter("price");
      $product_id = $request->getParameter("product_id");
      $is_public = $request->getParameter("public",0);
      
      $start_time = time();
      if($is_public==0){
        $accountList = Doctrine::getTable("BlogAccount")->findBy("customer_id", $customer_id);
      }else{
        $accountList = Doctrine::getTable("PublicAccount")->findAll();
      }
      foreach ($accountList as $account){
          $platform_id = $account->getPlatformId();
          switch ($platform_id) {
              case sfConfig::get("app_sina_platform_id"):
                  try{
                    $sinaPublisher = new PublishSina($customer_id,$is_public);
                    $sinaPublisher->setText($content);
                    $sinaPublisher->setPicUrl($pic_url);
                    $sinaPublisher->setProductId($product_id);
                    $sinaPublisher->publish();
                  }  catch (Exception $e){
                      $this->getLogger()->info("来自executePublishBlog：发布新浪微博失败，错误信息：".$e->getMessage());
                  }
                  break;
              case sfConfig::get("app_tqq_platform_id"):
                  try{
                    $tqqPublisher = new PublishTqq($customer_id,$is_public);
                    $tqqPublisher->setClientIp($request->getRemoteAddress());
                    $tqqPublisher->setServerIp(gethostbyname($request->getHost()));
                    $tqqPublisher->setText($content);
                    $tqqPublisher->setPicUrl($pic_url);
                    $tqqPublisher->setProductId($product_id);
                    $tqqPublisher->publish();
                  }  catch (Exception $e){
                      $this->getLogger()->info("来自executePublishBlog：发布腾讯微博失败，错误信息：".$e->getMessage());
                  }
                  break;
              case sfConfig::get("app_renren_platform_id"):
                  try{
                    $renrenPublisher = new PublishRenren($customer_id,$is_public);
                    $renrenPublisher->setProductId($product_id);
                    $renrenPublisher->setPicUrl($pic_url);
                    $renrenPublisher->setText($content);
                    $renrenPublisher->setTitle($title);
                    $renrenPublisher->publish();
                  }  catch (Exception $e){
                      $this->getLogger()->info("来自executePublishBlog：发布人人新鲜事失败，错误信息：".$e->getMessage());
                  }
                  break;
              case sfConfig::get("app_163_platform_id"):
                  try{
                    $publisher = new Publish163($customer_id, $is_public);
                    $publisher->setText($content);
                    $publisher->setProductId($product_id);
                    $publisher->setPicUrl($pic_url);
                    $publisher->publish();
                  }catch(Exception $e){
                      $this->getLogger()->info("来自executePublishBlog：发布网易微博失败，错误信息：".$e->getMessage());
                  }
                  break;
              case sfConfig::get("app_kaixin_platform_id"):
                  try{
                    $publisher = new PublishKaixin($customer_id, $is_public);
                    $publisher->setText($content);
                    $publisher->setProductId($product_id);
                    $publisher->setPicUrl($pic_url);
                    $publisher->publish();
                  
                  }catch(Exception $e){
                      $this->getLogger()->info("来自executePublishBlog：发布开心微博失败，错误信息：".$e->getMessage());
                  }
                  break;
              case sfConfig::get("app_douban_platform_id"):
                  try{
                    $publisher = new PublishDouban($customer_id, $is_public);
                    $publisher->setTitle($title);
                    $publisher->setText($content);
                    $publisher->setProductId($product_id);
                    $publisher->setPicUrl($pic_url);
                    $publisher->setRedirectUri($this->generateUrl("auth-douban",array(),true));
                    $publisher->publish();
                  
                  }catch(Exception $e){
                      $this->getLogger()->info("来自executePublishBlog：发布豆瓣微博失败，错误信息：".$e->getMessage());
                  }
                  break;
              case sfConfig::get("app_souhu_platform_id"):
                  try {
                    $publisher = new PublishSouhu($customer_id, $is_public);
                    $publisher->setText($content);
                    $publisher->setProductId($product_id);
                    $publisher->setPicUrl($pic_url);
                    $publisher->setRedirectUri($this->generateUrl("auth-souhu",array(),true));
                    $publisher->publish();
                    
                  } catch (Exception $exc) {
                      $this->getLogger()->info("来自executePublishBlog：发布豆瓣微博失败，错误信息：".$exc->getTraceAsString());
                  }

              default:
                  break;
          }
      }
      if($is_public==0){
          $customer = Doctrine::getTable("Customer")->findOneBy("id", $customer_id); 
          $customer->setLastPublishTime(date("Y-m-d H:m:s"));
          $customer->save();
      }else{
          $customer = Doctrine::getTable("Customer")->findOneBy("id", $customer_id); 
          $customer->updatePublicPublishTimes();
      }
      
      $end_time = time();
      $publish_result = Doctrine::getTable("Blog")->findBlogPublishResult($customer_id, $start_time, $end_time,$is_public);
      $publish_result["host"]=$request->getHost();
      $publish_result["error"] = 0;
      return $this->renderText(json_encode($publish_result));
    
  }
  
  public function executeTestPublishBlog(sfWebRequest $request){
      $is_public=0;
      $customer_id = $this->getUser()->getAttribute("user_id");
//      $publisher = new PublishDiandian($customer_id, $is_public);
//  //    $publisher->setTitle("包包2013新款 潮 女夏 韩版包金太狼的幸福生活米小米李小璐同款 ");
//      $publisher->setText("包包2013韩版新款潮包春夏 女包百搭休闲复古包单肩潮包斜挎小包 详细: http://item.taobao.com?id=21287183461 价格:24.90元");
//      $publisher->setProductId("26328548144");
//      $publisher->setPicUrl("http://img02.taobaocdn.com/bao/uploaded/i2/13942025045019474/T1Nn4AFgtbXXXXXXXX_!!0-item_pic.jpg");
//      $publisher->publish();
      
      $publisher = new PublishSouhu($customer_id, $is_public);
      //$publisher->setTitle("包包2013新款 潮 女夏 韩版包金太狼的幸福生活米小米李小璐同款 ");
      $publisher->setText("包包2013韩版新款潮包春夏 女包百搭休闲复古包单肩潮包斜挎小包 详细: http://item.taobao.com?id=21287183461 价格:24.90元");
      $publisher->setProductId("26328548144");
      $publisher->setPicUrl("http://img02.taobaocdn.com/bao/uploaded/i2/13942025045019474/T1Nn4AFgtbXXXXXXXX_!!0-item_pic.jpg");
      $publisher->setRedirectUri($this->generateUrl("auth-souhu",array(),true));
      $publisher->reflashToken();
  }
}

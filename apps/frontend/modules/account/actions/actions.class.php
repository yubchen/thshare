<?php

/**
 * account actions.
 *
 * @package    tbshare
 * @subpackage account
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class accountActions extends sfActions
{
 /**
  * Executes index action
  *
  * @param sfRequest $request A request object
  */
  public function executeIndex(sfWebRequest $request)
  {
    if(!$this->getUser()->isLogin()){
          $this->redirect("@to-auth-taobao");
      }
      if(!$this->getUser()->isOnService()){
          $this->redirect("@service-time-up");
      }
    $this->platforms = Doctrine::getTable("Platform")->readPlatformDetailList($this->getUser()->getAttribute("user_id"));
  }
  public function executeDisbindingBlog(sfWebRequest $request){
      if(!$this->getUser()->isLogin()){
          $this->redirect("@to-auth-taobao");
      }
      if(!$this->getUser()->isOnService()){
          $this->redirect("@service-time-up");
      }
      $account_id = $request->getParameter("account_id");
      if($account_id){
          $account = Doctrine::getTable("BlogAccount")->findOneBy("id", $account_id);
          if($account)
              $account->delete ();
      }
      return $this->redirect("@account-list");
  }
}

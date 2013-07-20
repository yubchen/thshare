<?php

class myUser extends sfBasicSecurityUser
{
    public function initialize(\sfEventDispatcher $dispatcher, \sfStorage $storage, $options = array()) {
        parent::initialize($dispatcher, $storage, $options);
        
    }
    
    public function isLogin(){

        if ($this->getAttribute("user_id")!=""){
            return true;
        }
        return false;
    }
    public function isOnService(){
        $customer_id = $this->getAttribute("user_id");
        $customer = Doctrine::getTable("Customer")->findOneBy("id", $customer_id);
        $this->setAttribute("deadline", $customer->get("deadline"));
        if($this->hasAttribute("deadline") && strtotime($this->getAttribute("deadline"))>time()){
            return true;
        }
        return false;
    }
}

<?php

/**
 * PlatformTable
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class PlatformTable extends Doctrine_Table
{
    /**
     * Returns an instance of this class.
     *
     * @return object PlatformTable
     */
    public static function getInstance()
    {
        return Doctrine_Core::getTable('Platform');
    }
    
    public function readPlatformList($customer_id, $detail=false){
        $bindAccount = Doctrine::getTable("BlogAccount")->createQuery("ba")
                ->leftJoin("ba.Platform p")
                ->where("ba.customer_id=?", $customer_id)
                ->andWhere("p.is_enable=?", 1)
                ->fetchArray();
        
        $bindPlatformIds = array();
        foreach ($bindAccount as $platform){
            if($platform["expires_in"]==0)
                array_push($bindPlatformIds, $platform["platform_id"]);
            else if($platform["expires_in"]>time()){
                array_push($bindPlatformIds, $platform["platform_id"]);
            }
        }
        
        
        
        if($detail){
            
        }else{
            $allPlatform = $this->getInstance()->findAll();
        }
        $bindPlatform = array();
        $unbindPlatform = array();
        foreach($allPlatform as $platform){
            if(in_array($platform["id"], $bindPlatformIds)){
                array_push($bindPlatform, $platform->toArray());
            }else{
                if($platform["is_enable"]==1)
                    array_push($unbindPlatform, $platform->toArray());
            }
        }
        
        return array(
            "bindPlatform"=>$bindPlatform, 
            "unbindPlatform"=>$unbindPlatform
        );
    }
    public function readPublicPlatformList(){
        $bindAccount = Doctrine::getTable("PublicAccount")->createQuery("pa")
                ->leftJoin("pa.Platform p")
                ->where("p.is_enable=?", 1)
                ->fetchArray();
        
        $bindPlatformIds = array();
        foreach ($bindAccount as $platform){
            if($platform["expires_in"]==0)
                array_push($bindPlatformIds, $platform["platform_id"]);
            else if($platform["expires_in"]>time()){
                array_push($bindPlatformIds, $platform["platform_id"]);
            }
        }
        
        $allPlatform = $this->getInstance()->findAll();
        $bindPlatform = array();
        $unbindPlatform = array();
        foreach($allPlatform as $platform){
            if(in_array($platform["id"], $bindPlatformIds)){
                array_push($bindPlatform, $platform->toArray());
            }else{
                if($platform["is_enable"]==1)
                    array_push($unbindPlatform, $platform->toArray());
            }
        }
        return array(
            "bindPlatform"=>$bindPlatform, 
            "unbindPlatform"=>$unbindPlatform
        );
        
    }
    public function readPlatformDetailList($customer_id){
        $bindPlatforms = $this->getInstance()->createQuery("p")
                    ->leftJoin("p.BlogAccount ba on p.id=ba.platform_id")
                    ->andWhere("ba.customer_id=?",$customer_id)
                    ->andWhere("p.is_enable=?", 1)
                    ->fetchArray();
        $bind_ids = array();
        foreach ($bindPlatforms as $bindPlatform){
            array_push($bind_ids, $bindPlatform["id"]);
        }
        if(count($bind_ids)==0){
            $unbindPlatforms = $this->getInstance()->findAll();
        }else{
            $unbindPlatforms = $this->getInstance()->createQuery("p")
                ->whereNotIn("p.id",$bind_ids )
                ->andWhere("p.is_enable=?", 1)
                ->fetchArray();
        }
        
        return array(
            "bindPlatform"=>$bindPlatforms, 
            "unbindPlatform"=>$unbindPlatforms
        );
    }
}
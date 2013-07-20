<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ParamUtil
 *
 * @author yubchen
 */
class ParamUtil {
    //put your code here
    public static function getParam($string){
        $list = explode("&", $string);
        print_r($list);
        $param = array();
        for($i=0; $i<count($list); $i++){
            $key_value = explode("=", $list[$i]);
            if(count($key_value)==2)
                $param[$key_value[0]] = $key_value[1];
        }
        return $param;
    }
}

?>

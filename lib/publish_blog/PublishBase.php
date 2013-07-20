<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PublishBase
 *
 * @author yubchen
 */
abstract class PublishBase {
    protected $logger;
    public function __construct() {
        $this->logger = sfContext::getInstance()->getLogger();;
    }
    abstract function publish();
}

?>

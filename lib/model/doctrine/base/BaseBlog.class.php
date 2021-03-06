<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('Blog', 'doctrine');

/**
 * BaseBlog
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $id
 * @property integer $customer_id
 * @property integer $platform_id
 * @property integer $product_id
 * @property string $contents
 * @property string $weibo_id
 * @property integer $publish_at
 * @property integer $is_public
 * @property Customer $Customer
 * @property Platform $Platform
 * 
 * @method integer  getId()          Returns the current record's "id" value
 * @method integer  getCustomerId()  Returns the current record's "customer_id" value
 * @method integer  getPlatformId()  Returns the current record's "platform_id" value
 * @method integer  getProductId()   Returns the current record's "product_id" value
 * @method string   getContents()    Returns the current record's "contents" value
 * @method string   getWeiboId()     Returns the current record's "weibo_id" value
 * @method integer  getPublishAt()   Returns the current record's "publish_at" value
 * @method integer  getIsPublic()    Returns the current record's "is_public" value
 * @method Customer getCustomer()    Returns the current record's "Customer" value
 * @method Platform getPlatform()    Returns the current record's "Platform" value
 * @method Blog     setId()          Sets the current record's "id" value
 * @method Blog     setCustomerId()  Sets the current record's "customer_id" value
 * @method Blog     setPlatformId()  Sets the current record's "platform_id" value
 * @method Blog     setProductId()   Sets the current record's "product_id" value
 * @method Blog     setContents()    Sets the current record's "contents" value
 * @method Blog     setWeiboId()     Sets the current record's "weibo_id" value
 * @method Blog     setPublishAt()   Sets the current record's "publish_at" value
 * @method Blog     setIsPublic()    Sets the current record's "is_public" value
 * @method Blog     setCustomer()    Sets the current record's "Customer" value
 * @method Blog     setPlatform()    Sets the current record's "Platform" value
 * 
 * @package    tbshare
 * @subpackage model
 * @author     Your name here
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class BaseBlog extends sfDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('blog');
        $this->hasColumn('id', 'integer', 4, array(
             'type' => 'integer',
             'fixed' => 0,
             'unsigned' => false,
             'primary' => true,
             'autoincrement' => true,
             'length' => 4,
             ));
        $this->hasColumn('customer_id', 'integer', 4, array(
             'type' => 'integer',
             'fixed' => 0,
             'unsigned' => false,
             'primary' => false,
             'notnull' => true,
             'autoincrement' => false,
             'length' => 4,
             ));
        $this->hasColumn('platform_id', 'integer', 4, array(
             'type' => 'integer',
             'fixed' => 0,
             'unsigned' => true,
             'primary' => false,
             'notnull' => true,
             'autoincrement' => false,
             'length' => 4,
             ));
        $this->hasColumn('product_id', 'integer', 8, array(
             'type' => 'integer',
             'fixed' => 0,
             'unsigned' => false,
             'primary' => false,
             'notnull' => false,
             'autoincrement' => false,
             'length' => 8,
             ));
        $this->hasColumn('contents', 'string', 512, array(
             'type' => 'string',
             'fixed' => 0,
             'unsigned' => false,
             'primary' => false,
             'notnull' => false,
             'autoincrement' => false,
             'length' => 512,
             ));
        $this->hasColumn('weibo_id', 'string', 128, array(
             'type' => 'string',
             'fixed' => 0,
             'unsigned' => false,
             'primary' => false,
             'notnull' => false,
             'autoincrement' => false,
             'length' => 128,
             ));
        $this->hasColumn('publish_at', 'integer', 8, array(
             'type' => 'integer',
             'fixed' => 0,
             'unsigned' => false,
             'primary' => false,
             'default' => '0',
             'notnull' => true,
             'autoincrement' => false,
             'length' => 8,
             ));
        $this->hasColumn('is_public', 'integer', 1, array(
             'type' => 'integer',
             'fixed' => 0,
             'unsigned' => false,
             'primary' => false,
             'default' => '0',
             'notnull' => true,
             'autoincrement' => false,
             'length' => 1,
             ));
    }

    public function setUp()
    {
        parent::setUp();
        $this->hasOne('Customer', array(
             'local' => 'customer_id',
             'foreign' => 'id'));

        $this->hasOne('Platform', array(
             'local' => 'platform_id',
             'foreign' => 'id'));
    }
}
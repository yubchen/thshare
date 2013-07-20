<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('PublicAccount', 'doctrine');

/**
 * BasePublicAccount
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $id
 * @property integer $platform_id
 * @property integer $customer_id
 * @property string $name
 * @property string $token
 * @property integer $expires_in
 * @property string $var1
 * @property string $var2
 * @property string $var3
 * @property string $var4
 * @property string $var5
 * @property string $var6
 * @property integer $is_enable
 * @property Platform $Platform
 * @property Customer $Customer
 * 
 * @method integer       getId()          Returns the current record's "id" value
 * @method integer       getPlatformId()  Returns the current record's "platform_id" value
 * @method integer       getCustomerId()  Returns the current record's "customer_id" value
 * @method string        getName()        Returns the current record's "name" value
 * @method string        getToken()       Returns the current record's "token" value
 * @method integer       getExpiresIn()   Returns the current record's "expires_in" value
 * @method string        getVar1()        Returns the current record's "var1" value
 * @method string        getVar2()        Returns the current record's "var2" value
 * @method string        getVar3()        Returns the current record's "var3" value
 * @method string        getVar4()        Returns the current record's "var4" value
 * @method string        getVar5()        Returns the current record's "var5" value
 * @method string        getVar6()        Returns the current record's "var6" value
 * @method integer       getIsEnable()    Returns the current record's "is_enable" value
 * @method Platform      getPlatform()    Returns the current record's "Platform" value
 * @method Customer      getCustomer()    Returns the current record's "Customer" value
 * @method PublicAccount setId()          Sets the current record's "id" value
 * @method PublicAccount setPlatformId()  Sets the current record's "platform_id" value
 * @method PublicAccount setCustomerId()  Sets the current record's "customer_id" value
 * @method PublicAccount setName()        Sets the current record's "name" value
 * @method PublicAccount setToken()       Sets the current record's "token" value
 * @method PublicAccount setExpiresIn()   Sets the current record's "expires_in" value
 * @method PublicAccount setVar1()        Sets the current record's "var1" value
 * @method PublicAccount setVar2()        Sets the current record's "var2" value
 * @method PublicAccount setVar3()        Sets the current record's "var3" value
 * @method PublicAccount setVar4()        Sets the current record's "var4" value
 * @method PublicAccount setVar5()        Sets the current record's "var5" value
 * @method PublicAccount setVar6()        Sets the current record's "var6" value
 * @method PublicAccount setIsEnable()    Sets the current record's "is_enable" value
 * @method PublicAccount setPlatform()    Sets the current record's "Platform" value
 * @method PublicAccount setCustomer()    Sets the current record's "Customer" value
 * 
 * @package    tbshare
 * @subpackage model
 * @author     Your name here
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class BasePublicAccount extends sfDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('public_account');
        $this->hasColumn('id', 'integer', 4, array(
             'type' => 'integer',
             'fixed' => 0,
             'unsigned' => false,
             'primary' => true,
             'autoincrement' => true,
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
        $this->hasColumn('customer_id', 'integer', 4, array(
             'type' => 'integer',
             'fixed' => 0,
             'unsigned' => false,
             'primary' => false,
             'notnull' => false,
             'autoincrement' => false,
             'length' => 4,
             ));
        $this->hasColumn('name', 'string', 64, array(
             'type' => 'string',
             'fixed' => 0,
             'unsigned' => false,
             'primary' => false,
             'notnull' => false,
             'autoincrement' => false,
             'length' => 64,
             ));
        $this->hasColumn('token', 'string', 256, array(
             'type' => 'string',
             'fixed' => 0,
             'unsigned' => false,
             'primary' => false,
             'notnull' => false,
             'autoincrement' => false,
             'length' => 256,
             ));
        $this->hasColumn('expires_in', 'integer', 4, array(
             'type' => 'integer',
             'fixed' => 0,
             'unsigned' => false,
             'primary' => false,
             'default' => '0',
             'notnull' => true,
             'autoincrement' => false,
             'length' => 4,
             ));
        $this->hasColumn('var1', 'string', 256, array(
             'type' => 'string',
             'fixed' => 0,
             'unsigned' => false,
             'primary' => false,
             'notnull' => false,
             'autoincrement' => false,
             'length' => 256,
             ));
        $this->hasColumn('var2', 'string', 256, array(
             'type' => 'string',
             'fixed' => 0,
             'unsigned' => false,
             'primary' => false,
             'notnull' => false,
             'autoincrement' => false,
             'length' => 256,
             ));
        $this->hasColumn('var3', 'string', 256, array(
             'type' => 'string',
             'fixed' => 0,
             'unsigned' => false,
             'primary' => false,
             'notnull' => false,
             'autoincrement' => false,
             'length' => 256,
             ));
        $this->hasColumn('var4', 'string', 256, array(
             'type' => 'string',
             'fixed' => 0,
             'unsigned' => false,
             'primary' => false,
             'notnull' => false,
             'autoincrement' => false,
             'length' => 256,
             ));
        $this->hasColumn('var5', 'string', 256, array(
             'type' => 'string',
             'fixed' => 0,
             'unsigned' => false,
             'primary' => false,
             'notnull' => false,
             'autoincrement' => false,
             'length' => 256,
             ));
        $this->hasColumn('var6', 'string', 256, array(
             'type' => 'string',
             'fixed' => 0,
             'unsigned' => false,
             'primary' => false,
             'notnull' => false,
             'autoincrement' => false,
             'length' => 256,
             ));
        $this->hasColumn('is_enable', 'integer', 1, array(
             'type' => 'integer',
             'fixed' => 0,
             'unsigned' => false,
             'primary' => false,
             'default' => '1',
             'notnull' => true,
             'autoincrement' => false,
             'length' => 1,
             ));
    }

    public function setUp()
    {
        parent::setUp();
        $this->hasOne('Platform', array(
             'local' => 'platform_id',
             'foreign' => 'id'));

        $this->hasOne('Customer', array(
             'local' => 'customer_id',
             'foreign' => 'id'));
    }
}
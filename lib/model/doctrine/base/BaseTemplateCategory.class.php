<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('TemplateCategory', 'doctrine');

/**
 * BaseTemplateCategory
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $id
 * @property string $name
 * @property string $description
 * 
 * @method integer          getId()          Returns the current record's "id" value
 * @method string           getName()        Returns the current record's "name" value
 * @method string           getDescription() Returns the current record's "description" value
 * @method TemplateCategory setId()          Sets the current record's "id" value
 * @method TemplateCategory setName()        Sets the current record's "name" value
 * @method TemplateCategory setDescription() Sets the current record's "description" value
 * 
 * @package    tbshare
 * @subpackage model
 * @author     Your name here
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class BaseTemplateCategory extends sfDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('template_category');
        $this->hasColumn('id', 'integer', 4, array(
             'type' => 'integer',
             'fixed' => 0,
             'unsigned' => false,
             'primary' => true,
             'autoincrement' => true,
             'length' => 4,
             ));
        $this->hasColumn('name', 'string', 128, array(
             'type' => 'string',
             'fixed' => 0,
             'unsigned' => false,
             'primary' => false,
             'notnull' => true,
             'autoincrement' => false,
             'length' => 128,
             ));
        $this->hasColumn('description', 'string', 512, array(
             'type' => 'string',
             'fixed' => 0,
             'unsigned' => false,
             'primary' => false,
             'notnull' => false,
             'autoincrement' => false,
             'length' => 512,
             ));
    }

    public function setUp()
    {
        parent::setUp();
        
    }
}
<?php
$xpdo_meta_map['vpEvent']= array (
  'package' => 'virtualpage',
  'version' => '1.1',
  'table' => 'vp_events',
  'extends' => 'xPDOSimpleObject',
  'fields' => 
  array (
    'name' => NULL,
    'description' => NULL,
    'rank' => 0,
    'active' => 1,
  ),
  'fieldMeta' => 
  array (
    'name' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '255',
      'phptype' => 'string',
      'null' => false,
    ),
    'description' => 
    array (
      'dbtype' => 'text',
      'phptype' => 'string',
      'null' => true,
    ),
    'rank' => 
    array (
      'dbtype' => 'tinyint',
      'precision' => '1',
      'attributes' => 'unsigned',
      'phptype' => 'integer',
      'null' => true,
      'default' => 0,
    ),
    'active' => 
    array (
      'dbtype' => 'tinyint',
      'precision' => '1',
      'phptype' => 'integer',
      'null' => true,
      'default' => 1,
    ),
  ),
  'composites' => 
  array (
    'Routes' => 
    array (
      'class' => 'vpRoute',
      'local' => 'id',
      'foreign' => 'event',
      'owner' => 'local',
      'cardinality' => 'many',
    ),
  ),
);

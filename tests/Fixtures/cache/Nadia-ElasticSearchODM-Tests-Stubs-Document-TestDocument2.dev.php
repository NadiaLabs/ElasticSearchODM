<?php return array (
  'className' => 'Nadia\\ElasticSearchODM\\Tests\\Stubs\\Document\\TestDocument2',
  'indexNamePrefix' => 'dev-',
  'indexName' => 'testing',
  'routing' => null,
  'indexTypeName' => 'log',
  'repositoryClassName' => 'Nadia\\ElasticSearchODM\\Tests\\Stubs\\Document\\Repository\\TestDocumentRepository',
  'templateName' => 'testing-template-name',
  'template' => 
  array (
    'template' => 'idx-testing-*',
    'settings' => 
    array (
      'number_of_shards' => '5',
    ),
    'mappings' => 
    array (
      'log' => 
      array (
        '_source' => 
        array (
          'enabled' => false,
        ),
        'properties' => 
        array (
          'id' => 
          array (
            'type' => 'keyword',
          ),
          'created_at' => 
          array (
            'type' => 'date',
            'format' => 'yyyy-MM-dd',
          ),
        ),
      ),
    ),
  ),
  'columnsElasticToObject' => 
  array (
    'id' => 'id',
    'created_at' => 'createdAt',
  ),
  'columnsObjectToElastic' => 
  array (
    'id' => 'id',
    'createdAt' => 'created_at',
  ),
  'metaColumnsElasticToObject' => 
  array (
    '_id' => 'docId',
  ),
  'metaColumnsObjectToElastic' => 
  array (
    'docId' => '_id',
  ),
);

<?php return array (
  'className' => 'Nadia\\ElasticsearchODM\\Tests\\Stubs\\Document\\TestDocument1',
  'indexNamePrefix' => 'dev-',
  'indexName' => 
  array (
    0 => 'Nadia\\ElasticsearchODM\\Tests\\Stubs\\Document\\TestDocument1',
    1 => 'generateIndexName',
  ),
  'routing' => 
  array (
    0 => 'Nadia\\ElasticsearchODM\\Tests\\Stubs\\Document\\TestDocument1',
    1 => 'generateRoutingName',
  ),
  'indexTypeName' => 'log',
  'repositoryClassName' => 'Nadia\\ElasticsearchODM\\Tests\\Stubs\\Document\\Repository\\TestDocumentRepository',
  'templateName' => 'testing-template-name',
  'template' => 
  array (
    'index_patterns' => ['idx-testing-*'],
    'order' => 0,
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

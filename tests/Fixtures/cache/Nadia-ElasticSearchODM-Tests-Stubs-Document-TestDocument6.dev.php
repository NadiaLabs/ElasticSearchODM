<?php return array (
  'className' => 'Nadia\\ElasticSearchODM\\Tests\\Stubs\\Document\\TestDocument6',
  'indexNamePrefix' => 'dev-',
  'indexName' => 'testing',
  'routing' => null,
  'indexTypeName' => 'log',
  'repositoryClassName' => 'Nadia\\ElasticSearchODM\\Tests\\Stubs\\Document\\Repository\\TestDocumentRepository',
  'templateName' => 'template-dev-testing-template-name',
  'template' => 
  array (
    'index_patterns' => ['idx-testing-*'],
    'order' => 0,
    'mappings' =>
    array (
      'log' => 
      array (
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

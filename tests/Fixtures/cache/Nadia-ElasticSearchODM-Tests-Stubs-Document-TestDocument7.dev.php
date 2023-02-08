<?php return array (
  'className' => 'Nadia\\ElasticSearchODM\\Tests\\Stubs\\Document\\TestDocument7',
  'indexNamePrefix' => 'dev-',
  'indexName' => 'testing',
  'routing' => NULL,
  'indexTypeName' => 'log',
  'repositoryClassName' => 'Nadia\\ElasticSearchODM\\Tests\\Stubs\\Document\\Repository\\TestDocumentRepository',
  'templateName' => 'template-dev-testing-template-name',
  'template' => 
  array (
    'template' => 'idx-testing-*',
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
          'diff' => 
          array (
            'foo' => 
            array (
              'type' => 'keyword',
            ),
            'bar' => 
            array (
              'type' => 'keyword',
            ),
            'foobar' => 
            array (
              'type' => 'keyword',
            ),
            'baz' => 
            array (
              'foo' => 
              array (
                'type' => 'keyword',
              ),
              'bar' => 
              array (
                'type' => 'keyword',
              ),
              'foobar' => 
              array (
                'type' => 'keyword',
              ),
            ),
          ),
        ),
      ),
    ),
  ),
  'columnsElasticToObject' =>
  array (
    'id' => 'id',
    'diff' => 'diff',
  ),
  'columnsObjectToElastic' =>
  array (
    'id' => 'id',
    'diff' => 'diff',
  ),
  'metaColumnsElasticToObject' =>
  array (
  ),
  'metaColumnsObjectToElastic' =>
  array (
  ),
);

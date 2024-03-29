<?php return array (
  'className' => 'Nadia\\ElasticsearchODM\\Tests\\Stubs\\Document\\TestDocument7',
  'indexNamePrefix' => 'dev-',
  'indexName' => 'testing',
  'routing' => NULL,
  'indexTypeName' => 'log',
  'repositoryClassName' => 'Nadia\\ElasticsearchODM\\Tests\\Stubs\\Document\\Repository\\TestDocumentRepository',
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
          'diff' =>
          array(
            'properties' =>
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
                'properties' =>
                array(
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

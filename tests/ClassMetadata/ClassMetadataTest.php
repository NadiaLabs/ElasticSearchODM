<?php

namespace Nadia\ElasticsearchODM\Tests\ClassMetadata;

use Nadia\ElasticsearchODM\ClassMetadata\ClassMetadata;
use Nadia\ElasticsearchODM\Helper\ElasticsearchHelper;
use PHPUnit\Framework\TestCase;

class ClassMetadataTest extends TestCase
{
    public function testConstruct()
    {
        $case = $this->getMetadataTestCase();
        $metadata = new ClassMetadata($case);

        foreach ($case as $key => $value) {
            $this->assertEquals($value, $metadata->{$key});
        }
    }

    public function testConstructException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $case = $this->getMetadataTestCase();

        unset($case['className']);

        $metadata = new ClassMetadata($case);
    }

    public function testGetIndexName()
    {
        $metadata = new ClassMetadata($this->getMetadataTestCase());

        $this->assertEquals('dev-idx-testing-20230117', $metadata->getIndexName(['createdAt' => '2023-01-17']));

        $case = $this->getMetadataTestCase();
        $case['indexName'] = 'fixed-index-name';
        $metadata = new ClassMetadata($case);

        $this->assertEquals('dev-fixed-index-name', $metadata->getIndexName(['createdAt' => '2023-01-17']));
    }

    public function testGetRouting()
    {
        $metadata = new ClassMetadata($this->getMetadataTestCase());

        $this->assertEquals('id:2023011700001', $metadata->getRouting(['id' => '2023011700001']));

        $case = $this->getMetadataTestCase();
        $case['routing'] = null;
        $metadata = new ClassMetadata($case);

        $this->assertEquals('', $metadata->getRouting(['id' => '2023011700001']));
    }

    /**
     * @throws \ReflectionException
     */
    public function testGetReflectionClass()
    {
        $metadata = new ClassMetadata($this->getMetadataTestCase());

        $this->assertInstanceOf(\ReflectionClass::class, $metadata->getReflectionClass());
    }

    private function getMetadataTestCase()
    {
        $return = [
            'version' => 'v123',
            'className' => 'Nadia\ElasticsearchODM\Tests\Stubs\Document\TestDocument1',
            'indexName' => [
                'Nadia\ElasticsearchODM\Tests\Stubs\Document\TestDocument1',
                'generateIndexName',
            ],
            'indexNamePrefix' => 'dev-',
            'indexTypeName' => 'log',
            'repositoryClassName' => 'Nadia\ElasticsearchODM\Tests\Stubs\Document\Repository\TestDocumentRepository',
            'routing' => [
                'Nadia\ElasticsearchODM\Tests\Stubs\Document\TestDocument1',
                'generateRoutingName',
            ],
            'metaColumnsElasticToObject' => [
                '_id' => 'id',
                '_source' => 'source',
            ],
            'metaColumnsObjectToElastic' => [
                'id' => '_id',
                'source' => '_source',
            ],
            'columnsElasticToObject' => [
                'id' => 'id',
                'created_at' => 'createdAt',
            ],
            'columnsObjectToElastic' => [
                'id' => 'id',
                'createdAt' => 'created_at',
            ],
            'templateName' => 'testing-template-name',
            'template' => [
                'template' => 'idx-testing-*',
                'settings' => [
                    'number_of_shards' => '5',
                ],
                'mappings' => [
                    'log' => [
                        'properties' => [
                            'id' => [
                                'type' => 'keyword',
                            ],
                            'created_at' => [
                                'type' => 'date',
                                'format' => 'yyyy-MM-dd',
                            ],
                        ]
                    ],
                ],
            ],
        ];

        if (version_compare(ElasticsearchHelper::getClientVersion(), '7.0.0', '>=')) {
            $return['template']['mappings'] = $return['template']['mappings']['log'];
        }

        return $return;
    }
}

<?php

namespace Nadia\ElasticSearchODM\Tests\Document;

use Nadia\ElasticSearchODM\ClassMetadata\ClassMetadataLoader;
use Nadia\ElasticSearchODM\Document\Manager;
use Nadia\ElasticSearchODM\ElasticSearch\Client;
use Nadia\ElasticSearchODM\Tests\Stubs\Document\Repository\TestDocumentRepository;
use Nadia\ElasticSearchODM\Tests\Stubs\Document\TestDocument1;
use PHPUnit\Framework\TestCase;
use Psr\Cache\InvalidArgumentException;

class RepositoryTest extends TestCase
{
    /**
     * @before
     */
    public function beforeTest()
    {
        $cacheDir = $this->getCacheDir();

        if (!file_exists($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testFindOneBy()
    {
        $indexes = ['dev-index-1', 'dev-index-2'];
        $expectedSearchResults = [
            [
                '_index' => 'dev-index-1',
                '_type' => 'log',
                '_id' => '1',
                '_routing' => 'id:2023-01-23-0001',
                '_source' => [
                    'id' => '2023-01-23-0001',
                    'created_at' => '2023-01-23',
                ],
            ],
        ];
        $expectedSearchBody = [
            'query' => ['bool' => ['must' => [['term' => ['id' => '2023-01-23-0001']]]]],
            'sort' => ['created_at' => ['order' => 'DESC']],
        ];
        $client = $this->createFindByClient($indexes, $expectedSearchResults, $expectedSearchBody, 1);
        $repo = new TestDocumentRepository($this->createManager($client));
        $result = $repo->findOneBy(
            ['index-1', 'index-2'],
            'log',
            ['id' => '2023-01-23-0001', '' => ''],
            ['created_at' => 'DESC']
        );

        $this->assertEquals($expectedSearchResults[0], $result);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testFindBy()
    {
        $indexes = ['dev-index-1', 'dev-index-2'];
        $expectedSearchResults = [
            [
                '_index' => 'dev-index-1',
                '_type' => 'log',
                '_id' => '1',
                '_routing' => 'id:2023-01-23-0001',
                '_source' => [
                    'id' => '2023-01-23-0001',
                    'created_at' => '2023-01-23',
                ],
            ],
        ];
        $expectedSearchBody = [
            'query' => ['bool' => ['must' => [['term' => ['id' => '2023-01-23-0001']]]]],
            'sort' => ['created_at' => ['order' => 'DESC']],
        ];
        $limit = 5;
        $client = $this->createFindByClient($indexes, $expectedSearchResults, $expectedSearchBody, $limit);
        $repo = new TestDocumentRepository($this->createManager($client));
        $result = $repo->findBy(
            ['index-1', 'index-2'],
            'log',
            ['id' => '2023-01-23-0001', '' => ''],
            ['created_at' => 'DESC'],
            $limit
        );

        $this->assertEquals($expectedSearchResults, $result);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testFindByWithWildCardIndexName()
    {
        $indexes = ['dev-index-*'];
        $expectedSearchResults = [
            [
                '_index' => 'dev-index-1',
                '_type' => 'log',
                '_id' => '1',
                '_routing' => 'id:2023-01-23-0001',
                '_source' => [
                    'id' => '2023-01-23-0001',
                    'created_at' => '2023-01-23',
                ],
            ],
        ];
        $expectedSearchBody = [
            'query' => ['bool' => ['must' => [['term' => ['id' => '2023-01-23-0001']]]]],
            'sort' => ['created_at' => ['order' => 'DESC']],
        ];
        $limit = 5;
        $client = $this->createFindByClient($indexes, $expectedSearchResults, $expectedSearchBody, $limit);
        $repo = new TestDocumentRepository($this->createManager($client));
        $result = $repo->findBy(
            $indexes,
            'log',
            ['id' => '2023-01-23-0001', '' => ''],
            ['created_at' => 'DESC'],
            $limit
        );

        $this->assertEquals($expectedSearchResults, $result);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testFindByForEmptyIndexes()
    {
        $client = $this->createFindByClient([], [], [], 6);
        $repo = new TestDocumentRepository($this->createManager($client));
        $result = $repo->findBy([], 'log', []);

        $this->assertEquals([], $result);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testFindByForMustNotQuery()
    {
        $expectedSearchResults = [
            [
                '_index' => 'dev-index-1',
                '_type' => 'log',
                '_id' => '2',
                '_routing' => 'id:2023-01-23-0002',
                '_source' => [
                    'id' => '2023-01-23-0002',
                    'created_at' => '2023-01-23',
                ],
            ],
        ];
        $expectedSearchBody = ['query' => ['bool' => ['must_not' => [['term' => ['id' => '2023-01-23-0001']]]]]];
        $limit = 7;
        $client = $this->createFindByClient(['dev-index-1'], $expectedSearchResults, $expectedSearchBody, $limit);
        $repo = new TestDocumentRepository($this->createManager($client));
        $result = $repo->findBy(['index-1'], 'log', ['!id' => '2023-01-23-0001'], [], $limit);

        $this->assertEquals($expectedSearchResults, $result);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testFindByForTermsQuery()
    {
        $expectedSearchResults = [
            [
                '_index' => 'dev-index-1',
                '_type' => 'log',
                '_id' => '3',
                '_routing' => 'id:2023-01-23-0003',
                '_source' => [
                    'id' => '2023-01-23-0003',
                    'created_at' => '2023-01-23',
                ],
            ],
        ];
        $expectedSearchBody = [
            'query' => ['bool' => ['must' => [['terms' => ['id' => ['2023-01-23-0003', '2023-01-23-0004']]]]]],
        ];
        $limit = 8;
        $client = $this->createFindByClient(['dev-index-1'], $expectedSearchResults, $expectedSearchBody, $limit);
        $repo = new TestDocumentRepository($this->createManager($client));
        $result = $repo->findBy(['index-1'], 'log', ['id' => ['2023-01-23-0003', '2023-01-23-0004']], [], $limit);

        $this->assertEquals($expectedSearchResults, $result);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testFindByInvalidOrderBy()
    {
        $this->expectException(\InvalidArgumentException::class);

        $client = $this->createFindByClient(['dev-index-1'], [], [], 6);
        $repo = new TestDocumentRepository($this->createManager($client));

        $repo->findBy(['index-1'], 'log', [], ['id' => 'invalid order']);
    }

    private function createFindByClient(array $indexes, array $expectedSearchResults, array $expectedSearchBody, $limit)
    {
        $client = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->setMethods(['getValidIndexNames', 'search'])
            ->getMock();

        $client->method('getValidIndexNames')->willReturn($indexes);
        $client->method('search')->willReturn(['hits' => ['hits' => $expectedSearchResults]]);

        if (!empty($expectedSearchResults)) {
            $client->expects($this->once())
                ->method('search')
                ->with([
                    'index' => join(',', $indexes),
                    'type' => 'log',
                    'size' => $limit,
                    'body' => $expectedSearchBody,
                ]);
        }

        return $client;
    }

    /**
     * @throws \ReflectionException
     */
    public function testWrite()
    {
        $indexParams = [
            'index' => 'dev-idx-testing-20230123',
            'type' => 'log',
            'body' => [
                'id' => '2023-01-23-0001',
                'created_at' => '2023-01-23',
            ],
            'routing' => 'id:2023-01-23-0001',
        ];
        $indexResult = [
            'index' => 'dev-index-1',
            '_type' => 'log',
            '_id' => 1,
            '_version' => 1,
            'created' => true,
            'result' => 'created',
        ];
        $client = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->setMethods(['index'])
            ->getMock();

        $client->method('index')->willReturn($indexResult);

        $client->expects($this->once())->method('index')->with($indexParams);

        $document = new TestDocument1('2023-01-23-0001', '2023-01-23');

        $repo = new TestDocumentRepository($this->createManager($client));
        $result = $repo->write($document);

        $this->assertEquals($indexResult, $result);
    }

    /**
     * @throws \ReflectionException
     */
    public function testBulkWrite()
    {
        $documents = [
            new TestDocument1('2023-01-22-0001', '2023-01-22'),
            new TestDocument1('2023-01-23-0001', '2023-01-23'),
        ];
        $bulkParams = [
            [
                'index' => 'dev-idx-testing-20230122',
                'type' => 'log',
                'body' => [
                    ['index' => ['_index' => 'dev-idx-testing-20230122', '_type' => 'log']],
                    ['id' => '2023-01-22-0001', 'created_at' => '2023-01-22'],
                ],
            ],
            [
                'index' => 'dev-idx-testing-20230123',
                'type' => 'log',
                'body' => [
                    ['index' => ['_index' => 'dev-idx-testing-20230123', '_type' => 'log']],
                    ['id' => '2023-01-23-0001', 'created_at' => '2023-01-23'],
                ],
            ],
        ];

        $client = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->setMethods(['bulk'])
            ->getMock();

        $client->method('bulk')->willReturn([]);
        $client->expects($this->exactly(2))->method('bulk')->withConsecutive([$bulkParams[0]], [$bulkParams[1]]);

        $repo = new TestDocumentRepository($this->createManager($client));
        $result = $repo->bulkWrite($documents);

        $this->assertEquals(
            ['dev-idx-testing-20230122', 'dev-idx-testing-20230123'],
            array_keys($result)
        );
    }

    /**
     * @throws \ReflectionException
     */
    public function testBulkWriteWithEmptyDocuments()
    {
        $client = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();

        $repo = new TestDocumentRepository($this->createManager($client));
        $result = $repo->bulkWrite([]);

        $this->assertEquals([], $result);
    }

    private function createManager($client)
    {
        $metadataLoader = new ClassMetadataLoader($this->getCacheDir(), false, 'dev-', 'dev');

        return new Manager($client, $metadataLoader);
    }

    private function getCacheDir()
    {
        return __DIR__ . '/../.cache';
    }
}

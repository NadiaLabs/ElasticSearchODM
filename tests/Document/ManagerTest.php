<?php

namespace Nadia\ElasticSearchODM\Tests\Document;

use Nadia\ElasticSearchODM\ClassMetadata\ClassMetadataLoader;
use Nadia\ElasticSearchODM\Document\IndexNameProvider;
use Nadia\ElasticSearchODM\Document\Manager;
use Nadia\ElasticSearchODM\Exception\RepositoryInheritanceInvalidException;
use Nadia\ElasticSearchODM\Exception\RepositoryNotExistsException;
use Nadia\ElasticSearchODM\Helper\ElasticSearchHelper;
use Nadia\ElasticSearchODM\Tests\PHPUnit\Framework\TestCase;
use Nadia\ElasticSearchODM\Tests\Stubs\Cache\Cache;
use Nadia\ElasticSearchODM\Tests\Stubs\Document\Repository\TestDocumentRepository;
use Nadia\ElasticSearchODM\Tests\Stubs\Document\TestDocument1;
use Nadia\ElasticSearchODM\Tests\Stubs\Document\TestDocument4;
use Nadia\ElasticSearchODM\Tests\Stubs\Document\TestDocument5;

class ManagerTest extends TestCase
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

    public function testConstructor()
    {
        $client = $this->createElasticSearchClientByElasticSearchClientBuilder();
        $metadataLoader = new ClassMetadataLoader($this->getCacheDir(), false, 'dev-', 'dev');
        $cache = new Cache();
        $indexNameProvider = new IndexNameProvider($client, 'dev-', $cache);
        $manager = new Manager($client, $metadataLoader, $indexNameProvider, $cache);

        $this->assertEquals($client, $manager->getClient());
        $this->assertEquals($cache, $manager->getCache());
        $this->assertEquals($indexNameProvider, $manager->getIndexNameProvider());
    }

    public function testGetRepository()
    {
        $manager = $this->createManager();

        $repo = $manager->getRepository(TestDocument1::class);
        $this->assertInstanceOf(TestDocumentRepository::class, $repo);

        // Test cached repository instance
        $repo = $manager->getRepository(TestDocument1::class);
        $this->assertInstanceOf(TestDocumentRepository::class, $repo);
    }

    public function testGetRepositoryWithNotExistsRepositoryClassName()
    {
        $manager = $this->createManager();

        $this->expectException(RepositoryNotExistsException::class);

        $manager->getRepository(TestDocument4::class);
    }

    public function testGetRepositoryWithInvalidInheritance()
    {
        $manager = $this->createManager();

        $this->expectException(RepositoryInheritanceInvalidException::class);

        $manager->getRepository(TestDocument5::class);
    }

    public function testUpdateTemplate()
    {
        $metadataFilename = 'Nadia-ElasticSearchODM-Tests-Stubs-Document-TestDocument1.dev.php';
        $metadata = require __DIR__ . '/../Fixtures/cache/' . $metadataFilename;

        foreach ($metadata['template']['index_patterns'] as &$indexPattern) {
            $indexPattern = $metadata['indexNamePrefix'] . $indexPattern;
        }
        if (version_compare(ElasticSearchHelper::getClientVersion(), '6.0.0', '<')) {
            $metadata['template']['template'] = join(',', $metadata['template']['index_patterns']);
            unset($metadata['template']['index_patterns']);
        }

        $updateResult = ['acknowledged' => true];
        $updateParams = ['name' => 'testing-template-name', 'body' => $metadata['template']];
        $client = $this->mockElasticSearchClientForTestUpdateTemplate($updateParams, $updateResult);

        $manager = $this->createManager($client);

        $result = $manager->updateIndexTemplate(TestDocument1::class);
        $this->assertEquals($updateResult, $result);

        // Make sure "updateTemplate" result is the same with the first one
        $result = $manager->updateIndexTemplate(TestDocument1::class);
        $this->assertEquals($updateResult, $result);
    }

    private function mockElasticSearchClientForTestUpdateTemplate(array $updateParams, array $updateResult)
    {
        if (class_exists('Elastic\Elasticsearch\Response\Elasticsearch')) {
            $updateResultResponseHeaders = [
                'Content-Type' => 'application/json',
                \Elastic\Elasticsearch\Response\Elasticsearch::HEADER_CHECK =>
                    \Elastic\Elasticsearch\Response\Elasticsearch::PRODUCT_NAME,
            ];
            $updateResultResponse = new \GuzzleHttp\Psr7\Response(
                200,
                $updateResultResponseHeaders,
                json_encode($updateResult)
            );
            $updateResult = new \Elastic\Elasticsearch\Response\Elasticsearch();
            $updateResult->setResponse($updateResultResponse);
        }

        $indicesNamespace = $this
            ->createMockBuilderAndOnlyMethods(ElasticSearchHelper::getNamespaceClassName('Indices'), ['putTemplate'])
            ->getMock();

        $indicesNamespace->method('putTemplate')->willReturn($updateResult);
        $indicesNamespace->expects($this->exactly(2))->method('putTemplate')->with($updateParams);

        $clientClassName = ElasticSearchHelper::getClientClassNameForPHPUnitMockBuilder();
        $client = $this
            ->createMockBuilderAndOnlyMethods($clientClassName, ['indices'])
            ->getMock();
        $client->method('indices')->willReturn($indicesNamespace);

        return $client;
    }

    /**
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function testGetValidIndexNames()
    {
        $client = $this->createElasticSearchClientByElasticSearchClientBuilder();
        $metadataLoader = new ClassMetadataLoader($this->getCacheDir(), false, 'dev-', 'dev');
        $cache = new Cache();
        $indexNameProvider = new IndexNameProvider($client, 'dev-', $cache);
        $manager = new Manager($client, $metadataLoader, $indexNameProvider, $cache);

        $cacheItem = $cache->getItem($indexNameProvider->getIndexAliasesCacheKey());
        $aliases = ['dev-index-001' => ['aliases' => []], 'dev-index-002' => ['aliases' => []]];

        $cacheItem->set($aliases);
        $cache->save($cacheItem);

        $indexNames = $manager->getValidIndexNames(['index-001', 'index-002', 'index-003']);
        $this->assertEquals(['dev-index-001', 'dev-index-002'], $indexNames);
    }

    private function createManager($client = null)
    {
        if (is_null($client)) {
            $client = $this->createElasticSearchClientByElasticSearchClientBuilder();
        }

        $metadataLoader = new ClassMetadataLoader($this->getCacheDir(), false, 'dev-', 'dev');

        return new Manager($client, $metadataLoader);
    }

    private function createElasticSearchClientByElasticSearchClientBuilder()
    {
        $clientBuilderClassName = 'Elastic\Elasticsearch\ClientBuilder';
        if (!class_exists($clientBuilderClassName)) {
            $clientBuilderClassName = 'Elasticsearch\ClientBuilder';
        }

        return (new $clientBuilderClassName())->build();
    }

    private function getCacheDir()
    {
        return __DIR__ . '/../.cache';
    }
}

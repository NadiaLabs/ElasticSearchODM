<?php

namespace Nadia\ElasticSearchODM\Tests\Document;

use Nadia\ElasticSearchODM\ClassMetadata\ClassMetadataLoader;
use Nadia\ElasticSearchODM\Document\IndexNameProvider;
use Nadia\ElasticSearchODM\Document\Manager;
use Nadia\ElasticSearchODM\ElasticSearch\ClientBuilder;
use Nadia\ElasticSearchODM\Tests\Stubs\Cache\Cache;
use Nadia\ElasticSearchODM\Tests\Stubs\Document\Repository\TestDocumentRepository;
use Nadia\ElasticSearchODM\Tests\Stubs\Document\TestDocument1;
use Nadia\ElasticSearchODM\Tests\Stubs\Document\TestDocument4;
use Nadia\ElasticSearchODM\Tests\Stubs\Document\TestDocument5;
use Nadia\ElasticSearchODM\Tests\Stubs\ElasticSearch\Client;
use Nadia\ElasticSearchODM\Tests\Stubs\ElasticSearch\IndicesNamespace;
use PHPUnit\Framework\TestCase;

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
        /** @var Client $client */
        $client = (new ClientBuilder())
            ->setClientClassName(Client::class)
            ->setIndexNamePrefix('dev-')
            ->build();
        $metadataLoader = new ClassMetadataLoader($this->getCacheDir(), false, 'dev-', 'dev');
        $cache = new Cache();
        $indexNameProvider = new IndexNameProvider($client, 'dev-', $cache);
        $manager = new Manager($client, $metadataLoader, $indexNameProvider, $cache);

        $this->assertEquals($client, $manager->getClient());
        $this->assertEquals($cache, $manager->getCache());
        $this->assertEquals($indexNameProvider, $manager->getIndexNameProvider());
    }

    /**
     * @throws \ReflectionException
     */
    public function testGetRepository()
    {
        /** @var Client $client */
        $client = (new ClientBuilder())
            ->setClientClassName(Client::class)
            ->setIndexNamePrefix('dev-')
            ->build();
        $manager = $this->createManager($client);

        $this->assertEquals($client, $manager->getClient());

        $repo = $manager->getRepository(TestDocument1::class);
        $this->assertInstanceOf(TestDocumentRepository::class, $repo);

        $repo = $manager->getRepository(TestDocument1::class);
        $this->assertInstanceOf(TestDocumentRepository::class, $repo);
    }

    public function testGetRepositoryWithInvalidRepositoryClassName()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->createManager()->getRepository(TestDocument4::class);
    }

    public function testGetRepositoryWithInvalidRepository()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->createManager()->getRepository(TestDocument5::class);
    }


    /**
     * @throws \ReflectionException
     */
    public function testUpdateTemplate()
    {
        $metadataFilename = 'Nadia-ElasticSearchODM-Tests-Stubs-Document-TestDocument1.dev.php';
        $metadata = require __DIR__ . '/../Fixtures/cache/' . $metadataFilename;
        $metadata['template']['template'] = $metadata['indexNamePrefix'] . $metadata['template']['template'];
        $updateResult = ['acknowledged' => true];
        $updateParams = ['name' => 'testing-template-name', 'body' => $metadata['template']];
        $indicesNamespace = $this->getMockBuilder(IndicesNamespace::class)
            ->disableOriginalConstructor()
            ->setMethods(['putTemplate'])
            ->getMock();
        $client = $this->getMockBuilder(\Nadia\ElasticSearchODM\ElasticSearch\Client::class)
            ->disableOriginalConstructor()
            ->setMethods(['indices'])
            ->getMock();

        $indicesNamespace->method('putTemplate')->willReturn($updateResult);
        $indicesNamespace->expects($this->exactly(2))->method('putTemplate')->with($updateParams);
        $client->method('indices')->willReturn($indicesNamespace);

        $manager = $this->createManager($client);

        $result = $manager->updateIndexTemplate(TestDocument1::class);
        $this->assertEquals($updateResult, $result);

        // Make sure "updateTemplate" result is the same with the first one
        $result = $manager->updateIndexTemplate(TestDocument1::class);
        $this->assertEquals($updateResult, $result);
    }

    public function testGetValidIndexNames()
    {
        /** @var Client $client */
        $client = (new ClientBuilder())
            ->setClientClassName(Client::class)
            ->setIndexNamePrefix('dev-')
            ->build();
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
        if (null === $client) {
            /** @var Client $client */
            $client = (new ClientBuilder())
                ->setClientClassName(Client::class)
                ->setIndexNamePrefix('dev-')
                ->build();
        }

        $metadataLoader = new ClassMetadataLoader($this->getCacheDir(), false, 'dev-', 'dev');

        return new Manager($client, $metadataLoader);
    }

    private function getCacheDir()
    {
        return __DIR__ . '/../.cache';
    }
}

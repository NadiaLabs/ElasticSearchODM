<?php

namespace Nadia\ElasticSearchODM\Tests\Document;

use Nadia\ElasticSearchODM\ClassMetadata\ClassMetadataLoader;
use Nadia\ElasticSearchODM\Document\Manager;
use Nadia\ElasticSearchODM\Document\Repository;
use Nadia\ElasticSearchODM\ElasticSearch\ClientBuilder;
use Nadia\ElasticSearchODM\Tests\Stubs\Document\Repository\InvalidRepository;
use Nadia\ElasticSearchODM\Tests\Stubs\Document\Repository\TestDocumentRepository;
use Nadia\ElasticSearchODM\Tests\Stubs\Document\TestDocument1;
use Nadia\ElasticSearchODM\Tests\Stubs\Document\TestDocument4;
use Nadia\ElasticSearchODM\Tests\Stubs\Document\TestDocument5;
use Nadia\ElasticSearchODM\Tests\Stubs\ElasticSearch\Client;
use Nadia\ElasticSearchODM\Tests\Stubs\ElasticSearch\IndicesNamespace;
use PHPUnit\Framework\TestCase;

class ManagerTest extends TestCase
{
    protected function setUp()
    {
        $cacheDir = $this->getCacheDir();

        if (!file_exists($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }
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
        $updateParams = ['name' => 'dev-testing-template-name', 'body' => $metadata['template']];
        $indicesNamespace = $this->getMockBuilder(IndicesNamespace::class)
            ->disableOriginalConstructor()
            ->setMethods(['putTemplate'])
            ->getMock();
        $client = $this->getMockBuilder(\Nadia\ElasticSearchODM\ElasticSearch\Client::class)
            ->disableOriginalConstructor()
            ->setMethods(['indices'])
            ->getMock();

        $indicesNamespace->method('putTemplate')->willReturn($updateResult);
        $indicesNamespace->expects($this->once())->method('putTemplate')->with($updateParams);
        $client->method('indices')->willReturn($indicesNamespace);

        $result = $this->createManager($client)->updateTemplate(TestDocument1::class);

        $this->assertEquals($updateResult, $result);
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

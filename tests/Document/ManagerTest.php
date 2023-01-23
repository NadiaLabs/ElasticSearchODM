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

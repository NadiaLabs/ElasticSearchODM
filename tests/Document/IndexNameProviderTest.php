<?php

namespace Nadia\ElasticSearchODM\Tests\Document;

use Elasticsearch\Client;
use Elasticsearch\Namespaces\IndicesNamespace;
use Nadia\ElasticSearchODM\Document\IndexNameProvider;
use Nadia\ElasticSearchODM\Tests\Stubs\Cache\Cache;
use PHPUnit\Framework\TestCase;

class IndexNameProviderTest extends TestCase
{
    public function testGetValidIndexNames()
    {
        $aliases = $this->getMockAliases();
        $client = $this->getMockClient($aliases);
        $cache = new Cache();
        $provider = new IndexNameProvider($client, 'dev-', $cache);

        $indexNames = $provider->getValidIndexNames(['index-001', 'index-003', 'index-not-exists']);
        $this->assertEquals(['dev-index-001', 'dev-index-003'], $indexNames);

        // Test when IndexNameProvider::$aliases is cached
        $indexNames = $provider->getValidIndexNames(['index-001', 'index-003', 'index-not-exists']);
        $this->assertEquals(['dev-index-001', 'dev-index-003'], $indexNames);

        // Test when indexNamePrefix is empty
        $aliases = $this->getMockAliasesWithoutIndexNamePrefix();
        $client = $this->getMockClient($aliases);
        $cache = new Cache();
        $provider = new IndexNameProvider($client, '', $cache);
        $indexNames = $provider->getValidIndexNames(['index-001', 'index-003', 'index-not-exists']);
        $this->assertEquals(['index-001', 'index-003'], $indexNames);
    }

    public function testGetValidIndexNamesWhenAliasesCacheExists()
    {
        $client = $this->getMockClient($this->getMockAliases());
        $cache = new Cache();
        $provider = new IndexNameProvider($client, 'dev-', $cache);

        $cacheItem = $cache->getItem($provider->getIndexAliasesCacheKey());
        $aliases = [];

        foreach ($this->getMockAliases() as $indexName => $indexNameAliases) {
            $aliases[$indexName] = true;

            foreach ($indexNameAliases['aliases'] as $aliasIndexName) {
                $aliases[$aliasIndexName] = true;
            }
        }
        $cacheItem->set($aliases);
        $cache->save($cacheItem);

        $indexNames = $provider->getValidIndexNames(['index-001', 'index-003', 'index-not-exists']);
        $this->assertEquals(['dev-index-001', 'dev-index-003'], $indexNames);
    }

    public function testGetValidIndexNamesWithRefreshIndexAliasesCache()
    {
        $aliases = $this->getMockAliases();
        $client = $this->getMockClient($aliases);
        $cache = new Cache();
        $provider = new IndexNameProvider($client, 'dev-', $cache);
        $provider->enableRefreshIndexAliasesCache();

        $indexNames = $provider->getValidIndexNames(['index-001', 'index-003', 'index-not-exists']);
        $this->assertEquals(['dev-index-001', 'dev-index-003'], $indexNames);

        $aliases = $this->getMockAliases();
        $client = $this->getMockClient($aliases);
        $cache = new Cache();
        $provider = new IndexNameProvider($client, 'dev-', $cache);
        $provider->disableRefreshIndexAliasesCache();

        $indexNames = $provider->getValidIndexNames(['index-001', 'index-003', 'index-not-exists']);
        $this->assertEquals(['dev-index-001', 'dev-index-003'], $indexNames);
    }

    private function getMockClient($aliases)
    {
        $indicesNamespace = $this->getMockBuilder(IndicesNamespace::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAliases'])
            ->getMock();
        $indicesNamespace->method('getAliases')->willReturn($aliases);

        $client = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->setMethods(['indices'])
            ->getMock();
        $client->method('indices')->willReturn($indicesNamespace);

        return $client;
    }

    private function getMockAliases()
    {
        return [
            'dev-index-001' => ['aliases' => []],
            'dev-index-002' => ['aliases' => []],
            'dev-index-003' => ['aliases' => ['dev-index-003-1', 'dev-index-003-2']],
        ];
    }

    private function getMockAliasesWithoutIndexNamePrefix()
    {
        return [
            'index-001' => ['aliases' => []],
            'index-002' => ['aliases' => []],
            'index-003' => ['aliases' => ['index-003-1', 'index-003-2']],
        ];
    }
}

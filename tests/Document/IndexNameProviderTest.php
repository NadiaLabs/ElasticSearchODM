<?php

namespace Nadia\ElasticSearchODM\Tests\Document;

use Nadia\ElasticSearchODM\Document\IndexNameProvider;
use Nadia\ElasticSearchODM\Helper\ElasticSearchHelper;
use Nadia\ElasticSearchODM\Tests\PHPUnit\Framework\TestCase;
use Nadia\ElasticSearchODM\Tests\Stubs\Cache\Cache;

class IndexNameProviderTest extends TestCase
{
    public function testGetValidIndexNames()
    {
        $aliases = $this->getMockAliases();
        $client = $this->mockElasticSearchClient($aliases);
        $cache = new Cache();
        $provider = new IndexNameProvider($client, 'dev-', $cache);

        $indexNames = $provider->getValidIndexNames(['index-001', 'index-003', 'index-not-exists']);
        $this->assertEquals(['dev-index-001', 'dev-index-003'], $indexNames);

        // Test when IndexNameProvider::$aliases is cached
        $indexNames = $provider->getValidIndexNames(['index-001', 'index-003', 'index-not-exists']);
        $this->assertEquals(['dev-index-001', 'dev-index-003'], $indexNames);

        // Test when indexNamePrefix is empty
        $aliases = $this->getMockAliasesWithoutIndexNamePrefix();
        $client = $this->mockElasticSearchClient($aliases);
        $cache = new Cache();
        $provider = new IndexNameProvider($client, '', $cache);
        $indexNames = $provider->getValidIndexNames(['index-001', 'index-003', 'index-not-exists']);
        $this->assertEquals(['index-001', 'index-003'], $indexNames);
    }

    public function testGetValidIndexNamesWhenAliasesCacheExists()
    {
        $client = $this->mockElasticSearchClient($this->getMockAliases());
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
        $client = $this->mockElasticSearchClient($aliases);
        $cache = new Cache();
        $provider = new IndexNameProvider($client, 'dev-', $cache);
        $provider->enableRefreshIndexAliasesCache();

        $indexNames = $provider->getValidIndexNames(['index-001', 'index-003', 'index-not-exists']);
        $this->assertEquals(['dev-index-001', 'dev-index-003'], $indexNames);

        $aliases = $this->getMockAliases();
        $client = $this->mockElasticSearchClient($aliases);
        $cache = new Cache();
        $provider = new IndexNameProvider($client, 'dev-', $cache);
        $provider->disableRefreshIndexAliasesCache();

        $indexNames = $provider->getValidIndexNames(['index-001', 'index-003', 'index-not-exists']);
        $this->assertEquals(['dev-index-001', 'dev-index-003'], $indexNames);
    }

    private function mockElasticSearchClient($aliases)
    {
        if (class_exists('Elastic\Elasticsearch\Response\Elasticsearch')) {
            $aliasesResponseHeaders = [
                'Content-Type' => 'application/json',
                \Elastic\Elasticsearch\Response\Elasticsearch::HEADER_CHECK =>
                    \Elastic\Elasticsearch\Response\Elasticsearch::PRODUCT_NAME,
            ];
            $aliasesResponse = new \GuzzleHttp\Psr7\Response(200, $aliasesResponseHeaders, json_encode($aliases));
            $aliases = new \Elastic\Elasticsearch\Response\Elasticsearch();
            $aliases->setResponse($aliasesResponse);
        }

        $indicesNamespaceClassName = ElasticSearchHelper::getNamespaceClassName('Indices');
        $indicesNamespaceMockMethods = ['getAlias'];
        if (version_compare(ElasticSearchHelper::getClientVersion(), '7.2.0', '<')) {
            $indicesNamespaceMockMethods = ['getAliases'];
        }
        $indicesNamespace = $this
            ->createMockBuilderAndOnlyMethods($indicesNamespaceClassName, $indicesNamespaceMockMethods)
            ->getMock();
        $indicesNamespace->method($indicesNamespaceMockMethods[0])->willReturn($aliases);

        $clientClassName = ElasticSearchHelper::getClientClassNameForPHPUnitMockBuilder();
        $client = $this
            ->createMockBuilderAndOnlyMethods($clientClassName, ['indices'])
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

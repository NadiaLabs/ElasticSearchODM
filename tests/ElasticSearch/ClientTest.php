<?php

namespace Nadia\ElasticSearchODM\Tests\ElasticSearch;

use Nadia\ElasticSearchODM\ElasticSearch\ClientBuilder;
use Nadia\ElasticSearchODM\Tests\Stubs\Cache\Cache;
use Nadia\ElasticSearchODM\Tests\Stubs\ElasticSearch\Client;
use PHPUnit\Framework\TestCase;
use Psr\Cache\InvalidArgumentException;

class ClientTest extends TestCase
{
    /**
     * @throws InvalidArgumentException
     */
    public function testGetValidIndexNames()
    {
        $aliases = $this->getMockAliases();

        /** @var Client $client1 */
        $client1 = (new ClientBuilder())
            ->setClientClassName(Client::class)
            ->setIndexNamePrefix('dev-')
            ->build();
        $client1->setMockAliases($aliases);

        $indexNames1 = ['index-001', 'index-002', 'index-004', 'index-003-2'];
        $validIndexNames1 = ['dev-index-001', 'dev-index-002', 'dev-index-003-2'];

        $this->assertEquals($validIndexNames1, $client1->getValidIndexNames($indexNames1));
        $this->assertEquals($validIndexNames1, $client1->getValidIndexNames($indexNames1));

        /** @var Client $client2 */
        $client2 = (new ClientBuilder())
            ->setClientClassName(Client::class)
            ->build();
        $client2->setMockAliases($aliases);

        $indexNames2 = ['dev-index-001', 'dev-index-002', 'dev-index-004', 'dev-index-003-2'];
        $validIndexNames2 = ['dev-index-001', 'dev-index-002', 'dev-index-003-2'];

        $this->assertEquals($validIndexNames2, $client2->getValidIndexNames($indexNames2));
        $this->assertEquals($validIndexNames2, $client2->getValidIndexNames($indexNames2));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testGetValidIndexNamesWithCache()
    {
        $cache = new Cache();
        $aliases = $this->getMockAliases();
        $indexNames = ['dev-index-001', 'dev-index-002', 'dev-index-004', 'dev-index-003-2'];
        $validIndexNames = ['dev-index-001', 'dev-index-002', 'dev-index-003-2'];
        $allValidIndexNames = array_combine(
            ['dev-index-001', 'dev-index-002', 'dev-index-003', 'dev-index-003-1', 'dev-index-003-2'],
            [true, true, true, true, true]
        );

        /** @var Client $client1 */
        $client1 = (new ClientBuilder())
            ->setClientClassName(Client::class)
            ->setCache($cache)
            ->build();
        $client1->setMockAliases($aliases)->enableRefreshCache();

        $this->assertEquals($validIndexNames, $client1->getValidIndexNames($indexNames));
        $this->assertEquals($allValidIndexNames, $cache->getItem(Client::getIndexAliasesCacheKey())->get());

        $this->assertEquals($validIndexNames, $client1->getValidIndexNames($indexNames));

        $client1->disableRefreshCache();
        $this->assertEquals($validIndexNames, $client1->getValidIndexNames($indexNames));

        /** @var Client $client2 */
        $client2 = (new ClientBuilder())
            ->setClientClassName(Client::class)
            ->setCache($cache)
            ->build();
        $client2->setMockAliases($aliases)->enableRefreshCache();

        $this->assertEquals($validIndexNames, $client2->getValidIndexNames($indexNames));

        /** @var Client $client3 */
        $client3 = (new ClientBuilder())
            ->setClientClassName(Client::class)
            ->setCache($cache)
            ->build();
        $client3->setMockAliases($aliases);

        $this->assertEquals($validIndexNames, $client3->getValidIndexNames($indexNames));
    }

    private function getMockAliases()
    {
        return [
            'dev-index-001' => ['aliases' => []],
            'dev-index-002' => ['aliases' => []],
            'dev-index-003' => ['aliases' => ['dev-index-003-1', 'dev-index-003-2']],
        ];
    }
}

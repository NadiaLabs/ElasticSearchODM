<?php

namespace Nadia\ElasticsearchODM\Document;

use Nadia\ElasticsearchODM\Helper\ElasticsearchHelper;
use Psr\Cache\CacheItemPoolInterface;

class IndexNameProvider
{
    /**
     * @var \Elastic\Elasticsearch\Client|\Elasticsearch\Client
     */
    protected $client;

    /**
     * @var string
     */
    protected $indexNamePrefix;

    /**
     * @var int
     */
    protected $indexNamePrefixLength;

    /**
     * @var CacheItemPoolInterface|null
     */
    protected $cache;

    /**
     * @var bool
     */
    protected $refreshIndexAliasesCache = false;

    /**
     * @var string[]
     */
    protected $aliases;

    /**
     * IndexNameProvider constructor.
     *
     * @param \Elastic\Elasticsearch\Client|\Elasticsearch\Client $client
     * @param string $indexNamePrefix
     * @param CacheItemPoolInterface|null $cache
     */
    public function __construct($client, $indexNamePrefix = '', CacheItemPoolInterface $cache = null)
    {
        $this->client = $client;
        $this->indexNamePrefix = $indexNamePrefix;
        $this->indexNamePrefixLength = strlen($indexNamePrefix);
        $this->cache = $cache;
    }

    /**
     * @param string[] $indexNames Index names without prefix
     *
     * @return string[] Valid index names with prefix
     *
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function getValidIndexNames(array $indexNames)
    {
        $this->loadValidIndexNames();

        $validIndexNames = [];

        foreach ($indexNames as $indexName) {
            $indexName = $this->indexNamePrefix . $indexName;

            if (false !== strpos($indexName, '*') || isset($this->aliases[$indexName])) {
                $validIndexNames[] = $indexName;
            }
        }

        return $validIndexNames;
    }

    /**
     * @return static
     *
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function loadValidIndexNames()
    {
        if (!$this->refreshIndexAliasesCache && !is_null($this->aliases)) {
            return $this;
        }

        if (!$this->refreshIndexAliasesCache && $this->cache instanceof CacheItemPoolInterface) {
            $cacheItem = $this->cache->getItem($this->getIndexAliasesCacheKey());

            if ($cacheItem->isHit()) {
                $this->aliases = (array)$cacheItem->get();

                return $this;
            }
        }

        $this->aliases = [];

        foreach ($this->getAliases() as $key => $alias) {
            if ($this->isValidIndexName($key)) {
                $this->aliases[$key] = true;
            }

            if (!empty($alias['aliases']) && is_array($alias['aliases'])) {
                foreach (array_keys($alias['aliases']) as $aliasIndexName) {
                    if ($this->isValidIndexName($aliasIndexName)) {
                        $this->aliases[$aliasIndexName] = true;
                    }
                }
            }
        }

        if ($this->cache instanceof CacheItemPoolInterface) {
            if (!isset($cacheItem)) {
                $cacheItem = $this->cache->getItem($this->getIndexAliasesCacheKey());
            }
            $cacheItem->set($this->aliases);
            $this->cache->save($cacheItem);
        }

        return $this;
    }

    /**
     * @return static
     */
    public function enableRefreshIndexAliasesCache()
    {
        $this->refreshIndexAliasesCache = true;

        return $this;
    }

    /**
     * @return static
     */
    public function disableRefreshIndexAliasesCache()
    {
        $this->refreshIndexAliasesCache = false;

        return $this;
    }

    public function getIndexAliasesCacheKey()
    {
        return 'elasticsearch_odm_client_index_aliases';
    }

    /**
     * @param string $indexName
     *
     * @return bool
     */
    protected function isValidIndexName($indexName)
    {
        if (0 === $this->indexNamePrefixLength) {
            return true;
        }

        return 0 === strncmp($indexName, $this->indexNamePrefix, $this->indexNamePrefixLength);
    }

    /**
     * @return array
     */
    protected function getAliases()
    {
        $indices = $this->client->indices();

        if (method_exists($indices, 'getAliases')) {
            return $indices->getAliases();
        }

        return ElasticSearchHelper::convertResponseToArray($indices->getAlias());
    }
}

<?php

namespace Nadia\ElasticSearchODM\ElasticSearch;

use Elasticsearch\Namespaces\AbstractNamespace;
use Elasticsearch\Transport;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;

class Client extends \Elasticsearch\Client
{
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
    protected $refreshCache = false;

    /**
     * @var string[]
     */
    protected $aliases;

    /**
     * Client constructor.
     *
     * @param Transport $transport
     * @param callable $endpoint
     * @param AbstractNamespace[] $registeredNamespaces
     * @param string $indexNamePrefix
     * @param CacheItemPoolInterface|null $cache
     */
    public function __construct(
        Transport $transport,
        callable $endpoint,
        array $registeredNamespaces,
        $indexNamePrefix = '',
        CacheItemPoolInterface $cache = null
    ) {
        parent::__construct($transport, $endpoint, $registeredNamespaces);

        $this->indexNamePrefix = $indexNamePrefix;
        $this->indexNamePrefixLength = strlen($indexNamePrefix);
        $this->cache = $cache;
    }

    /**
     * @param string[] $indexNames Index names without prefix
     *
     * @return string[] Valid index names with prefix
     *
     * @throws InvalidArgumentException
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
     * @throws InvalidArgumentException
     */
    public function loadValidIndexNames()
    {
        if (!$this->refreshCache && !is_null($this->aliases)) {
            return $this;
        }

        if (!$this->refreshCache && $this->cache instanceof CacheItemPoolInterface) {
            $cacheItem = $this->cache->getItem(static::getIndexAliasesCacheKey());

            if ($cacheItem->isHit()) {
                $this->aliases = (array)$cacheItem->get();

                return $this;
            }
        }

        $this->aliases = [];

        foreach ($this->indices()->getAliases() as $key => $alias) {
            if ($this->isValidIndexName($key)) {
                $this->aliases[$key] = true;
            }

            if (!empty($alias['aliases']) && is_array($alias['aliases'])) {
                foreach ($alias['aliases'] as $aliasIndexName) {
                    if ($this->isValidIndexName($aliasIndexName)) {
                        $this->aliases[$aliasIndexName] = true;
                    }
                }
            }
        }

        if ($this->cache instanceof CacheItemPoolInterface) {
            if (!isset($cacheItem)) {
                $cacheItem = $this->cache->getItem(static::getIndexAliasesCacheKey());
            }
            $cacheItem->set($this->aliases);
            $this->cache->save($cacheItem);
        }

        return $this;
    }

    /**
     * @return static
     */
    public function enableRefreshCache()
    {
        $this->refreshCache = true;

        return $this;
    }

    /**
     * @return static
     */
    public function disableRefreshCache()
    {
        $this->refreshCache = false;

        return $this;
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

    public static function getIndexAliasesCacheKey()
    {
        return 'elasticsearch_odm_client_index_aliases';
    }
}

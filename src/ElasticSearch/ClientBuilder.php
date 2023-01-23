<?php

namespace Nadia\ElasticSearchODM\ElasticSearch;

use Elasticsearch\Transport;
use Psr\Cache\CacheItemPoolInterface;

class ClientBuilder extends \Elasticsearch\ClientBuilder
{
    /**
     * @var string
     */
    private $clientClassName = Client::class;

    /**
     * @var string
     */
    private $indexNamePrefix = '';

    /**
     * @var CacheItemPoolInterface|null
     */
    private $cache;

    /**
     * @param string $clientClassName
     *
     * @return static
     */
    public function setClientClassName($clientClassName)
    {
        $this->clientClassName = $clientClassName;

        return $this;
    }

    /**
     * @param string $indexNamePrefix
     *
     * @return static
     */
    public function setIndexNamePrefix($indexNamePrefix)
    {
        $this->indexNamePrefix = $indexNamePrefix;

        return $this;
    }

    /**
     * @param CacheItemPoolInterface $cache
     *
     * @return static
     */
    public function setCache(CacheItemPoolInterface $cache)
    {
        $this->cache = $cache;

        return $this;
    }

    protected function instantiate(Transport $transport, callable $endpoint, array $registeredNamespaces)
    {
        return new $this->clientClassName(
            $transport,
            $endpoint,
            $registeredNamespaces,
            $this->indexNamePrefix,
            $this->cache
        );
    }
}

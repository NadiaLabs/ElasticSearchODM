<?php

namespace Nadia\ElasticSearchODM\Tests\Stubs\Cache;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

class Cache implements CacheItemPoolInterface
{
    /**
     * @var CacheItem[]
     */
    private $values = [];

    public function getItems(array $keys = array())
    {
        $result = [];

        foreach ($keys as $key) {
            $result[$key] = $this->getItem($key);
        }

        return $result;
    }

    public function getItem($key)
    {
        if ($this->hasItem($key)) {
            $this->values[$key] = new CacheItem($key, $this->values[$key]->get(), true);

            return $this->values[$key];
        }

        return $this->values[$key] = new CacheItem($key);
    }

    public function hasItem($key)
    {
        return isset($this->values[$key]);
    }

    public function clear()
    {
        $this->values = [];

        return true;
    }

    public function deleteItems(array $keys)
    {
        foreach ($keys as $key) {
            $this->deleteItem($key);
        }

        return true;
    }

    public function deleteItem($key)
    {
        unset($this->values[$key]);

        return true;
    }

    public function save(CacheItemInterface $item)
    {
        $this->values[$item->getKey()] = $item;

        return true;
    }

    public function saveDeferred(CacheItemInterface $item)
    {
        $this->values[$item->getKey()] = $item;

        return true;
    }

    public function commit()
    {
        return true;
    }
}

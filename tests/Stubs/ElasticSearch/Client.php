<?php

namespace Nadia\ElasticSearchODM\Tests\Stubs\ElasticSearch;

class Client extends \Nadia\ElasticSearchODM\ElasticSearch\Client
{
    private $mockAliases = [];

    public function indices()
    {
        return new IndicesNamespace($this->mockAliases);
    }

    public function setMockAliases(array $aliases)
    {
        $this->mockAliases = $aliases;

        return $this;
    }
}

<?php

namespace Nadia\ElasticSearchODM\Tests\Stubs\ElasticSearch;

class IndicesNamespace
{
    private $aliases = [];

    public function __construct(array $aliases)
    {
        $this->aliases = $aliases;
    }

    public function getAliases()
    {
        return $this->aliases;
    }
}

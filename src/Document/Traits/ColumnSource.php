<?php

namespace Nadia\ElasticsearchODM\Document\Traits;

use Nadia\ElasticsearchODM\Annotations as ES;

trait ColumnSource
{
    /**
     * @var string
     *
     * @ES\Source
     */
    public $docSource;
}

<?php

namespace Nadia\ElasticSearchODM\Document\Traits;

use Nadia\ElasticSearchODM\Annotations as ES;

trait ColumnSource
{
    /**
     * @var string
     *
     * @ES\Source
     */
    public $docSource;
}

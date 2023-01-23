<?php

namespace Nadia\ElasticSearchODM\Document\Traits;

use Nadia\ElasticSearchODM\Annotations as ES;

trait ColumnId
{
    /**
     * @var string
     *
     * @ES\Id
     */
    public $docId;
}

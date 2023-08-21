<?php

namespace Nadia\ElasticsearchODM\Document\Traits;

use Nadia\ElasticsearchODM\Annotations as ES;

trait ColumnId
{
    /**
     * @var string
     *
     * @ES\Id
     */
    public $docId;
}

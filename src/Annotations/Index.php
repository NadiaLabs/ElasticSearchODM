<?php

namespace Nadia\ElasticsearchODM\Annotations;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
final class Index
{
    public $name;

    public $type = 'default';
}

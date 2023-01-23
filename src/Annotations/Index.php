<?php

namespace Nadia\ElasticSearchODM\Annotations;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
final class Index
{
    public $name;

    public $type = 'default';
}

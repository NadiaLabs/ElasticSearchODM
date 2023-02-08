<?php

namespace Nadia\ElasticSearchODM\Annotations\Mappings;

/**
 * @Annotation
 * @Target({"ANNOTATION"})
 */
final class Object
{
    /**
     * @var \Nadia\ElasticSearchODM\Annotations\Column[]
     */
    public $properties = [];
}

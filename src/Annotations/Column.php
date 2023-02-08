<?php

namespace Nadia\ElasticSearchODM\Annotations;

/**
 * @Annotation
 * @Target({"PROPERTY", "ANNOTATION"})
 */
final class Column
{
    public $name;

    /**
     * An annotation in "\ElasticSearchODM\Annotations\Mappings"
     */
    public $mapping;
}

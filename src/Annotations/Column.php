<?php

namespace Nadia\ElasticSearchODM\Annotations;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 */
final class Column
{
    public $name;

    /**
     * An annotation in "\ElasticSearchODM\Annotations\Mappings"
     */
    public $mapping;
}

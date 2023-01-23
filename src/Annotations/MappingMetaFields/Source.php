<?php

namespace Nadia\ElasticSearchODM\Annotations\MappingMetaFields;

/**
 * @Annotation
 * @Target({"ANNOTATION"})
 */
final class Source
{
    public $enabled = true;

    public $includes = [];

    public $excludes = [];
}

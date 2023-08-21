<?php

namespace Nadia\ElasticsearchODM\Annotations\Mappings;

/**
 * @Annotation
 * @Target({"ANNOTATION"})
 */
final class ESObject
{
    /**
     * @var \Nadia\ElasticSearchODM\Annotations\Column[]
     */
    public $properties = [];
}

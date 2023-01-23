<?php

namespace Nadia\ElasticSearchODM\Annotations\Mappings;

/**
 * @Annotation
 * @Target({"ANNOTATION"})
 */
final class Date
{
    public $boost = 1.0;

    public $doc_values = true;

    public $format;

    public $ignore_malformed = false;

    public $index = true;

    public $null_value;

    public $store = false;
}

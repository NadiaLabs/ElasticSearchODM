<?php

namespace Nadia\ElasticSearchODM\Annotations\Mappings\Number;

/**
 * @Annotation
 * @Target({"ANNOTATION"})
 */
final class Double
{
    public $coerce = true;

    public $boost = 1.0;

    public $doc_values = true;

    public $ignore_malformed = false;

    public $include_in_all = false;

    public $index = true;

    public $null_value;

    public $store = false;
}

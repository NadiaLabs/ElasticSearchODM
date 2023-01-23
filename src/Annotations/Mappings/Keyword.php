<?php

namespace Nadia\ElasticSearchODM\Annotations\Mappings;

use Doctrine\Common\Annotations\Annotation\Enum;

/**
 * @Annotation
 * @Target({"ANNOTATION"})
 */
final class Keyword
{
    public $boost = 1.0;

    public $doc_values = true;

    public $eager_global_ordinals = false;

    public $ignore_above = 2147483647;

    public $include_in_all = false;

    public $index = true;

    /**
     * @Enum({"docs", "freqs", "potitions", "offsets"})
     */
    public $index_options = 'docs';

    public $norms = false;

    public $null_value;

    public $store = false;

    public $similarity = 'BM25';
}

<?php

namespace Nadia\ElasticsearchODM\Annotations\Mappings;

use Doctrine\Common\Annotations\Annotation\Enum;

/**
 * @Annotation
 * @Target({"ANNOTATION"})
 */
final class Text
{
    public $analyzer;

    public $boost = 1.0;

    public $eager_global_ordinals = false;

    public $fielddata = false;

    public $fielddata_frequency_filter;

    public $fields;

    public $include_in_all = false;

    public $index = true;

    /**
     * @Enum({"docs", "freqs", "potitions", "offsets"})
     */
    public $index_options = 'positions';

    public $norms = true;

    public $position_increment_gap = 100;

    public $store = false;

    public $search_analyzer;

    public $search_quote_analyzer;

    /**
     * @Enum({"BM25", "classic", "boolean"})
     */
    public $similarity = 'BM25';

    /**
     * @Enum({"no", "yes", "with_positions", "with_offsets", "with_positions_offsets"})
     */
    public $term_vector = 'no';
}

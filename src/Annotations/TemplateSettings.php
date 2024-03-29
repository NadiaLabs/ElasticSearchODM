<?php

namespace Nadia\ElasticsearchODM\Annotations;

/**
 * @Annotation
 * @Target({"ANNOTATION"})
 */
final class TemplateSettings
{
    public $number_of_shards;

    public $refresh_interval;
}

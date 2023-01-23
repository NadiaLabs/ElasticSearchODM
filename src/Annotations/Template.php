<?php

namespace Nadia\ElasticSearchODM\Annotations;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
final class Template
{
    public $name;

    public $index_name_pattern;

    /**
     * @var \Nadia\ElasticSearchODM\Annotations\TemplateSettings[]
     *   Array key is environment name (prod, dev, ...etc), "prod" is the default environment name.
     *   Array value is the TemplateSettings annotation.
     *   For example:
     *   <code><pre>
     *     Template(
     *       settings={
     *         "prod": TemplateSettings(number_of_shards="5", refresh_interval="60s"),
     *         "dev": TemplateSettings(number_of_shards="5"),
     *       }
     *     )
     *   </pre></code>
     */
    public $settings;

    public $mappingMetaFields = [];
}

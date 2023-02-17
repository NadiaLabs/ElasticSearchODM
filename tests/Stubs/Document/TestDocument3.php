<?php

namespace Nadia\ElasticSearchODM\Tests\Stubs\Document;

use Nadia\ElasticSearchODM\Annotations as ES;
use Nadia\ElasticSearchODM\Document\Traits\ColumnId;
use Nadia\ElasticSearchODM\Document\Traits\ColumnSource;

/**
 * An invalid document class without "ES\Document" annotation.
 *
 * Will throw an exception when loading this class metadata.
 *
 * @ES\Template(
 *     name="testing-template-name",
 *     index_patterns={"idx-testing-*"},
 *     settings={
 *       "prod": @ES\TemplateSettings(number_of_shards="5", refresh_interval="30s"),
 *       "dev": @ES\TemplateSettings(number_of_shards="5"),
 *     },
 *     mapping_meta_fields={@ES\MappingMetaFields\Source(enabled=false)}
 * )
 */
class TestDocument3
{
    use ColumnId;
    use ColumnSource;

    /**
     * @var string
     *
     * @ES\Column(mapping=@ES\Mappings\Keyword())
     */
    public $id;

    /**
     * @var string
     *
     * @ES\Column(name="created_at", mapping=@ES\Mappings\Date(format="yyyy-MM-dd"))
     */
    public $createdAt;
}

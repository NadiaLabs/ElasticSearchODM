<?php

namespace Nadia\ElasticSearchODM\Tests\Stubs\Document;

use Nadia\ElasticSearchODM\Annotations as ES;
use Nadia\ElasticSearchODM\Document\Traits\ColumnId;
use Nadia\ElasticSearchODM\Document\Traits\ColumnSource;

/**
 * A document class with a fixed index name and without a routing generating method.
 *
 * @ES\Document(
 *     index_name="testing",
 *     index_type_name="log",
 *     repository_class_name="Nadia\ElasticSearchODM\Tests\Stubs\Document\Repository\TestDocumentRepository",
 * )
 * @ES\Template(
 *     name="testing-template-name",
 *     index_patterns={"idx-testing-*"},
 *     settings={
 *       "prod": @ES\TemplateSettings(number_of_shards="5", refresh_interval="30s"),
 *       "dev": @ES\TemplateSettings(number_of_shards="5"),
 *     },
 *     mapping_meta_fields={@ES\MappingMetaFields\Source(enabled=false)},
 * )
 */
class TestDocument2
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

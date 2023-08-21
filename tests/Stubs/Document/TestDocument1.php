<?php

namespace Nadia\ElasticsearchODM\Tests\Stubs\Document;

use Nadia\ElasticsearchODM\Annotations as ES;
use Nadia\ElasticsearchODM\Document\DynamicIndexNameDocument;
use Nadia\ElasticsearchODM\Document\RoutingEnabledDocument;
use Nadia\ElasticsearchODM\Document\Traits\ColumnId;
use Nadia\ElasticsearchODM\Document\Traits\ColumnSource;

/**
 * A normal document class with general usages.
 *
 * @ES\Document(
 *     index_type_name="log",
 *     repository_class_name="Nadia\ElasticsearchODM\Tests\Stubs\Document\Repository\TestDocumentRepository",
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
class TestDocument1 implements DynamicIndexNameDocument, RoutingEnabledDocument
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

    /**
     * TestDocument1 constructor.
     *
     * @param string $id
     * @param string $createdAt
     */
    public function __construct($id, $createdAt)
    {
        $this->id = $id;
        $this->createdAt = $createdAt;
    }

    public static function generateIndexName($document)
    {
        /** @var self|array $document */
        $createdAt = is_object($document) ? $document->createdAt : $document['createdAt'];

        return 'idx-testing-' . str_replace('-', '', $createdAt);
    }

    public static function generateRoutingName($document)
    {
        return 'id:' . (is_object($document) ? $document->id : $document['id']);
    }
}

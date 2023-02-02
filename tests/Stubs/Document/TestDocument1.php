<?php

namespace Nadia\ElasticSearchODM\Tests\Stubs\Document;

use Nadia\ElasticSearchODM\Annotations as ES;
use Nadia\ElasticSearchODM\Document\DynamicIndexNameDocument;
use Nadia\ElasticSearchODM\Document\RoutingEnabledDocument;
use Nadia\ElasticSearchODM\Document\Traits\ColumnId;
use Nadia\ElasticSearchODM\Document\Traits\ColumnSource;

/**
 * A normal document class with general usages.
 *
 * @ES\Document(
 *     index_type_name="log",
 *     repository_class_name="Nadia\ElasticSearchODM\Tests\Stubs\Document\Repository\TestDocumentRepository",
 * )
 * @ES\Template(
 *     name="testing-template-name",
 *     index_name_pattern="idx-testing-*",
 *     settings={
 *       "prod": @ES\TemplateSettings(number_of_shards="5", refresh_interval="30s"),
 *       "dev": @ES\TemplateSettings(number_of_shards="5"),
 *     },
 *     mappingMetaFields={@ES\MappingMetaFields\Source(enabled=false)},
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
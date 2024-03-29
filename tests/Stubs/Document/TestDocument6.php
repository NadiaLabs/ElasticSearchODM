<?php

namespace Nadia\ElasticsearchODM\Tests\Stubs\Document;

use Nadia\ElasticsearchODM\Annotations as ES;
use Nadia\ElasticsearchODM\Document\Traits\ColumnId;

/**
 * A normal document class for testing template name.
 *
 * @ES\Document(
 *     index_name="testing",
 *     index_type_name="log",
 *     repository_class_name="Nadia\ElasticsearchODM\Tests\Stubs\Document\Repository\TestDocumentRepository",
 * )
 * @ES\Template(
 *     name="template-%s-testing-template-name",
 *     index_patterns={"idx-testing-*"},
 *     settings={
 *       "prod": @ES\TemplateSettings(number_of_shards="5", refresh_interval="30s"),
 *     },
 * )
 */
class TestDocument6
{
    use ColumnId;

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
     * TestDocument6 constructor.
     *
     * @param string $id
     * @param string $createdAt
     */
    public function __construct($id, $createdAt)
    {
        $this->id = $id;
        $this->createdAt = $createdAt;
    }
}

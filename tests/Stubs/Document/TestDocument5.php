<?php

namespace Nadia\ElasticSearchODM\Tests\Stubs\Document;

use Nadia\ElasticSearchODM\Annotations as ES;

/**
 * A document class with an invalid repository class
 *
 * @ES\Document(
 *     index_name="testing",
 *     index_type_name="log",
 *     repository_class_name="Nadia\ElasticSearchODM\Tests\Stubs\Document\Repository\InvalidRepository",
 * )
 * @ES\Template(
 *     name="testing-template-name",
 *     index_patterns={"idx-testing-*"},
 * )
 */
class TestDocument5
{
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

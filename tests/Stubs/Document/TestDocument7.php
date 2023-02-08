<?php

namespace Nadia\ElasticSearchODM\Tests\Stubs\Document;

use Nadia\ElasticSearchODM\Annotations as ES;

/**
 * A normal document class for testing object mapping.
 *
 * @ES\Document(
 *     index_name="testing",
 *     index_type_name="log",
 *     repository_class_name="Nadia\ElasticSearchODM\Tests\Stubs\Document\Repository\TestDocumentRepository",
 * )
 * @ES\Template(
 *     name="template-%s-testing-template-name",
 *     index_name_pattern="idx-testing-*",
 *     settings={
 *       "prod": @ES\TemplateSettings(number_of_shards="5", refresh_interval="30s"),
 *     },
 * )
 */
class TestDocument7
{
    /**
     * @var string
     *
     * @ES\Column(mapping=@ES\Mappings\Keyword())
     */
    public $id;

    /**
     * @var array
     *
     * @ES\Column(mapping=@ES\Mappings\Object(
     *   properties={
     *     @ES\Column(name="foo", mapping=@ES\Mappings\Keyword()),
     *     @ES\Column(name="bar", mapping=@ES\Mappings\Keyword()),
     *     @ES\Column(name="foobar", mapping=@ES\Mappings\Keyword()),
     *     @ES\Column(name="baz", mapping=@ES\Mappings\Object(
     *       properties={
     *         @ES\Column(name="foo", mapping=@ES\Mappings\Keyword()),
     *         @ES\Column(name="bar", mapping=@ES\Mappings\Keyword()),
     *         @ES\Column(name="foobar", mapping=@ES\Mappings\Keyword()),
     *       },
     *     )),
     *   }
     * ))
     */
    public $diff = [];

    /**
     * TestDocument7 constructor.
     *
     * @param string $id
     * @param array $diff
     */
    public function __construct($id, $diff)
    {
        $this->id = $id;
        $this->diff = $diff;
    }
}

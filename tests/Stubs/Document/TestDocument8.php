<?php

namespace Nadia\ElasticSearchODM\Tests\Stubs\Document;

use Nadia\ElasticSearchODM\Annotations as ES;

/**
 * A normal document class for testing object mapping with invalid property parameters.
 *
 * Will throw an exception when loading this class metadata.
 *
 * @ES\Document(
 *     index_name="testing",
 *     index_type_name="log",
 *     repository_class_name="Nadia\ElasticSearchODM\Tests\Stubs\Document\Repository\TestDocumentRepository",
 * )
 * @ES\Template(
 *     name="template-%s-testing-template-name",
 *     index_patterns={"idx-testing-*"},
 *     settings={
 *       "prod": @ES\TemplateSettings(number_of_shards="5", refresh_interval="30s"),
 *     },
 * )
 */
class TestDocument8
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
     * @ES\Column(mapping=@ES\Mappings\ESObject(
     *   properties={
     *     @ES\Column(mapping=@ES\Mappings\Keyword()),
     *   }
     * ))
     */
    public $diff = [];
}

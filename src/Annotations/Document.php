<?php

namespace Nadia\ElasticsearchODM\Annotations;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
final class Document
{
    public $index_name;

    public $index_type_name = 'default';

    public $repository_class_name;
}

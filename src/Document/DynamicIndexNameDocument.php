<?php

namespace Nadia\ElasticsearchODM\Document;

interface DynamicIndexNameDocument
{
    /**
     * Using document properties to generate dynamic index name
     *
     * For example, a document has values as below:
     * <code>
     * {
     *   "foo": "bar",
     *   "created_time": 1669824251
     * }
     * </code>
     *
     * We want to generate an index name "index_name_20221201",
     * the "20221201" part is generated from "created_time" property,
     * then implement "generateIndexName" method as below:
     *
     * <code>
     * public static function generateIndexName($document)
     * {
     *   // Generate 'Ymd' part from a Document object or a key-value array
     *   $createdAt = is_object($document) ? $document->getCreatedTime() : $document['created_time'];
     *
     *   return 'index_name_' . date('Ymd', $createdAt);
     * }
     * </code>
     *
     * @param array|object $document
     *
     * @return string
     */
    public static function generateIndexName($document);
}

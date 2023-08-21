<?php

namespace Nadia\ElasticsearchODM\Document;

interface RoutingEnabledDocument
{
    /**
     * Using document properties to generate routing name
     *
     * For example, a document has values as below:
     * <code>
     * {
     *   "uid": 6867926,
     * }
     * </code>
     *
     * We want to generate a routing name "uid:6867926",
     * the "20221201" part is generated from "uid" property,
     * then implement "generateRoutingName" method as below:
     *
     * <code>
     * public static function generateRoutingName($document)
     * {
     *   return 'uid:' . (is_object($document) ? $document->getUid() : $document['uid']);
     * }
     * </code>
     *
     * @param array|object $document
     *
     * @return string
     */
    public static function generateRoutingName($document);
}

<?php

namespace Nadia\ElasticSearchODM\Helper;

final class ElasticSearchHelper
{
    private static $clientClassName;

    private static $clientBuilderClassName;

    private static $clientClassNameForPHPUnitMockBuilder;

    private static $namespaceClassNames = [];

    private static $version;

    /**
     * @return string
     */
    public static function getClientVersion()
    {
        if (!self::$version) {
            $className = self::getClientClassName();

            self::$version = constant($className . '::VERSION');
        }

        return self::$version;
    }

    /**
     * @return string
     */
    public static function getClientClassName()
    {
        if (!self::$clientClassName) {
            $className = 'Elasticsearch\Client';

            if (!class_exists($className)) {
                $className = 'Elastic\Elasticsearch\Client';
            }

            self::$clientClassName = $className;
        }

        return self::$clientClassName;
    }

    /**
     * @return string
     */
    public static function getClientBuilderClassName()
    {
        if (!self::$clientBuilderClassName) {
            $className = 'Elastic\Elasticsearch\ClientBuilder';

            if (!class_exists($className)) {
                $className = 'Elasticsearch\ClientBuilder';
            }

            self::$clientBuilderClassName = $className;
        }

        return self::$clientBuilderClassName;
    }

    /**
     * @return string
     */
    public static function getClientClassNameForPHPUnitMockBuilder()
    {
        if (!self::$clientClassNameForPHPUnitMockBuilder) {
            $className = 'Elasticsearch\Client';

            if (!class_exists($className)) {
                $className = 'Nadia\ElasticSearchODM\Tests\Stubs\ElasticSearch\Client';
            }

            self::$clientClassNameForPHPUnitMockBuilder = $className;
        }

        return self::$clientClassNameForPHPUnitMockBuilder;
    }

    /**
     * @param string $name e.g. Indices, Nodes, ...etc.
     *
     * @return string
     */
    public static function getNamespaceClassName($name)
    {
        if (!isset(self::$namespaceClassNames[$name])) {
            $className = 'Elasticsearch\Namespaces\\' . $name . 'Namespace';

            if (!class_exists($className)) {
                $className = 'Elastic\Elasticsearch\Endpoints\\' . $name;
            }

            self::$namespaceClassNames[$name] = $className;
        }

        return self::$namespaceClassNames[$name];
    }

    /**
     * @param array|\Elastic\Elasticsearch\Response\Elasticsearch $result
     *
     * @return array
     */
    public static function convertResponseToArray($result)
    {
        if (!is_array($result) && get_class($result) === 'Elastic\Elasticsearch\Response\Elasticsearch') {
            return $result->asArray();
        }

        return $result;
    }
}

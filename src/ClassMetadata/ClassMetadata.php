<?php

namespace Nadia\ElasticSearchODM\ClassMetadata;

class ClassMetadata
{
    /**
     * @var string
     */
    public $version;

    /**
     * @var string
     */
    public $className;

    /**
     * @var string|callable
     */
    public $indexName;

    /**
     * @var string
     */
    public $indexNamePrefix;

    /**
     * @var string
     */
    public $indexTypeName;

    /**
     * @var string
     */
    public $repositoryClassName;

    /**
     * @var callable
     */
    public $routing;

    /**
     * @var array
     */
    public $metaColumnsElasticToObject = [];

    /**
     * @var array
     */
    public $metaColumnsObjectToElastic = [];

    /**
     * @var array
     */
    public $columnsElasticToObject = [];

    /**
     * @var array
     */
    public $columnsObjectToElastic = [];

    /**
     * @var string
     */
    public $templateName;

    /**
     * @var array
     */
    public $template;

    /**
     * @var \ReflectionClass
     */
    private $reflectionClass;

    public function __construct(array $metadata)
    {
        $fields = [
            'version',
            'className',
            'indexName',
            'indexNamePrefix',
            'indexTypeName',
            'repositoryClassName',
            'routing',
            'metaColumnsElasticToObject',
            'metaColumnsObjectToElastic',
            'columnsElasticToObject',
            'columnsObjectToElastic',
            'templateName',
            'template',
        ];

        foreach ($fields as $field) {
            if (!array_key_exists($field, $metadata)) {
                throw new \InvalidArgumentException('"' . $field . '" is missing');
            }
            $this->{$field} = $metadata[$field];
        }
    }

    /**
     * @param object|array $document
     *
     * @return string
     */
    public function getIndexName($document)
    {
        if (is_callable($this->indexName)) {
            $indexName = call_user_func($this->indexName, $document);
        } else {
            $indexName = $this->indexName;
        }

        return $this->indexNamePrefix . $indexName;
    }

    /**
     * @param object|array $document
     *
     * @return string
     */
    public function getRouting($document)
    {
        if (is_callable($this->routing)) {
            return call_user_func($this->routing, $document);
        }

        return '';
    }

    /**
     * @return \ReflectionClass
     *
     * @throws \ReflectionException
     */
    public function getReflectionClass()
    {
        if (is_null($this->reflectionClass)) {
            $this->reflectionClass = new \ReflectionClass($this->className);
        }

        return $this->reflectionClass;
    }
}

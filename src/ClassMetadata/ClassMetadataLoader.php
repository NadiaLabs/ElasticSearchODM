<?php

namespace Nadia\ElasticSearchODM\ClassMetadata;

use Doctrine\Common\Annotations\AnnotationReader;
use Nadia\ElasticSearchODM\Annotations as ES;
use Nadia\ElasticSearchODM\Document\DynamicIndexNameDocument;
use Nadia\ElasticSearchODM\Document\RoutingEnabledDocument;
use ReflectionClass;
use ReflectionProperty;

final class ClassMetadataLoader
{
    /**
     * @var string
     */
    private $cacheDirPath;

    /**
     * @var bool
     */
    private $updateCache;

    /**
     * @var string
     */
    private $indexNamePrefix;

    /**
     * @var string
     */
    private $env;

    /**
     * @var ClassMetadata[]
     */
    private $loaded = [];

    /**
     * ClassMetadataLoader constructor.
     *
     * @param string $cachePath
     */
    public function __construct($cachePath, $updateCache = false, $indexNamePrefix = '', $env = 'prod')
    {
        $this->cacheDirPath = rtrim($cachePath, '/ ');
        $this->updateCache = $updateCache;
        $this->indexNamePrefix = $indexNamePrefix;
        $this->env = $env;
    }

    /**
     * @throws \ReflectionException
     */
    public function load($className)
    {
        $className = ltrim($className, '\\ ');

        if (isset($this->loaded[$className])) {
            return $this->loaded[$className];
        }

        $cacheFilepath = $this->cacheDirPath . '/' . str_replace('\\', '-', $className) . '.' . $this->env . '.php';

        if (file_exists($cacheFilepath)) {
            $metadata = require $cacheFilepath;

            if (
                false === $this->updateCache
                || $metadata['version'] === $this->getVersion((new ReflectionClass($className))->getFileName())
            ) {
                return $this->loaded[$className] = new ClassMetadata($metadata);
            }
        }

        $reflectionClass = new ReflectionClass($className);
        $reader = $this->getReader();
        $metadata = [
            'version' => $this->getVersion($reflectionClass->getFileName()),
            'className' => $className,
            'indexNamePrefix' => $this->indexNamePrefix,
            'metaColumnsElasticToObject' => [],
            'metaColumnsObjectToElastic' => [],
        ];

        $this->resolveClassMetadata($reflectionClass, $reader->getClassAnnotations($reflectionClass), $metadata);

        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            $this->resolvePropertyMetadata(
                $reflectionProperty,
                $reader->getPropertyAnnotations($reflectionProperty),
                $metadata
            );
        }

        file_put_contents($cacheFilepath, '<?php return ' . var_export($metadata, true) . ";\n");

        return $this->loaded[$className] = new ClassMetadata($metadata);
    }

    /**
     * Enable to update metadata cache
     *
     * @return $this
     */
    public function enableUpdateCache()
    {
        $this->updateCache = true;

        return $this;
    }

    /**
     * Disable to update metadata cache
     *
     * @return $this
     */
    public function disableUpdateCache()
    {
        $this->updateCache = false;

        return $this;
    }

    private function resolveClassMetadata(ReflectionClass $reflectionClass, array $classAnnotations, array &$metadata)
    {
        $mappings = [];
        $defaultTemplateSettings = get_object_vars(new ES\TemplateSettings());
        $missingRequiredAnnotations = ['Document' => true, 'Template' => true];

        foreach ($classAnnotations as $annotation) {
            if ($annotation instanceof ES\Document) {
                if ($reflectionClass->implementsInterface(DynamicIndexNameDocument::class)) {
                    $metadata['indexName'] = [$reflectionClass->getName(), 'generateIndexName'];
                } else {
                    $metadata['indexName'] = $annotation->index_name;
                }

                if ($reflectionClass->implementsInterface(RoutingEnabledDocument::class)) {
                    $metadata['routing'] = [$reflectionClass->getName(), 'generateRoutingName'];
                } else {
                    $metadata['routing'] = null;
                }

                $metadata['indexTypeName'] = $annotation->index_type_name;
                $metadata['repositoryClassName'] = $annotation->repository_class_name;
                $missingRequiredAnnotations['Document'] = false;
            } elseif ($annotation instanceof ES\Template) {
                if (false !== strpos($annotation->name, '%s')) {
                    $metadata['templateName'] = vsprintf(
                        $annotation->name,
                        array_fill(0, substr_count($annotation->name, '%s'), $this->env)
                    );
                } else {
                    $metadata['templateName'] = $annotation->name;
                }

                $metadata['template'] = [
                    'template' => $annotation->index_name_pattern,
                    'settings' => [],
                ];

                foreach ($defaultTemplateSettings as $name => $defaultValue) {
                    if (
                        isset($annotation->settings[$this->env])
                        && $defaultValue !== $annotation->settings[$this->env]->{$name}
                    ) {
                        $metadata['template']['settings'][$name] = $annotation->settings[$this->env]->{$name};
                    }
                }

                foreach ($annotation->mappingMetaFields as $metaField) {
                    $metaFieldClassName = get_class($metaField);
                    $metaFieldName = array_slice(explode('\\', $metaFieldClassName), -1)[0];
                    $metaFieldName = '_' . $this->convertCamelToSnake($metaFieldName);
                    $default = new $metaFieldClassName();
                    $value = [];

                    foreach (get_object_vars($default) as $name => $defaultValue) {
                        if ($defaultValue !== $metaField->{$name}) {
                            $value[$name] = $metaField->{$name};
                        }
                    }

                    if (!empty($value)) {
                        $mappings[$metaFieldName] = $value;
                    }
                }

                $missingRequiredAnnotations['Template'] = false;
            }
        }

        foreach ($missingRequiredAnnotations as $name => $isMissing) {
            if ($isMissing) {
                throw new \InvalidArgumentException('Required annotation @' . $name . ' is missing');
            }
        }

        $metadata['template']['mappings'][$metadata['indexTypeName']] = $mappings;
    }

    private function resolvePropertyMetadata(
        ReflectionProperty $reflectionProperty,
        array $propertyAnnotations,
        array &$metadata
    ) {
        $propertyName = $reflectionProperty->getName();

        if (!isset($metadata['template']['mappings'][$metadata['indexTypeName']]['properties'])) {
            $metadata['template']['mappings'][$metadata['indexTypeName']]['properties'] = [];
        }
        $properties =& $metadata['template']['mappings'][$metadata['indexTypeName']]['properties'];

        foreach ($propertyAnnotations as $annotation) {
            if ($annotation instanceof ES\Column) {
                $className = get_class($annotation->mapping);
                $mappingType = array_slice(explode('\\', $className), -1)[0];
                $mappingType = $this->convertCamelToSnake($mappingType);
                $default = new $className();
                $property = ['type' => $mappingType];
                $elasticColumnName = empty($annotation->name) ? $propertyName : $annotation->name;

                foreach (get_object_vars($default) as $name => $defaultValue) {
                    if ($defaultValue !== $annotation->mapping->{$name}) {
                        $property[$name] = $annotation->mapping->{$name};
                    }
                }

                $properties[$elasticColumnName] = $property;
                $metadata['columnsElasticToObject'][$elasticColumnName] = $propertyName;
                $metadata['columnsObjectToElastic'][$propertyName] = $elasticColumnName;
            } elseif ($annotation instanceof ES\Id) {
                $metadata['metaColumnsElasticToObject']['_id'] = $propertyName;
                $metadata['metaColumnsObjectToElastic'][$propertyName] = '_id';
            }
        }
    }

    private function convertCamelToSnake($string)
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $string));
    }

    private function getReader()
    {
        return new AnnotationReader();
    }

    /**
     * @param string $documentFilepath
     *
     * @return string
     */
    private function getVersion($documentFilepath)
    {
        return md5_file($documentFilepath) . md5_file(__FILE__);
    }
}

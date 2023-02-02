<?php

namespace Nadia\ElasticSearchODM\Document;

use Nadia\ElasticSearchODM\ClassMetadata\ClassMetadata;
use Nadia\ElasticSearchODM\ClassMetadata\ClassMetadataLoader;
use Nadia\ElasticSearchODM\ElasticSearch\Client;

class Manager
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var ClassMetadataLoader
     */
    protected $classMetadataLoader;

    /**
     * @var Repository[]
     */
    protected $repositories = [];

    /**
     * Manager constructor.
     *
     * @param Client $client
     * @param ClassMetadataLoader $classMetadataLoader
     */
    public function __construct($client, ClassMetadataLoader $classMetadataLoader)
    {
        $this->client = $client;
        $this->classMetadataLoader = $classMetadataLoader;
    }

    /**
     * @param string $documentClassName
     *
     * @return Repository
     *
     * @throws \ReflectionException
     */
    public function getRepository($documentClassName)
    {
        if (isset($this->repositories[$documentClassName])) {
            return $this->repositories[$documentClassName];
        }

        $metadata = $this->getClassMetadata($documentClassName);
        $repoClassName = $metadata->repositoryClassName;

        if (!class_exists($repoClassName)) {
            throw new \InvalidArgumentException('The document repository "' . $repoClassName . '" is not exists!');
        }
        if (!is_subclass_of($repoClassName, Repository::class)) {
            throw new \InvalidArgumentException(
                'The document repository "' . $repoClassName . '" should extend "' . Repository::class . '"!'
            );
        }

        return $this->repositories[$documentClassName] = new $repoClassName($this);
    }

    /**
     * @param string $documentClassName
     *
     * @return ClassMetadata
     *
     * @throws \ReflectionException
     */
    public function getClassMetadata($documentClassName)
    {
        return $this->classMetadataLoader->load($documentClassName);
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }
}

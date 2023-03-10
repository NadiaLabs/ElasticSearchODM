<?php

namespace Nadia\ElasticSearchODM\Document;

use Elasticsearch\Client;
use Nadia\ElasticSearchODM\ClassMetadata\ClassMetadata;
use Nadia\ElasticSearchODM\ClassMetadata\ClassMetadataLoader;
use Nadia\ElasticSearchODM\Exception\RepositoryInheritanceInvalidException;
use Nadia\ElasticSearchODM\Exception\RepositoryNotExistsException;
use Psr\Cache\CacheItemPoolInterface;

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
     * @var IndexNameProvider
     */
    protected $indexNameProvider;

    /**
     * @var CacheItemPoolInterface|null
     */
    protected $cache;

    /**
     * @var Repository[]
     */
    protected $repositories = [];

    /**
     * Manager constructor.
     *
     * @param Client $client
     * @param ClassMetadataLoader $classMetadataLoader
     * @param IndexNameProvider $indexNameProvider
     * @param CacheItemPoolInterface|null $cache
     */
    public function __construct(
        $client,
        ClassMetadataLoader $classMetadataLoader,
        IndexNameProvider $indexNameProvider = null,
        CacheItemPoolInterface $cache = null
    ) {
        $this->client = $client;
        $this->classMetadataLoader = $classMetadataLoader;
        $this->indexNameProvider = $indexNameProvider;
        $this->cache = $cache;
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
            throw new RepositoryNotExistsException('The document repository "' . $repoClassName . '" is not exists!');
        }

        if (!is_subclass_of($repoClassName, Repository::class)) {
            throw new RepositoryInheritanceInvalidException(
                'The document repository "' . $repoClassName . '" should extend "' . Repository::class . '"!'
            );
        }

        return $this->repositories[$documentClassName] = new $repoClassName($this);
    }

    /**
     * @param string $documentClassName
     *
     * @return array
     *
     * @throws \ReflectionException
     */
    public function updateIndexTemplate($documentClassName)
    {
        $metadata = $this->getClassMetadata($documentClassName);
        $template = $metadata->template;

        foreach ($template['index_patterns'] as &$indexPattern) {
            $indexPattern = $metadata->indexNamePrefix . $indexPattern;
        }
        if (version_compare(Client::VERSION, '6.0.0', '<')) {
            $template['template'] = join(',', $template['index_patterns']);
            unset($template['index_patterns']);
        }

        $params = [
            'name' => $metadata->templateName,
            'body' => $template,
        ];

        return $this->getClient()->indices()->putTemplate($params);
    }

    /**
     * @param string[] $indexNames Index names without prefix
     *
     * @return string[] Valid index names with prefix
     *
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function getValidIndexNames(array $indexNames)
    {
        return $this->indexNameProvider->getValidIndexNames($indexNames);
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
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
     * @return IndexNameProvider
     */
    public function getIndexNameProvider()
    {
        return $this->indexNameProvider;
    }

    /**
     * @return CacheItemPoolInterface|null
     */
    public function getCache()
    {
        return $this->cache;
    }
}

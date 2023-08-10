<?php

namespace Nadia\ElasticSearchODM\Document;

use Nadia\ElasticSearchODM\ClassMetadata\ClassMetadata;
use Nadia\ElasticSearchODM\Exception\InvalidOrderByOrientationException;
use Nadia\ElasticSearchODM\Helper\ElasticSearchHelper;
use Psr\Cache\InvalidArgumentException;
use ReflectionException;

abstract class Repository
{
    /**
     * @var Manager
     */
    protected $dm;

    /**
     * Repository constructor.
     *
     * @param Manager $dm
     */
    public function __construct(Manager $dm)
    {
        $this->dm = $dm;
    }

    /**
     * @param string[] $indexNames
     * @param string $indexTypeName
     * @param array $criteria
     * @param array $orderBy
     *
     * @return array
     *
     * @throws InvalidArgumentException
     */
    public function findOneBy(array $indexNames, $indexTypeName, array $criteria, array $orderBy = [])
    {
        $result = $this->findBy($indexNames, $indexTypeName, $criteria, $orderBy, 1);

        return isset($result[0]) ? $result[0] : [];
    }

    /**
     * @param string[] $indexNames
     * @param string $indexTypeName
     * @param array $criteria
     * @param array $orderBy
     * @param int $limit
     *
     * @return array
     *
     * @throws InvalidArgumentException
     */
    public function findBy(array $indexNames, $indexTypeName, array $criteria, array $orderBy = [], $limit = 10)
    {
        $indexNames = $this->dm->getValidIndexNames($indexNames);

        if (empty($indexNames)) {
            return [];
        }

        $params = [
            'index' => join(',', $indexNames),
            'type' => $indexTypeName,
            'size' => $limit,
            'body' => [],
        ];

        foreach ($criteria as $columnName => $value) {
            $clause = 'must';

            if (isset($columnName[0]) && '!' === $columnName[0]) {
                $clause = 'must_not';
                $columnName = substr($columnName, 1);
            }
            if (empty($columnName)) {
                continue;
            }

            if (is_array($value)) {
                $params['body']['query']['bool'][$clause][] = ['terms' => [$columnName => $value]];
            } else {
                $params['body']['query']['bool'][$clause][] = ['term' => [$columnName => $value]];
            }
        }

        foreach ($orderBy as $columnName => $orientation) {
            $orientation = strtoupper($orientation);

            if ($orientation != 'ASC' && $orientation != 'DESC') {
                throw new InvalidOrderByOrientationException(
                    'Invalid order by orientation (column: "' . $columnName . '"), only allow "ASC" and "DESC"'
                );
            }

            $params['body']['sort'][$columnName] = ['order' => $orientation];
        }

        $result = $this->dm->getClient()->search($params);
        $result = ElasticSearchHelper::convertResponseToArray($result);

        return (empty($result['hits']['hits'])) ? [] : $result['hits']['hits'];
    }

    /**
     * @param object $document
     *
     * @return array
     *
     * @throws ReflectionException
     */
    public function write($document)
    {
        $metadata = $this->dm->getClassMetadata(get_class($document));
        $ref = $metadata->getReflectionClass();
        $body = [];

        foreach ($metadata->columnsObjectToElastic as $propertyName => $columnName) {
            $property = $ref->getProperty($propertyName);
            $property->setAccessible(true);

            $body[$columnName] = $property->getValue($document);
        }

        $params = [
            'index' => $metadata->getIndexName($document),
            'type' => $metadata->indexTypeName,
            'body' => $body,
        ];

        if ($routing = $metadata->getRouting($document)) {
            $params['routing'] = $routing;
        }

        return ElasticSearchHelper::convertResponseToArray($this->dm->getClient()->index($params));
    }

    /**
     * @param object[] $documents
     *
     * @return array
     *
     * @throws ReflectionException
     */
    public function bulkWrite($documents)
    {
        if (empty($documents)) {
            return [];
        }

        $groupedInfos = [];
        $results = [];

        foreach ($documents as $document) {
            $metadata = $this->dm->getClassMetadata(get_class($document));
            $groupedInfos[$metadata->getIndexName($document)][] = ['document' => $document, 'metadata' => $metadata];
        }

        foreach ($groupedInfos as $indexName => $infos) {
            $body = [];

            foreach ($infos as $info) {
                /** @var ClassMetadata $metadata */
                $metadata = $info['metadata'];
                $ref = $metadata->getReflectionClass();
                $indexParams = ['index' => ['_index' => $indexName, '_type' => $metadata->indexTypeName]];
                $data = [];

                if ($routing = $metadata->getRouting($info['document'])) {
                    $indexParams['index']['_routing'] = $routing;
                }

                foreach ($metadata->columnsObjectToElastic as $propertyName => $columnName) {
                    $property = $ref->getProperty($propertyName);
                    $property->setAccessible(true);

                    $data[$columnName] = $property->getValue($info['document']);
                }

                $body[] = $indexParams;
                $body[] = $data;
            }

            $result = $this->dm->getClient()->bulk([
                'index' => $indexName,
                'type' => $infos[0]['metadata']->indexTypeName,
                'body' => $body,
            ]);
            $result = ElasticSearchHelper::convertResponseToArray($result);

            $results[$indexName] = $result;
        }

        return $results;
    }
}

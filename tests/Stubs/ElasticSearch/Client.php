<?php

namespace Nadia\ElasticSearchODM\Tests\Stubs\ElasticSearch;

use Elastic\Elasticsearch\ClientInterface;
use Elastic\Elasticsearch\Endpoints\Indices;
use Elastic\Transport\Transport;
use Psr\Http\Message\RequestInterface;
use Psr\Log\LoggerInterface;

class Client implements ClientInterface
{
    public function search(): array
    {
        return [];
    }

    public function index(): array
    {
        return [];
    }

    public function bulk(): array
    {
        return [];
    }

    public function indices()
    {
        return null;
    }

    public function getTransport(): Transport
    {
        throw new \RuntimeException('Not implement "getTransport" method!');
    }

    public function getLogger(): LoggerInterface
    {
        throw new \RuntimeException('Not implement "getLogger" method!');
    }

    public function setAsync(bool $async): ClientInterface
    {
        return $this;
    }

    public function getAsync(): bool
    {
        return false;
    }

    public function setElasticMetaHeader(bool $active): ClientInterface
    {
        return $this;
    }

    public function getElasticMetaHeader(): bool
    {
        return true;
    }

    public function setResponseException(bool $active): ClientInterface
    {
        return $this;
    }

    public function getResponseException(): bool
    {
        return true;
    }

    public function sendRequest(RequestInterface $request)
    {
    }
}

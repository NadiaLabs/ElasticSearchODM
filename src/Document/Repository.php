<?php

namespace Nadia\ElasticSearchODM\Document;

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
}

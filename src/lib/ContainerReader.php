<?php

namespace App\Lib;

use Interop\Container\ContainerInterface;

/**
 * Container reader class.
 * This is for simplify the work with DC.
 */
abstract class ContainerReader
{
    /**
     * Container instance.
     *
     * @var Interop\Container\ContainerInterface
     */
    protected $container;

    /**
     * Setting the container object ref.
     *
     * @param Interop\Container\ContainerInterface  $container  Container
     *                                                          instance.
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Magic getter for serve the unset properties from the container.
     *
     * @param  string   $property   The asked property.
     * @return mixed                The property from the container if it's set.
     */
    public function __get($property)
    {
        if ($this->container->{$property}) {
            return $this->container->{$property};
        }
    }

}

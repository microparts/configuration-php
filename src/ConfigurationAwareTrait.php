<?php declare(strict_types=1);

namespace Microparts\Configuration;

/**
 * Basic Implementation of ConfigurationAwareInterface.
 */
trait ConfigurationAwareTrait
{
    /**
     * The Configuration instance.
     *
     * @var \Microparts\Configuration\ConfigurationInterface
     */
    protected $configuration;

    /**
     * Sets a configuration.
     *
     * @param \Microparts\Configuration\ConfigurationInterface $configuration
     */
    public function setConfiguration(ConfigurationInterface $configuration)
    {
        $this->configuration = $configuration;
    }
}


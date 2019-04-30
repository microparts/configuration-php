<?php declare(strict_types=1);

namespace Microparts\Configuration;

/**
 * Describes a configuration-aware instance.
 */
interface ConfigurationAwareInterface
{
    /**
     * Sets a configuration instance on the object.
     *
     * @param \Microparts\Configuration\ConfigurationInterface $configuration
     *
     * @return void
     */
    public function setConfiguration(ConfigurationInterface $configuration);
}


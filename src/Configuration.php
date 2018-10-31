<?php

namespace Tmconsulting;

use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

/**
 * Class Configuration
 * @package NEO
 */
class Configuration implements ConfigurationInterface
{
    /**
     * Name of CONFIG_PATH variable
     */
    private const CONFIG_PATH = 'CONFIG_PATH';
    /**
     * Name of stage ENV variable
     */
    private const STAGE = 'STAGE';

    /**
     * Config tree goes here
     */
    private $config;

    /**
     * Configuration constructor.
     */
    public function __construct()
    {
        $this->initialize();
    }

    /**
     * Get's a value from config by dot notation
     * E.g get('x.y', 'foo') => returns the value of $config['x']['y']
     * And if not exist, return 'foo'
     * @param $key
     * @param null $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        $config = $this->config;

        array_map(function ($key) use (&$config, $default) {
            $config = $config[$key] ?? $default;
        }, explode('.', $key));

        return $config;
    }

    /**
     * Gets all the tree config
     * @return mixed
     */
    public function getAll()
    {
        return $this->config;
    }

    /**
     * Initialize all the magic down here
     */
    protected function initialize()
    {
        $this->config = $this->merge($this->loadDefaults(), $this->loadStage());
    }

    /**
     * @return array
     */
    protected function loadDefaults()
    {
        return $this->parseConfiguration();
    }

    /**
     * Loads stage config
     * @return array
     */
    protected function loadStage()
    {
        return $this->parseConfiguration($this->getStage());
    }

    /**
     * Merges two trees in a correct
     * @param $defaultConfig
     * @param $stageConfig
     * @return array
     */
    protected function merge($defaultConfig, $stageConfig)
    {
        return array_merge($defaultConfig, $stageConfig);
    }

    /**
     * Parses configuration and makes a tree of it
     * @param string $stage
     * @return array
     */
    protected function parseConfiguration($stage = 'defaults')
    {
        $filelist = glob($this->getConfigurationPath() . '/' . $stage . '/*.yaml');
        $config = [];

        foreach ($filelist as $filename) {
            $yamlFileContent = $this->parseYamlFile($filename);

            if (empty($yamlFileContent)) {
                continue;
            }

            $config = $this->merge($config, current($yamlFileContent));
        }

        return $config;
    }

    /**
     * Parses the yaml file
     * @param $path
     * @return array|mixed
     */
    protected function parseYamlFile($path)
    {
        try {
            return Yaml::parseFile($path);
        } catch (ParseException $e) {
            return [];
        }
    }

    /**
     * Get the configuration path
     * @return array|false|string
     */
    protected function getConfigurationPath()
    {
        return $this->getEnvVariable(self::CONFIG_PATH, dirname(__DIR__) . '/configuration');
    }

    /**
     * Takes the stage variable from env
     * @return array|false|string
     */
    protected function getStage()
    {
        return $this->getEnvVariable(self::STAGE, 'defaults');
    }

    /**
     * Takes an env variable and returns default if not exist
     * @param $variable
     * @param $default
     * @return array|false|string
     */
    protected function getEnvVariable($variable, $default)
    {
        return getenv($variable) ?: $default;
    }
}
<?php

namespace Microparts\Configuration\Tests;

use Exception;
use InvalidArgumentException;
use Microparts\Configuration\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

class ConfigurationTest extends TestCase
{
    /**
     * @return array
     */
    public function getTrueMergedConfigurationForTestStage()
    {
        return [
            'hotelbook_params' => [
                'area_mapping' => [
                    'KRK' => 'Krakow',
                    'MSK' => 'Moscow',
                    'CHB' => 'Челябинск',
                ],
                'url'          => 'https://hotelbook.com/xml_endpoint',
                'username'     => 'TESt_USERNAME',
                'password'     => 'PASSWORD',
            ],
            'logging'          => 'info',
            'default_list'     => [
                'bar',
                'baz'
            ],
            'databases'        => [
                'redis' => [
                    'master' => [
                        'username' => 'R_USER',
                        'password' => 'R_PASS',
                    ],
                ],
            ],
        ];
    }

    public function testConfigurationModuleFlowWithDefaultBehavior()
    {
        putenv('CONFIG_PATH=' . __DIR__ . '/configuration');
        putenv('STAGE=test');

        $conf = new Configuration();
        $conf->load();

        $this->followAssertions($conf);
    }

    public function testConfigurationModuleFlowWithPassingPathAndStage()
    {
        $conf = new Configuration(__DIR__ . '/configuration', 'test');
        $conf->load();

        $this->followAssertions($conf);
    }

    public function testDefaultPathAndValue()
    {
        putenv('CONFIG_PATH=');
        putenv('STAGE=');

        $conf = new Configuration();

        $this->assertSame('/app/configuration', $conf->getPath());
        $this->assertSame('defaults', $conf->getStage());

        $conf->setPath('./config');
        $conf->setStage('prod');

        $this->assertSame('./config', $conf->getPath());
        $this->assertSame('prod', $conf->getStage());
    }

    /**
     * @param \Microparts\Configuration\Configuration $conf
     */
    private function followAssertions(Configuration $conf)
    {
        $array = $this->getTrueMergedConfigurationForTestStage();

        $this->assertSame($array, $conf->all());
        $this->assertSame($array['hotelbook_params'], $conf->get('hotelbook_params'));
        $this->assertTrue(isset($conf['hotelbook_params']));
        $this->assertSame($array['hotelbook_params']['area_mapping'], $conf->get('hotelbook_params.area_mapping'));
        $this->assertSame($array['hotelbook_params']['area_mapping'], $conf['hotelbook_params.area_mapping']);
    }

    public function testHowConfigurationMergeArraysWithEmpty()
    {
        $config = [
            'content_security_policy' => [
                'default-src \'self\' cdn.example.com',
                'img-src \'self\' img.example.com'
            ]
        ];

        try {
            $conf = new Configuration(__DIR__ . '/configuration_bug1', 'test');
            $conf->load();
        } catch (Exception $e) {
            // Undefined index 0 when checking is_array($base[$key]).
            $this->assertFalse((bool) $e);
        }

        $this->assertSame($config, $conf->all());
    }

    public function testHowConfigurationMergeArrays()
    {
        $config = [
            'content_security_policy' => [
                'default-src \'self\' cdn.example.com',
                'img-src \'self\' img.example.com'
            ]
        ];

        try {
            $conf = new Configuration(__DIR__ . '/configuration_bug2', 'test');
            $conf->load();
        } catch (Exception $e) {
            $this->assertFalse((bool) $e);
        }

        $this->assertSame($config, $conf->all());
    }

    public function testHowConfigurationCanBeFoundDirectoryAutomatically()
    {
        $this->xcopy(__DIR__ . '/configuration', './configuration');

        $conf = Configuration::auto();
        $conf->load();

        $this->assertSame('defaults', $conf->getStage());
        $this->assertNotEmpty($conf->all());

        $this->deleteDirectory('./configuration');
    }

    public function testHowConfigurationDumpYaml()
    {
        $conf = new Configuration(__DIR__ . '/configuration', 'test');
        $conf->load();

        $expected = PHP_EOL . Yaml::dump($this->getTrueMergedConfigurationForTestStage(), 10, 2);
        $this->assertSame($expected, $conf->dump());
    }

    public function testHowWorksUserMistakePrevention()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageRegExp('/Invalid\! Stage of config directory \[.*\] is not equals top of yaml content \[.*\]\./');

        $conf = new Configuration(__DIR__ . '/configuration_user_mistake1', 'test');
        $conf->load();
    }

    /**
     * Copy a file, or recursively copy a folder and its contents
     *
     * @author      Aidan Lister <aidan@php.net>
     * @version     1.0.1
     * @link        http://aidanlister.com/2004/04/recursively-copying-directories-in-php/
     * @param       string $source Source path
     * @param       string $dest Destination path
     * @param       int $permissions New folder creation permissions
     * @return      bool     Returns true on success, false on failure
     */
    private function xcopy($source, $dest, $permissions = 0755)
    {
        // Check for symlinks
        if (is_link($source)) {
            return symlink(readlink($source), $dest);
        }

        // Simple copy for a file
        if (is_file($source)) {
            return copy($source, $dest);
        }

        // Make destination directory
        if ( ! is_dir($dest)) {
            mkdir($dest, $permissions);
        }

        // Loop through the folder
        $dir = dir($source);
        while (false !== $entry = $dir->read()) {
            // Skip pointers
            if ($entry == '.' || $entry == '..') {
                continue;
            }

            // Deep copy directories
            $this->xcopy("$source/$entry", "$dest/$entry", $permissions);
        }

        // Clean up
        $dir->close();

        return true;
    }

    /**
     * https://stackoverflow.com/questions/1653771/how-do-i-remove-a-directory-that-is-not-empty
     *
     * @param $dir
     * @return bool
     */
    private function deleteDirectory($dir)
    {
        if ( ! file_exists($dir)) {
            return true;
        }

        if ( ! is_dir($dir)) {
            return unlink($dir);
        }

        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }

            if ( ! $this->deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
                return false;
            }

        }

        return rmdir($dir);
    }
}

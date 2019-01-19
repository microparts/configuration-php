<?php

namespace Microparts\Configuration\Tests;

use Exception;
use Microparts\Configuration\Configuration;
use PHPUnit\Framework\TestCase;

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
        $this->assertSame('local', $conf->getStage());

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
}

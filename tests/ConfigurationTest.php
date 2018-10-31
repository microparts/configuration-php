<?php

namespace Tmconsulting\Configuration\Tests;

use Tmconsulting\Configuration;
use PHPUnit\Framework\TestCase;

class ConfigurationTest extends TestCase
{
    private function getDefaultConfigFixture()
    {
        return [
            'key' => 'value',
            'key2' => 'value2',
            'key3' => 'value3',
            'extra' =>
                [
                    'key' => 'value',
                    'key2' => 'value2',
                    'key3' => 'value3',
                ],
        ];
    }

    private function getStageConfigFixture()
    {
        return
            [
                'key4' => 'value4',
                'key5' => 'value5',
                'key6' => 'value6',
                'extra' =>
                    [
                        'key' => 'value_overwrite',
                        'key2' => 'value_overwrite2',
                        'key3' => 'value_overwrite3',
                    ]
            ];
    }

    private function getMergedConfigFixture()
    {
        return [
            'key' => 'value',
            'key2' => 'value2',
            'key3' => 'value3',
            'key4' => 'value4',
            'key5' => 'value5',
            'key6' => 'value6',
            'extra' =>
                [
                    'key' => 'value_overwrite',
                    'key2' => 'value_overwrite2',
                    'key3' => 'value_overwrite3',
                ],
        ];
    }

    public function testConfigurationModuleFlow()
    {
        $mock = $this->getMockBuilder(Configuration::class)
            ->disableOriginalConstructor()
            ->setMethods(['loadStage', 'loadDefaults'])
            ->getMock();

        $mock->expects($this->once())
            ->method('loadDefaults')
            ->willReturn($this->getDefaultConfigFixture());

        $mock->expects($this->once())
            ->method('loadStage')
            ->willReturn($this->getStageConfigFixture());

        $mock->__construct();

        $this->assertEquals($this->getMergedConfigFixture(), $mock->getAll());
    }

    public function testConfigurationModule()
    {
        $configuration = new Configuration();

        $this->assertNotEmpty($configuration->getAll());
        $this->assertNotEmpty($configuration->get('key'));
        $this->assertEmpty($configuration->get('empty_something'));
    }
}

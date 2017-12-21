<?php

declare(strict_types = 1);

namespace KingsonDe\Marshal;

use KingsonDe\Marshal\Data\CollectionCallable;
use KingsonDe\Marshal\Example\Mapper\ArgumentMapper;
use KingsonDe\Marshal\Example\Mapper\ContainerMapper;
use KingsonDe\Marshal\Example\Mapper\ServiceMapper;
use KingsonDe\Marshal\Example\Model\Service;
use PHPUnit\Framework\TestCase;

class MarshalXmlTest extends TestCase {

    public function testSerializeXml() {
        $xml = MarshalXml::serializeItemCallable(function(\stdClass $user) {
            return [
                'root' => [
                    MarshalXml::ATTRIBUTES_KEY => [
                        'year' => 2017,
                    ],
                    'id'        => $user->id,
                    'score'     => [
                        MarshalXml::ATTRIBUTES_KEY => [
                            'public' => true,
                            'highscore' => 'yes',
                        ],
                        MarshalXml::DATA_KEY => $user->score,
                    ],
                    'email'     => $user->email,
                    'null'      => null,
                    'nicknames' => new CollectionCallable(function ($nickname) {
                        return [
                            'nickname' => $nickname,
                        ];
                    }, $user->nicknames),
                ],
            ];
        }, $this->createUser());

        $this->assertXmlStringEqualsXmlFile(__DIR__ . '/Fixtures/User.xml', $xml);
    }

    public function testSerializeRootNodeWithScalarValue() {
        $xml = MarshalXml::serializeItemCallable(function() {
            return [
                'root' => [
                    MarshalXml::ATTRIBUTES_KEY => [
                        'id' => 123,
                    ],
                    MarshalXml::DATA_KEY => 'Hello World!',
                ],
            ];
        });

        $this->assertXmlStringEqualsXmlString(
            '<?xml version="1.0" encoding="UTF-8"?><root id="123">Hello World!</root>',
            $xml
        );
    }

    public function testXmlMapper() {
        $xml = MarshalXml::serializeItem(new ContainerMapper(), ...$this->getServices());

        $this->assertXmlStringEqualsXmlFile(__DIR__ . '/Fixtures/Services.xml', $xml);
    }

    public function testBuildDataStructureIsNull() {
        $xml = MarshalXml::serializeItemCallable(function () {
            return null;
        });

        $this->assertXmlStringEqualsXmlString('<?xml version="1.0" encoding="UTF-8"?><root/>', $xml);
    }

    public function testSettingProlog() {
        MarshalXml::setVersion('1.1');
        MarshalXml::setEncoding('ISO-8859-15');

        $xml = MarshalXml::serializeItemCallable(function () {
            return [
                'root' => [
                    'currency' => '€',
                ]
            ];
        });

        $this->assertXmlStringEqualsXmlFile(__DIR__ . '/Fixtures/Currency.xml', $xml);
    }

    private function createUser() {
        $user            = new \stdClass();
        $user->id        = 123;
        $user->score     = 3.0;
        $user->email     = 'kingson@example.org';
        $user->nicknames = ['pfefferkuchenmann', 'lululu'];

        return $user;
    }

    private function getServices() {
        $argumentMapperService  = new Service(
            'marshal.mapper.argument',
            ArgumentMapper::class
        );
        $serviceMapperService   = new Service(
            'marshal.mapper.service',
            ServiceMapper::class,
            $argumentMapperService
        );
        $containerMapperService = new Service(
            'marshal.mapper.container',
            ContainerMapper::class,
            $argumentMapperService,
            $serviceMapperService
        );

        return [$containerMapperService, $serviceMapperService, $argumentMapperService];
    }
}

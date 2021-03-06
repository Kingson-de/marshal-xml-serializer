<?php

declare(strict_types = 1);

namespace KingsonDe\Marshal;

use KingsonDe\Marshal\Data\Collection;
use KingsonDe\Marshal\Data\CollectionCallable;
use KingsonDe\Marshal\Data\FlexibleData;
use KingsonDe\Marshal\Example\Mapper\ArgumentMapper;
use KingsonDe\Marshal\Example\Mapper\ContainerMapper;
use KingsonDe\Marshal\Example\Mapper\ServiceMapper;
use KingsonDe\Marshal\Example\Model\Service;
use KingsonDe\Marshal\Example\ObjectMapper\AcmeExampleIdMapper;
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
                    'email'     => [MarshalXml::CDATA_KEY => $user->email],
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

    /**
     * @expectedException \KingsonDe\Marshal\Exception\XmlSerializeException
     */
    public function testBuildDataStructureIsNull() {
        MarshalXml::serializeItemCallable(function () {
            return null;
        });
    }

    /**
     * @expectedException \KingsonDe\Marshal\Exception\XmlSerializeException
     */
    public function testSerializationFailed() {
        MarshalXml::serializeItemCallable(function () {
            return [
                'malformedXml' => [
                    '@node' => 'some value',
                ],
            ];
        });
    }

    /**
     * @expectedException \KingsonDe\Marshal\Exception\XmlSerializeException
     */
    public function testSerializeWithCollection() {
        $collection = new Collection(new ArgumentMapper(), [new Service('marshal.mapper.dummy')]);

        MarshalXml::serialize($collection);
    }

    /**
     * @expectedException \KingsonDe\Marshal\Exception\XmlSerializeException
     */
    public function testCollectionAtRootLevel() {
        MarshalXml::serializeCollection(new ArgumentMapper(), [new Service('marshal.mapper.dummy')]);
    }

    /**
     * @expectedException \KingsonDe\Marshal\Exception\XmlSerializeException
     */
    public function testCollectionCallableAtRootLevel() {
        MarshalXml::serializeCollectionCallable(function (Service $service) {
            return [
                'id' => $service->getId(),
            ];
        }, [new Service('marshal.mapper.dummy')]);
    }

    public function testSerializeRootNodeWithCDataSection() {
        $xml = MarshalXml::serializeItemCallable(function() {
            return [
                'root' => [
                    MarshalXml::CDATA_KEY => 'Hello World!',
                ],
            ];
        });

        $this->assertXmlStringEqualsXmlString(
            '<?xml version="1.0" encoding="UTF-8"?><root><![CDATA[Hello World!]]></root>',
            $xml
        );
    }

    public function testDeserializeMapperGeneratedXml() {
        $xml = MarshalXml::serializeItem(new ContainerMapper(), ...$this->getServices());

        $flexibleData = new FlexibleData(MarshalXml::deserializeXmlToData($xml));

        $newXml = MarshalXml::serialize($flexibleData);

        $this->assertXmlStringEqualsXmlString($xml, $newXml);
    }

    public function testDeserializeXmlFile() {
        $xml = file_get_contents(__DIR__ . '/Fixtures/Breakfast.xml');

        $flexibleData = new FlexibleData(MarshalXml::deserializeXmlToData($xml));

        $newXml = MarshalXml::serialize($flexibleData);

        $this->assertXmlStringEqualsXmlString($xml, $newXml);
    }

    public function testDeserializeToString() {
        $xml = file_get_contents(__DIR__ . '/Fixtures/Services.xml');

        $id = MarshalXml::deserializeXml($xml, new AcmeExampleIdMapper());

        $this->assertSame('$bi*"h\'g7?kj*ee', $id);
    }

    public function testDeserializeWithCallable() {
        $xml = file_get_contents(__DIR__ . '/Fixtures/Services.xml');

        $id = MarshalXml::deserializeXmlCallable($xml, function (FlexibleData $flexibleData) {
            return $flexibleData['container']['acme-example:config']['acme-example:id'][MarshalXml::CDATA_KEY];
        });

        $this->assertSame('$bi*"h\'g7?kj*ee', $id);
    }

    /**
     * @expectedException \KingsonDe\Marshal\Exception\XmlDeserializeException
     */
    public function testDeserializeInvalidXml() {
        MarshalXml::deserializeXmlToData('<@brokenXml>nothing</yolo>');
    }

    public function testModifyExistingXml() {
        $xml = file_get_contents(__DIR__ . '/Fixtures/Breakfast.xml');

        $flexibleData = new FlexibleData(MarshalXml::deserializeXmlToData($xml));

        $waffles = $flexibleData['breakfast_menu'][1];
        unset($flexibleData['breakfast_menu']);
        $flexibleData['breakfast_menu'] = $waffles;
        $flexibleData['breakfast_menu']['food']['price'] = '$8.15';

        $newXml = MarshalXml::serialize($flexibleData);

        $expectedXml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<breakfast_menu>
    <food>
        <name>Strawberry Belgian Waffles</name>
        <price>$8.15</price>
        <description>Light Belgian waffles covered with strawberries and whipped cream</description>
        <calories>900</calories>
    </food>
</breakfast_menu>
XML;

        $this->assertXmlStringEqualsXmlString($expectedXml, $newXml);
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

<?php

declare(strict_types = 1);

namespace KingsonDe\Marshal\Example\Mapper;

use KingsonDe\Marshal\AbstractXmlMapper;
use KingsonDe\Marshal\Example\Model\Service;

class ContainerMapper extends AbstractXmlMapper {

    public function map(Service ...$services) {
        return [
            'container' => [
                $this->attributes() => [
                    'xmlns'              => 'http://symfony.com/schema/dic/services',
                    'xmlns:xsi'          => 'http://www.w3.org/2001/XMLSchema-instance',
                    'xmlns:acme-example' => 'http://example.org/schema/dic/acme_example',
                ],
                $this->item(new BundleConfigMapper()),
                'parameters' => $this->collection(new ParameterMapper(), [
                    ['key' => 'key1', 'param' => 'param1'],
                    ['key' => 'key2', 'param' => 'param2'],
                ]),
                'services'   => $this->collection(new ServiceMapper(), $services),
            ],
        ];
    }
}

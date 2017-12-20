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
                ],
                'services' => $this->collection(new ServiceMapper(), $services),
            ],
        ];
    }
}

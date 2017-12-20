<?php

declare(strict_types = 1);

namespace KingsonDe\Marshal\Example\Mapper;

use KingsonDe\Marshal\AbstractXmlMapper;
use KingsonDe\Marshal\Example\Model\Service;

class ArgumentMapper extends AbstractXmlMapper {

    public function map(Service $service) {
        return [
            'argument' => [
                $this->attributes() => [
                    'type' => 'service',
                    'id'   => $service->getId(),
                ],
            ]
        ];
    }
}

<?php

declare(strict_types = 1);

namespace KingsonDe\Marshal\Example\Mapper;

use KingsonDe\Marshal\AbstractXmlMapper;
use KingsonDe\Marshal\Example\Model\Service;

class ServiceMapper extends AbstractXmlMapper {

    public function map(Service $service) {
        return [
            'service' => [
                $this->attributes() => [
                    'id' => $service->getId(),
                    'class' => $service->getClass(),
                ],
                $this->data() => $this->collection(new ArgumentMapper(), $service->getArguments()),
            ],
        ];
    }
}

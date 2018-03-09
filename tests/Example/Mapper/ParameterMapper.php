<?php

declare(strict_types = 1);

namespace KingsonDe\Marshal\Example\Mapper;

use KingsonDe\Marshal\AbstractXmlMapper;

class ParameterMapper extends AbstractXmlMapper {

    public function map(array $parameterData) {
        return [
            'parameter' => [
                $this->attributes() => [
                    'key' => $parameterData['key'],
                ],
                $this->cdata() => $parameterData['param'],
            ]
        ];
    }
}

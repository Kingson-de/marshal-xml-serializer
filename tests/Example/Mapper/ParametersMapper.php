<?php

declare(strict_types = 1);

namespace KingsonDe\Marshal\Example\Mapper;

use KingsonDe\Marshal\AbstractXmlMapper;

class ParametersMapper extends AbstractXmlMapper {

    public function map() {
        return [
            'parameters' => [
                'parameter' => $this->cdata('some parameter'),
            ],
        ];
    }
}

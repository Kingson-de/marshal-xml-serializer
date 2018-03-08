<?php

declare(strict_types = 1);

namespace KingsonDe\Marshal\Example\Mapper;

use KingsonDe\Marshal\AbstractXmlMapper;

class ParameterMapper extends AbstractXmlMapper {

    public function map(string $parameter) {
        return [
            'parameter' => $this->cdata($parameter),
        ];
    }
}

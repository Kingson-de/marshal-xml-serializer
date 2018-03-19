<?php

declare(strict_types = 1);

namespace KingsonDe\Marshal\Example\Mapper;

use KingsonDe\Marshal\AbstractXmlMapper;

class BundleConfigMapper extends AbstractXmlMapper {

    public function map() {
        return [
            'acme-example:config' => [
                'acme-example:id' => $this->cdata('$bi*"h\'g7?Kj*EE'),
            ]
        ];
    }
}

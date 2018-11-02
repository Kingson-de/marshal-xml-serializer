<?php

declare(strict_types = 1);

namespace KingsonDe\Marshal\Example\ObjectMapper;

use KingsonDe\Marshal\AbstractObjectMapper;
use KingsonDe\Marshal\Data\FlexibleData;
use KingsonDe\Marshal\MarshalXml;

class AcmeExampleIdMapper extends AbstractObjectMapper {

    public function map(FlexibleData $flexibleData, ...$additionalData) {
        return $flexibleData
            ->get('container')
            ->get('acme-example:config')
            ->get('acme-example:id')
            ->get(MarshalXml::CDATA_KEY);
    }
}

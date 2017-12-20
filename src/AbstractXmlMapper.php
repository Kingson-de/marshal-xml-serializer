<?php

declare(strict_types = 1);

namespace KingsonDe\Marshal;

class AbstractXmlMapper extends AbstractMapper {

    protected function attributes(): string {
        return MarshalXml::ATTRIBUTES_KEY;
    }

    protected function data(): string {
        return MarshalXml::DATA_KEY;
    }
}

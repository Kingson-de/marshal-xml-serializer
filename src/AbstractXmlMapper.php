<?php

declare(strict_types = 1);

namespace KingsonDe\Marshal;

/**
 * Concrete Mapper classes MUST implement a "map" function that must return an array or null.
 * The reason of not having an abstract function for the "map" function is type hinting.
 */
class AbstractXmlMapper extends AbstractMapper {

    protected function attributes(): string {
        return MarshalXml::ATTRIBUTES_KEY;
    }

    protected function data(): string {
        return MarshalXml::DATA_KEY;
    }
}

<?php

declare(strict_types = 1);

namespace KingsonDe\Marshal;

class XmlNodeParser {

    /**
     * @param string|int $name
     * @param mixed $data
     * @return XmlNode
     */
    public static function parseNode($name, $data): XmlNode {
        $node = new XmlNode($name);

        if (isset($data[MarshalXml::ATTRIBUTES_KEY])) {
            $node->setAttributes($data[MarshalXml::ATTRIBUTES_KEY]);
            unset($data[MarshalXml::ATTRIBUTES_KEY]);
        }

        if (\is_scalar($data)) {
            $node->setNodeValue($data);
        } elseif (isset($data[MarshalXml::DATA_KEY])) {
            $node->setNodeValue($data[MarshalXml::DATA_KEY]);
        } elseif (isset($data[MarshalXml::CDATA_KEY])) {
            $node->setNodeValue($data[MarshalXml::CDATA_KEY]);
            $node->setIsCData(true);
        }

        if (\is_array($data) && false === $node->hasNodeValue()) {
            $node->setChildrenNodes($data);
        }

        return $node;
    }
}

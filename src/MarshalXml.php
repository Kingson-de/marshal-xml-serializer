<?php

declare(strict_types = 1);

namespace KingsonDe\Marshal;

use KingsonDe\Marshal\Data\DataStructure;
use KingsonDe\Marshal\Exception\XmlSerializeException;

/**
 * @method static string serializeItem(AbstractMapper $mapper, ...$data)
 * @method static string serializeItemCallable(callable $mappingFunction, ...$data)
 * @method static string serializeCollection(AbstractMapper $mapper, ...$data)
 * @method static string serializeCollectionCallable(callable $mappingFunction, ...$data)
 */
class MarshalXml extends Marshal {

    const ATTRIBUTES_KEY = '@attributes';
    const DATA_KEY       = '@data';

    /**
     * @var string
     */
    protected static $version = '1.0';

    /**
     * @var string
     */
    protected static $encoding = 'UTF-8';

    public static function setVersion(string $version) {
        static::$version = $version;
    }

    public static function setEncoding(string $encoding) {
        static::$encoding = $encoding;
    }

    /**
     * @param DataStructure $dataStructure
     * @return string
     * @throws \KingsonDe\Marshal\Exception\XmlSerializeException
     */
    public static function serialize(DataStructure $dataStructure) {
        $data = static::buildDataStructure($dataStructure);

        try {
            $xml  = new \DOMDocument(static::$version, static::$encoding);

            if (null === $data) {
                $xmlRootNode = $xml->createElement('root');
                $xml->appendChild($xmlRootNode);

                return $xml->saveXML();
            }

            \reset($data);
            $rootNode = \key($data);

            if (isset($data[$rootNode][static::DATA_KEY])) {
                $xmlRootNode = $xml->createElement($rootNode, static::castValueToString($data[$rootNode][static::DATA_KEY]));
                unset($data[$rootNode][static::DATA_KEY]);
            } else {
                $xmlRootNode = $xml->createElement($rootNode);
            }

            if (isset($data[$rootNode][static::ATTRIBUTES_KEY])) {
                static::addAttributes($data[$rootNode][static::ATTRIBUTES_KEY], $xmlRootNode);
                unset($data[$rootNode][static::ATTRIBUTES_KEY]);
            }
            $xml->appendChild($xmlRootNode);

            static::processNodes($data[$rootNode], $xmlRootNode);

            return $xml->saveXML();
        } catch (\Exception $e) {
            throw new XmlSerializeException($e->getMessage(), $e->getCode(), $e);
        }

    }

    protected static function processNodes(array $nodes, \DOMElement $parentXmlNode) {
        foreach ($nodes as $node => $value) {
            $attributes = [];

            if (isset($value[static::ATTRIBUTES_KEY])) {
                $attributes = $value[static::ATTRIBUTES_KEY];
                unset($value[static::ATTRIBUTES_KEY]);
            }

            if (isset($value[static::DATA_KEY])) {
                $value = $value[static::DATA_KEY];
            }

            // new node with scalar value
            if (\is_scalar($value)) {
                $xmlNode = $parentXmlNode->ownerDocument->createElement($node, static::castValueToString($value));
                static::addAttributes($attributes, $xmlNode);
                $parentXmlNode->appendChild($xmlNode);
                continue;
            }

            // node collection of the same type
            if (\is_int($node)) {
                static::processNodes($value, $parentXmlNode);
                continue;
            }

            // new node that might contain other nodes
            $xmlNode = $parentXmlNode->ownerDocument->createElement($node);
            static::addAttributes($attributes, $xmlNode);
            $parentXmlNode->appendChild($xmlNode);
            if (\is_array($value)) {
                static::processNodes($value, $xmlNode);
            }
        }
    }

    protected static function addAttributes(array $attributes, \DOMElement $xmlNode) {
        foreach ($attributes as $name => $value) {
            $xmlNode->setAttribute($name, static::castValueToString($value));
        }
    }

    protected static function castValueToString($value): string {
        return (\is_string($value) ? $value : var_export($value, true));
    }
}

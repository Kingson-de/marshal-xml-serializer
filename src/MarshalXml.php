<?php

declare(strict_types = 1);

namespace KingsonDe\Marshal;

use KingsonDe\Marshal\Data\DataStructure;

/**
 * @method static string serializeItem(AbstractMapper $mapper, ...$data)
 * @method static string serializeItemCallable(callable $mappingFunction, ...$data)
 * @method static string serializeCollection(AbstractMapper $mapper, ...$data)
 * @method static string serializeCollectionCallable(callable $mappingFunction, ...$data)
 */
class MarshalXml extends Marshal {

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
     */
    public static function serialize(DataStructure $dataStructure) {
        $data = static::buildDataStructure($dataStructure);
        $xml  = new \DOMDocument(static::$version, static::$encoding);

        if (null === $data) {
            $xmlRootNode = $xml->createElement('root');
            $xml->appendChild($xmlRootNode);

            return $xml->saveXML();
        }

        \reset($data);
        $rootNode = \key($data);

        $xmlRootNode = $xml->createElement($rootNode);
        $xml->appendChild($xmlRootNode);

        static::processNodes($data[$rootNode], $xmlRootNode);

        return $xml->saveXML();
    }

    protected static function processNodes($nodes, \DOMElement $parentXmlNode) {
        foreach ($nodes as $node => $value) {
            // new node with scalar value
            if (\is_scalar($value)) {
                $xmlNode = $parentXmlNode->ownerDocument->createElement($node, (string)$value);
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
            $parentXmlNode->appendChild($xmlNode);
            if (\is_array($value)) {
                static::processNodes($value, $xmlNode);
            }
        }
    }
}

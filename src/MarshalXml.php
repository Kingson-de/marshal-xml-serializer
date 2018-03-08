<?php

declare(strict_types = 1);

namespace KingsonDe\Marshal;

use KingsonDe\Marshal\Data\Collection;
use KingsonDe\Marshal\Data\CollectionCallable;
use KingsonDe\Marshal\Data\DataStructure;
use KingsonDe\Marshal\Exception\XmlSerializeException;

/**
 * @method static string serializeItem(AbstractMapper $mapper, ...$data)
 * @method static string serializeItemCallable(callable $mappingFunction, ...$data)
 */
class MarshalXml extends Marshal {

    const ATTRIBUTES_KEY = '@attributes';
    const DATA_KEY       = '@data';
    const CDATA_KEY      = '@cdata';

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
        if ($dataStructure instanceof Collection || $dataStructure instanceof CollectionCallable) {
            throw new XmlSerializeException('Collections in XML cannot be generated at root level.');
        }

        $data = static::buildDataStructure($dataStructure);

        if (null === $data) {
            throw new XmlSerializeException('No data structure.');
        }

        try {
            $xml = new \DOMDocument(static::$version, static::$encoding);

            static::processNodes($data, $xml);

            return $xml->saveXML();
        } catch (\Exception $e) {
            throw new XmlSerializeException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public static function serializeCollection(AbstractMapper $mapper, ...$data) {
        throw new XmlSerializeException('Collections in XML cannot be generated at root level.');
    }

    public static function serializeCollectionCallable(callable $mappingFunction, ...$data) {
        throw new XmlSerializeException('Collections in XML cannot be generated at root level.');
    }

    /**
     * @param array $nodes
     * @param \DOMElement|\DOMDocument $parentXmlNode
     */
    protected static function processNodes(array $nodes, $parentXmlNode) {
        $dom = $parentXmlNode->ownerDocument ?? $parentXmlNode;

        foreach ($nodes as $name => $data) {
            $node = XmlNodeParser::parseNode($name, $data);

            // new node with scalar value
            if ($node->hasNodeValue()) {
                if ($node->isCData()) {
                    $xmlNode      = $dom->createElement($node->getName());
                    $cdataSection = $dom->createCDATASection($node->getNodeValue());
                    $xmlNode->appendChild($cdataSection);
                } else {
                    $xmlNode = $dom->createElement($node->getName(), $node->getNodeValue());
                }
                static::addAttributes($node, $xmlNode);
                $parentXmlNode->appendChild($xmlNode);
                continue;
            }

            // node collection of the same type
            if ($node->isCollection()) {
                static::processNodes($node->getChildrenNodes(), $parentXmlNode);
                continue;
            }

            // new node that might contain other nodes
            $xmlNode = $dom->createElement($node->getName());
            static::addAttributes($node, $xmlNode);
            $parentXmlNode->appendChild($xmlNode);
            if ($node->hasChildrenNodes()) {
                static::processNodes($node->getChildrenNodes(), $xmlNode);
            }
        }
    }

    protected static function addAttributes(XmlNode $node, \DOMElement $xmlNode) {
        foreach ($node->getAttributes() as $name => $value) {
            $xmlNode->setAttribute($name, $value);
        }
    }
}

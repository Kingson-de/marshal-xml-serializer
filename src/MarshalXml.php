<?php

declare(strict_types = 1);

namespace KingsonDe\Marshal;

use KingsonDe\Marshal\Data\Collection;
use KingsonDe\Marshal\Data\CollectionCallable;
use KingsonDe\Marshal\Data\DataStructure;
use KingsonDe\Marshal\Data\FlexibleData;
use KingsonDe\Marshal\Exception\XmlDeserializeException;
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

    /**
     * @param string $xml
     * @return array
     * @throws \KingsonDe\Marshal\Exception\XmlDeserializeException
     */
    public static function deserializeXmlToData(string $xml): array {
        try {
            $dom = new \DOMDocument();
            $dom->loadXML($xml);
            $data = [];

            static::deserializeNodes($dom, $data);

            // get namespaces
            $xpath = new \DOMXPath($dom);
            foreach ($xpath->query('namespace::*') as $namespace) {
                if ($namespace->nodeName !== 'xmlns:xml') {
                    $data[$dom->firstChild->nodeName][static::ATTRIBUTES_KEY][$namespace->nodeName]
                        = $namespace->nodeValue;
                }
            }
        } catch (\Exception $e) {
            throw new XmlDeserializeException($e->getMessage(), $e->getCode(), $e);
        }

        return $data;
    }

    /**
     * @param \DOMElement|\DOMDocument $parentXmlNode
     * @param array $data
     */
    protected static function deserializeNodes($parentXmlNode, array &$data) {
        $collectionNodeNames = [];

        foreach ($parentXmlNode->childNodes as $node) {
            if ($node instanceof \DOMText) {
                $data[static::DATA_KEY] = $node->textContent;
            }

            if ($node instanceof \DOMCdataSection) {
                $data[static::CDATA_KEY] = $node->data;
            }

            if ($node instanceof \DOMElement) {
                $value = [];

                if ($node->hasAttributes()) {
                    foreach ($node->attributes as $attribute) {
                        $value[static::ATTRIBUTES_KEY][$attribute->name] = $attribute->value;
                    }
                }

                // move node to collection if it exists twice
                if (isset($data[$node->nodeName])) {
                    $previousValue = $data[$node->nodeName];
                    unset($data[$node->nodeName]);

                    $data[][$node->nodeName]              = $previousValue;
                    $collectionNodeNames[$node->nodeName] = true;

                }

                if (isset($collectionNodeNames[$node->nodeName])) {
                    $data[][$node->nodeName] = $value;

                    if ($node->hasChildNodes()) {
                        \end($data);
                        static::deserializeNodes($node, $data[\key($data)][$node->nodeName]);
                    }
                } else {
                    $data[$node->nodeName] = $value;

                    if ($node->hasChildNodes()) {
                        static::deserializeNodes($node, $data[$node->nodeName]);
                    }
                }
            }
        }

        if (isset($data[static::DATA_KEY], $data[static::ATTRIBUTES_KEY]) && \count($data) === 2) {
            return;
        }

        if (isset($data[static::DATA_KEY])) {
            if (\count($data) === 1) {
                $data = $data[static::DATA_KEY];
            } else {
                unset($data[static::DATA_KEY]);
            }
        }
    }

    /**
     * @param string $xml
     * @param AbstractObjectMapper $mapper
     * @param mixed[] $additionalData
     * @return mixed
     */
    public static function deserializeXml(
        string $xml,
        AbstractObjectMapper $mapper,
        ...$additionalData
    ) {
        return $mapper->map(
            new FlexibleData(static::deserializeXmlToData($xml)),
            ...$additionalData
        );
    }

    /**
     * @param string $xml
     * @param callable $mappingFunction
     * @param mixed[] $additionalData
     * @return mixed
     */
    public static function deserializeXmlCallable(
        string $xml,
        callable $mappingFunction,
        ...$additionalData
    ) {
        return $mappingFunction(
            new FlexibleData(static::deserializeXmlToData($xml)),
            ...$additionalData
        );
    }
}

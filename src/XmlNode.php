<?php

declare(strict_types = 1);

namespace KingsonDe\Marshal;

class XmlNode {

    /**
     * @var string|int
     */
    private $name;

    /**
     * @var string[]
     */
    private $attributes = [];

    /**
     * @var mixed
     */
    private $nodeValue;

    /**
     * @var bool
     */
    private $cData = false;

    /**
     * @var array|null
     */
    private $childrenNodes;

    /**
     * @var bool
     */
    private $isCollection;

    /**
     * @param string|int $name
     */
    public function __construct($name) {
        $this->name         = $name;
        $this->isCollection = \is_int($name);
    }

    /**
     * @return int|string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @return string[]
     */
    public function getAttributes(): array {
        return $this->attributes;
    }

    /**
     * @param array $attributes
     */
    public function setAttributes(array $attributes) {
        $this->attributes = array_map([$this, 'castValueToString'], $attributes);
    }

    public function hasNodeValue(): bool {
        return (null !== $this->nodeValue);
    }

    /**
     * @return mixed
     */
    public function getNodeValue() {
        return $this->nodeValue;
    }

    /**
     * @param mixed $nodeValue
     */
    public function setNodeValue($nodeValue) {
        $this->nodeValue = $this->castValueToString($nodeValue);
    }

    public function isCData(): bool {
        return $this->cData;
    }

    public function setIsCData(bool $cDataValue) {
        $this->cData = $cDataValue;
    }

    /**
     * @return array|null
     */
    public function getChildrenNodes() {
        return $this->childrenNodes;
    }

    public function setChildrenNodes(array $childrenNodes) {
        $this->childrenNodes = $childrenNodes;
    }

    public function hasChildrenNodes(): bool {
        return \is_array($this->childrenNodes);
    }

    public function isCollection(): bool {
        return $this->isCollection;
    }

    /**
     * @param mixed $value
     * @return string
     */
    private function castValueToString($value): string {
        return (\is_string($value) ? $value : var_export($value, true));
    }
}

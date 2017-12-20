<?php

declare(strict_types = 1);

namespace KingsonDe\Marshal\Example\Model;

class Service {

    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $class;

    /**
     * @var Service[]
     */
    private $arguments;

    public function __construct(string $id, string $class = '', Service ...$arguments) {
        $this->id        = $id;
        $this->class     = $class;
        $this->arguments = $arguments;
    }

    public function getId(): string {
        return $this->id;
    }

    public function getClass(): string {
        return $this->class;
    }

    /**
     * @return Service[]
     */
    public function getArguments(): array {
        return $this->arguments;
    }
}

# Marshal XML Serializer

![Marshal Serializer logo](https://raw.githubusercontent.com/Kingson-de/marshal-serializer/master/marshal.png "Marshal Serializer")

[![License](https://img.shields.io/badge/License-Apache%202.0-blue.svg)](https://github.com/Kingson-de/marshal-xml-serializer/blob/master/LICENSE)
[![Build Status](https://travis-ci.org/Kingson-de/marshal-xml-serializer.svg?branch=master)](https://travis-ci.org/Kingson-de/marshal-xml-serializer)
[![Code Coverage](https://scrutinizer-ci.com/g/Kingson-de/marshal-xml-serializer/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/Kingson-de/marshal-xml-serializer/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Kingson-de/marshal-xml-serializer/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Kingson-de/marshal-xml-serializer/?branch=master)

## Introduction

Marshal is [serializing](https://en.wikipedia.org/wiki/Serialization) / [marshalling](https://en.wikipedia.org/wiki/Marshalling_(computer_science)) data structures to a format that can be used to build messages for transferring data through the wires.

Marshal XML Serializer will directly serialize the data to XML, it is built on top of the [Marshal Serializer](https://github.com/Kingson-de/marshal-serializer).

## Installation

Easiest way to install the library is via composer:
```
composer require kingson-de/marshal-xml-serializer
```

The following PHP versions are supported:
* PHP 7.0
* PHP 7.1
* PHP 7.2

## Execute tests
Just run:
```
composer test
```

Or without code coverage:
```
composer quicktest
```

## Usage

### How to create Data Structures which can be serialized?

Please check the [Marshal Serializer README](https://github.com/Kingson-de/marshal-serializer/blob/master/README.md) for more information.

### How to use the Marshal XML Serializer library?

The library provides several static methods to create your XML data once you defined the data structures.

More detailed description will be added soon.

Check the `tests/Example` folder for now. 

## License

This project is released under the terms of the [Apache 2.0 license](https://github.com/Kingson-de/marshal-xml-serializer/blob/master/LICENSE).

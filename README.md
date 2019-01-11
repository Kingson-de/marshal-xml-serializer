# Marshal XML Serializer

![Marshal Serializer logo](https://raw.githubusercontent.com/Kingson-de/marshal-serializer/master/marshal.png "Marshal Serializer")

[![License](https://img.shields.io/badge/License-Apache%202.0-blue.svg)](https://github.com/Kingson-de/marshal-xml-serializer/blob/master/LICENSE)
[![Build Status](https://travis-ci.org/Kingson-de/marshal-xml-serializer.svg?branch=master)](https://travis-ci.org/Kingson-de/marshal-xml-serializer)
[![Code Coverage](https://scrutinizer-ci.com/g/Kingson-de/marshal-xml-serializer/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/Kingson-de/marshal-xml-serializer/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Kingson-de/marshal-xml-serializer/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Kingson-de/marshal-xml-serializer/?branch=master)

## Introduction

Marshal XML is [serializing](https://en.wikipedia.org/wiki/Serialization) / [marshalling](https://en.wikipedia.org/wiki/Marshalling_(computer_science)) data structures to XML. It is also deserializing / unmarshalling XML back to the data structures.

## Installation

Easiest way to install the library is via composer:
```
composer require kingson-de/marshal-xml-serializer
```

The following PHP versions are supported:
* PHP 7.0
* PHP 7.1
* PHP 7.2
* PHP 7.3

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

```php
<?php

use KingsonDe\Marshal\Data\Item;
use KingsonDe\Marshal\MarshalXml;

$xml = MarshalXml::serialize(new Item($mapper, $model));
// or
$xml = MarshalXml::serializeItem($mapper, $model);
// or
$xml = MarshalXml::serializeItemCallable(function (User $user) {
    return [
        'root' => [
            'username'  => $user->getUsername(),
            'email'     => $user->getEmail(),
            'birthday'  => $user->getBirthday()->format('Y-m-d'),
            'followers' => count($user->getFollowers()),
        ],
    ];
}, $user);
```

Be aware `MarshalXml::serializeCollection` and `MarshalXml::serializeCollectionCallable` methods are not available.
Collections in XML cannot be generated at root level.
But after defining the root node you can use collections anywhere.

#### How to define XML attributes?

If you are using a concrete implementation that is extending AbstractXmlMapper you can use the `attributes` method.

```php
<?php

use KingsonDe\Marshal\AbstractXmlMapper;

class RootMapper extends AbstractXmlMapper {
    
    public function map(){
        return [
            'root' => [
                $this->attributes() => [
                    'xmlns' => 'http://example.org/xml',
                ],
            ],
        ];
    }
}
```

If you are using a callable you need to use the `MarshalXml::ATTRIBUTES_KEY` constant.

```php
<?php

use KingsonDe\Marshal\MarshalXml;

$xml = MarshalXml::serializeItemCallable(function () {
    return [
        'root' => [
            MarshalXml::ATTRIBUTES_KEY => [
                'xmlns' => 'http://example.org/xml',
            ],
        ],
    ];
});
```

This will generate:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<root xmlns="http://example.org/xml"/>
```

#### How to define XML node values?

This is pretty simple:

```php
<?php

use KingsonDe\Marshal\MarshalXml;

$xml = MarshalXml::serializeItemCallable(function (User $user) {
    return [
        'root' => [
            'user' => $user->getUsername(),
        ],
    ];
}, $user);
```

This will generate:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<root>
  <user>Kingson</user>
</root>
```

#### How to define XML node values as CDATA?

Then you must use the `cdata` method for concrete Mapper implementations or `MarshalXml::CDATA_KEY` for callable.

```php
<?php

use KingsonDe\Marshal\AbstractXmlMapper;

class UserMapper extends AbstractXmlMapper {
    
    public function map(User $user){
        return [
            'root' => [
                'user' => $this->cdata($user->getUsername()),
            ],
        ];
    }
}
```

```php
<?php

use KingsonDe\Marshal\MarshalXml;

$xml = MarshalXml::serializeItemCallable(function (User $user) {
    return [
        'root' => [
            'user' => [
                MarshalXml::CDATA_KEY => $user->getUsername(),
            ],
        ],
    ];
}, $user);
```

This will generate:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<root>
  <user><![CDATA[Kingson]]></user>
</root>
```

#### But how to define XML node values if the node also has attributes?

Then you must use the `data` \/ `cdata` method for concrete Mapper implementations or `MarshalXml::DATA_KEY` \/ `MarshalXml::CDATA_KEY` for callable.

```php
<?php

use KingsonDe\Marshal\AbstractXmlMapper;

class UserMapper extends AbstractXmlMapper {
    
    public function map(User $user){
        return [
            'root' => [
                'user' => [
                    $this->attributes() => [
                        'xmlns' => 'http://example.org/xml',
                    ],
                    $this->data() => $user->getUsername(),
                ],
                'userCDATA' => [
                    $this->attributes() => [
                        'xmlns' => 'http://example.org/xml',
                    ],
                    $this->cdata() => $user->getUsername(),
                ],
            ],
        ];
    }
}
```

```php
<?php

use KingsonDe\Marshal\MarshalXml;

$xml = MarshalXml::serializeItemCallable(function (User $user) {
    return [
        'root' => [
            'user' => [
                MarshalXml::ATTRIBUTES_KEY => [
                    'xmlns' => 'http://example.org/xml',
                ],
                MarshalXml::DATA_KEY => $user->getUsername(),
            ],
            'userCDATA' => [
                MarshalXml::ATTRIBUTES_KEY => [
                    'xmlns' => 'http://example.org/xml',
                ],
                MarshalXml::CDATA_KEY => $user->getUsername(),
            ],
        ],
    ];
}, $user);
```

This will generate:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<root>
  <user xmlns="http://example.org/xml">Kingson</user>
  <userCDATA xmlns="http://example.org/xml"><![CDATA[Kingson]]></userCDATA>
</root>
```

### Deserializing / Unmarshalling

To transform XML back to your structure use Marshal's deserialize functions.
You need a class extending the AbstractObjectMapper which will be passed to the deserializeXml function.

```php
<?php

use KingsonDe\Marshal\AbstractObjectMapper;
use KingsonDe\Marshal\Data\FlexibleData;
use KingsonDe\Marshal\MarshalXml;

class AcmeExampleIdMapper extends AbstractObjectMapper {

    public function map(FlexibleData $flexibleData, ...$additionalData) {
        return $flexibleData
            ->get('container')
            ->get('acme-example:config')
            ->get('acme-example:id')
            ->get(MarshalXml::CDATA_KEY);
    }
}
```

```php
<?php

use KingsonDe\Marshal\MarshalXml;

$id = MarshalXml::deserializeXml($xml, new AcmeExampleIdMapper());
```

Another option would be to use the deserializeCallable function.

```php
<?php

use KingsonDe\Marshal\MarshalXml;

$id = MarshalXml::deserializeXmlCallable($xml, function (FlexibleData $flexibleData) {
    return $flexibleData['container']['acme-example:config']['acme-example:id'][MarshalXml::CDATA_KEY];
});
```

### Modify existing XML

An easy way to modify existing XML is to use FlexibleData.
Here is an example:

```php
<?php

use KingsonDe\Marshal\Data\FlexibleData;
use KingsonDe\Marshal\MarshalXml;

$xml = '<?xml version="1.0" encoding="UTF-8"?><root><tag>Hello World!</tag></root>';

$flexibleData = new FlexibleData(MarshalXml::deserializeXmlToData($xml));

$flexibleData['root']['tag'] = 'some other text';

$modifiedXml = MarshalXml::serialize($flexibleData);
```

## License

This project is released under the terms of the [Apache 2.0 license](https://github.com/Kingson-de/marshal-xml-serializer/blob/master/LICENSE).

<?php

declare(strict_types = 1);

namespace KingsonDe\Marshal;

use KingsonDe\Marshal\Data\CollectionCallable;
use PHPUnit\Framework\TestCase;

class MarshalXmlTest extends TestCase {

    public function testSerializeXml() {
        $xml = MarshalXml::serializeItemCallable(function(\stdClass $user) {
            return [
                'root' => [
                    'id'        => $user->id,
                    'score'     => $user->score,
                    'email'     => $user->email,
                    'null'      => null,
                    'followers' => new CollectionCallable(function ($username) {
                        return [
                            'username' => $username,
                        ];
                    }, $user->followers),
                ],
            ];
        }, $this->createUser());

        $this->assertXmlStringEqualsXmlFile(__DIR__ . '/Fixtures/User.xml', $xml);
    }

    private function createUser() {
        $user            = new \stdClass();
        $user->id        = 123;
        $user->score     = 3.4;
        $user->email     = 'kingson@example.org';
        $user->followers = ['pfefferkuchenmann', 'lululu'];

        return $user;
    }
}

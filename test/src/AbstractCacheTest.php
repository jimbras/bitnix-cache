<?php declare(strict_types=1);

/**
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as
 *  published by the Free Software Foundation, either version 3 of the
 *  License, or (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program. If not, see <https://www.gnu.org/licenses/agpl-3.0.txt>.
 */

namespace Bitnix\Cache;

use RuntimeException,
    PHPUnit\Framework\TestCase;

/**
 * @version 0.1.0
 */
class AbstractCacheTest extends TestCase {

    private Cache $cache;

    public function setUp() : void {
        $this->cache = $this->getMockBuilder(AbstractCache::CLASS)
            ->getMockForAbstractClass();
    }

    public function testLoadReturnsStoredCacheValue() {
        $this->cache
            ->expects($this->once())
            ->method('fetch')
            ->with('foo')
            ->will($this->returnValue('bar'));

        $this->assertEquals(
            'bar',
            $this->cache->load('foo', function() {
                throw new RuntimeException('Unexpected call');
            })
        );
    }

    public function testLoadInvokesLoaderAndStoresValueInTheCache() {
        $cache = [];
        $this->cache
            ->expects($this->any())
            ->method('fetch')
            ->will($this->returnCallback(function($key) use (&$cache) {
                return $cache[$key] ?? null;
            }));
        $this->cache
            ->expects($this->any())
            ->method('store')
            ->will($this->returnCallback(function($key, $value) use (&$cache) {
                $cache[$key] = $value;
            }));

        $this->assertNull($this->cache->fetch('foo'));
        $this->assertEquals(
            'bar',
            $this->cache->load('foo', fn() => 'bar')
        );
        $this->assertEquals('bar', $this->cache->fetch('foo'));
    }

    public function testToString() {
        $this->assertIsString((string) $this->cache);
    }
}

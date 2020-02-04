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

use Memcached,
    PHPUnit\Framework\TestCase;

/**
 * @version 0.1.0
 */
class MemcachedTest extends TestCase {

    private Memcached $store;
    private Cache $cache;

    public function setUp() : void {
        $this->store = new Memcached();
        $this->store->addServer('127.0.0.1', 11211);
        $this->store->setOption(Memcached::OPT_BINARY_PROTOCOL, true);
        $this->cache = new MemcachedCache($this->store, 'test');
    }

    public function tearDown() : void {
        $this->store->flush();
        $this->store->quit();
    }

    public function testFetchReturnsNullOnCacheMiss() {
        $this->assertNull($this->cache->fetch('foo'));
    }

    public function testFetchReturnsStoredItem() {
        $this->cache->store('foo', 'bar');
        $this->assertEquals('bar', $this->cache->fetch('foo'));
    }

    public function testFetchReturnsNullForExpiredCachedItems() {
        $this->cache->store('foo', 'bar', Cache::ONE_HOUR);
        $this->assertEquals('bar', $this->cache->fetch('foo'));
        $this->cache->touch('foo', Cache::ONE_WEEK);
        $this->assertEquals('bar', $this->cache->fetch('foo'));
        $this->cache->touch('foo', -Cache::ONE_HOUR);
        $this->assertNull($this->cache->fetch('foo'));
    }

    public function testPurgeRemovesSelectedItemsFromTheCache() {
        $this->cache->store('foo', 'bar');
        $this->cache->store('zig', 'zag');
        $this->cache->purge('foo');
        $this->assertNull($this->cache->fetch('foo'));
        $this->assertEquals('zag', $this->cache->fetch('zig'));
    }

    public function testPurgeRemovesAllItemsFromTheCache() {
        $this->cache->store('foo', 'bar');
        $this->cache->store('zig', 'zag');
        $this->cache->purge();
        $this->assertNull($this->cache->fetch('foo'));
        $this->assertNull($this->cache->fetch('zig'));
    }
}

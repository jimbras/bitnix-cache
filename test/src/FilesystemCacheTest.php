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

use PHPUnit\Framework\TestCase;

/**
 * @version 0.1.0
 */
class FilesystemCacheTest extends TestCase {

    private const ROOT = __DIR__ . '/_filesystem/_cache';

    private Cache $cache;

    public function setUp() : void {
        $this->cache = new FilesystemCache(self::ROOT);
    }

    public function tearDown() : void {
        foreach (\glob(self::ROOT . '/*') as $file) {
            \unlink($file);
        }

        foreach ([self::ROOT, \dirname(self::ROOT)] as $dir) {
            if (\is_dir($dir)) {
                \rmdir($dir);
            }
        }
    }

    public function testFetchReturnsNullIfItemIsNotCached() {
        $this->assertNull($this->cache->fetch('foo'));
    }

    public function testFetchReturnsCachedItem() {
        $this->cache->store('foo', 'bar');
        $this->assertEquals('bar', $this->cache->fetch('foo'));
    }

    public function testFetchReturnsCachedItemWithCorrectType() {
        $this->cache->store('foo', 1);
        $this->assertSame(1, $this->cache->fetch('foo'));
    }

    public function testFetchReturnsNullForExpiredCachedItems() {
        $this->cache->store('foo', 'bar');
        $this->cache->touch('foo', -Cache::ONE_HOUR);
        $this->assertNull($this->cache->fetch('foo'));
    }

    public function testDeleteRemovesSelectedItemsFromTheCache() {
        $this->cache->store('foo', 'bar');
        $this->cache->store('zig', 'zag');
        $this->cache->delete('foo');
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

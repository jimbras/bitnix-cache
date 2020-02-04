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

use Memcached;

/**
 * @version 0.1.0
 */
final class MemcachedCache extends AbstractCache {

    /**
     * @var Memcached
     */
    private Memcached $store;

    /**
     * @var string
     */
    private string $prefix;

    /**
     * @param Memcached $store
     * @param string $prefix
     */
    public function __construct(Memcached $store, string $prefix) {
        $this->store = $store;
        $this->prefix = $prefix . ':';
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function fetch(string $key) {
        $item = $this->store->get($this->prefix . $key);
        if (Memcached::RES_SUCCESS === $this->store->getResultCode()) {
            return $item;
        }
        return null;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param int $ttl
     */
    public function store(string $key, $value, int $ttl = self::ONE_HOUR) : void {
        $this->store->set($this->prefix . $key, $value, $ttl);
    }

    /**
     * @param string $key
     * @param int $ttl
     */
    public function touch(string $key, int $ttl = self::ONE_HOUR) : void {
        $key = $this->prefix . $key;

        if ($ttl < 0) {
            $this->store->delete($key);
        } else {
            $this->store->touch($key, $ttl);
        }
    }

    /**
     * @param string ...$keys
     */
    public function delete(string ...$keys) : void {
        foreach ($keys as $key) {
            $this->store->delete($this->prefix . $key);
        }
    }

    /**
     * ...
     */
    public function purge() : void {
        // Memcached::getAllKeys() is not reliable, just clear everything
        $this->store->flush();
    }

}

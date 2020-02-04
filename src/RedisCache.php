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

use Redis;

/**
 * @version 0.1.0
 */
final class RedisCache extends AbstractCache {

    /**
     * @var Redis
     */
    private Redis $store;

    /**
     * @var string
     */
    private string $prefix;

    /**
     * @param Redis $redis
     * @param string $prefix
     */
    public function __construct(Redis $redis, string $prefix) {

        if (Redis::SERIALIZER_NONE === $redis->getOption(Redis::OPT_SERIALIZER)) {
            $redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);
        }

        $this->store = $redis;
        $this->prefix = $prefix . ':';
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function fetch(string $key) {
        $key = $this->prefix . $key;

        if (false !== ($value = $this->store->get($key))) {
            return $value;
        }

        return $this->store->exists($key) ? false : null;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param int $ttl
     */
    public function store(string $key, $value, int $ttl = self::ONE_HOUR) : void {
        $key = $this->prefix . $key;
        $this->store->set($key, $value);
        if ($ttl) {
            $this->store->expire($key, $ttl);
        }
    }

    /**
     * @param string $key
     * @param int $ttl
     */
    public function touch(string $key, int $ttl = self::ONE_HOUR) : void {
        $key = $this->prefix . $key;
        if ($this->store->exists($key)) {
            $this->store->expire($key, $ttl);
        }
    }

    /**
     * @param string ...$keys
     */
    public function purge(string ...$keys) : void {
        if ($keys) {
            foreach ($keys as $key) {
                $this->store->del($this->prefix . $key);
            }
        } else {
            foreach ($this->store->keys($this->prefix . '*') as $key) {
                $this->store->del($key);
            }
        }
    }
}

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

/**
 * @version 0.1.0
 */
final class ApcuCache extends AbstractCache {

    /**
     * @var string
     */
    private string $prefix;

    /**
     * @param string $prefix
     */
    public function __construct(string $prefix) {
        $this->prefix = $prefix . ':';
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function fetch(string $key) {
        $item = \apcu_fetch($this->prefix . $key, $ok);
        return $ok ? $item : null;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param int $ttl
     */
    public function store(string $key, $value, int $ttl = self::ONE_HOUR) : void {
        \apcu_store($this->prefix . $key, $value, $ttl);
    }

    /**
     * @param string $key
     * @param int $ttl
     */
    public function touch(string $key, int $ttl = self::ONE_HOUR) : void {
        $key = $this->prefix . $key;
        if (\apcu_exists($key)) {
            \apcu_store($key, \apcu_fetch($key), $ttl);
        }
    }

    /**
     * @param string ...$keys
     */
    public function purge(string ...$keys) : void {
        if ($keys) {
            foreach ($keys as $key) {
                \apcu_delete($this->prefix . $key);
            }
        } else {
            $items = \apcu_cache_info()['cache_list'];

            foreach ($items as $item) {
                $key = $item['info'];
                if (0 === \strpos($key, $this->prefix)) {
                    \apcu_delete($key);
                }
            }
        }
    }
}

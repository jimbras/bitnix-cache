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
final class RuntimeCache extends AbstractCache {

    /**
     * @var array
     */
    private array $cache = [];

    /**
     * @param string $key
     * @return mixed
     */
    public function fetch(string $key) {
        if (isset($this->cache[$key])) {
            list($ttl, $value) = $this->cache[$key];
            if (!$ttl || \time() < $ttl) {
                return $value;
            }
            unset($this->key);
        }
        return null;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param int $ttl
     */
    public function store(string $key, $value, int $ttl = self::ONE_HOUR) : void {
        if ($ttl) {
            $ttl += \time();
        }
        $this->cache[$key] = [$ttl, $value];
    }

    /**
     * @param string $key
     * @param int $ttl
     */
    public function touch(string $key, int $ttl = self::ONE_HOUR) : void {
        if (isset($this->cache[$key])) {
            if ($ttl) {
                $ttl += \time();
            }
            $this->cache[$key][0] = $ttl;
        }
    }

    /**
     * @param string ...$keys
     */
    public function purge(string ...$keys) : void {
        if ($keys) {
            foreach ($keys as $key) {
                if (isset($this->cache[$key])) {
                    unset($this->cache[$key]);
                }
            }
        } else {
            $this->cache = [];
        }
    }
}

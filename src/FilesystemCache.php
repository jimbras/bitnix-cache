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
final class FilesystemCache extends AbstractCache {

    /**
     * @var string
     */
    private string $root;

    /**
     * @param string $root
     */
    public function __construct(string $root) {
        $this->root = \str_replace(['\\', '/'], \DIRECTORY_SEPARATOR, $root);
    }

    /**
     * @param string $key
     * @return string
     */
    private function file(string $key) : string {
        return $this->root
              . \DIRECTORY_SEPARATOR
              . \md5($key)
              . '.cache';
    }

    /**
     * @param string $file
     * @return null|string
     */
    private function read(string $file) : ?string {
        if (\is_file($file) && \is_readable($file)) {
            return \file_get_contents($file);
        }
        return null;
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function fetch(string $key) {
        $file = $this->file($key);
        if ($data = $this->read($file)) {
            list ($expire, $flag, $content) = \explode('|', $data, 3);
            if (0 == $expire || \time() <= $expire) {
                return $flag ? \unserialize($content) : $content;
            }
            // expired
            \unlink($file);
        }
        return null;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param int $ttl
     */
    public function store(string $key, $value, int $ttl = self::ONE_HOUR) : void {
        $flag = 0;
        $file = $this->file($key);
        $dir = \dirname($file);

        if (!\is_dir($dir) && !\mkdir($dir, 0755, true)) {
            return;
        }

        if ($ttl) {
            $ttl += \time();
        }

        if (!\is_string($value)) {
            $flag = 1;
            $value = \serialize($value);
        }

        \file_put_contents($file, \sprintf('%s|%s|%s', $ttl, $flag, $value), LOCK_EX);
    }

    /**
     * @param string $key
     * @param int $ttl
     */
    public function touch(string $key, int $ttl = self::ONE_HOUR) : void {
        $file = $this->file($key);
        if ($data = $this->read($file)) {
            list ($expire, $flag, $content) = \explode('|', $data, 3);
            if ($ttl) {
                $ttl += \time();
            }
            \file_put_contents($file, \sprintf('%s|%s|%s', $ttl, $flag, $content), LOCK_EX);
        }
    }

    /**
     * @param string ...$keys
     */
    public function delete(string ...$keys) : void {
        foreach ($keys as $key) {
            if (\is_file($file = $this->file($key))) {
                \unlink($file);
            }
        }
    }

    /**
     * ...
     */
    public function purge() : void {
        foreach (\glob($this->root . \DIRECTORY_SEPARATOR . '*.cache') as $file) {
            \unlink($file);
        }
    }

}

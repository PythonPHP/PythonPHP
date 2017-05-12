<?php
/**
 * PythonPHP - Ports of various python standard library functionality to PHP
 * Copyright (C) 2017 Matthew Gamble <pythonphp@matthewgamble.net>
 *
 * This project is free software: you can redistribute it and/or modify it under
 * the terms of the GNU General Public License Version 3 as published by the Free
 * Software Foundation. No other version currently applies to this project. This
 * software is distributed without any warranty. Please see LICENSE.txt for the
 * full text of the license.
 */

declare(strict_types=1);

namespace PythonPHP\Path;

use InvalidArgumentException;

class PosixPath
{
    use GenericPath;

    /**
     * @param string $path
     * @return string
     */
    public function abspath(string $path): string
    {
        if ($this->isabs($path) === false) {
            $path = $this->join($this->getCwd(), $path);
        }
        return $this->normpath($path);
    }

    /**
     * This is split into its own function for testing purposes.
     *
     * @return string
     */
    protected function getCwd(): string
    {
        return getcwd();
    }

    /**
     * @param string $path
     * @return bool
     */
    public function isabs(string $path): bool
    {
        return strpos($path, "/") === 0;
    }

    /**
     * @param string $path
     * @param string[] $paths
     * @return string
     */
    public function join(string $path, ...$paths): string
    {
        $result = $path;
        foreach ($paths as $segment) {
            if (strpos($segment, "/") === 0) {
                $result = $segment;
            } elseif ((!$result) || strrpos($result, "/") === strlen($result) - 1) {
                $result .= $segment;
            } else {
                $result .= "/" . $segment;
            }
        }
        return $result;
    }

    /**
     * @param string $path
     * @return string
     */
    public function normpath(string $path): string
    {
        if ($path === "") {
            return ".";
        }
        $initialSlashes = (int) (strpos($path, "/") === 0);
        // POSIX allows one or two initial slashes, but treats three or more as a single slash
        if ($initialSlashes && strpos($path, "//") === 0 && (!(strpos($path, "///") === 0))) {
            $initialSlashes = 2;
        }

        $segments = explode("/", $path);
        $newSegments = array();
        foreach ($segments as $segment) {
            if ($segment === "" || $segment === ".") {
                continue;
            }
            if (
                $segment !== ".." ||
                ((!$initialSlashes) && (!$newSegments)) ||
                ($newSegments && array_slice($newSegments, -1, 1)[0] === "..")
            ) {
                $newSegments[] = $segment;
            } elseif ($newSegments) {
                array_pop($newSegments);
            }
        }

        $newPath = implode("/", $newSegments);
        if ($initialSlashes) {
            $newPath = str_repeat("/", $initialSlashes) . $newPath;
        }
        return $newPath;
    }

    /**
     * @param string $path
     * @param string|null $start
     * @return string
     */
    public function relpath(string $path, $start = null): string
    {
        if (!$path) {
            throw new InvalidArgumentException("No path specified");
        }
        if ($start === null) {
            $start = ".";
        }
        $startList = array_values(array_filter(explode("/", $this->abspath($start))));
        $pathList = array_values(array_filter(explode("/", $this->abspath($path))));

        $i = count($this->commonprefix(array($startList, $pathList)));

        $relList = array_merge(
            array_fill(0, (count($startList) - $i), ".."),
            array_slice($pathList, $i)
        );
        if (!$relList) {
            return ".";
        }

        return $this->join(...$relList);
    }
}

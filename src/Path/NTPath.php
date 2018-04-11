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
use function PythonPHP\zip;
use RuntimeException;

class NTPath
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
        $path = $this->splitdrive($path)[1];
        return strlen($path) > 0 && in_array($path[0], array("\\", "/"));
    }

    /**
     * @param string $path
     * @param string[] $paths
     * @return string
     */
    public function join(string $path, ...$paths): string
    {
        list($resultDrive, $resultPath) = $this->splitdrive($path);
        foreach ($paths as $segment) {
            list($segmentDrive, $segmentPath) = $this->splitdrive($segment);
            if ($segmentPath && in_array($segmentPath[0], array("\\", "/"))) {
                if ($segmentDrive || (!$resultDrive)) {
                    $resultDrive = $segmentDrive;
                }

                $resultPath = $segmentPath;
                continue;
            } elseif ($segmentDrive && $segmentDrive !== $resultDrive) {
                if (strtolower($segmentDrive) !== strtolower($resultDrive)) {
                    $resultDrive = $segmentDrive;
                    $resultPath = $segmentPath;
                    continue;
                }

                $resultDrive = $segmentDrive;
            }

            if ($resultPath && in_array(substr($resultPath, -1, 1), array("\\", "/")) === false) {
                $resultPath .= "\\";
            }

            $resultPath .= $segmentPath;
        }

        if ($resultPath && in_array($resultPath[0], array("\\", "/")) === false && $resultDrive && substr($resultDrive, -1, 1) !== ":") {
            return $resultDrive . "\\" . $resultPath;
        }

        return $resultDrive . $resultPath;
    }

    /**
     * @param string $path
     * @return string
     */
    public function normcase(string $path): string
    {
        return strtolower(str_replace("/", "\\", $path));
    }

    /**
     * @param string $path
     * @return string
     */
    public function normpath(string $path): string
    {
        $startsWith = function(string $path, $prefixes) {
            foreach ((array) $prefixes as $prefix) {
                if (strpos($path, $prefix) === 0) {
                    return true;
                }
            }
            return false;
        };
        if ($startsWith($path, array("\\\\.\\", "\\\\?\\"))) {
            return $path;
        }

        $path = str_replace("/", "\\", $path);
        list($prefix, $path) = $this->splitdrive($path);

        if (strpos($path, "\\") === 0) {
            $prefix .= "\\";
            $path = ltrim($path, "\\");
        }

        $unset = function(&$array, $offset) { unset($array[$offset]); $array = array_values($array); };
        $segments = explode("\\", $path);
        $i = 0;
        while ($i < count($segments)) {
            if ((!$segments[$i]) || $segments[$i] === ".") {
                $unset($segments, $i);
            } elseif ($segments[$i] === "..") {
                if ($i > 0 && $segments[$i - 1] !== "..") {
                    $unset($segments, $i - 1);
                    $unset($segments, $i - 1);
                    $i -= 1;
                } elseif ($i === 0 && strrpos($prefix, "\\") === strlen($prefix) - 1) {
                    $unset($segments, $i);
                } else {
                    $i += 1;
                }
            } else {
                $i += 1;
            }
        }

        if ((!$prefix) && (!$segments)) {
            $segments[] = ".";
        }

        return $prefix . implode("\\", $segments);
    }

    /**
     * @param string $path
     * @param string|null $start
     * @return string
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function relpath(string $path, $start = null): string
    {
        if (!$path) {
            throw new InvalidArgumentException("No path specified");
        }

        if ($start === null) {
            $start = ".";
        }

        $startAbs = $this->abspath($this->normpath($start));
        $pathAbs = $this->abspath($this->normpath($path));
        list($startDrive, $startRest) = $this->splitdrive($startAbs);
        list($pathDrive, $pathRest) = $this->splitdrive($pathAbs);

        if ($this->normcase($startDrive) !== $this->normcase($pathDrive)) {
            throw new RuntimeException(sprintf('path is on mount %1$s, start on mount %2$s', $pathDrive, $startDrive));
        }

        $startList = array_values(array_filter(explode("\\", $startRest)));
        $pathList = array_values(array_filter(explode("\\", $pathRest)));

        $i = 0;
        foreach (zip($startList, $pathList) as $zip) {
            list($e1, $e2) = $zip;
            if ($this->normcase($e1) !== $this->normcase($e2)) {
                break;
            }
            $i += 1;
        }

        $relList = array_merge(
            array_fill(0, (count($startList) - $i), ".."),
            array_slice($pathList, $i)
        );
        if (!$relList) {
            return ".";
        }

        return $this->join(...$relList);
    }

    /**
     * @param string $path
     * @return array
     */
    public function splitdrive(string $path): array
    {
        if (strlen($path) >= 2) {
            $normalisedPath = str_replace("/", "\\", $path);
            if (substr($normalisedPath, 0, 2) === "\\\\" && substr($normalisedPath, 2, 1) !== "\\") {
                $index = strpos($normalisedPath, "\\", 2);
                if ($index === false) {
                    return array("", $path);
                }

                $index2 = strpos($normalisedPath, "\\", $index + 1);
                if ($index2 === $index + 1) {
                    return array("", $path);
                }

                if ($index2 === false) {
                    $index2 = strlen($path);
                }

                return array(substr($path, 0, $index2), substr($path, $index2));
            }

            if (substr($normalisedPath, 1, 1) === ":") {
                return array(substr($path, 0, 2), substr($path, 2));
            }
        }

        return array("", $path);
    }
}

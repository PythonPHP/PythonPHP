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

class Path
{
    /**
     * @var PosixPath|NTPath
     */
    private $internal;

    public function __construct()
    {
        if (DIRECTORY_SEPARATOR === "\\") {
            $this->internal = new NTPath();
        } else {
            $this->internal = new PosixPath();
        }
    }

    /**
     * @param string $path
     * @return string
     */
    public function abspath(string $path): string
    {
        return $this->internal->abspath($path);
    }

    /**
     * @param string $path
     */
    public function isabs(string $path): bool
    {
        return $this->internal->isabs($path);
    }

    /**
     * @param string $path
     * @param string[] $paths
     * @return string
     */
    public function join(string $path, ...$paths): string
    {
        return $this->internal->join($path, ...$paths);
    }

    /**
     * @param string $path
     * @return string
     */
    public function normpath(string $path): string
    {
        return $this->internal->normpath($path);
    }

    /**
     * @param string $path
     * @param string|null $start
     * @return string
     */
    public function relpath(string $path, $start = null): string
    {
        return $this->internal->relpath($path, $start);
    }
}

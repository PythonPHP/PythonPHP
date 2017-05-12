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

namespace PythonPHP;

/**
 * This class exists so that you can inject it into other classes, and then
 * mock the dependency for testing purposes.
 */
class PyFunctions
{
    /**
     * An implementation of Python's enumerate() function in PHP.
     *
     * @param iterable|string $sequence
     * @param int $start
     * @return Generator
     * @yields mixed
     */
    public function enumerate($sequence, int $start = 0): Generator
    {
        return enumerate($sequence, $start);
    }

    /**
     * An implementation of Python's zip() function in PHP.
     *
     * @param array $iterables
     * @return Generator
     * @yields array
     */
    public function zip(...$iterables): Generator
    {
        return zip(...$iterables);
    }
}

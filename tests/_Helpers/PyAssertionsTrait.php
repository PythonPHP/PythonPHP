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

namespace PythonPHP\Tests\_Helpers;

trait PyAssertionsTrait
{
    /**
     * Asserts that a string starts with a given prefix.
     * Uses a custom constraint class that allows for the prefix to be an empty string,
     * which is valid when calling str.startswith() in python.
     *
     * @param string $prefix
     * @param string $string
     * @param string $message
     */
    public static function assertPythonStringStartsWith($prefix, $string, $message = '')
    {
        if (!is_string($prefix)) {
            throw InvalidArgumentHelper::factory(1, 'string');
        }

        if (!is_string($string)) {
            throw InvalidArgumentHelper::factory(2, 'string');
        }

        $constraint = new PythonStringStartsWithConstraint($prefix);

        static::assertThat($string, $constraint, $message);
    }
}

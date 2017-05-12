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

namespace PythonPHP\Tests;

use ArrayIterator;
use Generator;
use function PythonPHP\zip;
use PHPUnit\Framework\TestCase;

class PyFunctionsTest extends TestCase
{
    /**
     * @dataProvider zipProvider
     */
    public function testZip(array $expected, ...$iterables)
    {
        $generator = zip(...$iterables);
        $this->assertInstanceOf(Generator::class, $generator);
        $this->assertSame($expected, iterator_to_array($generator));
    }

    public function zipProvider()
    {
        // expected, ...iterables
        return array(
            array([[1, "a"], [2, "b"], [3, "c"]], [1, 2, 3], ["a", "b", "c"]),
            array([[1, "a"], [2, "b"], [3, "c"]], new ArrayIterator([1, 2, 3]), ["a", "b", "c"]),
            array([[1, "a"], [2, "b"], [3, "c"]], [1, 2, 3], new ArrayIterator(["a", "b", "c"])),
            array([[1, "a"], [2, "b"]], [1, 2, 3], ["a", "b"]),
            array([], [], ["a", "b", "c"]),
            array([], [1, 2, 3], []),
            array([[1, "a", ["x"]], [2, "b", ["y"]], [3, "c", ["z"]]], [1, 2, 3], ["a", "b", "c"], [["x"], ["y"], ["z"]]),
            array([["a", "x"], ["b", "y"], ["c", "z"]], "abc", "xyz"),
            array([["a", 1], ["b", 2], ["c", 3]], "abc", [1, 2, 3]),
            array([], "", "abcdef"),
        );
    }
}

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
use PHPUnit\Framework\TestCase;

use function PythonPHP\enumerate;
use function PythonPHP\zip;

class PyFunctionsTest extends TestCase
{
    /**
     * @dataProvider enumerateProvider
     * @param array $expected
     * @param iterable|string $iterable
     * @param int $start
     */
    public function testEnumerate(array $expected, $iterable, int $start = 0)
    {
        $generator = enumerate($iterable, $start);
        $this->assertInstanceOf(Generator::class, $generator);
        $this->assertSame($expected, iterator_to_array($generator));
    }

    public function enumerateProvider()
    {
        // expected, iterable
        return [
            [["a", "b", "c"], "abc"],
            [[5 => "a", 6 => "b", 7 => "c"], "abc", 5],
            [[6 => "x", 7 => "y", 8 => "z"], ["x", "y", "z"], 6],
            [[1 => 2, 2 => 4, 3 => 6, 4 => 8, 5 => 10], range(2, 10, 2), 1],
            [[7 => "Z", 8 => "o", 9 => "ë"], "Zoë", 7],
            [[4 => "Z", 5 => "o", 6 => "ë"], [1 => "Z", 2 => "o", 3 => "ë"], 4],
        ];
    }

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
            array([["Z", "Z"], ["o", "o"], ["ë", "é"]], "Zoë", "Zoé"),
        );
    }
}

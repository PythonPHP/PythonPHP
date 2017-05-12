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

use Generator;
use Iterator;

/**
 * An implementation of Python's enumerate() function in PHP.
 *
 * @param iterable|string $sequence
 * @param int $start
 * @return Generator
 * @yields mixed
 */
function enumerate($sequence, int $start = 0): Generator
{
    if (!$sequence) {
        return;
    }
    // PHP strings aren't iterable like they are in python, so we have
    // to split them up into character arrays first.
    if (is_string($sequence) === true) {
        $sequence = str_split($sequence);
    }
    $n = $start;
    foreach ($sequence as $elem) {
        yield $n => $elem;
        $n++;
    }
}

/**
 * An implementation of Python's zip() function in PHP.
 *
 * @param array $iterables
 * @return Generator
 * @yields array
 */
function zip(...$iterables): Generator
{
    $arrays = array_map(function($iterable) {
        if (is_string($iterable)) {
            // PHP strings aren't iterable like they are in python, so we have
            // to split the up into character arrays first.
            if (strlen($iterable)) {
                return str_split($iterable);
            } else {
                // If you pass an empty string to str_split(), it will return
                // an array with one element - an empty string.
                return array();
            }
        } elseif ($iterable instanceof Iterator) {
            return iterator_to_array($iterable);
        } else {
            return $iterable;
        }
    }, $iterables);

    $iterCount = min(...array_map("count", $arrays));
    for ($x = 0; $x < $iterCount; $x++) {
        $result = array_column($arrays, $x);
        yield $result;
    }
}

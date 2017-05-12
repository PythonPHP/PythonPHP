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

use function PythonPHP\enumerate;

trait GenericPath
{
    /**
     * Yes, the python documentation suggests that this should be able to handle arrays
     * of length other than 2. No, the code doesn't do that. No, I don't know why.
     *
     * @param array $paths Each of the two elements can be a string, or an array of strings.
     * @return array|string
     */
    public function commonprefix(array $paths)
    {
        if (!$paths) {
            return "";
        }
        $s1 = min($paths);
        $s2 = max($paths);

        foreach (enumerate($s1) as $i => $c) {
            if ($c !== $s2[$i]) {
                if (is_array($s1)) {
                    return array_slice($s1, 0, $i);
                } else {
                    return substr($s1, 0, $i);
                }
            }
        }
        return $s1;
    }
}

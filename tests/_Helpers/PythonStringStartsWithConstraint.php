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

use PHPUnit\Framework\Constraint\StringStartsWith;

class PythonStringStartsWithConstraint extends StringStartsWith
{
    /**
     * var string
     */
    private $prefix;

    /**
     * @param string $prefix
     */
    public function __construct(string $prefix)
    {
        parent::__construct($prefix);

        $this->prefix = $prefix;
    }

    /**
     * Evaluates the constraint for parameter $other. Returns true if the
     * constraint is met, false otherwise.
     * Overridden to allow an empty string as the prefix, which is allowed
     * when calling str.startswith() in python.
     *
     * @param mixed $other Value or object to evaluate.
     * @return bool
     */
    protected function matches($other): bool
    {
        if (strlen($this->prefix) === 0) {
            return true;
        }

        return parent::matches($other);
    }
}

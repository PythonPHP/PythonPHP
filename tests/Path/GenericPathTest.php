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

namespace PythonPHP\Tests\Path;

use PythonPHP\Path\GenericPath;
use PythonPHP\Tests\_Helpers\PyAssertionsTrait;
use PHPUnit\Framework\TestCase;

class GenericPathTest extends TestCase
{
    use PyAssertionsTrait;

    /**
     * @var GenericPath
     */
    protected $genericPath;

    protected function setUp()
    {
        parent::setUp();
        $this->genericPath = $this->getMockForTrait(GenericPath::class);
    }

    public function testCommonPrefix()
    {
        $cases = array(
            array(
                "paths" => array(),
                "expected" => "",
            ),
            array(
                "paths" => array("/home/matthew/abc", "/home/matt/abc"),
                "expected" => "/home/matt",
            ),
            array(
                "paths" => array("/home/matthew/abc", "/home/matthew/def"),
                "expected" => "/home/matthew/",
            ),
            array(
                "paths" => array("/home/matthew/ghi", "/home/matthew/ghi"),
                "expected" => "/home/matthew/ghi",
            ),
            array(
                "paths" => array("home:matthew:ghi", "home:matt:ghi"),
                "expected" => "home:matt",
            ),
            array(
                "paths" => array("home:matthew:ghi", "home:matthew:jkl"),
                "expected" => "home:matthew:",
            ),
            array(
                "paths" => array("home:matthew:jkl", "home:matthew:jkl"),
                "expected" => "home:matthew:jkl",
            ),
            array(
                "paths" => array("\\home\\matthew\\abc", "\\home\\matthew\\def"),
                "expected" => "\\home\\matthew\\",
            ),
            array(
                "paths" => array("\\home\\matthew", "\\home\\matthew\\xyz"),
                "expected" => "\\home\\matthew",
            ),
            array(
                "paths" => array(
                    array("home", "matthew"),
                    array("usr", "bin"),
                ),
                "expected" => array(),
            ),
            array(
                "paths" => array(
                    array("home", "matthew"),
                    array("home", "matthew", "test"),
                ),
                "expected" => array("home", "matthew"),
            ),
        );
        foreach ($cases as $case) {
            $this->assertSame($case["expected"], $this->genericPath->commonprefix($case["paths"]));
        }
    }

    public function testCommonPrefix2()
    {
        $testList = array("", "abc", "Xbcd", "Xb", "XY", "abcd", "aXc", "abd", "ab", "aX", "abcX");
        foreach ($testList as $string1) {
            foreach ($testList as $string2) {
                $p = $this->genericPath->commonprefix(array($string1, $string2));
                $this->assertPythonStringStartsWith($p, $string1);
                $this->assertPythonStringStartsWith($p, $string2);
                if ($string1 !== $string2) {
                    $n = strlen($p);
                    $this->assertNotEquals(substr($string1, $n, $n + 1), substr($string2, $n, $n + 1));
                }
            }
        }
    }
}

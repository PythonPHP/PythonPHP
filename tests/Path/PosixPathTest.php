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

use PythonPHP\Path\PosixPath;
use PHPUnit\Framework\TestCase;

class PosixPathTest extends TestCase
{
    /**
     * @param PosixPath
     */
    protected $posixPath;

    protected function setUp()
    {
        parent::setUp();
        $this->posixPath = new PosixPath();
    }

    /**
     * @dataProvider abspathProvider
     */
    public function testAbspath(string $expected, string $path)
    {
        $mock = $this->getMockBuilder(PosixPath::class)
            ->setMethods(array("getCwd"))
            ->getMock();
        $mock->expects($this->exactly(strpos($path, "/") === 0 ? 0 : 1))
            ->method("getCwd")
            ->willReturn("/usr/lib/php");

        $this->assertSame($expected, $mock->abspath($path));
    }

    /**
     * @return array
     */
    public function abspathProvider()
    {
        // expected, path
        return array(
            array("/usr/lib/php", ""),
            array("/usr/lib/php/test1", "test1"),
            array("/usr/lib/php/test1/test2", "test1/test2"),
            array("/usr/lib/test3", "../test3"),
            array("/usr/test4/test3", "../../test4/test3"),
            array("/usr/lib/test5", "../test5/."),
            array("/usr/lib/php/test6", "./test6"),
            array("/var/tmp/php", "/var/tmp/php"),
            array("/var/tmp/php/test1", "/var/tmp/php/test1"),
            array("/var/tmp", "/var/tmp/php/.."),
            array("/var/tmp", "/var/tmp/../../var/tmp"),
            array("/var/tmp/test2", "/var/tmp/php/./../test2"),
        );
    }

    public function testIsabs()
    {
        $cases = array(
            array("path" => "", "expected" => false),
            array("path" => "/", "expected" => true),
            array("path" => "/foo", "expected" => true),
            array("path" => "/foo/bar", "expected" => true),
            array("path" => "foo/bar", "expected" => false),
        );
        foreach ($cases as $case) {
            $this->assertSame($case["expected"], $this->posixPath->isabs($case["path"]));
        }
    }

    public function testJoin()
    {
        $cases = array(
            array("segments" => array(""), "expected" => ""),
            array("segments" => array("", "", ""), "expected" => ""),
            array("segments" => array("/foo", "bar", "/bar", "baz"), "expected" => "/bar/baz"),
            array("segments" => array("/foo", "bar", "baz"), "expected" => "/foo/bar/baz"),
            array("segments" => array("/foo/", "bar/", "baz/"), "expected" => "/foo/bar/baz/"),
            array("segments" => array("foo", "bar", "baz"), "expected" => "foo/bar/baz"),
            array("segments" => array("foo", "bar", "baz/"), "expected" => "foo/bar/baz/"),
            array("segments" => array("foo/", "bar/", "baz/"), "expected" => "foo/bar/baz/"),
            array("segments" => array("/usr", "lib", "..", "bin"), "expected" => "/usr/lib/../bin"),
        );
        foreach ($cases as $case) {
            $this->assertSame($case["expected"], $this->posixPath->join(...$case["segments"]));
        }
    }

    public function testNormpath()
    {
        $cases = array(
            array("path" => "", "expected" => "."),
            array("path" => "/", "expected" => "/"),
            array("path" => "//", "expected" => "//"),
            array("path" => "///", "expected" => "/"),
            array("path" => "///foo/.//bar//", "expected" => "/foo/bar"),
            array("path" => "///foo/.//bar//.//..//.//baz", "expected" => "/foo/baz"),
            array("path" => "///..//./foo/.//bar", "expected" => "/foo/bar"),
            array("path" => "/abc/def/", "expected" => "/abc/def"),
            array("path" => "/abc/xyz", "expected" => "/abc/xyz"),
        );
        foreach ($cases as $case) {
            $this->assertSame($case["expected"], $this->posixPath->normpath($case["path"]));
        }
    }

    /**
     * @dataProvider relpathProvider
     */
    public function testRelpath(string $expected, string $path, $start = null)
    {
        $getCwdCallCount = 0;
        if (strpos($path, "/") !== 0) {
            $getCwdCallCount++;
        }
        if (strpos((string) $start, "/") !== 0) {
            $getCwdCallCount++;
        }

        $mock = $this->getMockBuilder(PosixPath::class)
            ->setMethods(array("getCwd"))
            ->getMock();
        $mock->expects($this->exactly($getCwdCallCount))
            ->method("getCwd")
            ->willReturn("/home/user/bar");

        $this->assertSame($expected, $mock->relpath($path, $start));
    }

    public function relpathProvider()
    {
        // expected, path, start
        return array(
            array("a", "a"),
            array("a", "/home/user/bar/a"),
            array("a/b", "a/b"),
            array("../a/b", "../a/b"),
            array("../bar/a", "a", "../b"),
            array("../bar/a/b", "a/b", "../c"),
            array("../../a", "a", "b/c"),
            array(".", "a", "a"),
            array("../../../foo/bar/bat", "/foo/bar/bat", "/x/y/z"),
            array("bat", "/foo/bar/bat", "/foo/bar"),
            array("foo/bar/bat", "/foo/bar/bat", "/"),
            array("../../..", "/", "/foo/bar/bat"),
            array("../foo/bar/bat", "/foo/bar/bat", "/x"),
            array("../../../x", "/x", "/foo/bar/bat"),
            array(".", "/", "/"),
            array(".", "/a", "/a"),
            array(".", "/a/b", "/a/b"),
        );
    }
}

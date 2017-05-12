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

use PythonPHP\Path\NTPath;
use PHPUnit\Framework\TestCase;

class NTPathTest extends TestCase
{
    /**
     * @param NTPath
     */
    protected $ntPath;

    protected function setUp()
    {
        parent::setUp();
        $this->ntPath = new NTPath();
    }

    /**
     * @dataProvider abspathProvider
     */
    public function testAbspath(string $expected, string $path)
    {
        $mock = $this->getMockBuilder(NTPath::class)
            ->setMethods(array("getCwd"))
            ->getMock();
        $mock->expects($this->atMost(1))
            ->method("getCwd")
            ->willReturn("C:\\Users\\Matthew");

        $this->assertSame($expected, $mock->abspath($path));
    }

    public function abspathProvider()
    {
        // expected, path
        return array(
            array("C:\\", "C:\\"),
            array("C:\\Users\\Matthew", "."),
            array("C:\\Users", ".."),
            array("C:\\", "..\\.."),
            array("C:\\Programs", "..\\..\\Programs"),
            array("D:\\MyData", "D:\\MyData"),
        );
    }

    public function testIsabs()
    {
        $cases = array(
            array("path" => "c:\\", "expected" => true),
            array("path" => "\\\\conky\\mountpoint", "expected" => false),
            array("path" => "\\\\conky\\mountpoint\\", "expected" => true),
            array("path" => "\\foo", "expected" => true),
            array("path" => "\\foo\\bar", "expected" => true),
            array("path" => "foo\\bar", "expected" => false),
            array("path" => "foo\\bar\\", "expected" => false),
        );
        foreach ($cases as $case) {
            $this->assertSame($case["expected"], $this->ntPath->isabs($case["path"]));
        }
    }

    public function testJoinSingleCharacters()
    {
        $cases = array(
            array("segments" => array(""), "expected" => ""),
            array("segments" => array("", "", ""), "expected" => ""),
            array("segments" => array("a"), "expected" => "a"),
            array("segments" => array("/a"), "expected" => "/a"),
            array("segments" => array("\\a"), "expected" => "\\a"),
            array("segments" => array("a:"), "expected" => "a:"),
            array("segments" => array("a:", "\\b"), "expected" => "a:\\b"),
            array("segments" => array("a", "\\b"), "expected" => "\\b"),
            array("segments" => array("a", "b", "c"), "expected" => "a\\b\\c"),
            array("segments" => array("a\\", "b", "c"), "expected" => "a\\b\\c"),
            array("segments" => array("a", "b\\", "c"), "expected" => "a\\b\\c"),
            array("segments" => array("a", "b", "\\c"), "expected" => "\\c"),
            array("segments" => array("d:\\", "\\pleep"), "expected" => "d:\\pleep"),
            array("segments" => array("d:\\", "a", "b"), "expected" => "d:\\a\\b"),
        );
        foreach ($cases as $case) {
            $this->assertSame($case["expected"], $this->ntPath->join(...$case["segments"]));
        }
    }

    public function testJoinEmptyStrings()
    {
        $cases = array(
            array("segments" => array(""), "expected" => ""),
            array("segments" => array("", "", ""), "expected" => ""),
            array("segments" => array("", "a"), "expected" => "a"),
            array("segments" => array("", "", "", "", "a"), "expected" => "a"),
            array("segments" => array("a", ""), "expected" => "a\\"),
            array("segments" => array("a", "", "", "", ""), "expected" => "a\\"),
            array("segments" => array("a\\", ""), "expected" => "a\\"),
            array("segments" => array("a\\", "", "", "", ""), "expected" => "a\\"),
            array("segments" => array("a/", ""), "expected" => "a/"),
        );
        foreach ($cases as $case) {
            $this->assertSame($case["expected"], $this->ntPath->join(...$case["segments"]));
        }
    }

    public function testJoinMultipleCharacters()
    {
        $cases = array(
            array("segments" => array("a/b", "x/y"), "expected" => "a/b\\x/y"),
            array("segments" => array("/a/b", "x/y"), "expected" => "/a/b\\x/y"),
            array("segments" => array("/a/b/", "x/y"), "expected" => "/a/b/x/y"),
            array("segments" => array("c:", "x/y"), "expected" => "c:x/y"),
            array("segments" => array("c:a/b", "x/y"), "expected" => "c:a/b\\x/y"),
            array("segments" => array("c:a/b/", "x/y"), "expected" => "c:a/b/x/y"),
            array("segments" => array("c:/", "x/y"), "expected" => "c:/x/y"),
            array("segments" => array("c:/a/b", "x/y"), "expected" => "c:/a/b\\x/y"),
            array("segments" => array("c:/a/b/", "x/y"), "expected" => "c:/a/b/x/y"),
            array("segments" => array("//computer/share", "x/y"), "expected" => "//computer/share\\x/y"),
            array("segments" => array("//computer/share/", "x/y"), "expected" => "//computer/share/x/y"),
            array("segments" => array("//computer/share/a/b", "x/y"), "expected" => "//computer/share/a/b\\x/y"),
        );
        foreach ($cases as $case) {
            $this->assertSame($case["expected"], $this->ntPath->join(...$case["segments"]));
        }
    }

    public function testJoinSecondSegmentAbs()
    {
        $cases = array(
            array("segments" => array("a/b", "/x/y"), "expected" => "/x/y"),
            array("segments" => array("/a/b", "/x/y"), "expected" => "/x/y"),
            array("segments" => array("c:", "/x/y"), "expected" => "c:/x/y"),
            array("segments" => array("c:a/b", "/x/y"), "expected" => "c:/x/y"),
            array("segments" => array("c:/", "/x/y"), "expected" => "c:/x/y"),
            array("segments" => array("c:/a/b", "/x/y"), "expected" => "c:/x/y"),
            array("segments" => array("//computer/share", "/x/y"), "expected" => "//computer/share/x/y"),
            array("segments" => array("//computer/share/", "/x/y"), "expected" => "//computer/share/x/y"),
            array("segments" => array("//computer/share/a", "/x/y"), "expected" => "//computer/share/x/y"),
        );
        foreach ($cases as $case) {
            $this->assertSame($case["expected"], $this->ntPath->join(...$case["segments"]));
        }
    }

    public function testJoinMultipleDrivesSame()
    {
        $cases = array(
            array("segments" => array("c:", "C:x/y"), "expected" => "C:x/y"),
            array("segments" => array("c:a/b", "C:x/y"), "expected" => "C:a/b\\x/y"),
            array("segments" => array("c:/", "C:x/y"), "expected" => "C:/x/y"),
            array("segments" => array("c:/a/b", "C:x/y"), "expected" => "C:/a/b\\x/y"),
            array("segments" => array("c:a/b", "C:\\x\\y"), "expected" => "C:\\x\\y"),
            array("segments" => array("c:/a/b", "C:/x/y"), "expected" => "C:/x/y"),
        );
        foreach ($cases as $case) {
            $this->assertSame($case["expected"], $this->ntPath->join(...$case["segments"]));
        }
    }

    public function testJoinDriveOverride()
    {
        $params1 = array("", "a/b", "/a/b", "c:", "c:a/b", "c:/", "c:/a/b", "//computer/share", "//computer/share/", "//computer/share/a/b");
        $params2 = array("d:", "d:x/y", "d:/", "d:/x/y", "//machine/common", "//machine/common/", "//machine/common/x/y");
        foreach ($params1 as $param1) {
            foreach ($params2 as $param2) {
                $this->assertSame($param2, $this->ntPath->join($param1, $param2));
            }
        }
    }

    public function testJoinMore()
    {
        $cases = array(
            array("segments" => array("\\\\computer\\share\\", "a", "b"), "expected" => "\\\\computer\\share\\a\\b"),
            array("segments" => array("\\\\computer\\share", "a", "b"), "expected" => "\\\\computer\\share\\a\\b"),
            array("segments" => array("\\\\computer\\share", "a\\b"), "expected" => "\\\\computer\\share\\a\\b"),
            array("segments" => array("\\\\computer\\share", "a/b"), "expected" => "\\\\computer\\share\\a/b"),
            array("segments" => array("//computer/share/", "a", "b"), "expected" => "//computer/share/a\\b"),
            array("segments" => array("//computer/share", "a", "b"), "expected" => "//computer/share\\a\\b"),
            array("segments" => array("//computer/share", "a/b"), "expected" => "//computer/share\\a/b"),
            array("segments" => array("//computer/share", "a\\b"), "expected" => "//computer/share\\a\\b"),
        );
        foreach ($cases as $case) {
            $this->assertSame($case["expected"], $this->ntPath->join(...$case["segments"]));
        }
    }

    public function testNormcase()
    {
        $cases = array(
            array("path" => "C:\\red", "expected" => "c:\\red"),
            array("path" => "d:/blue", "expected" => "d:\\blue"),
            array("path" => "E:/gReEn\\yeLLow", "expected" => "e:\\green\\yellow"),
        );
        foreach ($cases as $case) {
            $this->assertSame($case["expected"], $this->ntPath->normcase($case["path"]));
        }
    }

    public function testNormpath()
    {
        $cases = array(
            array("path" => "A//////././//.//B", "expected" => "A\\B"),
            array("path" => "A/./B", "expected" => "A\\B"),
            array("path" => "A/foo/../B", "expected" => "A\\B"),
            array("path" => "C:A//B", "expected" => "C:A\\B"),
            array("path" => "D:A/./B", "expected" => "D:A\\B"),
            array("path" => "e:A/foo/../B", "expected" => "e:A\\B"),
            array("path" => "C:///A//B", "expected" => "C:\\A\\B"),
            array("path" => "D:///A/./B", "expected" => "D:\\A\\B"),
            array("path" => "e:///A/foo/../B", "expected" => "e:\\A\\B"),
            array("path" => "..", "expected" => ".."),
            array("path" => ".", "expected" => "."),
            array("path" => "", "expected" => "."),
            array("path" => "/", "expected" => "\\"),
            array("path" => "c:/", "expected" => "c:\\"),
            array("path" => "/../.././..", "expected" => "\\"),
            array("path" => "c:/../../..", "expected" => "c:\\"),
            array("path" => "../.././..", "expected" => "..\\..\\.."),
            array("path" => "K:../.././..", "expected" => "K:..\\..\\.."),
            array("path" => "C:////a/b", "expected" => "C:\\a\\b"),
            array("path" => "//machine/share//a/b", "expected" => "\\\\machine\\share\\a\\b"),
            array("path" => "\\\\.\\NUL", "expected" => "\\\\.\\NUL"),
            array("path" => "\\\\?\\D:/XY\\Z", "expected" => "\\\\?\\D:/XY\\Z"),
        );
        foreach ($cases as $case) {
            $this->assertSame($case["expected"], $this->ntPath->normpath($case["path"]));
        }
    }

    /**
     * @dataProvider relpathProvider
     */
    public function testRelpath(string $expected, string $path, $start = null)
    {
        $getCwdCallCount = 0;
        if (preg_match("/^[A-Za-z\.]/", $path)) {
            if (strpos($path, ":") !== 1) {
                $getCwdCallCount++;
            }
        }
        if ($start === null) {
            $getCwdCallCount++;
        } else {
            if (preg_match("/^[A-Za-z\.]/", $start)) {
                if (strpos($path, ":") !== 1) {
                    $getCwdCallCount++;
                }
            }
        }

        $mock = $this->getMockBuilder(NTPath::class)
            ->setMethods(array("getCwd"))
            ->getMock();
        $mock->expects($this->exactly($getCwdCallCount))
            ->method("getCwd")
            ->willReturn("C:\\Users\\Matthew");

        $this->assertSame($expected, $mock->relpath($path, $start));
    }

    public function relpathProvider()
    {
        // expected, path, start
        return array(
            array("a", "a"),
            array("a", "C:\\Users\\Matthew\\a"),
            array("a\\b", "a/b"),
            array("..\\a\\b", "../a/b"),
            array("..\\Matthew\\a", "a", "../b"),
            array("..\\Matthew\\a\\b", "a/b", "../c"),
            array("..\\..\\a", "a", "b/c"),
            array("..\\..\\foo\\bar\\bat", "c:/foo/bar/bat", "c:/x/y"),
            array("..\\..\\a", "//conky/mountpoint/a", "//conky/mountpoint/b/c"),
            array(".", "a", "a"),
            array("..\\..\\..\\foo\\bar\\bat", "/foo/bar/bat", "/x/y/z"),
            array("bat", "/foo/bar/bat", "/foo/bar"),
            array("foo\\bar\\bat", "/foo/bar/bat", "/"),
            array("..\\..\\..", "/", "/foo/bar/bat"),
            array("..\\foo\\bar\\bat", "/foo/bar/bat", "/x"),
            array("..\\..\\..\\x", "/x", "/foo/bar/bat"),
            array(".", "/", "/"),
            array(".", "/a", "/a"),
            array(".", "/a/b", "/a/b"),
            array(".", "c:/foo", "C:/FOO"),
        );
    }

    public function testSplitdrive()
    {
        $cases = array(
            array("path" => "c:\\foo\\bar", "expected" => array("c:", "\\foo\\bar")),
            array("path" => "c:/foo/bar", "expected" => array("c:", "/foo/bar")),
            array("path" => "\\\\conky\\mountpoint\\foo\\bar", "expected" => array("\\\\conky\\mountpoint", "\\foo\\bar")),
            array("path" => "//conky/mountpoint/foo/bar", "expected" => array("//conky/mountpoint", "/foo/bar")),
            array("path" => "\\\\\\conky\\mountpoint\\foo\\bar", "expected" => array("", "\\\\\\conky\\mountpoint\\foo\\bar")),
            array("path" => "///conky/mountpoint/foo/bar", "expected" => array("", "///conky/mountpoint/foo/bar")),
            array("path" => "\\\\conky\\\\mountpoint\\foo\\bar", "expected" => array("", "\\\\conky\\\\mountpoint\\foo\\bar")),
            array("path" => "//conky//mountpoint/foo/bar", "expected" => array("", "//conky//mountpoint/foo/bar")),
            array("path" => "//conky/MOUNTPOİNT/foo/bar", "expected" => array("//conky/MOUNTPOİNT", "/foo/bar")),
        );
        foreach ($cases as $case) {
            $this->assertSame($case["expected"], $this->ntPath->splitdrive($case["path"]));
        }
    }
}

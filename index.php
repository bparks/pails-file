<?php

//This module needs no introduction

namespace Pails\File;

class Config
{
	static $commonFilePath = "files";
	static $userFilePath = "users";
	static $adminUsers = array();

	public static function setCommonFilePath ($path)
	{
		self::$commonFilePath = $path;
	}

	public static function getCommonFilePath ()
	{
		return self::$commonFilePath;
	}

	public static function setUserFilePath ($path)
	{
		self::$userFilePath = $path;
	}

	public static function getUserFilePath ()
	{
		return self::$userFilePath;
	}
}
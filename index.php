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

	public static function allowUserToManageCommon ($users)
	{
		if (is_array($users))
			self::$adminUsers = array_merge(self::$adminUsers, $users);
		else
			self::$adminUsers[] = $users;
	}

	public static function isPermittedToManageCommon ($user)
	{
		if (in_array($user, self::$adminUsers))
			return true;
		return false;
	}
}
<?php

	require_once('nagObject.class.php');

	/**
	 * A class to parse through the given nagios configuration file(s) and then
	 * flatten out the config if required
	 * 
	 * @author Corey Shaw <corey.shaw@gmail.com>
	 */
	class baseParse{

		private static $libDir = null;
		private static $autoExt = null;
		private static $scanDir = null;

		public static function autoload($class){
			self::setPackageDir();
			self::setAutoloadExtenstions();
			self::setScanDir();

			$extList = self::$autoExt;
			$scanDir = self::$scanDir;
			foreach($scanDir as $curDir){
				foreach($extList as $curExt){
					$filePath = self::$libDir . '/' . $curDir . '/' . $class . $curExt;

					if(file_exists($filePath)){
						include($filePath);
					}
				}
			}
		}

		private static function setPackageDir(){
			if(!is_null(self::$libDir)){
				return;
			}
			self::$libDir = dirname(__FILE__);
		}

		private static function setAutoloadExtenstions(){
			if(!is_null(self::$autoExt)){
				return;
			}

			$ourExt = array('.class.php');
			self::$autoExt = array_merge(explode(',', spl_autoload_extensions()), $ourExt);
		}

		private static function setScanDir(){
			if(!is_null(self::$scanDir)){
				return;
			}

			$ourScanDirs = array(
				'.',
				'objectClasses'
			);
			self::$scanDir = $ourScanDirs;
		}

	}

?>
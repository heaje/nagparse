<?php

	require('nagObject.class.php');

	/**
	 * A class to parse through the given nagios configuration file(s) and then
	 * flatten out the config if required
	 * 
	 * @author Corey Shaw <corey.shaw@gmail.com>
	 */
	class nagParse{

		const NAG_TEMPLATE_USE_KEY = 'use';
		const NAG_TEMPLATE_NAME_KEY = 'name';
		const NAG_TEMPLATE_REGISTER_KEY = 'register';

		private $flatConfigUnusedParams = array(
			self::NAG_TEMPLATE_USE_KEY,
			self::NAG_TEMPLATE_NAME_KEY,
			self::NAG_TEMPLATE_REGISTER_KEY
		);
		private $globalConfig = array();
		private $rawConfig = array();
		private $rawTemplates = array();
		private $flatTemplates = array();

		/**
		 * Parse the given Nagios configuration file.  This could be the nagios.cfg file
		 * or any other nagios configuration file.
		 * @param string $parseFile The Nagios configuration file to parse
		 * @throws BadMethodCallException
		 * @throws OutOfBoundsException
		 * @throws RuntimeException
		 */
		public function parseConfigFile($parseFile){
			if(!is_string($parseFile)){
				throw new BadMethodCallException('You must provide a valid string');
			}

			$fileContents = file($parseFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
			if($fileContents === FALSE){
				throw new OutOfBoundsException('Cannot open config file $parseFile');
			}
			$inObject = false;
			$curObjectType = null;

			foreach($fileContents as $line){
				$line = trim($line);
				$match = array();

				// This is a line that begins with a comment character. We skip these lines.
				if(preg_match('/^(\s*#)/', $line) === 1){
					continue;
				}

				/*
				 * An object parameter and its value.  This only matches if a 'define'
				 * section was already found.  
				 * There must be a better regex to use here that will ignore all white space
				 * at the end.  If it can be discovered, the trim() commands in nagObject.class.php
				 * in the setParam() method could be removed.  That would save a full one second off
				 * of the total run time.
				 */
				elseif($inObject && preg_match('/^\s*(\S+)\s+(.+)/', $line, $match) === 1){
					$key = $match[1];

					/*
					 * Search for the ';' character in the line.  Unless this is a command_line parameter
					 * for a command, we need to assume that anything after the ';' is a comment and should
					 * be removed.
					 */
					$semicolonIndex = strrpos($match[2], ';');
					$value = ($key != 'command_line' && $semicolonIndex !== FALSE) ? substr($match[2], 0, $semicolonIndex) : $match[2];
					$curObj->setParam($key, $value);
				}

				// The start of an object definition
				elseif(preg_match('/^\s*define\s+(\S+)\s*{\s*/', $line, $match) === 1){
					if(!$inObject){
						$inObject = true;
						$curObjectType = strtolower($match[1]);
						$classType = 'nag'.ucfirst($curObjectType);
						$curObj = new $classType();
					}
					else{
						throw new RuntimeException('Found \'define\' within another object!');
					}
				}

				// The end of an object definition.
				elseif(preg_match('/^\s*}/', $line) === 1){
					if($inObject){
						$inObject = false;
						$objName = $curObj->getName();

						// Drop the object in the rawTemplates variable if it is a template
						if($curObj->getIsTemplate()){
							$templateName = $curObj->getTemplateName();
							$this->rawTemplates[$curObjectType][$templateName] = $curObj;
						}


						if($curObj->getIsRegistered()){
							/*
							 * Service definitions are special cases because service_description
							 * does NOT have to be unique.  As a result, it is entirely possible
							 * for multiple services to have the same "name".  Services are indexed
							 * numerically for this reason.
							 */
							if($curObjectType == "service" || $curObjectType == "servicedependency"){
								$this->rawConfig[$curObjectType][] = $curObj;
							}
							else{
								$this->rawConfig[$curObjectType][$objName] = $curObj;
							}
						}
					}
					else{
						throw new RuntimeException('Found closing \'}\' outside of an object!');
					}
				}

				// These are nagios.cfg parameters
				elseif(preg_match('/^\s*(\S+)\s*=\s*(\S+)/', $line, $match) === 1){
					$key = $match[1];
					$value = $match[2];
					switch($key){
						// Refers to a configuration file that should be read
						case "cfg_file":
						case "resource_file":
							$this->ParseConfigFile($value);
							break;
						/*
						 * This is a reference to a directory with *.cfg files.
						 * When this option is found, go into the directory and
						 * parse all valid Nagios configuration files.
						 */
						case "cfg_dir":
							if(is_dir($value)){
								$configFiles = scandir($value);
								foreach($configFiles as $filename){
									if($filename === '.' || $filename === '..'){
										continue;
									}
									$fullPath = $value.'/'.$filename;
									$fileExt = pathinfo($fullPath, PATHINFO_EXTENSION);
									if($fileExt === 'cfg'){
										$this->ParseConfigFile($fullPath);
									}
									else{
										continue;
									}
								}
							}
							else{
								throw new OutOfBoundsException($value.' does not exist!');
							}
							break;
						// All other configuration options only found in nagios.cfg
						default:
							$this->globalConfig[$key] = $value;
							break;
					}
				}

				// Move along because this line probably isn't needed for anything.
				else{
					continue;
				}
			}
		}

		/**
		 * Evaluates all of the dependencies for each Nagios object and flattens out
		 * their configuration.
		 * @return array An associative array of all objects with their flattened config
		 */
		public function flattenConfig(){
			$flatConfig = array();

			// Parse through all object types in the rawConfig array
			foreach(array_keys($this->rawConfig) as $objType){
				$evalArray = $this->rawConfig[$objType];

				foreach($evalArray as $objName => $nagObj){
					// Inherit parameters from templates if they are used
					$useName = $nagObj->getParam(self::NAG_TEMPLATE_USE_KEY);
					$deps = (isset($useName)) ? $this->evalDependency($objType, $useName) : false;
					$newParams = ($deps !== false) ? array_merge($deps, $nagObj->getParams()) : $nagObj->getParams();

					/*
					 * Remove parameters that don't matter anymore.  These are parameters
					 * that should not be inherited from templates and don't matter after
					 * the configuration has been flattened.
					 */
					$newParams = $this->removeUnusedParams($newParams);

					// Replace the objects current configuration with the flattened one.
					$nagObj->replaceParams($newParams);

					// Add the flattened template (if it is one) to an array
					if($nagObj->getIsTemplate()){
						$useName = $nagObj->getTemplateName();
						$this->flatTemplates[$objType][$useName] = $nagObj;
					}

					// Add the flattened object to an array
					if($nagObj->getIsRegistered()){
						$flatConfig[$objType][$objName] = $nagObj;
					}
				}
			}

			return $flatConfig;
		}

		/**
		 * Parses the entire flattened config array and replaces all $USERx$ macros
		 * with their actual values
		 * @param type $flattenedConfig A Nagios flattened configuration from the flattenConfig() method.
		 * @return array Returns the modified flattened config
		 * @throws BadMethodCallException
		 */
		public function parseMacros($flattenedConfig){
			if(!is_array($flattenedConfig)){
				throw new BadMethodCallException("Param #1 must be an array");
			}

			foreach($flattenedConfig as $objects){
				foreach($objects as $curObj){
					$objParams = $curObj->getParams();
					foreach($objParams as $key => $value){
						$match = array();
						if(is_string($value)){
							if(preg_match_all('/(\$USER\d+\$)/', $value, $match) > 0){
								foreach($match[0] as $macroName){
									$value = str_replace($macroName, $this->globalConfig[$macroName], $value);
								}
								$curObj->setParam($key, $value);
							}
						}
					}
				}
			}

			return $flattenedConfig;
		}

		/**
		 * Remove unneccessary configuration parameters from a parameter array
		 * @param array $params The array of parameters from which to remove unnecessary stuff
		 * @return array The parameter array without unnecessary stuff
		 */
		private function removeUnusedParams($params){
			foreach(array_values($this->flatConfigUnusedParams) as $unusedParam){
				if(isset($params[$unusedParam])){
					unset($params[$unusedParam]);
				}
			}

			return $params;
		}

		/**
		 * Evaluate dependencies for Nagios objects.  Will recursively evaluate dependencies.
		 * @param string $objType The type of object for which to evaluate dependencies
		 * @param string $useName The name of the template to use for dependencies
		 * @return mixed Returns an array of template parameters on success.  Returns FALSE
		 * if the template cannot be found.
		 */
		private function evalDependency($objType, $useName){
			/*
			 * Check to see if the template in question has already been flattened.
			 * If so, just return the config right away.
			 */
			if(isset($this->flatTemplates[$objType][$useName])){
				return $this->flatTemplates[$objType][$useName]->getParams();
			}

			// Return false if the template name given doesn't exist
			if(!isset($this->rawTemplates[$objType][$useName])){
				return false;
			}
			$nagTemplateObj = $this->rawTemplates[$objType][$useName];

			/*
			 * If the template in question also inherits stuff from another template, recursively
			 * follow the path to get all dependencies.
			 */
			$nagTemplateUseName = $nagTemplateObj->getParam(self::NAG_TEMPLATE_USE_KEY);
			$deps = (isset($nagTemplateUseName)) ? $this->evalDependency($objType, $nagTemplateUseName) : false;
			$newParams = ($deps !== false) ? array_merge($deps, $nagTemplateObj->getParams()) : $nagTemplateObj->getParams();
			$nagTemplateObj->replaceParams($newParams);

			return $nagTemplateObj->getParams();
		}

		/**
		 * Return the global configuration array
		 * @return array Returns the global configuration array
		 */
		public function getGlobalConfig(){
			return $this->globalConfig;
		}

	}

?>
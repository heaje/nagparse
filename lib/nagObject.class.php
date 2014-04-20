<?php

	// Import all the nagios object classes
	define(NAGIOS_CLASS_DIR, 'lib/objectClasses');
	require('lib/nagObjectInterface.php');
	$nagiosClasses = scandir(NAGIOS_CLASS_DIR);

	foreach($nagiosClasses as $class){
		if($class == '.' || $class == '..'){
			continue;
		}
		include(NAGIOS_CLASS_DIR.'/'.$class);
	}

	/**
	 * A generic class to define Nagios objects.  This is used to set parameters and perform
	 * basic generic tasks.
	 *
	 * @author Corey Shaw <corey.shaw@gmail.com>
	 */
	class nagobject{

		const NAG_OBJ_TEMPLATE_NAME_PARAM = 'name';
		const NAG_OBJ_REGISTER_KEY = 'register';

		protected $type = null;
		protected $name = null;
		protected $templateName = null;
		protected $params = array();
		protected $isTemplate = false;
		protected $isRegistered = true;

		protected function __construct($type, $params = null, $objNameParam, $stringListParams){
			$this->type = $type;

			if(isset($params) && is_array($params)){
				$this->replaceParams($newParamArray, $objNameParam, $stringListParams);
			}
		}

		/**
		 * Return the object name
		 * @return string The object name
		 */
		public function getName(){
			return $this->name;
		}

		/**
		 * Return the object templatename
		 * @return string The object template name
		 */
		public function getTemplateName(){
			return $this->templateName;
		}

		/**
		 * Return true if this object is a template, false otherwise
		 * @return boolean 
		 */
		public function getIsTemplate(){
			return $this->isTemplate;
		}

		/**
		 * Return true if this object is registered, false otherwise
		 * @return boolean 
		 */
		public function getIsRegistered(){
			return $this->isRegistered;
		}

		/**
		 * Return the requested object parameter
		 * @return mixed 
		 */
		public function getParam($paramName){
			return $this->params[$paramName];
		}

		/**
		 * Return an array of all the object parameters
		 * @return array
		 */
		public function getParams(){
			return $this->params;
		}

		/**
		 * Set whether or not this object is a template
		 * @param boolean $isTemplate
		 */
		public function isTemplate($isTemplate = true){
			return $this->isTemplate = $isTemplate;
		}

		/**
		 * Set whether or not this object is registered
		 * @param boolean $isRegistered
		 */
		public function isRegistered($isRegistered = true){
			return $this->isRegistered = $isRegistered;
		}

		/**
		 * Wipes out the current parameters on the object and replaces them with the given
		 * parameter array
		 * @param array $newParamArray The parameters to assign to the object
		 * @param string $objNameParam The parameter that defines the object name
		 * @param array $stringListParams The parameters that can be comma-delimited lists
		 * @throws BadMethodCallException
		 */
		protected function replaceParams($newParamArray, $objNameParam, $stringListParams){
			if(!is_array($newParamArray)){
				throw new BadMethodCallException('Param #1 must be an array');
			}

			$this->params = array();
			foreach($newParamArray as $key => $value){
				$this->setParam($key, $value, $objNameParam, $stringListParams);
			}
		}

		/**
		 * Assign the given parameter with the given value to the objet
		 * @param string $paramName The parameter for which to set the value
		 * @param string $value The value for the parameter
		 * @param string $objNameParam The parameter that defines the object name
		 * @param array $stringListParams The parameters that can be comma-delimited lists
		 */
		protected function setParam($paramName, $value, $objNameParam, $stringListParams){
			/*
			 * These two trim() commands cause the parsing and flattening to take a full
			 * one second longer.  I don't know how to fix these parameters without using
			 * trim() though.
			 */
			$paramName = trim($paramName);
			if(is_string($value)){
				$value = trim($value);
			}

			// Set the name of this object if the given parameter is the object name parameter
			if($paramName == $objNameParam){
				$this->name = $value;
			}
			/*
			 * Set the name for this template and mark it as a template if the given parameter
			 * is the template name parameter
			 */
			elseif($paramName == self::NAG_OBJ_TEMPLATE_NAME_PARAM){
				$this->templateName = $value;
				$this->isTemplate(true);
			}
			/*
			 * Mark the host as not registered if the given parameter is the registered parameter
			 * and it is set to zero
			 */
			elseif($paramName == self::NAG_OBJ_REGISTER_KEY && $value == 0){
				$this->isRegistered(false);
			}

			// Only convert a comma-delimited list into an array if it isn't already an array
			if(array_key_exists($paramName, $stringListParams) && !is_array($value)){
				$value = $this->convertStringListToArray($value);
			}

			return $this->params[$paramName] = $value;
		}

		/**
		 * Takes the given string and creates an array from it using the given delimiter
		 * @param string $string The string from which to create an array
		 * @param string $delimiter The string on which to explode the string into an array
		 * @return array The exploded string as an array
		 * @throws BadMethodCallException
		 */
		protected function convertStringListToArray($string, $delimiter = ','){
			if(!is_string($string)){
				return $string;
			}

			$string = preg_replace('/\s*'.$delimiter.'\s*/', $delimiter, $string);
			return explode($delimiter, $string);
		}

	}
	
<?php

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
		protected static $commonListParams = array('use' => true);

		protected function __construct($type, $params = null, $objNameParam, $stringListParams){
			$this->type = $type;
			if(is_null($objNameParam)){
				$this->name = spl_object_hash($this);
			}

			if(isset($params) && is_array($params)){
				$this->replaceParams($params, $objNameParam, $stringListParams);
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
			return (isset($this->params[$paramName])) ? $this->params[$paramName] : null;
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
		 * Get the object as a Nagios configuration block
		 * @param integer $keyValuePadding The amount of space to pad parameters
		 * @return string
		 */
		public function toString($keyValuePadding = 40, $newLine = true){
			$stringArray = array(
				'define ' . $this->type . '{'
			);
			foreach($this->params as $key => $value){
				$print = true;
				if(is_array($value)){
					if(count($value) > 0){
						$value = $this->convertArrayToStringList($value, ',');
						$print = true;
					}
					else{
						$print = false;
					}
				}

				if($print){
					$stringArray[] = "\t" . str_pad($key, $keyValuePadding) . ' ' . $value;
				}
			}
			$stringArray[] = ($newLine) ? "}\n" : '}';

			return implode("\n", $stringArray);
		}

		protected function inheritParam($paramName, $newValue, $objNameParam, $stringListParams){
			$currentValue = $this->getParam($paramName);
			if(!isset($currentValue) || $currentValue == '' || (is_array($currentValue) && count($currentValue) == 0)){
				$this->setParam($paramName, $newValue, $objNameParam, $stringListParams);
			}
			elseif(array_key_exists($paramName, $stringListParams)){
				$append = (preg_match('/^\+/', $currentValue[0]) === 1) ? true : false;

				if($append){
					if(!is_array($newValue)){
						$newValue = $this->convertStringListToArray($newValue);
					}
					
					// Now that we've inherited things, get rid of the appending character
					$mergedValues = array_merge($currentValue, $newValue);
					$this->setParam($paramName, preg_replace('/^\+/', '', $mergedValues));
				}
				
			}
		}

		public function deleteParam($paramName){
			unset($this->params[$paramName]);
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
			$appendValue = false;

			$paramName = trim($paramName);

			if(is_string($value)){
				$value = trim($value);

				if($value == 'null'){
					if(isset($this->params[$paramName])){
						unset($this->params[$paramName]);
					}
					return;
				}
				elseif($value[0] == "+"){
					$appendValue = true;
					$value = substr($value, 1);
				}
			}

			// Set the name of this object if the given parameter is the object name parameter
			if($paramName == $objNameParam){
				$this->name = $value;
			}

			/*
			 * Set the name for this template and mark it as a template if the given parameter
			 * is the template name parameter
			 */
			if($paramName == self::NAG_OBJ_TEMPLATE_NAME_PARAM){
				$this->templateName = $value;
				$this->isTemplate(true);
			}

			/*
			 * Mark the host as not registered if the given parameter is the registered parameter
			 * and it is set to zero
			 */
			if($paramName == self::NAG_OBJ_REGISTER_KEY && $value == 0){
				$this->isRegistered(false);
			}

			// Only convert a comma-delimited list into an array if it isn't already an array
			if(array_key_exists($paramName, $stringListParams) || array_key_exists($paramName, self::$commonListParams)){
				if(!is_array($value)){
					$valueArray = $this->convertStringListToArray($value);
					if(isset($this->params[$paramName])){
						$value = ($appendValue) ? array_unique(array_merge($this->params[$paramName], $valueArray)) : array_unique($valueArray);
					}
					else{
						$value = $valueArray;
					}
				}
			}

			$this->params[$paramName] = $value;
		}

		/**
		 * Takes the given array and creates an string from it using the given delimiter
		 * @param string $array The array from which to create an string
		 * @param string $delimiter The string on which to implode the array into an string
		 * @return array The imploded array as a string
		 * @throws BadMethodCallException
		 */
		protected function convertArrayToStringList($array, $delimiter = ','){
			if(!is_array($array)){
				return $array;
			}

			return implode($delimiter, $array);
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

			$string = preg_replace('/\s*' . $delimiter . '\s*/', $delimiter, $string);
			$array = preg_split('/' . $delimiter . '/', $string, null, PREG_SPLIT_NO_EMPTY);
			return $array;
			//$string = preg_replace('/'.$delimiter.$delimiter.'+/', $delimiter, $string);
			//return explode($delimiter, $string);
		}

	}
	
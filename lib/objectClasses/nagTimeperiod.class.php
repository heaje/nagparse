<?php

	/**
	 * A Nagios "timeperiod" object class.
	 * 
	 * @author Corey Shaw <corey.shaw@gmail.com>
	 * @version 0.1
	 */
	class nagTimeperiod extends nagObject implements nagObjectInterface{

		const NAG_OBJ_TYPE = "timeperiod";
		const NAG_OBJ_NAME_PARAM = "timeperiod_name";

		// A list of object parameters that are comma-delimited strings
		private static $stringListParams = array();
		
		/*
		 * Nagios Timeperiod objects can have arbitrary parameter names that
		 * indicate a name for a timeperiod.  Each of those parameters can be
		 * comma-delimited lists.  As a result, an exclude list is needed to
		 * know which parameters are NOT comma-delimited lists.
		 */
		private static $stringListExclude = array(
			"timeperiod_name" => true,
			"alias" => true
		);

		/**
		 * Nagios Timeperiods have a special constructor because of their ability
		 * to have arbitrary parameters.  This constructor accounts for that.
		 * @param array $params An array of the parameters to assign to the object
		 */
		public function __construct($params = null){
			parent::__construct(self::NAG_OBJ_TYPE, $params, self::NAG_OBJ_NAME_PARAM, self::$stringListParams);

			if(isset($params) && is_array($params)){
				foreach(array_keys($params) as $paramName => $value){
					if(!array_key_exists($paramName, self::$stringListExclude) && !is_array($value)){
						$params[$paramName] = $this->convertStringListToArray($value);
					}
					else{
						continue;
					}
				}
			}
		}
		
		public function inheritParam($paramName, $value){
			parent::inheritParam($paramName, $value, self::NAG_OBJ_NAME_PARAM, self::$stringListParams);
		}

		/**
		 * Nagios Timeperiods have a special setParam() method because of their ability
		 * to have arbitrary parameters.  This method accounts for that.
		 * @param string $paramName The parameter name
		 * @param mixed $value The parameter value
		 */
		public function setParam($paramName, $value){
			parent::setParam($paramName, $value, self::NAG_OBJ_NAME_PARAM, self::$stringListParams);

			if(!array_key_exists($paramName, self::$stringListExclude) && !is_array($value)){
				$value = $this->convertStringListToArray($value);
			}

			$this->params[$paramName] = $value;
		}

		public function replaceParams($newParamArray){
			parent::replaceParams($newParamArray, self::NAG_OBJ_NAME_PARAM, self::$stringListParams);
		}

	}

?>
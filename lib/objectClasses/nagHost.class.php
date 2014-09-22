<?php

	/**
	 * A Nagios "host" object class.
	 * 
	 * @author Corey Shaw <corey.shaw@gmail.com>
	 * @version 0.1
	 */
	class nagHost extends nagObject implements nagObjectInterface{

		const NAG_OBJ_TYPE = "host";
		const NAG_OBJ_NAME_PARAM = "host_name";

		// A list of object parameters that are comma-delimited strings
		private static $stringListParams = array(
			"contacts" => true,
			"contact_groups" => true,
			"hostgroups" => true,
			"parents" => true
		);
		
		public function inheritParam($paramName, $value){
			parent::inheritParam($paramName, $value, self::NAG_OBJ_NAME_PARAM, self::$stringListParams);
		}

		public function __construct($params = null){
			parent::__construct(self::NAG_OBJ_TYPE, $params, self::NAG_OBJ_NAME_PARAM, self::$stringListParams);
		}

		public function setParam($paramName, $value){
			parent::setParam($paramName, $value, self::NAG_OBJ_NAME_PARAM, self::$stringListParams);
		}

		public function replaceParams($newParamArray){
			parent::replaceParams($newParamArray, self::NAG_OBJ_NAME_PARAM, self::$stringListParams);
		}

		public function getCommand(){
			return $this->convertStringListToArray($this->getParam("check_command"), "!");
		}

		public function getEventHandler(){
			return $this->convertStringListToArray($this->getParam("event_handler"), "!");
		}

	}

?>
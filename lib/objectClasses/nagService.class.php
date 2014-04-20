<?php

	/**
	 * A Nagios "service" object class.
	 * 
	 * @author Corey Shaw <corey.shaw@gmail.com>
	 * @version 0.1
	 */
	class nagService extends nagObject implements nagObjectInterface{

		const NAG_OBJ_TYPE = "service";
		const NAG_OBJ_NAME_PARAM = "service_description";

		// A list of object parameters that are comma-delimited strings
		private static $stringListParams = array(
			"host_name" => true,
			"hostgroup_name" => true,
			"servicegroups" => true,
			"contacts" => true,
			"contact_groups" => true
		);

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
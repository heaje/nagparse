<?php

	/**
	 * A Nagios "contactgroup" object class.
	 * 
	 * @author Corey Shaw <corey.shaw@gmail.com>
	 * @version 0.1
	 */
	class nagContactgroup extends nagObject implements nagObjectInterface{

		const NAG_OBJ_TYPE = "contactgroup";
		const NAG_OBJ_NAME_PARAM = "contactgroup_name";

		// A list of object parameters that are comma-delimited strings
		private static $stringListParams = array(
			"members" => true,
			"contactgroup_members" => true
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

	}

?>
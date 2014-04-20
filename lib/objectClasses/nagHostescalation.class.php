<?php

	/**
	 * A Nagios "Host Escalation" object class.
	 * 
	 * @author Corey Shaw <corey.shaw@gmail.com>
	 * @version 0.1
	 */
	class nagHostescalation extends nagObject implements nagObjectInterface{

		const NAG_OBJ_TYPE = "hostescalation";
		const NAG_OBJ_NAME_PARAM = null;

		// A list of object parameters that are comma-delimited strings
		private static $stringListParams = array(
			"host_name" => true,
			"hostgroup_name" => true,
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

	}

?>
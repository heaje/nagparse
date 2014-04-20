<?php

	/**
	 * A Nagios "servicegroup" object class.
	 * 
	 * @author Corey Shaw <corey.shaw@gmail.com>
	 * @version 0.1
	 */
	class nagServicegroup extends nagObject implements nagObjectInterface{

		const NAG_OBJ_TYPE = "servicegroup";
		const NAG_OBJ_NAME_PARAM = "servicegroup_name";

		// A list of object parameters that are comma-delimited strings
		protected $stringListParams = array(
			"members" => true,
			"servicegroup_members" => true
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
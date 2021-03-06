<?php

	/**
	 * A Nagios "Host Extendend Information" object class.
	 * 
	 * @author Corey Shaw <corey.shaw@gmail.com>
	 * @version 0.1
	 */
	class nagHostextinfo extends nagObject implements nagObjectInterface{

		const NAG_OBJ_TYPE = "hostextinfo";
		const NAG_OBJ_NAME_PARAM = "host_name";

		// A list of object parameters that are comma-delimited strings
		private static $stringListParams = array();

		public function __construct($params = null){
			parent::__construct(self::NAG_OBJ_TYPE, $params, self::NAG_OBJ_NAME_PARAM, self::$stringListParams);
		}
		
		public function inheritParam($paramName, $value){
			parent::inheritParam($paramName, $value, self::NAG_OBJ_NAME_PARAM, self::$stringListParams);
		}

		public function setParam($paramName, $value){
			parent::setParam($paramName, $value, self::NAG_OBJ_NAME_PARAM, self::$stringListParams);
		}

		public function replaceParams($newParamArray){
			parent::replaceParams($newParamArray, self::NAG_OBJ_NAME_PARAM, self::$stringListParams);
		}

	}

?>
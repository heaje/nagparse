<?php

	/**
	 * A Nagios "command" object class.
	 * 
	 * @author Corey Shaw <corey.shaw@gmail.com>
	 * @version 0.1
	 */
	class nagCommand extends nagObject implements nagObjectInterface{

		const NAG_OBJ_TYPE = "command";
		const NAG_OBJ_NAME_PARAM = "command_name";

		// A list of object parameters that are comma-delimited strings
		private static $stringListParams = array(
			"exclude"
		);

		public function __construct($params = null){
			parent::__construct(self::NAG_OBJ_TYPE, $params, self::NAG_OBJ_NAME_PARAM, self::$stringListParams);
		}
		
		public function inheritParam($paramName, $value){
			return parent::inheritParam($paramName, $value, self::NAG_OBJ_NAME_PARAM, self::$stringListParams);
		}

		public function setParam($paramName, $value){
			return parent::setParam($paramName, $value, self::NAG_OBJ_NAME_PARAM, self::$stringListParams);
		}

		public function replaceParams($newParamArray){
			return parent::replaceParams($newParamArray, self::NAG_OBJ_NAME_PARAM, self::$stringListParams);
		}
		
		public function getRelationships(){
			return parent::getRelationships(array());
		}

	}

?>
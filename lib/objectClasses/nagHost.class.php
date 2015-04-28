<?php

	/**
	 * A Nagios 'host' object class.
	 * 
	 * @author Corey Shaw <corey.shaw@gmail.com>
	 * @version 0.1
	 */
	class nagHost extends nagObject implements nagObjectInterface{

		const NAG_OBJ_TYPE = 'host';
		const NAG_OBJ_NAME_PARAM = 'host_name';

		// A list of object parameters that are comma-delimited strings
		private static $stringListParams = array(
			'contacts' => true,
			'contact_groups' => true,
			'hostgroups' => true,
			'parents' => true
		);
		
		private static $relationships = array(
			'contacts' => true,
			'contact_groups' => true,
			'hostgroups' => true,
			'parents' => true,
			'notification_period' => true,
			'event_handler' => true,
			'check_period' => true,
			'notification_period' => true,
			'check_command' => true
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
			return parent::getRelationships(self::$relationships);
		}

		public function getCommand(){
			return $this->convertStringListToArray($this->getParam('check_command'), '!');
		}

		public function getEventHandler(){
			return $this->convertStringListToArray($this->getParam('event_handler'), '!');
		}

	}

?>
<?php

	/**
	 * A Nagios 'contact' object class.
	 * 
	 * @author Corey Shaw <corey.shaw@gmail.com>
	 * @version 0.1
	 */
	class nagContact extends nagObject implements nagObjectInterface{

		const NAG_OBJ_TYPE = 'contact';
		const NAG_OBJ_NAME_PARAM = 'contact_name';

		// A list of object parameters that are comma-delimited strings
		private static $stringListParams = array(
			'contactgroups' => true,
		);
		
		private static $relationships = array(
			'contactgroups' => true,
			'host_notification_period' => true,
			'service_notification_period' => true,
			'host_notification_commands' => true,
			'service_notification_commands' => true,
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

		public function getHostCommand(){
			return $this->convertStringListToArray($this->getParam('host_notification_commands'), '!');
		}

		public function getServiceCommand(){
			return $this->convertStringListToArray($this->getParam('service_notification_commands'), '!');
		}
		
		public function getRelationships(){
			return parent::getRelationships(self::$relationships);
		}

	}

?>
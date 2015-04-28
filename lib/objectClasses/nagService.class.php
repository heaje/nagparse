<?php

	/**
	 * A Nagios 'service' object class.
	 * 
	 * @author Corey Shaw <corey.shaw@gmail.com>
	 * @version 0.1
	 */
	class nagService extends nagObject implements nagObjectInterface{

		const NAG_OBJ_TYPE = 'service';
		const NAG_OBJ_NAME_PARAM = 'service_description';

		// A list of object parameters that are comma-delimited strings
		private static $stringListParams = array(
			'host_name' => true,
			'hostgroup_name' => true,
			'servicegroups' => true,
			'contacts' => true,
			'contact_groups' => true
		);
		private static $relationships = array(
			'host_name' => true,
			'hostgroup_name' => true,
			'servicegroups' => true,
			'contacts' => true,
			'contact_groups' => true,
			'check_command' => true
		);
		private static $memberParams = array(
			'host_name' => true,
			'hostgroup_name' => true,
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

		public function getCommand(){
			return $this->convertStringListToArray($this->getParam('check_command'), '!');
		}

		public function getEventHandler(){
			return $this->convertStringListToArray($this->getParam('event_handler'), '!');
		}
		
		public function getRelationships(){
			return parent::getRelationships(self::$relationships);
		}

		public function addMember($memberName, $isHostgroup = false){
			$param = ($isHostgroup) ? 'hostgroup_name' : 'host_name';
			if(!in_array($memberName, $this->params[$param])){
				$this->params[$param][] = $memberName;
			}
		}

		public function hasMember($memberName, $isHostgroup = false){
			$param = ($isHostgroup) ? 'hostgroup_name' : 'host_name';
			if(isset($this->params[$param])){
				return in_array($memberName, $this->params[$param]);
			}
			else{
				return false;
			}
		}

		public function hasMembers(){
			$return = false;
			foreach(array_keys(self::$memberParams) as $memberParam){
				if(isset($this->params[$memberParam]) && count($this->params[$memberParam]) > 0){
					$return = true;
				}
			}

			return $return;
		}

		public function removeMember($memberName, $isHostgroup = false){
			$param = ($isHostgroup) ? 'hostgroup_name' : 'host_name';
			$memberKey = array_search($memberName, $this->params[$param]);
			if($memberKey !== false){
				unset($this->params[$param][$memberKey]);
			}
		}

		public function getMembers(){
			$members = array();
			foreach(array_keys(self::$memberParams) as $memParam){
				$members[$memParam] = isset($this->params[$memParam]) ? $this->params[$memParam] : array();
			}
			return $members;
		}

	}

?>
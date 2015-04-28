<?php

	/**
	 * A Nagios 'contactgroup' object class.
	 * 
	 * @author Corey Shaw <corey.shaw@gmail.com>
	 * @version 0.1
	 */
	class nagContactgroup extends nagObject implements nagObjectInterface{

		const NAG_OBJ_TYPE = 'contactgroup';
		const NAG_OBJ_NAME_PARAM = 'contactgroup_name';

		// A list of object parameters that are comma-delimited strings
		private static $stringListParams = array(
			'members' => true,
			'contactgroup_members' => true
		);
		
		private static $memberParams = array(
			'members' => true,
			'contactgroup_members' => true
		);
		
		private static $relationships = array(
			'members' => true,
			'contactgroup_members' => true
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
			$relationships = parent::getRelationships(self::$relationships);
			if(isset($relationships['members'])){
				$relationships['contact'] = $relationships['members'];
				unset($relationships['members']);
			}
			return $relationships;
		}
		
		public function addMember($memberName, $isHostgroup = false){
			$param = ($isHostgroup) ? 'contactgroup_members' : 'members';
			if(!in_array($memberName, $this->params[$param])){
				$this->params[$param][] = $memberName;
			}
		}
		
		public function hasMember($memberName, $isHostgroup = false){
			$param = ($isHostgroup) ? 'contactgroup_members' : 'members';
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
			$param = ($isHostgroup) ? 'contactgroup_members' : 'members';
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
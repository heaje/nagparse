<?php

	/**
	 * The interface to define the basic requirements of a Nagios object class
	 * @author Corey Shaw <corey.shaw@gmail.com>
	 */
	interface nagObjectInterface{

		public function setParam($paramName, $value);

		public function replaceParams($newParamArray);

		public function __construct($params = null);
		
		public function inheritParam($paramName, $value);
	}
	
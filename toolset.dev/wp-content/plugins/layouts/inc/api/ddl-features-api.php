<?php
/**
 * Class WPDDL_features
 */

class WPDDL_features
{

	function __construct()
	{

		$this->_support_features = array('fixed-layout', 'fluid-layout');
		
		$this->_all_features = $this->_support_features;
		$this->_all_features[] = 'post-content-cell';
		$this->_all_features[] = 'post-loop-cell';
		
	}


	function remove_ddl_support($feature)
	{
		if (($key = array_search($feature, $this->_support_features)) !== false) {
			unset($this->_support_features[$key]);
			return true;
		} else {
			return false;
		}
	}

	function add_ddl_support($feature)
	{
		if (($key = array_search($feature, $this->_all_features)) !== false) {
			if (($key = array_search($feature, $this->_support_features)) === false) {
				$this->_support_features[] = $feature;
				return true;
			}
		}
		
		return false;
	}

	function is_feature($feature)
	{
		return in_array($feature, $this->_support_features);
	}
}

;

global $wpddl_features;
$wpddl_features = new WPDDL_features();

function remove_ddl_support($feature)
{
	global $wpddl_features;
	return $wpddl_features->remove_ddl_support($feature);
}

function add_ddl_support($feature) {
	global $wpddl_features;
	return $wpddl_features->add_ddl_support($feature);
}
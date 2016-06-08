<?php
namespace Application;

use FW\Model as FW_Model;

/**
* Model
*/
class Model extends FW_Model
{
	

	function __construct($config)
	{
		$this->has_sequence = $this->has_sequence === null ? true : $this->has_sequence;
		$this->has_name = $this->has_name === null ? true : $this->has_name;
		$this->has_history = $this->has_history === null ? true : $this->has_history;
		$this->has_recycler = $this->has_recycler === null ? true : $this->has_recycler;
		$this->has_visibility = $this->has_visibility === null ? true : $this->has_visibility;


		// $this->default_mm_link_class_name = $this->default_mm_link_class_name === null ? "\\Application\\MMLink": $this->default_mm_link_class_name;

		parent::__construct($config);
	}
}
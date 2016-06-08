<?php

namespace FW;

/**
* MMLink
*/
class MMLink extends Model
{
	function __construct($init = true)
	{
		$this->table_prefix = "";
		parent::__construct($init);
	}

	//GETTERS AND SETTERS
	public function set_table_name($table_name)
	{
		$this->table_name = $table_name;
	}

	public function set_properties($properties)
	{
		$this->properties = $properties;
	}

	public function set_relations($relations)
	{
		$this->relations = $relations;
	}
}
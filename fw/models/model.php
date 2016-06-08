<?php
namespace FW;

/**
* Model
*/
class Model
{
	protected $config;
	protected $environment;

	//model items
	protected $environment_specific;
	protected $name;
	protected $properties;
	protected $relation;
	protected $meta_data;

	//predefined properties
	protected $has_sequence;
	protected $has_name;
	protected $has_history;
	protected $has_recycler;

	function __construct($config)
	{
		$this->config = $config;
		$this->init_parameters();
		$this->init_model_items();
	}

	//INIT

	/**
	 * Init the general parameters.
	 */
	private function init_parameters()
	{
		$this->environment_specific = true;
		$this->name = basename(str_replace("\\", "/", strtolower(get_class($this))));
	}

	/**
	 * Init the properties and relations.
	 */
	private function init_model_items()
	{
		$this->properties = array();
		$this->relations = array();

		$this->properties["id"] = array(
			"type" => "int",
			"unsigned" => true,
			"null" => false,
			"auto increment" => true,
			"primary" => true,
		);


		if ($this->has_name) {
			$this->properties["name"] = array(
				"type" => "varchar",
				"length" => 255,
				"null" => false,
				"unique" => array(
					"name" => true,
				),
			);
		}

		if($this->has_sequence) {
			$this->properties["sequence"] = array(
				"type" => "int",
				"unsigned" => true,
				"default" => 1,
				"null" => false,
			);
		}

		if ($this->has_history) {
			$this->properties["changed_on"] = array(
				"type" => "datetime",
				"null" => false,
				"default" => "'0000-00-00 00:00:00'",
			);

			$this->relations["changed_by"] = array(
				"reference" => array(
					"model" => "\\Managements\\User\\User",
					"property" => "id",
				)
			);

			$this->properties["insert_on"] = array(
				"type" => "datetime",
				"null" => false,
				"default" => "'0000-00-00 00:00:00'",
			);

			$this->relations["insert_by"] = array(
				"reference" => array(
					"model" => "\\Managements\\User\\User",
					"property" => "id",
				)
			);
		}

		if($this->has_recycler) {
			$this->properties["is_active"] = array(
				"type" => "tinyint",
				"unsigned" => true,
				"null" => false,
				"default" => 1,
			);
		}
	}

	//END INIT

	//CHECKS

	/**
	 * Check if the column exists
	 * @param string $column 
	 * @return boolean
	 */
	public function column_exists($column)
	{
		return array_key_exists($column, $this->properties) || (array_key_exists($column, $this->relations) && ($this->relations[$column]["type"] == "mo" || $this->relations[$column]["type"] == "oo"));
	}

	//END CHECKS

	//GETTERS AND SETTERS

	/**
	 * Get the name of the model.
	 * @return string
	 */
	public function get_name()
	{
		return $this->name;
	}

	/**
	 * Is the model an environment specific model
	 * @return boolean
	 */
	public function is_environment_specific()
	{
		return $this->environment_specific;
	}

	/**
	 * Get the model properties.
	 * @return array
	 */
	public function get_properties()
	{
		return $this->properties;
	}

	/**
	 * Get a specific property
	 * @param string $property 
	 * @return array
	 */
	public function get_property($property)
	{
		return array_key_exists($property, $this->properties) ? $this->properties[$property] : false;
	}

	/**
	 * Get the model relations.
	 * @return array
	 */
	public function get_relations()
	{
		return $this->relations;
	}

	/**
	 * Get the model meta data.
	 * @return array
	 */
	public function get_meta_data()
	{
		return $this->meta_data;
	}

	//END GETTERS AND SETTERS


	// //TABLE

	// /**
	//  * Table name
	//  */
	// protected $table_name;

	// /**
	//  * Table alias
	//  */
	// protected $table_name_alias;

	// /**
	//  * Prefix to prepend on table_name
	//  */
	// protected $table_prefix = null;

	// //END TABLE

	// //PROPERTIES

	// /**
	//  * List of properties.
	//  * Also known as the table columns
	//  */
	// protected $properties = array();

	// /**
	//  * List of default properties. 
	//  * Also known as the table columns
	//  */
	// protected $default_properties = array();

	// /**
	//  * Default property values. If a property value is not set,
	//  * it is filled in with the default property value.
	//  */
	// protected $default_property_values = array(
	// 	"order" => 1000,
	// 	"type" => "varchar(255)",
	// 	"unsigned" => false,
	// 	"not null" => false,
	// 	"default" => false,
	// 	"unique" => false,
	// 	"primary" => false,
	// 	"autoincrement" => false,
	// );

	// //END PROPERTIES

	// //RELATIONS

	// /**
	//  * List of relations with other models.
	//  * Their are four sorts of relations:
	//  * 	- one to one (oo)
	//  *  - one to many (om)
	//  *  - many to one (mo)
	//  *  - many to many (mm)
	//  */
	// protected $relations = array();

	// /**
	//  * List of default relations with other models.
	//  */
	// protected $default_relations = array();

	// /**
	//  * Default relation values. If a relation value is not set,
	//  * it is filled in with the default property value.
	//  */
	// protected $default_relation_values = array(
	// 	"model" => false,
	// 	"current_column" => "id",
	// 	"referenced_column" => "id",
	// 	"sort" => "mo",
	// 	"delete" => "CASCADE",
	// 	"update" => "CASCADE",
	// 	"property_changes" => array( //property changes to merge with the properties of the referenced column
	// 		"default" => null,
	// 		"not null" => false,
	// 		"primary" => false,
	// 		"autoincrement" => false,
	// 		"order" => 1000,
	// 	),
	// );

	// /**
	//  * If link name is not a key in mm_link_class_names, take the default link_class_name
	//  */
	// protected $default_mm_link_class_name;

	// /**
	//  * Class names for many to many links.
	//  */
	// protected $mm_link_class_names = array();

	// /**
	//  * List of links not to follow
	//  */
	// protected $no_links = array();

	// //END RELATIONS

	// //SELECT

	// /**
	//  * List of properties or relations to select.
	//  * The key is the alias and the value is the column name.
	//  */
	// protected $select_variables = array();


	// /**
	//  * List of relations to add to query via joins
	//  */
	// protected $select_relations = array();

	// /**
	//  * List of constraints
	//  */
	// //insert constraints 
	// // needed data
	// // 	- left operand
	// // 	- operand
	// //	- right operand
	// //	- group number
	// //	- group combine and, or ...
	// // 
	// protected $select_constraints = array();

	// /**
	//  * List of order bys.
	//  */
	// protected $select_order_by = array();

	// //END SELECT

	// //INSERT

	// /**
	//  * List of variables to insert
	//  */
	// protected $insert_variables = array();

	// /**
	//  * List of relations to insert
	//  */
	// protected $insert_relations = array();

	// //END INSERT

	// //MODEL PROPERTIES

	// /**
	//  * Add a sequence to the entries
	//  */
	// protected $has_sequence;

	// /**
	//  * Give every model standard a name
	//  */
	// protected $has_name;

	// /**
	//  * Save the last change and insert datetime and user
	//  */
	// protected $has_history;

	// /**
	//  * Enable a Recycle
	//  */
	// protected $has_active;

	// /**
	//  * Enable datetime ranges where the entry is visible
	//  */
	// protected $has_visibility;

	// //END MODEL PROPERTIES

	
	// function __construct($init = true)
	// {
	// 	$this->table_name = basename(str_replace("\\", "/", strtolower(get_class($this))));
	// 	if($this->default_mm_link_class_name === null) {
	// 		$this->default_mm_link_class_name = "\\FW\\MMLink";
	// 	}
	// 	if($this->table_prefix === null) {
	// 		$this->table_prefix = strtolower($_GET["environment"])."_";
	// 	}

	// 	if($init) {
	// 		$this->init();
	// 		$this->combine_properties();
	// 	}
	// }

	// /**
	//  * init initializes the column properties and relations. 
	//  */
	// public function init() 
	// {
	// 	$this->default_properties["id"] = array(
	// 		"order" => 0,
	// 		"type" => "int(11)",
	// 		"unsigned" => true,
	// 		"primary" => array(
	// 			"id" => true
	// 		),
	// 		"not null" => true,
	// 		"autoincrement" => true,
	// 	);
	// 	$this->select_variables["id"] = "id";

	// 	if($this->has_sequence) {
	// 		$this->default_properties["sequence"] = array(
	// 			"order" => 1,
	// 			"type" => "int(11)",
	// 			"unsigned" => true,
	// 			"default" => 1,
	// 			"not null" => true,
	// 		);
	// 	}

	// 	if($this->has_name) {
	// 		$this->default_properties["name"] = array(
	// 			"order" => 2,
	// 			"type" => "varchar(255)",
	// 			"not null" => true,
	// 			"unique" => array(
	// 				"name" => true,
	// 			),
	// 		);
	// 	}

	// 	if($this->has_visibility) {
	// 		$this->default_properties["visible_from"] = array(
	// 			"order" => 1001,
	// 			"type" => "datetime",
	// 			"default" => "'0000-00-00 00:00:00'",
	// 			"not null" => true,
	// 		);

	// 		$this->default_properties["visible_till"] = array(
	// 			"order" => 1002,
	// 			"type" => "datetime",
	// 			"default" => "'0000-00-00 00:00:00'",
	// 			"not null" => true,
	// 		);
	// 	}

	// 	if($this->has_history) {
	// 		$this->default_properties["insert_on"] = array(
	// 			"order" => 1003,
	// 			"type" => "datetime",
	// 			"default" => "'0000-00-00 00:00:00'",
	// 			"not null" => true,
	// 		);
	// 		$this->default_relations["insert_by"] = array(
	// 			"property_changes" => array(
	// 				"order" => 1004,
	// 			),
	// 			"model" => new \Managements\User\User(false),
	// 		);
	// 		$this->default_properties["changed_on"] = array(
	// 			"order" => 1005,
	// 			"type" => "datetime",
	// 			"default" => "'0000-00-00 00:00:00'",
	// 			"not null" => true,
	// 		);	
	// 		$this->default_relations["changed_by"] = array(
	// 			"property_changes" => array(
	// 				"order" => 1006,
	// 			),
	// 			"model" => new \Managements\User\User(false),
	// 		);
	// 	}

	// 	if($this->has_active) {
	// 		$this->default_properties["is_active"] = array(
	// 			"order" => 1007,
	// 			"type" => "tinyint(1)",
	// 			"not null" => true,
	// 			"default" => 1,
	// 		);
	// 	}	
	// }

	// //PROPERTIES

	// /**
	//  * Combine the default_properties with the custom properties.
	//  */
	// public function combine_properties() 
	// {
	// 	//merge the default_properties with the initialized properties
	// 	$this->properties = array_merge($this->default_properties, $this->properties);
		
	// 	//foreach property merge the default property values with the initialized property values
	// 	foreach ($this->properties as &$property) {
	// 		$property = array_merge($this->default_property_values, $property);
	// 	}
	// 	uasort($this->properties, array($this, "sort_properties"));

	// 	//merge the default_relations with the initialized relations
	// 	$this->relations = array_merge($this->default_relations, $this->relations);

	// 	//foreach relation merge the default relation values with the initialized relation values
	// 	foreach ($this->relations as $relation_name => &$relation) {
	// 		$property_changes = $this->default_relation_values["property_changes"];
	// 		if(array_key_exists("property_changes", $relation)) {
	// 			$property_changes = array_merge($property_changes, $relation["property_changes"]);
	// 		}

	// 		$relation = array_merge($this->default_relation_values, $relation);
	// 		$relation["property_changes"] = $property_changes;
	// 		if($relation["sort"] != "mm") {
	// 			$relation["current_column"] = $relation_name;
	// 		} else {
	// 			$relation["mm_model"] = $this->get_mm_model($relation_name, $relation);
	// 		}
	// 	}
	// 	uasort($this->relations, array($this, "sort_relations"));
	// }

	// private function sort_properties($a, $b)
	// {
	// 	return $a["order"] - $b["order"];
	// }

	// public function property_exists($property_name)
	// {
	// 	return array_key_exists($property_name, $this->properties);
	// }

	// //END PROPERTIES

	// //RELATIONS

	// private function sort_relations($a, $b)
	// {
	// 	return $a["property_changes"]["order"] - $b["property_changes"]["order"];
	// }

	// public function add_relation($relation)
	// {
	// 	$this->relations[$relation["current_column"]] = $relation;
	// 	uasort($this->relations, array($this, "sort_relations"));
	// }

	// public function remove_relation($relation)
	// {
	// 	if(array_key_exists($relation["current_column"], $this->relations)) {
	// 		unset($this->relations[$relation["current_column"]]);
	// 	}
	// }

	// //END RELATIONS

	// //SELECT

	// /**
	//  * Initialize a group number to use for constraint combination.
	//  */
	// public function init_group_number($group_number = null)
	// {
	// 	if($group_number === null) {
	// 		end($this->select_constraints);
	// 		$key = key($this->select_constraints);
	// 		if($key === null) {
	// 			$key = 1;
	// 			$this->select_constraints[$key] = array();
	// 		}
	// 		$group_number = $key;
	// 	} else if(is_int($group_number) && $group_number > 0) {
	// 		if(!array_key_exists($group_number, $this->select_constraints)) {
	// 			$this->select_constraints[$group_number] = array();
	// 		}
	// 	} else {
	// 		return false;
	// 	}
	// 	return $group_number;
	// }

	// /**
	//  * Add some default select constraints, variables, orders,...
	//  */
	// public function add_select_defaults()
	// {
	// 	if($this->has_sequence) {
	// 		$this->add_select_order_by("sequence");
	// 	}

	// 	if($this->has_name) {
	// 		$this->add_select_variable("name");
	// 		$this->add_select_order_by("name");
	// 	}

	// 	if($this->has_active) {
	// 		$this->add_select_constraint("is_active", 0, "<=>", "NOT");
	// 	}
	// }

	// //END SELECT


	// //GETTERS AND SETTERS
	// public function get_properties()
	// {
	// 	return $this->properties;
	// }

	// public function get_properties_by_column($column_name)
	// {
	// 	if(array_key_exists($column_name, $this->properties)) {
	// 		return $this->properties[$column_name];
	// 	}
	// 	return false;
	// }

	// public function get_relations()
	// {
	// 	return $this->relations;
	// }

	// public function get_relation_by_column($column_name)
	// {
	// 	if(array_key_exists($column_name, $this->relations)) {
	// 		return $this->relations[$column_name];
	// 	}
	// 	return false;
	// }

	// public function has_relations()
	// {
	// 	return !empty($relations);
	// }

	// public function get_table_name($use_alias = true)
	// {
	// 	if($use_alias && !empty($this->table_name_alias)) {
	// 		return $this->table_prefix.$this->table_name_alias;	
	// 	}
	// 	return $this->table_prefix.$this->table_name;
	// }

	// public function set_table_name_alias($alias)
	// {
	// 	$this->table_name_alias = $alias;
	// }


	// public function make_table_names_unique()
	// {
	// 	$tables = array($this->get_table_name() => $this);
	// 	foreach ($this->select_relations as $column_name => $relation) {
	// 		$relation_tables = $relation["relation_model"]->make_table_names_unique();
	// 		$this->check_and_change_table_names($relation_tables, $tables);
	// 		if(array_key_exists("mm_model", $relation)) {
	// 			$relation_mm_tables = $relation["mm_model"]->make_table_names_unique();
	// 			$this->check_and_change_table_names($relation_mm_tables, $tables);
	// 		}
	// 	}
	// 	return $tables;
	// }

	// private function check_and_change_table_names($relation_tables, &$tables)
	// {
	// 	foreach ($relation_tables as $table_name => $table) {
	// 		if(array_key_exists($table_name, $tables)) {
	// 			$counter = 1;
	// 			while(array_key_exists($table_name."_".$counter, $tables)) {
	// 				$counter++;
	// 			}
	// 			$table->set_table_name_alias($table_name."_".$counter);
	// 			$tables[$table_name."_".$counter] = $table;
	// 		} else {
	// 			$tables[$table_name] = $table;
	// 		}
	// 	}
	// }

	// public function add_no_link($link_name)
	// {
	// 	$this->no_links[$link_name] = "";
	// }

	// public function remove_no_link($link_name)
	// {
	// 	if(array_key_exists($link_name, $this->no_links)) {
	// 		unset($this->no_links[$link_name]);	
	// 	}
	// }

	// public function get_no_links()
	// {
	// 	return array_keys($this->no_links);
	// }

	// public function get_mm_model($link_name, $relation) 
	// {
	//     $mm_table_name = $this->get_mm_table_name($relation["model"]);


	//     $relation_column_name = $relation["model"]->get_table_name()."_".$relation["referenced_column"];
	//     $current_column_name = $this->get_table_name()."_".$relation["current_column"];

	//     $relation["sort"] = "mo";
	//     $link_relations = array(
	//         $relation_column_name => $relation,
	//         $current_column_name => array(
	//             "model" => $this,
	//             "referenced_column" => $relation["current_column"],
	//         ),
	//     );

	//     if(array_key_exists($link_name, $this->mm_link_class_names)) {
	//     	$class_name = $this->mm_link_class_names[$link_name];
	//     } else {
	//     	$class_name = $this->default_mm_link_class_name;
	//     }
	//     $mm_link = new $class_name(false);
	//     $mm_link->set_table_name($mm_table_name);
	//     $mm_link->set_relations($link_relations);
	//     $mm_link->init();
	//     $mm_link->combine_properties();

	//     $mm_link->add_no_link($relation_column_name);
	//     $mm_link->add_no_link($current_column_name);

	//     return $mm_link;
	// }

	// public function get_select_variables($also_relations = true)
	// {
	// 	$this->select_variables = array_combine(array_map(function($element) {
	// 		return $this->get_table_name().".".$element;
	// 	}, array_keys($this->select_variables)), $this->select_variables);

	// 	$variables = array(
	// 		"variables" => $this->select_variables,
	// 		"relations" => array(),
	// 		"multiple" => true,
	// 		"table_name" => $this->get_table_name(),
	// 	);

	// 	if($also_relations) {
	// 		foreach ($this->select_relations as $column_name => $relation) {
	// 			//get mm variables
	// 			if($mm_model = array_key_exists("mm_model", $relation)) {
	// 				$mm_relation = $relation["mm_model"]->get_select_variables();
	// 			}

	// 			//get relation variables
	// 			$relation_variables = $relation["relation_model"]->get_select_variables();
	// 			switch($this->relations[$column_name]["sort"]) {
	// 				case "mo":
	// 					$relation_variables["multiple"] = false;
	// 				break;
	// 			}

	// 			if($mm_model) {
	// 				$mm_realtion_column_name = $this->relations[$column_name]["model"]->get_table_name();
	// 				$relation_variables["multiple"] = false;
	// 				$mm_relation["relations"][$mm_realtion_column_name] = $relation_variables;
	// 				$variables["relations"][$column_name] = $mm_relation;
	// 			} else  {
	// 				$variables["relations"][$column_name] = $relation_variables;
	// 			}
	// 		}
	// 	}
	// 	return $variables;
	// }

	// public function add_select_variable($select_variable, $alias = null)
	// {
	// 	if($alias === null) {
	// 		if(array_key_exists($select_variable, $this->properties)) {
	// 			$alias = $select_variable;
	// 		} else {
	// 			return false;
	// 		}
	// 	} elseif(is_numeric($alias)) {
	// 		return false;
	// 	} 
	// 	$this->select_variables[$alias] = $select_variable;
	// 	return true;
	// }

	// public function add_select_mm_variable($link_name, $select_variable, $alias = null)
	// {
	// 	if(array_key_exists($link_name, $this->relations) && $this->relations[$link_name]["sort"] == "mm") {
	// 		$mm_model = $this->relations[$link_name]["mm_model"];
	// 		$result = $mm_model->add_select_variable($select_variable, $alias);
	// 		return $result;
	// 	} else {
	// 		return false;
	// 	}
	// }

	// public function add_select_variables($select_variables) 
	// {
	// 	$result = true;
	// 	foreach ($select_variables as $alias => $select_variable) {
	// 		if(is_numeric($alias)) {
	// 			$alias = null;
	// 		}
	// 		$succes = $this->add_select_variable($select_variable, $alias);
	// 		if(!$succes) {
	// 			$result = false;
	// 		}
	// 	}
	// 	return $result;
	// }

	// public function add_select_mm_variables($link_name, $select_variables)
	// {
	// 	$result = true;
	// 	foreach ($select_variables as $alias => $select_variable) {
	// 		if(is_numeric($alias)) {
	// 			$alias = null;
	// 		}
	// 		$succes = $this->add_select_mm_variable($link_name, $select_variable, $alias);
	// 		if(!$succes) {
	// 			$result = false;
	// 		}
	// 	}
	// 	return $result;
	// }

	// public function get_select_relations()
	// {
	// 	$relations = array();
	// 	foreach ($this->select_relations as $column_name => $relation) {
	// 		$relations[$column_name] = $relation;
	// 		foreach ($relation["relation_model"]->get_select_relations() as $relation_column_name => $relation_model) {
	// 			if(array_key_exists($relation_column_name, $relations)) {
	// 				$counter = 1;
	// 				while(array_key_exists($new_key = $relation_column_name."_".$counter, $relations)) {
	// 					$counter++;
	// 				}
	// 				$relation_column_name = $new_key;
	// 			}
	// 			$relations[$relation_column_name] = $relation_model;
	// 		}
	// 	}
	// 	return $relations;
	// }

	// public function add_select_relation($relation_model, $column_name, $join = null)
	// {
	// 	$new_relation = null;
	// 	$mm_model = null;
	// 	$relation_index = $column_name;
	// 	if(array_key_exists($column_name, $this->relations)) {
	// 		//the column name is linked to the current model.
	// 		$new_relation = array(
	// 			"current_model" => $this,
	// 			"relation_model" => $relation_model,
	// 			"join" => $join,
	// 		);
	// 		if($this->relations[$column_name]["sort"] == "mm") {
	// 			$mm_model = $this->relations[$column_name]["mm_model"];
	// 		}
	// 	} else {
	// 		foreach ($this->relations as $relation_name => $relation) {
	// 			//loop through the relations and check if the column name is a referenced column. If so and the table names are the same add the relation.
	// 			if($column_name == $relation["referenced_column"] && $relation["model"]->get_table_name() == $relation_model->get_table_name()) {
	// 				$new_relation = array(
	// 					"current_model" => $this,
	// 					"relation_model" => $relation_model,
	// 					"join" => $join,
	// 				);
	// 				$relation_index = $relation_name;
	// 				break;
	// 			}
	// 		}
	// 	}

	// 	if(!empty($mm_model)) {
	// 		$new_relation["mm_model"] = $mm_model;
	// 	}

	// 	if(!empty($new_relation)) {
	// 		$this->select_relations[$relation_index] = $new_relation;
	// 		return true;
	// 	}
	// 	return false;
	// }

	// public function get_select_constraints()
	// {
	// 	$constraints = $this->select_constraints;

	// 	foreach ($this->select_relations as $relation) {
	// 		$constraints = array_merge_recursive($constraints, $relation["relation_model"]->get_select_constraints());
	// 	}
	// 	return $constraints;
	// }

	// public function add_select_constraint($left_operand, $right_operand, $operand = null, $function = null, $group_number = null)
	// {
	// 	$group_number = $this->init_group_number($group_number);

	// 	if($group_number) {
	// 		$this->select_constraints[$group_number][] = array(
	// 			"model" => $this,
	// 			"left_operand" => $left_operand,
	// 			"operand" => $operand,
	// 			"right_operand" => $right_operand,
	// 			"function" => $function,
	// 		);
	// 		return $group_number;
	// 	} else {
	// 		return false;
	// 	}
	// }

	// public function add_select_constraint_combine($group_combine, $group_number = null)
	// {
	// 	$group_number = $this->init_group_number($group_number);

	// 	if($group_number) {
	// 		if(!array_key_exists("combine", $this->select_constraints[$group_number])) {
	// 			$this->select_constraints[$group_number]["combine"] = $group_combine;
	// 			return $group_number;
	// 		} else {
	// 			//TODO error combine already exists
	// 			return false;
	// 		}
	// 	} else {
	// 		if(!array_key_exists("combine", $this->select_constraints)) {
	// 			$this->select_constraints["combine"] = $group_combine;
	// 			return true;
	// 		} else {
	// 			//TODO error combine already exists
	// 			return false;
	// 		}
	// 	}
	// }

	// public function add_select_order_by($column, $type = null)
	// {
	// 	if($this->property_exists($column)) {
	// 		$this->select_order_by[] = array(
	// 			"model" => $this,
	// 			"column" => $column,
	// 			"type" => $type
	// 		);
	// 	}
	// 	return false;
	// }

	// public function add_select_mm_order_by($link_name, $column, $type = null)
	// {
	// 	if(array_key_exists($link_name, $this->relations) && $this->relations[$link_name]["sort"] == "mm") {
	// 		$mm_model = $this->relations[$link_name]["mm_model"];
	// 		return $mm_model->add_select_order_by($column, $type);
	// 	} else {
	// 		return false;
	// 	}
	// }

	// public function get_select_order_by()
	// {
	// 	$order_by = $this->select_order_by;

	// 	foreach ($this->select_relations as $relation) {
	// 		if(array_key_exists("mm_model", $relation)) {
	// 			$order_by = array_merge($order_by, $relation["mm_model"]->get_select_order_by());
	// 		}
	// 		$order_by = array_merge($order_by, $relation["relation_model"]->get_select_order_by());
	// 	}
	// 	return $order_by;
	// }

	// public function add_insert_data($data)
	// {
	// 	foreach ($data as $key => $value) {
	// 		if($this->property_exists($key) && !is_array($value)) {
	// 			$this->insert_variables[$key] = $value;
	// 		}
	// 	}
	// }

	// public function get_insert_properties()
	// {
	// 	return $this->insert_variables;
	// }

	// public function get_mm_table_name($model_one, $use_alias = true) 
	// {
	//     $table_names = array($model_one->get_table_name($use_alias), $this->get_table_name($use_alias));
	//     sort($table_names);
	//     return "link_".$table_names[0]."_".$table_names[1];
	// }
}
<?php
//TODO make select prepare statements to prevent sql injection
namespace FW;

/**
 * Database
 */
class Database
{
    private $config;

    //environment
    private $use_environment;
    private $default_environment;
    
    //connection
    private $db_host;
    private $db_username;
    private $db_password;
    private $db_name;

    public $connection;

    //logging
    private $log_queries;
    private $queries;

    //config parameters and defaults
    private $charset;
    private $engine;
    private $collate;

    //default
    private $default_join;
    private $default_combine;
    private $default_operand;
    private $default_order_by_type;
    private $default_column_type;
    private $default_reference_column_changes;
    private $default_relation_type;
    private $default_relation_delete_value;
    private $default_relation_update_value;

    //valid mysql
    private $valid_joins;
    private $valid_combines;
    private $valid_operands;
    private $valid_order_by_types;
    private $valid_functions;
    private $valid_types;
    private $valid_length_types;
    private $valid_decimals_types;
    private $valid_unsigned_types;
    private $valid_character_set_types;
    private $valid_binary_types;
    private $valid_value_types;
    private $invalid_default_types;
    private $valid_character_sets;
    private $valid_collates;
    private $valid_relation_types;
    private $valid_relation_delete_values;

    //create
    private static $in_creation = array();
    

    function __construct($config) 
    {
        $this->config = $config;
        $this->init_config_parameters();
        $this->connect();
    }

    //INIT

    /**
     * Initialize the config values.
     */
    private function init_config_parameters()
    {
        $this->valid_joins = array("inner", "outer", "left", "right", "full outer");
        $this->valid_combines = array("and", "or");
        $this->valid_operands = array("=", "<=>", ">=", ">", "IS NOT", "IS", "<=", "<", "LIKE", "!=", "<>", "NOT LIKE", "IN");
        $this->valid_order_by_types = array("asc", "desc");
        $this->valid_functions = array("NOT");

        //get data
        $defaults = array(
            "join" => "inner",
            "combine" => "and",
            "operand" => "=",
            "order_by_type" => "asc",
        );

        $config_variables = array(
            "use_environment",
            "default_environment",
            "log_queries",
            "charset",
            "engine",
            "collate",
            "db_host",
            "db_username",
            "db_password",
            "db_name",
        );

        $mulitple_data = array_merge(array_map(function($element) {
            return "default_".$element;
        }, array_keys($defaults)), $config_variables);

        $data = $this->config->get_multiple_data($mulitple_data);

        //set data
        foreach ($config_variables as $value) {
            $this->$value = $data[$value];
        }
        $this->db_host = in_array($_SERVER["REMOTE_ADDR"], array("127.0.0.1","::1")) ? "localhost" : $this->db_host;

        if($this->log_queries) {
            $this->queries = array(
                "success" => array(),
                "error" => array(),
            );
        }

        foreach ($defaults as $default => $value) {
            $default_name = "default_{$default}";
            $valid_name = "valid_{$default}s";
            $this->$default_name = in_array($data[$default_name], $this->$valid_name) ? $data[$default_name] : $value;
        }
    }

    /**
     * Initialize parameters needed to create a table.
     */
    private function init_create_paramters()
    {
        if(empty($this->valid_types)) {
            $this->valid_types = array("BIT", "TINYINT", "SMALLINT", "MEDIUMINT", "INT", "INTEGER", "BIGINT", "REAL", "DOUBLE", "FLOAT", "DECIMAL", "NUMERIC", "DATE", "TIME", "TIMESTAMP", "DATETIME", "YEAR", "CHAR", "VARCHAR", "BINARY", "VARBINARY", "TINYBLOB", "BLOB", "MEDIUMBLOB", "LONGBLOB", "TINYTEXT", "TEXT", "MEDIUMTEXT", "LONGTEXT", "ENUM", "SET");
            $this->valid_length_types = array("BIT", "TINYINT", "SMALLINT", "MEDIUMINT", "INT", "INTEGER", "BIGINT", "REAL", "DOUBLE", "FLOAT", "DECIMAL", "NUMERIC", "CHAR", "VARCHAR", "BINARY", "VARBINARY");
            $this->valid_decimals_types = array("REAL", "DOUBLE", "FLOAT", "DECIMAL", "NUMERIC");
            $this->valid_unsigned_types = array("TINYINT", "SMALLINT", "MEDIUMINT", "INT", "INTEGER", "BIGINT", "REAL", "DOUBLE", "FLOAT", "DECIMAL", "NUMERIC");
            $this->valid_character_set_types = array("CHAR", "VARCHAR", "TINYTEXT", "TEXT", "MEDIUMTEXT", "LONGTEXT", "ENUM", "SET");
            $this->valid_binary_types = array("TINYTEXT", "TEXT", "MEDIUMTEXT", "LONGTEXT");
            $this->valid_value_types = array("ENUM", "SET");
            $this->invalid_default_types = array("TINYBLOB", "BLOB", "MEDIUMBLOB", "LONGBLOB", "TINYTEXT", "TEXT", "MEDIUMTEXT", "LONGTEXT");
            $this->valid_character_sets = array("big5", "dec8", "cp850", "hp8", "koi8r", "latin1", "latin2", "swe7", "ascii", "ujis", "sjis", "hebrew", "tis620", "euckr", "koi8u", "gb2312", "greek", "cp1250", "gbk", "latin5", "armscii8", "utf8", "ucs2", "cp866", "keybcs2", "macce", "macroman", "cp852", "latin7", "utf8mb4", "cp1251", "utf16", "cp1256", "cp1257", "utf32", "binary", "geostd8", "cp932", "eucjpms");
            $this->valid_collates = array("big5_chinese_ci", "dec8_swedish_ci", "cp850_general_ci", "hp8_english_ci", "koi8r_general_ci", "latin1_swedish_ci", "latin2_general_ci", "swe7_swedish_ci", "ascii_general_ci", "ujis_japanese_ci", "sjis_japanese_ci", "hebrew_general_ci", "tis620_thai_ci", "euckr_korean_ci", "koi8u_general_ci", "gb2312_chinese_ci", "greek_general_ci", "cp1250_general_ci", "gbk_chinese_ci", "latin5_turkish_ci", "armscii8_general_ci", "utf8_general_ci", "ucs2_general_ci", "cp866_general_ci", "keybcs2_general_ci", "macce_general_ci", "macroman_general_ci", "cp852_general_ci", "latin7_general_ci", "utf8mb4_general_ci", "cp1251_general_ci", "utf16_general_ci", "cp1256_general_ci", "cp1257_general_ci", "utf32_general_ci", "binary", "geostd8_general_ci", "cp932_japanese_ci", "eucjpms_japanese_ci");
            $this->valid_relation_types = array("mo", "om", "mm", "oo");
            $this->valid_relation_delete_values = array("cascade", "set null", "restrict", "no action");

            $config_data = $this->config->get_multiple_data(array(
                "default_column_type",
                "default_reference_column_changes",
                "default_relation_type",
                "default_relation_delete_value",
                "default_relation_update_value",
            ));

            foreach ($config_data as $key => $value) {
                $this->$key = $value;
            }
        }
    }

    /**
     * Initialize parameters needed to alter a table.
     */
    private function init_alter_paramters()
    {
        $this->init_create_paramters();
    }

    //END INIT

    //DATABASE ACTIONS
    
    /**
     * Connect with the database.
     */
    private function connect() 
    {
        $this->connection = mysqli_connect($this->db_host, $this->db_username, $this->db_password, $this->db_name);
        $this->connection->set_charset($this->charset);
        if ($this->connection->connect_errno) {
            header("Location: ".$this->config->get_data("error_page_503")."database-connection");
            //TODO set error pages
        }
    }

    /**
     * Execute the query.
     * @param $query
     * @return mysqli_result $result
     */
    public function query($query) 
    {
        if (!($result = $this->connection->query($query))) {
            var_dump($query);
            var_dump($this->connection->error);
            // $result = $this->handle_error_query($query, false);
        } else {
            //log query as a success query
            $this->queries["success"][] = $query;
        }
        return $result;
    }

    
    public function last_insert_id()
    {
        return $this->connection->insert_id;
    }

    /**
     * Convert a mysqli result to a numeric array
     * @param mysqli_result $result 
     * @param string $key
     * @return array $array
     */
    public function result_to_array($result, $key = false)
    {
        $array = array();
        while($row = $result->fetch_assoc()) {
            if((bool)$key) {
                $array[$row[$key]] = $row;
            } else {
                $array[] = $row;
            }
        }
        return $array;
    }

    //END DATABASE ACTIONS

    //DUMP

    /**
     * Get the properties, relations and meta data via the table_name.
     * @param string $table_name 
     * @return array $data
     */
    public function dump($table_name)
    {
        $column_result = $this->query("SHOW COLUMNS FROM `{$table_name}`");

        if(empty($column_result)) {
            return false;
        }

        $keys_result = $this->query("SELECT * FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE LEFT JOIN INFORMATION_SCHEMA.TABLE_CONSTRAINTS ON (INFORMATION_SCHEMA.TABLE_CONSTRAINTS.CONSTRAINT_NAME = INFORMATION_SCHEMA.KEY_COLUMN_USAGE.CONSTRAINT_NAME AND INFORMATION_SCHEMA.TABLE_CONSTRAINTS.TABLE_SCHEMA = INFORMATION_SCHEMA.KEY_COLUMN_USAGE.TABLE_SCHEMA AND INFORMATION_SCHEMA.TABLE_CONSTRAINTS.TABLE_NAME = INFORMATION_SCHEMA.KEY_COLUMN_USAGE.TABLE_NAME) WHERE INFORMATION_SCHEMA.KEY_COLUMN_USAGE.TABLE_SCHEMA = '{$this->db_name}' AND INFORMATION_SCHEMA.KEY_COLUMN_USAGE.TABLE_NAME = '{$table_name}'");

        $columns = $this->result_to_array($column_result, "Field");
        $keys = $this->result_to_array($keys_result); 
        $columns_keys = $this->combine_columns_and_keys($columns, $keys);

        $properties = $this->columns_keys_to_properties($columns_keys);
        $relations = $this->columns_keys_to_relations($columns_keys);

        \FW\FW::dump($properties, "black");

        $data = array(
            "name" => $table_name,
            "properties" => $properties,
            "relations" => array(),
            "meta_data" => array(), //TODO dump with metha data
        );
    }

    /**
     * Combine the column and keys data from a table.
     * @param array $columns 
     * @param array $keys 
     * @return array
     */
    private function combine_columns_and_keys($columns, $keys)
    {
        foreach ($keys as $key) {
            if(array_key_exists($key["COLUMN_NAME"], $columns)) {
                if(!array_key_exists("keys", $columns[$key["COLUMN_NAME"]])) {
                    $columns[$key["COLUMN_NAME"]]["keys"] = array();
                }
                $columns[$key["COLUMN_NAME"]]["keys"][] = $key;
            }
        }
        return $columns;
    }

    /**
     * Convert the column and key data to property data
     * @param array $columns_keys 
     * @return array 
     */
    private function columns_keys_to_properties($columns_keys)          
    {
        $properties = array();
        foreach ($columns_keys as $name => $column_key) {

            if($this->is_column_key_relation($column_key)) {
                continue;
            }

            if(!array_key_exists($name, $properties)) {
                $properties[$name] = array();
            }

            $type_items = explode(" ", $column_key["Type"]);
            //add the type items
            foreach ($type_items as $key => $type_item) {
                if($key == 0 && preg_match_all("/^([a-zA-Z]+)(\(([0-9]+),{0,1}([0-9]*)\)){0,1}$/", $type_item, $matches)) {
                    $properties[$name]["type"] = $matches[1][0];
                    if(!empty($matches[2][0])) {
                        $properties[$name]["length"] = $matches[3][0];

                        if(!empty($matches[4][0])) {
                            $properties[$name]["decimals"] = $matches[4][0];
                        }
                    }
                } elseif(strcasecmp($type_item, "unsigned") == 0) {
                    $properties[$name]["unsigned"] = true;
                } elseif(strcasecmp($type_item, "zerofill") == 0) {
                    $properties[$name]["zerofill"] = true;
                } elseif(preg_match("/^charcter set (.+)/i", $type_item, $matches)) {
                    $properties[$name]["character set"] = $matches[1][0];
                } elseif(preg_match("/^collate (.+)/i", $type_item, $matches)) {
                    $properties[$name]["collate"] = $matches[1][0];
                } elseif(preg_match_all("/^(enum|set)\((.+)\)$/i", $type_item, $matches)) {
                    $values = explode("','", substr($matches[2][0], 1, -1));
                    $properties[$name]["values"] = $values;
                }
            }

            //add null
            if($column_key["Null"] == "NO") {
                $properties[$name]["null"] = false;
            }

            //add default
            if(!empty($column_key["Default"])) {
                $properties[$name]["default"] = $column_key["Default"];    
            }

            //add auto increment
            if(!empty($column_key["Extra"])) {
                if($column_key["Extra"] == "auto_increment") {
                    $properties[$name]["auto increment"] = true;    
                }
            }

            //add primary key
            if($column_key["Key"] == "PRI") {
                $properties[$name]["primary"] = true;
            }
            
            //add unique key
            //if($column_key[""])

            //add unique key
        }
        return $properties;
    }

    private function columns_keys_to_relations($columns_keys)
    {
        $relations = array();
        foreach ($columns_keys as $name => $column_key) {

            if(!$this->is_column_key_relation($column_key)) {
                continue;
            }

            if(!array_key_exists($name, $relations)) {
                $relations[$name] = array();
            }

        }
    }

    /**
     * Check if the current column_key value is a relation.
     * @param array $column_key 
     * @return boolean
     */
    private function is_column_key_relation($column_key)
    {
        $is_relation = false; 
        if(array_key_exists("keys", $column_key)) {
            foreach ($column_key["keys"] as $key) {
                if(!empty($key["REFERENCED_TABLE_NAME"])) {
                    $is_relation = true;
                    break;
                }
            }
        }
        return $is_relation;
    }

    //END DUMP

    //ALTER

    /**
     * Alter the model
     * @param FW\Model $model 
     * @param boolean &$queries If false all the queries will be executed. If true all the queries will be stored in this parameter.
     * @return boolean
     */
    public function alter($model, &$queries = false)
    {
        $this->init_alter_paramters();
        
        if(!$this->valid_model($model)) {
            return false;
        } 

        $table_name = $this->get_table_name($model);
        $table_data = $this->dump($table_name);

        $properties = $model->get_properties();
        $relations = $model->get_relations();
        $meta_data = $model->get_meta_data(); //TODO alter with meta data

        $changes = array();

        if(!empty($properties)) {
            $this->properties_to_changes($properties, $changes, $table_data);
        }

        if(!empty($relations)) {
            $this->relations_to_changes($relations, $changes, $table_data);
        }


        $query = "ALTER TABLE `{$table_name}` ".\FW\FW::implode_recursive(", ", $changes);
    }

    /**
     * Use the properties to build the changes.
     * @param array $properties Model properties: the key is the name and the value is an array with data of the property
     * @param array &$changes Columns: the key is the type of change and the value is an array that can be combine to SQL
     * @param string $table_data The current meta data from the table on the mysql server.
     */
    private function properties_to_changes(&$properties, &$changes, $table_data)
    {
        
    }

    /**
     * Use the relations to build the changes.
     * @param array $relation Model relations: the key is the name and the value is an array with data of the relation
     * @param array &$changes Columns: the key is the name of the column and the value is an array that can be combine to SQL
     * @param string $table_data The current meta data from the table on the mysql server.
     */
    private function relations_to_changes(&$relations, &$changes, $table_data)
    {

    }

    //END ALTER

    //CREATE

    /**
     * Create the model
     * @param FW\Model $model 
     * @param array &$queries If false all the queries will be executed. If true all the queries will be stored in this parameter.
     * @return boolean
     */
    public function create($model, &$queries = false)
    {

        $this->init_create_paramters();
        
        if(!$this->valid_model($model)) {
            return false;
        } 


        $table_name = $this->get_table_name($model);
        self::$in_creation[$table_name] = array();
        $this->dump($table_name);
        die();

        $properties = $model->get_properties();
        $relations = $model->get_relations();
        $meta_data = $model->get_meta_data(); //TODO create with meta data

        $columns = array();
        $keys = array();
        $indexes = array();

        if(!empty($properties)) {
            $this->properties_to_columns($properties, $columns);
            $this->properties_to_keys($properties, $keys);
        }

        if(!empty($relations)) {
            $this->relations_to_columns($relations, $columns);
            $this->relations_to_keys($relations, $keys, $table_name);
        }

        $query = "CREATE TABLE `{$table_name}` (".implode(", ", $columns).", ".\FW\FW::implode_recursive(", ", $keys).")";
        if(!(bool)$queries) {
            $this->query($query);
        } else {
            $queries[] = $query;
        }

        if(self::$in_creation[$table_name]) {

            foreach (self::$in_creation[$table_name] as $query) {
                $this->query($query);
            }
        }

        unset(self::$in_creation[$table_name]);






        // $columns_from_properties = $this->properties_to_columns($properties);
        // $columns_from_relations = $this->relations_to_columns($relations);
        // $columns = array_merge($columns_from_properties, $columns_from_relations);

        // $keys_from_properties = $this->properties_to_keys($properties);
        // $keys_from_relations = $this->relations_to_keys($relations);
        // $keys = array_merge($keys_from_properties, $keys_from_relations);

        // $indexes_from_properties = $this->properties_to_indexes($properties);
        // $indexes_from_relations = $this->relations_to_indexes($relations);
        // $indexes = array_merge($indexes_from_properties, $indexes_from_relations);


        // //process data
        // $if_not_exists = $meta["error_on_already_exists"] ? "" : "IF NOT EXISTS";
        // $table_identifier = $this->get_table_identifier($meta["table_name"], $meta["environment_specific"]);

        // $query = "CREATE TABLE {$if_not_exists} `{$table_identifier}`  ";
        // if(array_key_exists("copy_table_from", $meta)) {
        //     //just copy table structure
        //     // $copy_identifie
        // } else {
        //     //create new table structure
        // }
    }


    /**
     * Use the properties to build the columns.
     * @param array $properties Model properties: the key is the name and the value is an array with data of the property
     * @param array &$columns Columns: the key is the name of the column and the value is an array that can be combine to SQL
     */
    private function properties_to_columns(&$properties, &$columns)
    {
        $already_auto_increment = false;
        foreach ($properties as $name => &$property) {

            //check the length of the column name.
            if(strlen($name) > 64) {
                \FW\FW::error("The name of property '{$name}' is too long.");
            }

            //TODO check if the column name, has the allowed characters.

            //add the property to the columns if it didn't already exists.
            if(!array_key_exists($name, $columns)) {
                $columns[$name] = array(
                    "name" => "`{$name}`",
                );
            }

            //add the type to the column
            if(!array_key_exists("type", $property)) {
                $property["type"] = $this->default_column_type;
            }

            if(!in_array(strtoupper($property["type"]), $this->valid_types)) {
                \FW\FW::error("The type of property '{$name}' is not valid: '{$property["type"]}'.", "warning");
                $property["type"] = "varchar";
            }

            $columns[$name]["type"] = strtoupper($property["type"]);

            //add length
            if(array_key_exists("length", $property)) {
                if(!in_array(strtoupper($property["type"]), $this->valid_length_types)) {
                    \FW\FW::error("The property '{$name}' can't have a lenght value.", "notice");
                } elseif(!is_int($property["length"])) {
                    \FW\FW::error("The length of property '{$name}' has to be an integer and not '{$value}'.", "notice");
                } else {
                    $columns[$name]["length"] = "(".$property["length"];
                }
            }

            //add decimals
            if (array_key_exists("decimals", $property)) {
                if(!in_array(strtoupper($property["type"]), $this->valid_decimals_types)) {
                    \FW\FW::error("The property '{$name}' can't have a decimal value.", "notice");
                } elseif(!is_int($property["decimals"])) {
                    \FW\FW::error("The decimals of property '{$name}' has to be an integer and not '{$value}'.", "notice");
                } elseif (!array_key_exists("length", $columns[$name])) {
                    \FW\FW::error("The length value has to be set, if the decimals value is set for property '{$name}'.");
                } else {
                    $columns[$name]["decimals"] = ", ".$property["decimals"].")";
                }
            } elseif(array_key_exists("length", $columns[$name])) {
                $columns[$name]["length"] .= ")";
            }

            //add unsigned
            if (array_key_exists("unsigned", $property)) {
                if(!in_array(strtoupper($property["type"]), $this->valid_unsigned_types)) {
                    \FW\FW::error("The property '{$name}' can't have a unsigned value.", "notice");
                }
                if((bool) $property["unsigned"]) {
                    $columns[$name]["unsigned"] = "UNSIGNED";
                }
            }

            //add zerofill
            if (array_key_exists("zerofill", $property)) {
                if(!in_array(strtoupper($property["type"]), $this->valid_unsigned_types)) {
                    \FW\FW::error("The property '{$name}' can't have a zerofill value.", "notice");
                }
                if((bool) $property["zerofill"]) {
                    $columns[$name]["zerofill"] = "ZEROFILL";
                }
            }

            //add character set
            if (array_key_exists("character set", $property)) {
                if(!in_array(strtoupper($property["type"]), $this->valid_character_set_types)) {
                    \FW\FW::error("The property '{$name}' can't have a character set value.", "notice");
                } elseif(!in_array($property["character set"], $this->valid_character_sets)) {
                    \FW\FW::error("The character set '{$value}' is not valid for property '{$name}'.");
                } else {
                    $columns[$name]["character set"] = $property["character set"];
                }
            }

            //add collate set
            if (array_key_exists("collate", $property)) {
                if(!in_array(strtoupper($property["type"]), $this->valid_character_set_types)) {
                    \FW\FW::error("The property '{$name}' can't have a collate value.", "notice");
                } elseif(!in_array($property["collate"], $this->valid_collates)) {
                    \FW\FW::error("The collate '{$value}' is not valid for property '{$name}'.");
                } else {
                    $columns[$name]["collate"] = $property["collate"];
                }
            }

            //add binary
            if(array_key_exists("binary", $property)) {
                if(!in_array(strtoupper($property["type"]), $this->valid_binary_types)) {
                    \FW\FW::error("The property '{$name}' can't be binary.");
                }
                if((bool) $property["binary"]) {
                    $columns[$name]["binary"] = "BINARY";
                }
            }

            //add values
            if(array_key_exists("values", $property)) {
                if(!in_array(strtoupper($property["type"]), $this->valid_value_types)) {
                    \FW\FW::error("The property '{$name}' can't have a values value.");
                } elseif(!is_array($property["values"])) {
                    \FW\FW::error("The values type of property '{$name}' has to be an array.");
                } else {
                    $columns[$name]["values"] = implode(",", $property["values"]);
                }
            }

            //add null
            if(array_key_exists("null", $property)) {
                if((bool) $property["null"]) {
                    $columns[$name]["null"] = "NULL";
                } else {
                    $columns[$name]["null"] = "NOT NULL";
                }
            }

            //add default
            if(array_key_exists("default", $property)) {
                if(in_array(strtoupper($property["type"]), $this->invalid_default_types)) {
                    \FW\FW::error("The property '{$name}' can't have a default value.");
                } elseif ($property["default"] == "CURRENT_TIMESTAMP" && strtoupper($property["type"]) != "TIMESTAMP") {
                    \FW\FW::error("The default value can only be 'CURRENT_TIMESTAMP' if the type of the property is 'TIMESTAMP' for property '{$name}'.");
                } elseif (!is_numeric($property["default"]) && $property["default"][0] != "'" && $property["default"][0] != "\"" && $property["default"][0] != $property["default"][strlen($property["default"])-1]) {
                    \FW\FW::error("The value default of property '{$name}' has to be quoted.");
                } else {
                    $columns[$name]["default"] = "DEFAULT {$property['default']}";
                }
            }

            //add auto increment
            if(array_key_exists("auto increment", $property)) {
                if((bool)$already_auto_increment) {
                    \FW\FW::error("Their is an auto increment in property '{$already_auto_increment}' and in property '{$name}'. Their can be only one auto increment per model.");
                } else {
                    $columns[$name]["auto increment"] = "AUTO_INCREMENT";
                    $already_auto_increment = $name;
                }
            }

            $columns[$name] = implode(" ", $columns[$name]);
        }
    }

    /**
     * Use the relations to build the columns.
     * @param array $relation Model relations: the key is the name and the value is an array with data of the relation
     * @param array &$columns Columns: the key is the name of the column and the value is an array that can be combine to SQL
     */
    private function relations_to_columns(&$relations, &$columns)
    {
        foreach ($relations as $name => &$relation) {
            //their are only relation columns with a many to one and a one to one relation.
            if($this->valid_relation($name, $relation) && ($relation["type"] = "mo" || $relation["type"] = "oo")) {
                $referenced_property = $relation["reference"]["model"]->get_property($relation["reference"]["property"]);

                //change referenced property values.
                if(!array_key_exists("changes", $relation["reference"])) {
                    $relation["reference"]["changes"] = $this->default_reference_column_changes;
                } 

                foreach ($relation["reference"]["changes"] as $key => $value) {
                    if(is_bool($value) && !$value) {
                        //if value is false remove the value.
                        unset($referenced_property[$key]);
                    } else {
                        //update the value.
                        $referenced_property[$key] = $value;
                    }
                }

                //change property to column
                $referenced_properties = array($name => $referenced_property);
                $this->properties_to_columns($referenced_properties, $columns);
            }
        }
    }


    /**
     * Use the properties to build the column keys.
     * @param array $properties Model properties: the key is the name and the value is an array with the data of the property.
     * @param array &$keys Keys: the key is the name of the key and the value is an array that can be combine to SQL
     */
    private function properties_to_keys(&$properties, &$keys)
    {
        foreach ($properties as $name => &$property) {

            //add primary keys
            if(array_key_exists("primary", $property) && $property["primary"]) {
                if(!array_key_exists("primary", $keys)) {
                    $keys["primary"] = array();
                }
                $keys["primary"][] = $name;
            }

            //add unique keys
            if(array_key_exists("unique", $property)) {
                if(!is_array($property["unique"])) {
                    \FW\FW::error("The unique value has to be an array for the property '{$name}'.", "warning");
                } else {
                    if(!array_key_exists("unique", $keys)) {
                        $keys["unique"] = array();
                    }
                    foreach ($property["unique"] as $value) {
                        if(is_bool($value) && $value) {
                            $keys["unique"][] = array($name);
                        } elseif(!array_key_exists($value, $keys["unique"])) {
                            $keys["unique"][$value] = array($name);
                        } else {
                            $keys["unique"][$value][] = $name;
                        }
                    }
                }
            }
        }

        if(array_key_exists("primary", $keys)) {
            $keys["primary"] = "PRIMARY KEY (`".implode("`, `", $keys["primary"])."`)";
        }

        if(array_key_exists("unique", $keys)) {
            foreach ($keys["unique"] as $unique_name => &$columns) {
                if(is_int($unique_name)) {
                    $unique_name = $columns[0];
                }
                $columns = "UNIQUE KEY `UQ_{$unique_name}` (`".implode("`, `", $columns)."`)";
            }
            $keys["unique"] = implode(", ",$keys["unique"]);
        }
    }

    /**
     * Use the relation to build the column keys.
     * @param array $relations Model relations: the key is the name and the value is an array with the data of the relation.
     * @param array &$keys Keys: the key is the name of the key and the value is an array that can be combine to SQL
     * @param string $table_name
     */
    private function relations_to_keys(&$relations, &$keys, $table_name)
    {   
        //set the key types if not already set
        if(!array_key_exists("foreign", $keys)) {
            $keys["foreign"] = array();
        }

        $counter = 0;
        foreach ($relations as $name => &$relation) {
            //if there is a link to a yet non-existing table the keys has to be set via the alter commando after the table is created.
            $alter = false;
            if($this->valid_relation($name, $relation)) {
        		//their are only foreign keys with a many to one and a one to one relation.
                if ($relation["type"] = "mo" || $relation["type"] = "oo") {
                    $model = $relation["reference"]["model"];
                    $referenced_table_name = $this->get_table_name($relation["reference"]["model"]);
                    var_dump($referenced_table_name);

                    if(!$this->model_exists_as_table($model)) {
                        if(!array_key_exists($referenced_table_name, self::$in_creation)) {
                            $this->create($model);
                        } else {
                            //the table is in creation modus. the keys are after the table is created with an alter query.
                            $alter = true;
                        }
                    }

                    if(array_key_exists("multi_id", $relation)) {
                        //multiple relation columns are combined in one relation.
                        $key = $relation["multi_id"];
                    } else {
                        $key = $counter++;
                    }

                    if(!array_key_exists($key, $keys["foreign"])) {
                        $keys["foreign"][$key] = array(
                            "type" => $relation["type"],
                            "table_name" => $referenced_table_name,
                            "alter" => $alter,
                            "columns" => array(
                                $name => $relation["reference"]["property"]
                            ),
                            "delete" => $relation["delete"],
                            "update" => $relation["update"],
                        );
                    } else {
                        //multiple relation columns are combined in one relation.
                        if(
                            $keys["foreign"][$key]["table_name"] == $referenced_table_name && 
                            $keys["foreign"][$key]["type"] == $relation["type"]
                        ) {
                            $keys["foreign"][$key]["columns"][$name] = $relation["reference"]["property"];
                        } else {
                            \FW\FW::error("The multi column relation '{$name}' has a different reference model or relation type.");
                        }
                    }
                }
            }
        } 

        foreach ($keys["foreign"] as $id => &$key) {
            $columns = array_keys($key["columns"]);
            $referenced_columns = array_values($key["columns"]);

            $sql_key = "CONSTRAINT `{$table_name}_".implode("_", $columns)."` ";
            $sql_key .= "FOREIGN KEY `FK_{$table_name}_".implode("_", $columns)."` (`".implode("`, `", $columns)."`) ";
            $sql_key .= "REFERENCES `{$key["table_name"]}` (`".implode("`, `", $referenced_columns)."`) ";
            $sql_key .= "ON DELETE {$key["delete"]} ";
            $sql_key .= "ON UPDATE {$key["update"]} ";

            if($key["alter"]) {
                //the referenced table is in creation. Alter the foreign key creation after the referenced table is created.
                self::$in_creation[$key["table_name"]][] = $sql_key;
                unset($keys["foreign"][$id]);
            } else {
                $key = $sql_key;
            }
        }

        if(empty($keys["foreign"])) {
            unset($keys["foreign"]);
        }
    }

    /**
     * Get the definition of mysql indexes by the model properties.
     */
    private function properties_to_indexes($properties)
    {
        
    }

    /**
     * Get the definition of mysql indexes by the model relations.
     */
    private function relations_to_indexes($relations)
    {
        
    }

    //END CREATE

    //CHECKS

    /**
     * Check if the relation is valid and add eventually default values.
     * @param string $name The name of the relation.
     * @param array &$relation a relations had to have a valid type, and reference key. The value of the reference key is an array with an existing model and a property.
     * @return boolean 
     */
    public function valid_relation($name, &$relation)
    {
        //check if the type is set, otherwise take the default value.
        if(!array_key_exists("type", $relation)) {
            $relation["type"] = $this->default_relation_type;
        }

        //check if the relation type is valid.
        if(!in_array($relation["type"], $this->valid_relation_types)) {
            \FW\FW::error("The relation type of relation '{$name}' is not valid.", "warning");
            $relation["type"] = "mo";
        }

        //check if their is a reference with a valid model and property.
        if(!array_key_exists("reference", $relation)) {
            \FW\FW::error("Their is no reference specified for relation '{$name}' with type '{$relation["type"]}'");
            return false;
        } elseif(!array_key_exists("model", $relation["reference"]) || (is_string($relation["reference"]["model"]) && !class_exists($relation["reference"]["model"]))) {
            \FW\FW::error("Their is no model reference specified for relation '{$name}' with type '{$relation["type"]}'");
            return false;
        } else {
            $model_name = $relation["reference"]["model"];
            $relation["reference"]["model"] = new $model_name($this->config);
            if(!$this->valid_model($relation["reference"]["model"])) {
                return false;
            }

            if(!array_key_exists("property", $relation["reference"]) || !$relation["reference"]["model"]->column_exists($relation["reference"]["property"])) {
                \FW\FW::error("Their is no property for the reference or the property doesn't exists for relation '{$name}' with type '{$relation["type"]}'");
                return false;
            }
        }

        //check if the delete value is set.
        if(!array_key_exists("delete", $relation)) {
            $relation["delete"] = $this->default_relation_delete_value;
        }

        //check if the delete value is valid.
        if(!in_array(strtolower($relation["delete"]), $this->valid_relation_delete_values)) {
            \FW\FW::error("The relation delete value of relation '{$name}' is not valid.", "warning");
            $relation["delete"] = "no action";
        }

        //check if the update value is set.
        if(!array_key_exists("update", $relation)) {
            $relation["update"] = $this->default_relation_update_value;
        }

        //check if the update value is valid.
        if(!in_array(strtolower($relation["update"]), $this->valid_relation_delete_values)) {
            \FW\FW::error("The relation update value of relation '{$name}' is not valid.", "warning");
            $relation["update"] = "no action";
        }

        return true;
    }

    /**
     * Check if the model is a valid FW Model
     * @param FW\Model $model The model to check. 
     * @return boolean
     */
    private function valid_model($model)
    {
        if(is_object($model)) {
           $parents = \FW\FW::get_parents_class($model);
           if(!in_array("FW\\Model", $parents)) {
               \FW\FW::error("Their is non valid model detected with class name '".get_class($model)."'.");
               return false;
           }
           return true;     
        } else {
            \FW\FW::error("Their exists a model that isn't an object.");
            return false;
        }
    }

    /**
     * Check if the model exists in the database.
     * @param FW\Model $model 
     * @return boolean
     */
    private function model_exists_as_table($model)
    {
        $table_name = $this->get_table_name($model);
        return $this->table_exists($table_name);
    }

    /**
     * Check if the table name exists in the database.
     * @param string $table_name 
     * @return boolean
     */
    private function table_exists($table_name)
    {
        $query = "SHOW TABLES LIKE '{$table_name}';";
        $result = $this->query($query);
        return $result->num_rows > 0;
    }

    //END CHECK

    //GETTERS AND SETTERS

    /**
     * Get the table identifier to use in SQL.
     * @param FW\Model $model 
     * @return string
     */
    private function get_table_identifier($model)
    {
        return "`{$this->db_name}`.`".$this->get_table_name($model)."`";
    }

    /**
     * Get the name of the table.
     * @param FW\Model $model
     * @return string
     */
    private function get_table_name($model)
    {
        $environment_specific = "";
        $name = $model->get_name();
        if($this->use_environment && $model->is_environment_specific()) {
           $environment_specific .= array_key_exists("environment", $_GET) ? $_GET["environment"] : $this->default_environment;
           $environment_specific .= "_";
        }
        return $environment_specific.$name;
    }

    //END GETTERS AND SETTERS






























































    

    // /**
    //  * Execute an prepared query
    //  * @var $query
    //  * @var $values the values to use in the query
    //  */
    // public function prepared_query($query, $values)
    // {
    //     $statement = $this->connection->prepare($query);
    //     if(!$statement) {
    //         $result = $this->handle_error_query($query);
    //     } else {
    //         $result = array();
    //         $is_insert = preg_match("/^INSERT/", $query);
    //         foreach ($values as $value_row) {
    //             $call_parameters = array();
    //             $types = "";
    //             $call_parameters[] = &$types;
                
    //             foreach ($value_row as $column_name => $variable) {
    //                 $types .= $variable["type"];
    //                 $call_parameters[] = &$variable["value"];
    //             }

    //             call_user_func_array(array($statement, "bind_param"), $call_parameters);
    //             $statement->execute();
    //             if($is_insert) {
    //                 $result[] = $statement->insert_id;
    //             } else {
    //                 $result[] = $statement->get_result();
    //             }
    //             $statement->close();
    //         }
    //     }
    //     return $result;
    // }

    // /**
    //  * If a query doesn't succeed. Try to get it work via the error
    //  * @var $query,
    //  * @var $prepared. Was the execution a prepared statement?
    //  */
    // private function handle_error_query($query, $prepared = true)
    // {
    //     $new_query_possible = false;
    //     $result = false;

    //     switch($this->connection->errno) {
    //         case 1146: //tabel doesn't exists
    //             preg_match("/\'([^']*)\'/", $this->connection->error, $matches);
    //             //first try to remove db_name
    //             $table_name = preg_replace("/^".$this->db_name."\./", "", $matches[1]);
    //             //if there is a table_prefix try to remove it
    //             $table_name = preg_replace("/^".$this->table_prefix."/", "", $table_name);
                
    //             //check if the table model is in the database and exists
    //             //if so than we can create the table from the model.
    //             $result = $this->get_model_by_table_name($table_name);
    //             if($result) {
    //                 $model_name = $result["model"];
    //                 $model = new $model_name();
    //                 $new_query_possible = $this->create($model);  
    //             }         
    //         break;
    //         case 1022: //key already exists
    //             //all keys are unique. If a key name already exists, the same key would be written, so we return true.
    //             //TODO prevent this error to check if a key already exists => dificulty with after_queries
    //             return true;
    //         default: 
    //             //log query as an error query
    //             if($this->log_queries) {
    //                 $this->queries["error"][] = array(
    //                     "error_nr" => $this->connection->errno,
    //                     "error" => $this->connection->error,
    //                     "query" => $query,
    //                 );
    //             }
    //     }

    //     if($new_query_possible) {
    //         if ($prepared) {
    //             $result = $this->prepared_query($query);
    //         } else {
    //             $result = $this->query($query);
    //         }
    //     }
    //     return $result;
    // }
    
    // //CREATE TABLE
    
    // /**
    //  * Create the table based on the model.
    //  */
    // public function create_old($model, $overwrite = false, $only_generate_queries = false) 
    // {
    //     $table_name = $model->get_table_name();
    //     $properties = $model->get_properties();
    //     $queries = array();
        
    //     //check if there is a relation
    //     $empty_relations = !$model->has_relations();
        
    //     //tablename is alphanumeric or -_ and has at least one property or a relation
    //     if (preg_match("/^[a-zA-Z0-9-_]+$/", $table_name) && !(empty($properties) && $empty_relations)) {
            
    //         //if overwrite don't add the 'if not exists' to create table
    //         $exists = $overwrite ? "" : "IF NOT EXISTS";
            
    //         //get the formatted data for the query
    //         list($columns, $meta_data, $after_queries) = $this->format_create_table_properties($model);
            
    //         //build the query and execute
    //         $query = "CREATE TABLE $exists `$table_name` (" . $columns . ") ENGINE={$this->engine} DEFAULT CHARSET={$this->charset} COLLATE={$this->collate} " . implode(" ", $meta_data) . ";";
    //         if($only_generate_queries) {
    //             $queries[] = $query;
    //         } else {
    //             // $result = true;
    //             $result = $this->query($query);
    //         }
            
    //         //execute the after queries based on the properties and relations.
    //         if ($only_generate_queries || ($result && !empty($after_queries))) {          
    //             foreach ($after_queries as $after_query) {
    //                 if($only_generate_queries) {
    //                     $queries[] = $after_query;
    //                 } elseif (!$this->query($after_query)) {
    //                     return false;
    //                 }
                    
    //             }
    //         } else {
               
    //         }

    //         if($only_generate_queries) {
    //             return $queries;
    //         } else {
    //             return $result;
    //         }
    //     } else {
    //         throw new Exception("Table create: no valid table name or their are no properties or relations.", 1);
    //     }
    //     return false;
    // }
    
    // private function format_create_table_properties($model) 
    // {
    //     $table_name = $model->get_table_name();
    //     $column_properties = $model->get_properties();
    //     $relations = $model->get_relations();
    //     $no_links = $model->get_no_links();

    //     $columns = array();
    //     $keys = array();
    //     $table_data = array();
    //     $after_query = array();
        
    //     //relations
    //     $relations_after_query = array();
    //     foreach ($relations as $column_name => $relation) {
    //         $this->format_create_column_relation($column_name, $relation, $model, $column_properties, $after_query, $relations_after_query);
    //     }

    //     if (!empty($relations_after_query)) {
    //         $after_query[] = "ALTER TABLE `{$table_name}` " . implode(", ", $relations_after_query) . ";";
    //     }
        
    //     //columns based on properties
    //     uasort($column_properties, array($this, "sort_columns"));
    //     foreach ($column_properties as $column_name => $properties) {
    //         $this->format_create_column_properties($column_name, $properties, $columns, $keys, $table_data);
    //     }
        
    //     //combine keys
    //     $this->combine_keys($keys, $table_name);
        
    //     return array(implode(", ", array_merge($columns, $keys)), $table_data, $after_query);
    // }

    // private function combine_keys(&$keys, $table_name)
    // {
    //     foreach ($keys as $keysort => &$subkeys) {
    //         foreach ($subkeys as $keyname => &$columnnames) {
    //             $name = "";
    //             switch ($keysort) {
    //                 case "primary":
    //                     $sort = "PRIMARY KEY ";
    //                     break;

    //                 case "unique":
    //                     $sort = "UNIQUE KEY ";
    //                     $name = "`UNIQ_{$table_name}_{$keyname}` ";
    //                     break;

    //                 case "key":
    //                     $sort = "KEY ";
    //                     $name = "`IDX_{$keyname}` ";
    //                     break;
    //             }
    //             $columnnames = $sort . $name . "(`" . implode("`, `", $columnnames) . "`)";
    //         }
    //         $subkeys = $keysort == "primary" ? array_shift($subkeys) : implode(", ", $subkeys);
    //     }
    // }

    // private function sort_columns($a, $b)
    // {
    //     $variables = array("a", "b");
    //     foreach ($variables as $variable) {
    //         $variable_name = $variable."_order";
    //         $$variable_name = null;
    //         $temp = $$variable;
    //         if(array_key_exists("order", $temp)) {
    //             $$variable_name = $temp["order"];
    //         } elseif(isset($temp["property_changes"]["order"])) {
    //             $$variable_name = $temp["property_changes"]["order"];
    //         }
    //     }
        
    //     return $a_order - $b_order;
    // }
    
    // private function format_create_column_properties($column_name, $column_properties, &$columns, &$keys, &$table_data) 
    // {
    //     $column = "`" . $column_name . "`";
    //     foreach ($column_properties as $key => $column_property) {
    //         switch ($key) {
    //             case "type":
    //                 $column.= " " . $column_property;
    //                 if (strtolower(substr($column_property, 0, 7)) == "varchar") {
    //                     $column.= " COLLATE {$this->collate}";
    //                 }
    //                 break;

    //             case "autoincrement":
    //                 $column.= $column_property ? " AUTO_INCREMENT" : "";
    //                 if ($column_property) {
    //                     $table_data[] = "AUTO_INCREMENT=" . (is_numeric($column_property) ? $column_property : "1");
    //                 }
    //                 break;

    //             case "not null":
    //                 $column.= $column_property ? " NOT NULL" : "";
    //                 break;

    //             case "unsigned":
    //                 $column.= $column_property ? " unsigned" : "";
    //                 break;

    //             case "default":
    //                 $column.= $column_property === false ? "" : " DEFAULT " . $this->format_create_column_default_property($column_property);
    //                 break;

    //             case "unique":
    //             case "primary":
    //             case "key":
    //                 if ($column_property) {
    //                     if (!array_key_exists($key, $keys) || !is_array($keys[$key])) {
    //                         $keys[$key] = array();
    //                     }
    //                     if (is_array($column_property)) {
    //                         foreach ($column_property as $key_name => $property) {
    //                             if (!array_key_exists($key_name, $keys[$key]) || !is_array($keys[$key][$key_name])) {
    //                                 $keys[$key][$key_name] = array();
    //                             }
    //                             if ($property) {
    //                                 $keys[$key][$key_name][] = $column_name;
    //                             }
    //                         }
    //                     } else {
    //                         $keys[$key][] = $column_name;
    //                     }
    //                 }
    //                 break;
    //         }
    //     }
    //     $columns[] = $column;
    // }
    
    // private function format_create_column_default_property($property) 
    // {
    //     if ($property === null) {
    //         return "NULL";
    //     }
    //     return $property;
    // }
    
    // private function change_column_to_key_properties($column_properties, $changes) 
    // {
    //     foreach ($changes as $name => $change) {
    //         if (array_key_exists($name, $column_properties) || $name == "key") {
    //             $column_properties[$name] = $change;
    //         }
    //     }
    //     return $column_properties;
    // }

    // private function format_create_column_relation($column_name, $relation, $model, &$column_properties, &$after_query, &$relations_after_query)
    // {
    //     $table_name = $model->get_table_name();
    //     $no_links = $model->get_no_links();

    //     if($relation["model"]) {
    //         $relation_model = $relation["model"];
    //         $relation_model->init();
    //         $relation_model->combine_properties();
    //         $relation_model->add_no_link($column_name);

    //         $relation_model_table_name = $relation_model->get_table_name();
    //     } else {
    //         //recursive model
    //         $relation_model = $model;
    //         $relation_model_table_name = $table_name;
    //     }

    //     //check if table exists or tablename is same as the current table or is a one-to-many link.  If not create relation table
    //     if ($relation_model_table_name != $table_name && !$this->table_exists($relation_model) && !in_array($column_name, $no_links)) {
    //         switch($relation["sort"]) {
    //             case "om":
    //                 $relation_to_current_model = $relation;
    //                 $copy_model_class = get_class($model);
    //                 $copy_model = new $copy_model_class(false);
    //                 $relation_to_current_model["model"] = $copy_model;
    //                 $relation_to_current_model["sort"] = "mo";
    //                 $relation_model->add_relation($relation_to_current_model);
    //                 $after_query = array_merge($after_query, $this->create($relation_model, false, true));
    //                 break;
    //             case "mm":
    //                 $after_query = array_merge($after_query, $this->create($relation_model, false, true));
    //                 break;
    //             case "oo": case "mo":
    //                 $this->create($relation_model);
    //         }
    //     }
        
    //     switch ($relation["sort"]) {
    //         case "oo":
    //         case "mo":
    //         case "recursive":
    //             //add indexes for foreign keys
    //             if ($this->engine == "InnoDB") {
    //                 $relation["property_changes"]["key"] = array("{$relation["sort"]}_{$table_name}_{$column_name}" => true,);
    //                 $relations_after_query[] = "ADD CONSTRAINT `FK_{$relation["sort"]}_{$table_name}_{$column_name}` FOREIGN KEY (`{$column_name}`) REFERENCES `{$relation_model_table_name}` (`{$relation["referenced_column"]}`)" . ($relation["delete"] ? " ON DELETE " . strtoupper($relation["delete"]) : "") . ($relation["update"] ? " ON UPDATE " . strtoupper($relation["update"]) : "");
    //             }
                
    //             $relation_model_link_properties = $this->change_column_to_key_properties($relation_model->get_properties_by_column($relation["referenced_column"]), $relation["property_changes"]);
    //             $column_properties[$column_name] = $relation_model_link_properties;
    //             break;

    //         case "mm":
    //             $mm_table_name = $model->get_mm_table_name($relation_model);
    //             if(!$this->table_exists($mm_table_name)) {
    //                 $after_query = array_merge($after_query, $this->create($relation["mm_model"], false, true));
    //             }
    //             break;
    //     }

    //     return array($after_query, $relations_after_query);
    // }

    // //END CREATE TABLE

    // //ALTER TABLE
    // public function alter($model)
    // {
    //     $columns = $this->get_columns($model, true);
    //     $properties = $model->get_properties();
    //     $relations = $model->get_relations();
    //     $properties_relations = array_merge($properties, $relations);
    //     uasort($properties_relations, array($this, "sort_columns"));
    //     $table_name = $model->get_table_name();
    //     $keys = $this->get_keys($model);

    //     $remove_columns = array();
    //     $update_columns = array();
    //     $add_columns = array();

    //     $property = reset($properties);
    //     $relation = reset($relations);
    //     $property_key = key($properties);
    //     $relation_key = key($relations);

    //     foreach($columns as $column_name => $column) {
    //         if(array_key_exists("REFERENCED_TABLE_NAME", $column) && !empty($column["REFERENCED_TABLE_NAME"])) {
    //             //the column is a relation
    //             $goto_next_relation = true;
    //             if($column_name != $relation_key) {
    //                 //the order of the relation has changed, their is a new relations, the relation is removed of the relation key is many-to-many or one-to-many link.
    //                 if(!array_key_exists($column_name, $relations)) {
    //                     //the relation is removed
    //                     $remove_columns[$column_name] = "";
    //                     $goto_next_relation = false;
    //                 } else {
    //                     //loop through inserted relations to check if their are subsequent new relations
    //                     $related_key = FW::get_prev_key($column_name, $columns);
    //                     $relative = "after";
    //                     if(!$related_key) {
    //                         $related_key = FW::get_next_key($column_name, $columns);
    //                         $relative = "before";
    //                     }
    //                     while(!array_key_exists($relation_key, $columns)) {
    //                         // their is a new relation
    //                         $add_columns[$relation_key] = array(
    //                             "related_key" => $related_key,
    //                             "relative" => $relative,
    //                             "changes" => $relation,
    //                         );

    //                         $related_key = $relation_key;
    //                         $relation = next($relations);
    //                         $relation_key = key($relations);
    //                     }

    //                     if($column_name != $relation_key) {
    //                         if($relation["sort"] == "mm" || $relation["sort"] == "om") {
    //                             //TODO the relation is a many-to-many or one-to-many link.
    //                         } else {
    //                             //order is changed and maybe the relation is changed
    //                             $related_key = FW::get_prev_key($column_name, $properties_relations);
    //                             $relative = "after";
    //                             if(!$related_key) {
    //                                 $related_key = FW::get_next_key($column_name, $properties_relations);
    //                                 $relative = "before";
    //                             }
    //                             $update_columns[$column_name] = array(
    //                                 "related_key" => $related_key,
    //                                 "relative" => $relative,
    //                                 "changes" => $relation,
    //                             );
    //                         }
    //                     }
    //                 } 
    //             }

    //             if($column_name == $relation_key) {
    //                 // check if the relation has changed
    //                 $changes = $this->get_relation_changes($relation, $column, $keys);
    //                 if(!empty($changes)) {
    //                     $update_columns[$column_name] = array(
    //                         "changes" => $property,
    //                     );
    //                 }
    //             }

    //             if($goto_next_relation) {
    //                 //init the next relation
    //                 $relation = next($relations);
    //                 $relation_key = key($relations);
    //             }
    //         } else {
    //             $goto_next_property = true;
    //             if($column_name != $property_key) {
    //                 if(!array_key_exists($column_name, $properties)) {
    //                     // the property is removed
    //                     $remove_columns[$column_name] = "";
    //                     $goto_next_property = false;
    //                 } else {
    //                     //loop through inserted properties to check if their are subsequent properties
    //                     $related_key = FW::get_prev_key($column_name, $columns);
    //                     $relative = "after";
    //                     if(!$related_key) {
    //                         $related_key = FW::get_next_key($column_name, $columns);
    //                         $relative = "before";
    //                     }
    //                     while(!array_key_exists($property_key, $columns)) {
    //                         // their is a new property
    //                         $add_columns[$property_key] = array(
    //                             "related_key" => $related_key,
    //                             "relative" => $relative,
    //                             "changes" => $property,
    //                         );

    //                         $related_key = $property_key;
    //                         $property = next($properties);
    //                         $property_key = key($properties);
    //                     }

    //                     if($column_name != $property_key) {
    //                         //order is changed and maybe the property is changed
    //                         $related_key = FW::get_prev_key($column_name, $properties_relations);
    //                         $relative = "after";
    //                         if(!$related_key) {
    //                             $related_key = FW::get_next_key($column_name, $properties_relations);
    //                             $relative = "before";
    //                         }
    //                         $update_columns[$column_name] = array(
    //                             "related_key" => $related_key,
    //                             "relative" => $relative,
    //                             "changes" => $property,
    //                         );
    //                     }
    //                 }
    //             }

    //             if($column_name == $property_key) {
    //                 // check if the property has changed
    //                 $changes = $this->get_property_changes($property, $column, $keys);
    //                 if(!empty($changes)) {
    //                    $update_columns[$column_name] = array(
    //                        "changes" => $property,
    //                    );
    //                 }
    //             }

    //             if($goto_next_property) {
    //                 //init the next property
    //                 $property = next($properties);
    //                 $property_key = key($properties);
    //             }
    //         }
    //     }

    //     //build query
    //     if(!(empty($remove_columns) && empty($update_columns) && empty($add_columns))) {
    //         $query = "ALTER TABLE `{$table_name}` ";
    //         $changes = array();
            
    //         $columns = array();
    //         $keys = array();
    //         $table_data = array();
    //         foreach ($update_columns as $column_name => $properties) {

    //             $this->format_create_column_properties($column_name, $properties["changes"], $columns, $keys, $table_data);
    //             $extra_query = "";
    //             if(array_key_exists("related_key", $properties)) {
    //                 $extra_query = "AFTER `{$properties["related_key"]}`";
    //             }
    //             $changes[] = "CHANGE COLUMN `{$column_name}` {$update_query} {$extra_query} ";
    //         }

    //         foreach ($remove_columns as $column_name => $properties) {
    //             $changes[] = "DROP COLUMN `{$column_name}`";
    //         }

    //         if($table_name == "form") {
    //             \FW\FW::dump($add_columns, "green");
    //         }

    //         foreach ($add_columns as $column_name => $properties) {
    //             list($add_query, $extra_keys, $extra_table_data)  = $this->format_create_column_properties($column_name, $properties["changes"]);
    //             $extra_query = "";
    //             if(array_key_exists("related_key", $properties)) {
    //                 $extra_query = "AFTER `{$properties["related_key"]}`";
    //             }
    //             $changes[] = "ADD COLUMN {$add_query} {$extra_query} ";
    //         }

    //         $query .= implode(", ", $changes);
    //         return $this->query($query);
    //     }
    //     return false;
    // }

    // private function get_property_changes($model_properties, $database_properties, $keys)
    // {
    //     $result = array();
    //     foreach ($model_properties as $property_name => $property) {
    //         $add = false;
    //         switch($property_name) {
    //             case "type": 
    //                 $add = strpos(strtolower($database_properties["Type"]) , str_replace(" ", "", strtolower($property))) === false;
    //             break;
    //             case "unsigned": 
    //                 $add = ($property && strpos($database_properties["Type"] , "unsigned") === false) || (!$property && strpos($database_properties["Type"] , "unsigned") !== false);
    //             break;
    //             case "not null":
    //                 $add = ($property && $database_properties["Null"] != "NO") || (!$property && $database_properties["Null"] == "NO");
    //             break;
    //             case "default":
    //                 $add = $property !== false && trim($property, "'") != $database_properties["Default"];
    //             break;
    //             //TODO support multi key changes
    //             // case "unique":
    //             //     if($property === false) {
    //             //         $uni_property = false;
    //             //     } else {
    //             //         $uni_property = reset($property);
    //             //     }
    //             //     $add = ($uni_property && strpos($database_properties["Key"] , "UNI") === false) || 
    //             //             (!$uni_property && strpos($database_properties["Key"] , "UNI") !== false);
    //             //     if($add) {
    //             //         var_dump($keys);
    //             //         var_dump($database_properties);
    //             //         var_dump($property);
    //             //     }
    //             // break;
    //             case "primary":
    //                 if($property === false) {
    //                     $pri_property = false;
    //                 } else {
    //                     $pri_property = reset($property);
    //                 }
    //                 $add = ($pri_property && strpos($database_properties["Key"] , "PRI") === false) || (!$pri_property && strpos($database_properties["Key"] , "PRI") !== false);
    //             break;
    //             case "autoincrement":
    //                 $add = ($property && strpos($database_properties["Extra"] , "auto_increment") === false) || (!$property && strpos($database_properties["Extra"] , "auto_increment") !== false);
    //             break;
    //             default:
    //             break;
    //         }
    //         if($add) {
    //             $result[$property_name] = $property;
    //         }
    //     }
    //     return $result;
    // }

    // private function get_relation_changes($model_relations, $database_properties, $keys)
    // {
    //     $result = array();
    //     var_dump($model_relations);
    //     var_dump($database_properties);
    //     foreach ($model_relations as $property_name => $property) {
    //         $add = false;
    //         switch($property_name) {
    //             case "type": 
    //                 $add = strpos(strtolower($database_properties["Type"]) , str_replace(" ", "", strtolower($property))) === false;
    //             break;
    //             case "unsigned": 
    //                 $add = ($property && strpos($database_properties["Type"] , "unsigned") === false) || (!$property && strpos($database_properties["Type"] , "unsigned") !== false);
    //             break;
    //             case "not null":
    //                 $add = ($property && $database_properties["Null"] != "NO") || (!$property && $database_properties["Null"] == "NO");
    //             break;
    //             case "default":
    //                 $add = $property !== false && trim($property, "'") != $database_properties["Default"];
    //             break;
    //             //TODO support multi key changes
    //             // case "unique":
    //             //     if($property === false) {
    //             //         $uni_property = false;
    //             //     } else {
    //             //         $uni_property = reset($property);
    //             //     }
    //             //     $add = ($uni_property && strpos($database_properties["Key"] , "UNI") === false) || 
    //             //             (!$uni_property && strpos($database_properties["Key"] , "UNI") !== false);
    //             //     if($add) {
    //             //         var_dump($keys);
    //             //         var_dump($database_properties);
    //             //         var_dump($property);
    //             //     }
    //             // break;
    //             case "primary":
    //                 if($property === false) {
    //                     $pri_property = false;
    //                 } else {
    //                     $pri_property = reset($property);
    //                 }
    //                 $add = ($pri_property && strpos($database_properties["Key"] , "PRI") === false) || (!$pri_property && strpos($database_properties["Key"] , "PRI") !== false);
    //             break;
    //             case "autoincrement":
    //                 $add = ($property && strpos($database_properties["Extra"] , "auto_increment") === false) || (!$property && strpos($database_properties["Extra"] , "auto_increment") !== false);
    //             break;
    //             default:
    //             break;
    //         }
    //         if($add) {
    //             $result[$property_name] = $property;
    //         }
    //     }
    //     return $result;
    // }

    // //END ALTER TABLE
    
    // //SELECT ENTRIES

    // public function select_first($model)
    // {
    //     $result = $this->select($model);
    //     if($result && count($result) > 0) {
    //         return array_shift($result);
    //     }
    //     return false;
    // }

    // public function select($model) 
    // {
    //     $table_name = $model->get_table_name(false);
    //     $table_name_alias = $model->get_table_name();
    //     $model->make_table_names_unique();
    //     $grouped_variables = $model->get_select_variables();

    //     $variables = $this->format_select_variables($grouped_variables);
    //     $relations = $this->format_select_relations($model);
    //     $constraints = $this->format_select_constraints($model);
    //     $order_by = $this->format_select_order_by($model);

    //     //build query
    //     $query = "SELECT {$variables} FROM `{$table_name}` AS `{$table_name_alias}` {$relations} ";
    //     $query .= !empty($constraints) ? "WHERE {$constraints} " : "";
    //     $query .= !empty($order_by) ? "ORDER BY {$order_by} " : "";
    //     // \FW\FW::dump($query, "black");

    //     //execute query
    //     $result = $this->query($query);
    //     return $result ? $this->format_select_result($result, $grouped_variables) : false;
    // }
    
    // private function format_select_variables($grouped_variables) 
    // {
    //     $formatted_variables = array();
    //     $this->format_select_model_variables($grouped_variables, $formatted_variables);
        
    //     foreach ($formatted_variables as $alias => &$data) {
    //         $data = "{$data} AS `{$alias}`";
    //     }
        
    //     return implode(", ", $formatted_variables);
    // }

    // private function format_select_model_variables($grouped_variables, &$formatted_variables)
    // {
    //     foreach ($grouped_variables["variables"] as $alias => $variable) {
    //         $table_name = explode(".", $alias, 2);
    //         $formatted_variables[$alias] = "`".$table_name[0]."`.`".$variable."`";
    //     }

    //     foreach ($grouped_variables["relations"] as $column_name => $relation) {
    //         $this->format_select_model_variables($relation, $formatted_variables);
    //     }
    // }

    // private function format_select_relations($model)
    // {
    //     $relation_query = "";

    //     $relations = $model->get_select_relations();
    //     foreach ($relations as $column_name => $relation) {
    //         //delete counter from column name
    //         if(preg_match("/^(.+)_[0-9]+$/", $column_name, $matches)) {
    //             $column_name = $matches[1];
    //         }
    //         if(!in_array($relation["join"], $this->valid_joins)) {
    //             $relation["join"] = $this->default_join;
    //         }

    //         $relation_properties = $relation["current_model"]->get_relation_by_column($column_name);
    //         $current_properties = $relation["relation_model"]->get_relation_by_column($relation_properties["referenced_column"]);
    //         $relation_table_name = $relation["relation_model"]->get_table_name(false);
    //         $relation_table_name_alias = $relation["relation_model"]->get_table_name();
    //         $current_table_name = $relation["current_model"]->get_table_name(false);
    //         $current_table_name_alias = $relation["current_model"]->get_table_name();
            
    //         switch($relation_properties["sort"]) {
    //             case "mo": case "oo":
    //                 $relation_query .= strtoupper($relation["join"])." JOIN `{$relation_table_name}` AS `{$relation_table_name_alias}` ON `{$current_table_name_alias}`.`{$relation_properties["current_column"]}` = `{$relation_table_name_alias}`.`{$relation_properties["referenced_column"]}` ";
    //             break;
    //             case "om": 
    //             	$relation_query .= strtoupper($relation["join"])." JOIN `{$relation_table_name}` AS `{$relation_table_name_alias}` ON `{$current_table_name_alias}`.`{$current_properties["referenced_column"]}` = `{$relation_table_name_alias}`.`{$relation_properties["referenced_column"]}` ";
    //             break;
    //             case "mm":
    //                 $mm_table_name = $relation["current_model"]->get_mm_table_name($relation["relation_model"], false);
    //                 $mm_table_name_alias = $relation["current_model"]->get_mm_table_name($relation["relation_model"]);
    //                 $mm_current_column = $current_table_name."_".$relation_properties["current_column"];
    //                 $mm_relation_column = $relation_table_name."_".$relation_properties["referenced_column"];
    //                 $relation_query .= strtoupper($relation["join"])." JOIN `{$mm_table_name}` AS `{$mm_table_name_alias}` ON `{$current_table_name_alias}`.`{$relation_properties["current_column"]}` = `{$mm_table_name_alias}`.`{$mm_current_column}` ";
    //                 $relation_query .= strtoupper($relation["join"])." JOIN `{$relation_table_name}` AS `{$relation_table_name_alias}` ON `{$mm_table_name_alias}`.`{$mm_relation_column}` = `{$relation_table_name_alias}`.`{$relation_properties["referenced_column"]}` ";
    //             break;
    //         }
    //     }
    //     return $relation_query;
    // }

    // private function format_select_constraints($model)
    // {
    //     $constraints = $model->get_select_constraints();
    //     foreach ($constraints as $group_number => &$group_constraints) {
    //         $combine = array_key_exists("combine", $group_constraints) && in_array($group_constraints["combine"], $this->valid_combines) ? $group_constraints["combine"] : $this->default_combine;
    //         foreach($group_constraints as &$constraint) {
    //             $constraint = $this->format_select_constraint($constraint);
    //         }
    //         $group_constraints = "(".implode(" ".strtoupper($combine)." ", $group_constraints).")";
    //     }
    //     $combine = array_key_exists("combine", $constraints) && in_array($constraints["combine"], $this->valid_combines) ? $constraints["combine"] : $this->default_combine;
    //     return implode(" ".strtoupper($combine)." ", $constraints);
    // }

    // private function format_select_constraint($constraint)
    // {
    //     $model = $constraint["model"];

    //     // check if the left operand is a column
    //     if(is_string($constraint["left_operand"]) && $model->property_exists($constraint["left_operand"])) {
    //         $constraint["left_operand"] = "`".$model->get_table_name()."`.`".$constraint["left_operand"]."`";
    //     }

    //     // check if the right operand is a column
    //     if(is_string($constraint["right_operand"]) && $model->property_exists($constraint["right_operand"])) {
    //         $constraint["right_operand"] = "`".$model->get_table_name()."`.`".$constraint["right_operand"]."`";
    //     }

    //     // check if you can combine the right operand
    //     if(is_array($constraint["right_operand"])) {
    //         $constraint["right_operand"] = "(".implode(", ",$constraint["right_operand"]).")";
    //     }

    //     // set the default operand if their is no operand set
    //     if(!in_array($constraint["operand"], $this->valid_operands)) {
    //         $constraint["operand"] = $this->default_operand;
    //     }

    //     $formatted_constraint = $constraint["left_operand"]." ".$constraint["operand"]." ".$constraint["right_operand"];

    //     if(in_array($constraint["function"], $this->valid_functions)) {
    //         $formatted_constraint = strtoupper($constraint["function"])."({$formatted_constraint})";
    //     }
    //     return $formatted_constraint;
    // }

    // private function format_select_order_by($model)
    // {
    //     $order_bys = $model->get_select_order_by();
    //     foreach($order_bys as &$order_by) {
    //         $order_by = "`".$order_by["model"]->get_table_name()."`.`".$order_by["column"]."` ".strtoupper(in_array($order_by["type"], $this->valid_order_by_types) ? $order_by["type"] : $this->default_order_by_type); 
    //     }
    //     return implode(", ", $order_bys);
    // }

    // public function format_select_result($result, $aliassen)
    // {
    //     $entries = array();
    //     while($row = $result->fetch_assoc()) {
    //         $this->add_entry_part($entries, $row, $aliassen);
    //     }
    //     return $entries;
    // }

    // private function add_entry_part(&$entries, $row, $aliassen)
    // {
    //     reset($aliassen["variables"]);
    //     $id_key = key($aliassen["variables"]);
    //     $id = $row[$id_key];
    //     if($id == "") {
    //         return ;
    //     }
    //     if(!$aliassen["multiple"] || !array_key_exists($id, $entries)) {
    //         $entry = array();
    //         foreach ($aliassen["variables"] as $alias => $value) {
    //             $short_alias = substr($alias, strlen($id_key)-2);
    //             $entry[$short_alias] = $row[$alias];
    //         }
    //         if($aliassen["multiple"]) {
    //             $entries[$id] = $entry;
    //         } elseif(empty($entries)) {
    //             $entries = $entry;
    //         }
    //     }

    //     foreach ($aliassen["relations"] as $column_name => $relation) {
    //         if($aliassen["multiple"]) {
    //             if(!array_key_exists($column_name, $entries[$id])) {
    //                 if($relation["multiple"]) {
    //                     $entries[$id][$column_name] = array();                    
    //                 } else {
    //                     $entries[$id][$column_name] = null;                    
    //                 }
    //             }
    //             $this->add_entry_part($entries[$id][$column_name], $row, $relation);
    //         } else {
    //             if(!array_key_exists($column_name, $entries)) {
    //                 if($relation["multiple"]) {
    //                     $entries[$column_name] = array();                    
    //                 } else {
    //                     $entries[$column_name] = null;                    
    //                 }
    //             }
    //             $this->add_entry_part($entries[$column_name], $row, $relation);
    //         }
    //     }
    // }

    // //END SELECT ENTRIES

    // //INSERT ENTRIES

    // public function insert($model_list)
    // {
    //     $result = array();
    //     foreach ($model_list as $table_name => $models) {
    //         if(is_array($models)) {
    //             $result[$table_name] = $this->multiple_insert($models);
    //         } else {
    //             $result[$table_name] = $this->single_insert($models);
    //         }
    //     }
    //     return $result;
    // }

    // private function multiple_insert($models)
    // {
    //     //TODO multiple insert
    // }

    // private function single_insert($model)
    // {
    //     $table_name = $model->get_table_name();
    //     $insert_properties = $model->get_insert_properties();
    //     $columns = $this->get_insert_columns($insert_properties, $table_name);
    //     list($question_values, $values) = $this->get_insert_values($insert_properties);


    //     $query = "INSERT INTO {$table_name} {$columns} VALUES {$question_values}";
    //     return $this->prepared_query($query, array($values));
    // }

    // private function get_insert_columns($properties, $table_name)
    // {
    //     $short_column_names = array_keys($properties);
    //     $column_names = array_map( function($element) use ($table_name) {
    //         return "`{$table_name}`.`{$element}`";
    //     }, $short_column_names);
    //     return "(".implode(", ", $column_names).")";
    // }

    // private function get_insert_values($properties)
    // {
    //     $question_values = "(".implode(", ",array_fill(0, count($properties), "?")).")";
    //     $values = array();
    //     foreach ($properties as $column_name => $value) {
    //         $values[$column_name] = $this->get_insert_value($value);
    //     }
    //     return array(
    //         $question_values,
    //         $values,
    //     );
    // }

    // private function get_insert_value($value)
    // {
    //     $type = null;
    //     $new_value = null;

    //     switch(gettype($value)) {
    //         case "string":
    //             $type = "s";
    //             $new_value = (string) $value;
    //         break;
    //         case "boolean": case "integer":
    //             $type = "i";
    //             $new_value = (int) $value;
    //         break;
    //         case "double":
    //             $type = "d";
    //             $new_value = (double) $value;
    //         break;
    //         default:
    //             return false;
    //         break;
    //     }

    //     if($type !== null && $new_value !== null) {
    //         return array(
    //             "type" => $type,
    //             "value" => $new_value,
    //         );
    //     } else {
    //         return false;
    //     }
    // }

    // //END INSERT ENTRIES
    
    // //META DATA INFO

    // public function table_exists($model) 
    // {
    //     $name = is_string($model) ? $model : $model->get_table_name();
    //     $query = "SHOW TABLES LIKE '{$name}';";
    //     $result = $this->query($query);
    //     return $result->num_rows > 0;
    // }

    // public function key_exists($key, $model)
    // {
    //     $keys = $this->get_keys($model);
    //     foreach ($keys as $key_name => $key_object) {
    //         if($key == $key_name) {
    //             return true;
    //         }
    //     }
    //     return false;
    // }

    // public function get_keys($model)
    // {
    //     $keys = array();
    //     $name = is_string($model) ? $model : $model->get_table_name();
    //     $query = "SHOW INDEX FROM `{$name}`";
    //     $result = $this->query($query);
    //     while($result && $row = $result->fetch_assoc()) {
    //         $keys[$row["Key_name"]] = $row;
    //     }
    //     return $keys;
    // }

    // private function get_columns($model, $add_relations = false)
    // {
    //     $columns = array();
    //     $name = is_string($model) ? $model : $model->get_table_name();

    //     if($add_relations) {
    //         $relations = $this->get_relations($model);    
    //     }

    //     $query = "SHOW COLUMNS FROM `{$name}`;";
    //     $result = $this->query($query);
    //     while($result && $row = $result->fetch_assoc()) {
    //         if($add_relations && array_key_exists($row["Field"], $relations)) {
    //             $columns[$row["Field"]] = array_merge($row, $relations[$row["Field"]]);
    //         } else {
    //             $columns[$row["Field"]] = $row;
    //         }
    //     }
    //     return $columns;
    // }

    // private function get_relations($model)
    // {
    //     $relations = array();
    //     $name = is_string($model) ? $model : $model->get_table_name();
    //     $query = "SELECT COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_NAME = '{$name}'";
    //     $result = $this->query($query);
    //     while ($result && $row = $result->fetch_assoc()) {
    //         if(!empty($row["REFERENCED_TABLE_NAME"])) {
    //             $relations[$row["COLUMN_NAME"]] = $row;
    //         }
    //     }
    //     return $relations;
    // }

    // //END META DATA INFO

    // //QUERIES

    // public function update_create_models($model_names = null)
    // {
    //     if($model_names !== null) {
    //         $result = $model_names;
    //     } else {
    //         $model_table = new \Managements\Database\Model();
    //         $model_table->add_select_variable("name");
    //         $result = $this->select($model_table);
    //     }

    //     foreach ($result as $id => $model) {
    //         if(is_string($model)) {
    //             $model_name = $model;
    //         } else {
    //             $model_name = $model["name"];
    //         }

    //         $model = new $model_name();

    //         if($this->table_exists($model)) {
    //             $this->alter($model);
    //         } else {
    //             $this->create($model);
    //         }
    //     }
    // }

    // public function get_model_by_table_name($table_name)
    // {
    //     $model_table = new \Managements\Database\Model();
    //     $model_table->add_select_variable("name");
    //     $model_table->add_select_constraint("table", "'{$table_name}'");
    //     return $this->select($model_table);
    // }

    // //END QUERIES
    
    // //GETTERS AND SETTERS
    // public function get_queries()
    // {
    //     return $this->queries;
    // }
}

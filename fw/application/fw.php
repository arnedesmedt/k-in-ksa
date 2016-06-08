<?php
namespace FW;

/**
* Global
*/
class FW
{
	/**
	 * Dump the variable in a specific color. The back trace is also added.
	 */
	public static function dump($dump, $color = "black")
	{
		$backtrace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT);
		echo "<pre style='color: ".$color."'>";
		print_r($dump);
		echo "<br/><ul>";
		for($i = count($backtrace)-1; $i > 0; $i--) {
			if(array_key_exists("file", $backtrace[$i])) {
				echo "<li>".$backtrace[$i]["function"]." - called in ".basename($backtrace[$i]['file'])." on line {$backtrace[$i]['line']}</li>";
			}
		}
		echo "</ul>";
		echo "<br/><br/>FW_trace_array called at ".$backtrace[1]["function"]." => <b>".basename($backtrace[0]["file"]).":".$backtrace[0]["line"]."</b>";
		echo "</pre>";
	}

	/**
	 * Throw an exception
	 */
	public static function error($message, $type = "error")
	{
		switch($type) {
			case "error":
				$type = E_USER_ERROR;
			break;
			case "warning":
				$type = E_USER_WARNING;
			break;
			case "notice":
				$type = E_USER_NOTICE;
			break;
			default:
				$type = E_USER_WARNING;
			break;
		}
		trigger_error($message, $type);
	}

	/**
	 * Get the previous key based on the current key.
	 */
	public static function get_prev_key($key, $array = array(), $no_borders = true)				
	{
		$keys = array_keys($array);
		$found_index = array_search($key, $keys);
		if ($found_index === false) {
			return false;
		} else if($found_index === 0) {
			return $no_borders ? end($array) : false;
		} else {
		    return $keys[$found_index-1];
		}
	}

	/**
	 * Get the next key based on the current key.
	 */
	public static function get_next_key($key, $array = array(), $no_borders = true)				
	{
		$keys = array_keys($array);
		$found_index = array_search($key, $keys);
		if ($found_index === false) {
			return false;
		} else if($found_index === count($keys)-1) {
			return $no_borders ? reset($array) : false;
		} else {
		    return $keys[$found_index+1];
		}
	}

	/**
	 * Copy tree structure recursive.
	 */
	public static function recursive_copy($source_path, $target_path)
	{
		$directory = opendir($source_path); 
	    @mkdir($target_path); 
	    while(false !== ( $file = readdir($directory)) ) { 
	        if (( $file != "." ) && ( $file != ".." )) { 
	            if (is_dir($source_path."/".$file)) { 
	                self::recursive_copy($source_path."/".$file,$target_path."/".$file); 
	            } 
	            else { 
	                copy($source_path."/".$file,$target_path."/".$file); 
	            } 
	        } 
	    } 
	    closedir($directory); 
	}

	/**
	 * Get files of a specific directory.
	 */
	public static function get_files($directory_name, $regex = null)
	{
		$recursive_directory_iterator = new \RecursiveDirectoryIterator($directory_name, \RecursiveDirectoryIterator::FOLLOW_SYMLINKS);
		$recursive_iterator_iterator = new \RecursiveIteratorIterator($recursive_directory_iterator);
		if(!empty($regex)) {
			return new \RegexIterator($recursive_iterator_iterator , $regex);
		} else {
			return $recursive_iterator_iterator;
		}
	}

	/**
	 * Get the line of parents from a certain object.
	 * @param Object $object
	 * @return array An array of strings with the parent class names
	 */
	public static function get_parents_class($object)
	{
		$reflection_class = new \ReflectionClass($object);
		$line = array();
		while ($reflection_class = $reflection_class->getParentClass()) {
		    $line[] = $reflection_class->getName();
		}
		return $line;
	}

	/**
	 * Implode all recursive array items with glue.
	 * @param string $glue
	 * @param array $array
	 * @param boolean $delete_empty delete an array when the result of the implode is empty.
	 */
	public static function implode_recursive($glue, $array, $delete_empty = true)
	{
		foreach ($array as $key => &$value) {
			if(is_array($value)) {
				$value = self::implode_recursive($glue, $value);
				if($delete_empty && empty($value)) {
					unset($array[$key]);
				}
			}
		}
		return implode($glue, $array);
	}
}
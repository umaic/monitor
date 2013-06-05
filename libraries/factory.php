<?php
/**
 * Main controller
 *
 * @package     Monitor
 * @link 		http://monitor.colombiashh.org/api
 */
class Factory
{
    // The parameterized factory method
    public static function create($type)
    {
        require dirname( __FILE__ ).'/../config.php';

        $lib_dir = $config['libraries'];
        
        $cls = array(
			'file' 	=> 'FileManager',
		);
        
        if ($type != 'mysql') {
        
            $f = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $cls[$type])); 

            if (include_once $lib_dir.'/'.$f.'.php') {
                
                $classname = $cls[$type];

                return new $classname;

            } 
            else {
                throw new Exception ("$type in DAO Class not found");
            }
        }
        else {
            if (include_once $lib_dir.'/mysql.php') {
                
                return Mysql::getInstance();

            } 
            else {
                throw new Exception ("$type in DAO Class not found");
            }

        }
    }
}
?>

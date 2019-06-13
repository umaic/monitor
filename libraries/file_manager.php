<?php
class FileManager {

	var $path;

	function setPath($path){
		$this->path = $path;
	}

	function guardar($archivo){
		if (@copy($archivo,$this->path)){
			return true;
		}
		else
			return false;
	}

	function open($path,$p){
		return fopen($path,$p);
	}

	function write($fp,$c){
		return fwrite($fp,$c);
	}
    
    function read($fp, $p) {
		$c = fread($fp, filesize($p));
        return $c;
    }

	function delete($path){
		unlink($path);
	}

	function close($fp){
		fclose($fp);
	}

	function exists($path){
		return file_exists($path);
	}

	//Fecha de modificacion, si no existe devuelve 0
	function updateDate($path){
		return filemtime($path);
	}
}
?>

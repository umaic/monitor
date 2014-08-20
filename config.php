<?php 

// Se usa en libraries

$config['base'] = '';

if (empty($_SERVER['DOCUMENT_ROOT'])) {
    $root = dirname(__FILE__);
}
else {
    $root = $_SERVER['DOCUMENT_ROOT'].'/'.$config['base'];
}

$config['base_path'] = $root;
$config['libraries'] = $config['base_path'].'/libraries';

?>

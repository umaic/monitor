<?php 

// Se isa en libraries
$config['base'] = ($_SERVER['SERVER_NAME'] == 'localhost') ? 'monitor' : '';
$config['base_path'] = $_SERVER['DOCUMENT_ROOT'].'/'.$config['base'];
$config['libraries'] = $config['base_path'].'/libraries';
?>

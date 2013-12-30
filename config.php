<?php 

// Se isa en libraries
$config['base'] = ($_SERVER['SERVER_NAME'] == 'localhost' || $_SERVER['SERVER_NAME'] == '190.66.6.168') ? 'monitor' : '';
$config['base_path'] = $_SERVER['DOCUMENT_ROOT'].'/'.$config['base'];
$config['libraries'] = $config['base_path'].'/libraries';
?>

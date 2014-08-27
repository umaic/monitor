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
$config['cache_pdf'] = $config['base_path'].'/ss';
$config['cache_reportes'] = $config['base_path'].'/z';
$config['yyyy_ini'] = 2008;
$config['nombre_reporte_csv'] = 'incidentes'; 

?>

<?php 
/** 
 * Monitor(tm) : Rapid Development Framework (http://monitor.colombiassh.org)
 * Copyright 2012, OCHA Colombia (http://colombiassh.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * PHP version 5
 *
 * @category  Script
 * @package   Monitor
 * @author    Ruben Rojas <rojasr@un.org>
 * @copyright 2012 OCHA Colombia (http://colombiassh.org)
 * @license   MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @link      http://monitor.colombiassh.org Monitor
*/

$cli = (isset($argv)) ? true : false;

if ($cli === false) {
    session_start();
}

// PHP >= 5.3
//date_default_timezone_set('America/Bogota');


$base = '/';
if (isset($_SERVER['SERVER_NAME'])) {
    if (in_array($_SERVER['SERVER_NAME'], array('localhost','190.66.6.168'))) {
        $base = '/monitor/';  // Comienza con slash, se usa al incluir los assets
    } 
}

define ('BASE', $base);  // Comienza con slash, se usa al incluir los assets

include "config.php";
require 'controllers/monitor.php';
$mc = new MonitorController;

if (isset($_SESSION)) {
    if (empty($_SESSION['mapa_tipo'])) {
        $_SESSION['mapa_tipo'] = 'afectacion';
    }

    if (empty($_SESSION['acceso'])) {
        $_SESSION['acceso'] = 0;
    }
}

// Get o CLI
$m = '';

if ($cli) {
    $m = $argv[1];
}
else {

    if (!empty($_GET) && isset($_GET['m'])) {
        $m = $_GET['m'];
    }
}
// Clean URL
if (!empty($m)) {
    switch($m) {
        case 'totalxd':
            
            if (!empty($_GET['ini']) && is_numeric($_GET['ini'])) {
                $ini = $_GET['ini'];
            } 
            if (!empty($_GET['fin']) && is_numeric($_GET['fin'])) {
                $fin = $_GET['fin'];
            }

            $cats = (empty($_GET['c'])) ? array() : $_GET['c'];
            $states = (empty($_GET['states'])) ? array() : $_GET['states'];
            $afectacion = (empty($_GET['afectacion'])) ? 0 : $_GET['afectacion'];
            $totalxd = $mc->totalxd($ini, $fin, $cats, $afectacion, $states);
            
            header('Content-type: text/json');
            header('Content-type: application/json'); 
            echo json_encode($totalxd);
        break;
        
        case 'getIncidentesPortal':
            
            if (!empty($_GET['ini']) && is_numeric($_GET['ini'])) {
                $ini = $_GET['ini'];
            } 
            if (!empty($_GET['fin']) && is_numeric($_GET['fin'])) {
                $fin = $_GET['fin'];
            }

            $cats = (empty($_GET['c'])) ? array() : $_GET['c'];
            $states = (empty($_GET['states'])) ? array() : $_GET['states'];
            $incidentes = $mc->getIncidentesPortal($ini, $fin, $cats, $states, $_GET['limiti']);
            
            header('Content-type: text/json');
            header('Content-type: application/json');
            echo $_GET['callback'] . '('.json_encode($incidentes).');';
        break;
        
        case 'getResumenPortalHome':
            
            if (!empty($_GET['ini']) && is_numeric($_GET['ini'])) {
                $ini = $_GET['ini'];
            } 
            if (!empty($_GET['fin']) && is_numeric($_GET['fin'])) {
                $fin = $_GET['fin'];
            }

            $incidentes = $mc->getResumenPortalHome($ini, $fin);
            
            header('Content-type: text/json');
            header('Content-type: application/json');
            echo $_GET['callback'] . '('.json_encode($incidentes).');';
        break;
    
        case 'export':
            $mc->export($_GET['t'],$_GET['csv'],$_GET['nom']);
        break;
        
        case 'download_incidents':
            $mc->downloadIncidents();
        break;

        case 'session_var':
            $mc->setSessionVar($_GET['var'], $_GET['valor']);
        break;

        case 'genCachePdfDiario':
            $mc->genCachePdfDiario();
        break;
    }
}
else {
    $layout = (empty($_GET['layout'])) ? 'monitor' : $_GET['layout'];
    $fl = dirname( __FILE__ ).'/views/'.$layout.'.php';

    //$ini = strtotime("-1 week");
    $ini = strtotime(date('Y')."-1-1");
    $fin = strtotime(date('Y')."-12-31");

    $cats_hide = array('ec' => array(
                                    52, //C. Complementaria: Homicidio
                                    53  // C. Complementaria: Intento de homicidio
                                    ),
                       'dn' => array(0)
                      );
    
    // Se usaba este para mostrar el total por años
    //$totalxy = $mc->total($cats_hide);
    $yyyy = date('Y');
    for ($a=$yyyy;$a>=2008;$a--) {
        $totalxy[] = $a;
    }
    
    $_t = $mc->totalecdn();
    $tec = $_t['ec'];
    $tdn = $_t['dn'];

    if ($layout != 'portal_home') {
        // Categorias para filtros
        $cats_db = $mc->getCats();
        $cats_f = $cats_db['tree'];
        $cats_u = (empty($_GET['c'])) ? $cats_db['h'] : explode(',', $_GET['c']);
    }

    switch($layout) {
        case 'portal':
            $state_id = 0;
            $centroid = '';
            if (!empty($state)){
                $state_info = $mc->getStateCentroid($state);
                
                $state_id = $state_info[0];
                $centroid = $state_info[1];
            }

        break;
    }

    if (file_exists($fl)) {
        require $fl;
    }
    else {
        echo '._. No layout ._.';
    }
    
}

?>

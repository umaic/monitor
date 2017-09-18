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
 * @copyright 2016 UMAIC Colombia (http://umaic.org)
 * @license   MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @link      http://monitor.umaic.org Monitor
*/

$cli = (isset($argv)) ? true : false;

if ($cli === false) {
    session_start();

    if (strpos($_SERVER['SERVER_NAME'], 'umaic') === false &&
        strpos($_SERVER['SERVER_NAME'], 'local') === false &&
        strpos($_SERVER['SERVER_NAME'], 'portal2')) {
        header('Location: http://monitor.umaic.org');
    }
}

// PHP >= 5.3
//date_default_timezone_set('America/Bogota');
///   define ('BASE', '/'); /// para online
define ('BASE', '/');  // Comienza con slash, se usa al incluir los assets

require 'config.php';
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
$mod = '';

if ($cli) {
    $mod = $argv[1];
}
else {
    if (!empty($_GET) && isset($_GET['mod'])) {
        $mod = $_GET['mod'];
    }
}

// Clean URL
if (!empty($mod)) {
	
    switch($mod) {
        case 'totalxd':

            if (!empty($_GET['ini']) && is_numeric($_GET['ini'])) {
                $ini = $_GET['ini'];
            }
            if (!empty($_GET['fin']) && is_numeric($_GET['fin'])) {
                $fin = $_GET['fin'];
            }

            $cats = (empty($_GET['c'])) ? '' : $_GET['c'];
            $states = (empty($_GET['states'])) ? '' : $_GET['states'];
            //$afectacion = (empty($_GET['afectacion'])) ? 0 : $_GET['afectacion'];

            $totalxd = $mc->totalxd($ini, $fin, $cats, $states);

            header('Content-type: text/json');
            header('Content-type: application/json');

            echo $totalxd;
        break;

        case 'getIncidentesPortal':

            if (!empty($_GET['ini']) && is_numeric($_GET['ini'])) {
                $ini = $_GET['ini'];
            }
            if (!empty($_GET['fin']) && is_numeric($_GET['fin'])) {
                $fin = $_GET['fin'];
            }

            $cats = (empty($_GET['c'])) ? '' : $_GET['c'];
            $states = (empty($_GET['states'])) ? '' : $_GET['states'];
            $limiti = (empty($_GET['limiti'])) ? 0 : $_GET['limiti'];

            $incidentes = $mc->getIncidentesPortal($ini, $fin, $cats, $states, $limiti);
            header('Content-type: text/json');
            header('Content-type: application/json');

            $cb = false;
            if (isset($_GET['callback'])) {
                echo $_GET['callback'].'(';
                $cb = true;
            }

            echo json_encode($incidentes);

            if ($cb) {
                echo ');';
            }

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
            $mc->downloadIncidents($_GET['f']);
        break;

        case 'session_var':
            $mc->setSessionVar($_GET['var'], $_GET['valor']);
        break;

        case 'genCachePdfDiario':
            $mc->genCachePdfDiario();
        break;

        case 'genCacheReportesDiario':
            $mc->genCacheReportesDiario();
        break;

        case 'genCacheTotalesDiario':
            $mc->genCacheTotalesDiario();
        break;

        case 'totalPeriodo':
            $mc->totalPeriodo($_GET['vd'],$_GET['p'],$_GET['v']);
        break;

        case 'checkCacheJson':
            $mc->checkCacheJson();
        break;

        case 'variacion':
            $p1 = $_GET['p1'];
            $p2 = $_GET['p2'];
            $ecdn = $_GET['ecdn'];

            $cats = (empty($_GET['c'])) ? array() : $_GET['c'];
            $states = (empty($_GET['states'])) ? array() : $_GET['states'];

            header('Content-type: text/json');
            header('Content-type: application/json');

            echo $mc->variacion($p1,$p2,$ecdn,$cats,$states);
        break;

        case 'geojson':

            header('Content-type: text/json');
            header('Content-type: application/json');

            $qs = $_GET;
            unset($qs['mod']);
            unset($qs['cluster']);
            unset($qs['server']);

            $server = $_GET['server'];

            echo $mc->genJson($server.'/json/'.$_GET['cluster'].'/?', $qs);

        break;

        default:
            echo "<img src='media/img/logo.png'>";
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
    for ($a=$yyyy;$a>=$config['yyyy_ini'];$a--) {
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

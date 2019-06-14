<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="shortcut icon" href="https://monitor.salahumanitaria.co/favicon.ico" />
<title>OCHA Colombia Monitor Humanitario</title>
    <link href="https://fonts.googleapis.com/css?family=Roboto&display=swap" rel="stylesheet">
<?php
date_default_timezone_set('America/Bogota');
//echo date('Y-m-d H:i:s', intval('2017-01-02'));
$dev = true;

if ($dev) { ?>
    <link type="text/css" rel="stylesheet" href="<?php echo BASE ?>media/css/brand.css" />
    <link type="text/css" rel="stylesheet" href="<?php echo BASE ?>media/css/ol.css" />
    <link type="text/css" rel="stylesheet" href="<?php echo BASE ?>media/css/fe.css" />
    <link type="text/css" rel="stylesheet" href="<?php echo BASE ?>media/css/orange.css" />
    <link type="text/css" rel="stylesheet" href="<?php echo BASE ?>media/css/fa/css/font-awesome.min.css" />
    <link type="text/css" rel="stylesheet" href="https://ajax.aspnetcdn.com/ajax/jquery.ui/1.12.1/themes/flick/jquery-ui.css" />
    <link type="text/css" rel="stylesheet" href="<?php echo BASE ?>media/css/jquery.dataTables.min.css" />
    <link type="text/css" rel="stylesheet" href="<?php echo BASE ?>media/css/popover.css" />
    <link type="text/css" rel="stylesheet" href="<?php echo BASE ?>media/css/geostats.css" />
    <link type="text/css" rel="stylesheet" href="<?php echo BASE ?>media/css/nouislider.css" />
    <link type="text/css" rel="stylesheet" href="<?php echo BASE ?>media/css/HoldOn.min.css" />
<?php
}
else { ?>
    <link type="text/css" rel="stylesheet" href="<?php echo BASE ?>media/css/m.css?v={random number/string}" />
<?php
}
?>
</head>

<?php

$sala = 'salahumanitaria.co';
$meses = array('Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre');
$url_violencia = "violenciaarmada/reports/submit";
$url_desastres = "desastres/reports/submit";
$ayer_time = strtotime('-1 Day');
$ayer = date('d', $ayer_time).' de '.$meses[date('n', $ayer_time) - 1].' de '.date('Y', $ayer_time);

// Test geonode server
$geonode = true;

function filesize_formatted($path)
{
    $size = filesize($path);
    $units = array( 'B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
    $power = $size > 0 ? floor(log($size, 1024)) : 0;
    return number_format($size / pow(1024, $power), 2, '.', ',') . ' ' . $units[$power];
}

?>

<body>
    <div id="loading" class="alpha60">
        <img src="<?php echo BASE ?>media/img/ajax-loader.png" />
    </div>
    <div id="brand">
        <div id="ocha_div" class="left">
            <a href="http://salahumanitaria.co" target="_blank">
                <div id="ocha" class=""></div>
            </a>
        </div>
        <div id="logo_div" class="right">
            <div id="logo" class=""></div>
        </div>
        <div class="right">
            <!-- <ul>
                <li><a id="lmh" href="#">¿Qué es <b>Monitor</b>?</a></li>
                <li><a href="https://salahumanitaria.co" target="_blank">https://salahumanitaria.co</a></li>
                <li><a href="https://sidi.salahumanitaria.org" target="_blank">Sidi</a></li>
                <li><a href="https://geonode.salahumanitaria.co" target="_blank">Geonode</a></li>
                <li><a href="https://wiki.salahumanitaria.co" target="_blank">Wiki</a></li>
            </ul> -->
        </div>
        <div class="clear"></div>
    </div>
    <div id="menu_div" class="left">
        <div id="menu" class="">
            <ul>
                <!-- <li id="fen_nino" class="featured dn" style="background:url(media/img/fenomeno_nino_bg.jpg);">
                    <h3 style="font-size: 16px !important;margin:3px !important;">
                    Fenómeno del niño
                    </h3>
                    <div class="note">
                        Sequías, incendios forestales e inundaciones desde Marzo 2015
                        <br /><br />
                        <h3 style="font-size:15px ;margin:0"><i class="fa fa-map-marker"></i> <u>Ver Eventos</u></h3>
                    </div>

                </li> -->
                <li id="tec">
                    <img src="<?php echo BASE ?>media/img/logo_ec_compact.png" border="0" class="left" />
                    <a href="<?php echo $url_violencia ?>" target="_blank">
                        Reportar un evento de violencia
                    </a>
                    <!--
                    <div class="it tot">
                        [ Total Eventos: <b><?php echo number_format($tec) ?></b> ]
                    </div>-->
                </li>
                <li id="tdn">
                    <img src="<?php echo BASE ?>media/img/logo_dn_compact.png" border="0" class="left" />
                    <a href="<?php echo $url_desastres ?>" target="_blank">
                        Reportar un evento de desastres
                    </a>
                    <!--
                    <div class="it tot">Total Eventos: <b><?php echo number_format($tdn) ?></b></div>
                    -->
                </li>
                <li><i class="fa fa-filter fa-2x"></i> OPCIONES</li>
                <li class="sub" data-div="ini_fin">
                    <i class="fa fa-2x fa-calendar fa-pull-left"></i>Filtrar por fecha
                </li>
                <li class="sub" data-div="fcat_dn">
                    <i class="fa fa-2x fa-fire fa-pull-left"></i>Categorias de desastres
                </li>
                <li class="sub" data-div="fcat_ec">
                    <i class="fa fa-2x fa-bullseye fa-pull-left"></i>
                    Categorias de violencia
                </li>
                <li class="sub" data-div="variacion">
                    <i class="fa fa-2x fa-line-chart fa-pull-left"></i>
                    Calcular variación
                </li>
                <li class="sub menu_totales" data-div="totales">
                    <i class="fa fa-2x fa-table fa-pull-left"></i>
                    Totales por año
                </li>
                <li class="sub" data-div="descargar">
                    <i class="fa fa-2x fa-download fa-pull-left"></i>
                    Descargar eventos
                </li>
                <!--<li class="sub" data-div="fcat_acceso"><span class="menu_acceso">Restricci&oacute;n al acceso</span></li>-->
                <li class="sub hide" data-div="fcat_1612"><span class="menu_1612">Menores en conflicto</span></li>
            </ul>
            <div>
                <br /><br />
                &nbsp;&nbsp;
                <!-- <img src="media/img/logo_OCHA.png" /> -->
                &nbsp;
                <!-- <img src="media/img/logo_PNUD.jpg" /> -->
            </div>
        </div>
    </div>
    <div id="menua" class="hide">
        <div id="aaaa">
            <input type="hidden" id="currentCatE" value="0">
            <input type="hidden" id="currentCatD" value="0">
            <input type="hidden" id="yyyy_ini" value="">
            <input type="hidden" id="yyyy_fin" value="">
            <?php 
            ?>
        </div>
    </div>
    <div id="content_div" class="left">
        <div id="header">
            <div id="b" class="">
                <div id="colombia" class="left">
                    COLOMBIA
                </div>
                <div id="titulo_general" class="left">
                    <div id="tgt" class=""></div>
                    <div id="tgc" class=""></div>
                </div>
                <div id="submenu" class="right">
                    <!--<div id="collapse" class="collapse left"></div>-->
                    <div class="mapa_tipo menu_activo left op" data-tipo="afectacion">
                        <i class="fa fa-users fa-2x"></i><br />Afectados
                    </div>
                    <div class="mapa_tipo left op" data-tipo="eventos">
                         <i class="fa fa-map-marker fa-2x"></i><br />Eventos
                    </div>
                    <div id="group_fts" class="mapa_tipo left op">
                        <i class="fa fa-spinner fa-2x"></i><br /> Desagrupar</span>
                    </div>
                    <div id="layers" class="left op">
                         <i class="fa fa-bars fa-2x"></i><br />+ Capas
                    </div>
                    <div id="depto" class="left op">
                        <div class="select">
                            <div class="inline">
                                <div id="depto_t">Colombia</div>
                            </div>
                            <div class="inline dropdown"></div>
                        </div>
                        <ul id="depto_dropdown" class="hide">
                            <li>Colombia</li>
                        </ul>
                    </div>
                    <!--<div class="expand left"></div>-->
                </div>
                <div id="qlmh" class="hide">
                    <div id="qmm" class="left">
                        <h2>&iquest;Qu&eacute; es y para qu&eacute; sirve <b>Monitor</b>?</h2>
                        <b>MONITOR</b> es una herramienta administrada por OCHA que est&aacute; a disposici&oacute;n de la comunidad humanitaria en Colombia y del p&uacute;blico en general con el objetivo de recopilar, categorizar y georreferenciar eventos de violencia armada y de desastres naturales.
                        <br /><br />
                        <b>MONITOR</b> se alimenta de m&uacute;ltiples fuentes como medios de comunicaci&oacute;n locales y nacionales, &nbsp;instituciones del Estado, organizaciones de la sociedad civil e informaci&oacute;n recopilada en terreno por socios humanitarios nacionales e internacionales, entre otros. No se trata de un instrumento de verificaci&oacute;n de cada uno de los eventos reportados y no pretende reflejar la totalidad de la afectaci&oacute;n humanitaria.&nbsp;
                    </div>
                    <div class="left">
                        <br /><br />
                        <iframe width="400" height="225" src="//www.youtube.com/embed/MI1XWaq58os" frameborder="0" allowfullscreen></iframe>
                    </div>
                    <div class="left">
                        <br><br>S&oacute;lo para categor&iacute;as espec&iacute;ficas de <b>MONITOR</b> como el desplazamiento masivo o el confinamiento, OCHA puede hacer seguimiento en profundidad en zonas con presencia de los Equipos Locales de Coordinación (conformados por agencias de Naciones Unidas y ONG internacionales) y otros socios en terreno. El Monitor se alimenta y revisa en tiempo real por varios alimentadores en Bogot&aacute; y las regiones, por tanto es posible que la informaci&oacute;n pueda variar, dependiendo del momento de consulta.
                        La informaci&oacute;n de <b>MONITOR</b> no refleja o compromete la posici&oacute;n del Equipo Humanitario de Pa&iacute;s o de Naciones Unidas.
                        <br>
                    </div>
                </div>
            </div>
            <div class="clear"></div>
        </div>

        <!-- Content -->
        <div id="content">
            <div id="map" class="map_monitor">
                <div id="popup"></div>
            </div>
            <div id="featured" class="hide">
                <div><b>Eventos destacados por:</b></div>
                <div id="t">- Movilizaci&oacute;n social <br />- Paro</div>
            </div>
            <div id="variacion_legend">
            </div>
            <div id="layers_div" class="hide filtro">
                <div class="left">
                    <div class="left">
                        <h1 class="dosis">Adicionar capas al mapa</h1>
                    </div>
                    <div id="layers_loading" class="hide right">
                        <img src="media/img/ajax-loader-mini.gif" /><span>Cargando capa....</span>
                    </div>
                    <div class="clear"></div>
                </div>
                <div class="right">
                    <a data-div="layers_div" href="#" class="close"><img alt="Cerrar" src="media/img/close.png"></a>
                </div>
                <div class="clear"></div>
                <?php 
                if ($geonode) { ?>
                    <p>
                        Capas disponibles en el sistema <a href="http://geonode.salahumanitaria.co" target="_blank">GEONODE</a>
                        de Sala Humanitaria</a>
                    </p>
                    <div class="left">
                        <input type="text" id="layers_search" name="" value="" placeholder="Buscar por palabra en nombre o descripci&oacute;n" />
    <!--                    &nbsp;<a href="#" id="geonode_limpiar">Limpiar</a>-->
                    </div>
                    <div class="clear"></div>
                    <ul id="layers_ul">
                        <!-- Se carga en fe.js --> 
                    </ul>
                <?php
                }
                else { ?>
                    <p>El servicio: <a href="http://geonode.<?php echo $sala ?>" target="_blank">http://geonode.<?php echo $sala ?></a> no se encuentra disponible</p>
                <?php
                }
                ?>
            </div>
            <div id="slide_cluster">

                <div id="slide_cluster_text">
                    Deslice la barra para cambiar el agrupamiento
                </div>
                <div id="slide_cluster_bar">
                </div>
                <input type="hidden" id="group_level" value="0" />
            </div>
            <div id="totalxd" class="">
                <div id="loading_data" class="alpha60">
                    <img src="<?php echo BASE ?>media/img/ajax-loader.png" />
                        &nbsp;Cargando datos....
                </div>
                <!-- Data right -->
                <div id="data">
                    <!-- Tabs -->
                    <div id="tabs">
                        <div id="minmax_total" class="minimize"></div>
                        <ul>
                            <li><a href="#tendencia">Resumen</a></li>
                            <li><a href="#resumen">Categorias</a></li>
                            <li><a href="#departamentos">Departamentos</a></li>
                        </ul>
                        <div id="tendencia">
                            <div id="chart_t_s">
                                <div class="left" id="chart_total">
                                    <div class="ec ct">
                                        <div class="total_n" id="chart_total_v"></div>
                                        <div class="total_t">total violencia</div>
                                    </div>
                                    <div class="dn ct">
                                        <div class="total_n" id="chart_total_d"></div>
                                        <div class="total_t">total desastres</div>
                                    </div>
                                </div>
                                <div class="left ec" id="chart_subtotal">
                                    <div>
                                        <div class="left">
                                            <div class="subtotal_n" id="civiles"></div>
                                            <div class="subtotal_t">civiles</div>
                                        </div>
                                        <div class="left">
                                            <div class="subtotal_n" id="afros"></div>
                                            <div class="subtotal_t">afro </br>colombianos</div>
                                        </div>
                                        <div class="left">
                                            <div class="subtotal_n" id="indigenas"></div>
                                            <div class="subtotal_t">indígenas</div>
                                        </div>
                                        <div class="clear"></div>
                                    </div>
                                    <div>
                                        <div class="left">
                                            <div class="subtotal_n" id="mujeres"></div>
                                            <div class="subtotal_t">mujeres</div>
                                        </div>
                                        <div class="left">
                                            <div class="subtotal_n" id="hombres"></div>
                                            <div class="subtotal_t">hombres</div>
                                        </div>
                                        <div class="left">
                                            <div class="subtotal_n" id="menores"></div>
                                            <div class="subtotal_t">niños</div>
                                        </div>
                                        <div class="clear"></div>
                                    </div>
                                </div>
                                <div class="clear"></div>
                            </div>
                            <div id="chart_1" class="chart"></div>
                            <div id="charts_pie">
                                <div class="ec"><h2>Violencia Armada</h2></div>
                                <div id="chart_2" class="chart "></div>
                                <div id="chart_3" class="chart "></div>
                                <div id="chart_4" class="chart hide"></div>
                                <div id="chart_5" class="chart hide"></div>
                            </div>
                        </div>
                        <div id="resumen">
                            <div class="div_table_totalxd">
                                <div id="resumen_ec" class="hide">
                                    <div id="resumen_total_ec" class="ec resumen_total">
                                        <div id="resumen_total_ec_num" class="num"></div>
                                        <div class="cat bold">Total de
                                            <span class="data_title">
                                                Personas afectadas
                                            </span>
                                        </div>
                                    </div>
                                    <div class="hide resumen_row">
                                        <div class="cat_color hide">&nbsp;</div>
                                        <div class="num"></div>
                                        <div class="cat"></div>
                                    </div>
                                </div>
                                <div id="resumen_dn" class="hide">
                                    <div id="resumen_total_dn" class="dn resumen_total">
                                        <div id="resumen_total_dn_num" class="num"></div>
                                        <div class="cat">Total de
                                            <span class="data_title">
                                                Personas afectadas
                                            </span>
                                        </div>
                                    </div>
                                    <div class="hide resumen_row">
                                        <div class="num"></div>
                                        <div class="cat"></div>
                                    </div>
                                </div>
                                <div class="clear"></div>
                            </div>
                        </div>
                        <div id="departamentos">
                            <div id="div_table_totalxd" class="div_table_totalxd">
                                <table id="table_totalxd">
                                    <thead>
                                        <tr>
                                            <!--<th><input type="checkbox" id="totalxd_all_chk" value="0" checked></th>-->
                                            <th class="d"></th><th class="ec">Violencia</th><th class="dn">Desastres</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <!-- /Tabs -->
                    
                    <!-- Tabla variacion -->
                    <div id="variacion_data" class="hide">
                    </div>
                    <!-- /Tabla variacion -->
                </div>
                <!--/ Data right -->
                
                <div class="clear"></div>
            </div>
            
            <!-- DIV PARA OPCIONES DE MENU IZQUIERDO, ABSOLUTAS A CONTENT -->
            <!-- Filtro categorias Violencia -->
            <div id="fcat_ec" class="filtro fcat" data-index="2">
                <div class="left">
                     <h2 class="dosis">Categorias Violencia Armada</h2>
                     <br />
                    <div class="inline linko">
                        <a href="https://wiki.salahumanitaria.co/wiki/Sistema_de_categor%C3%ADas_del_m%C3%B3dulo_de_eventos_de_conflicto" target="_blank">&nbsp;Definici&oacute;n de categorias</a>
                    </div>
                    <div class="inline">
                    |&nbsp;<a class="tn_fcat" href="#">Seleccionar todas/ninguna</a>
                    </div>
                </div>
                <div class="right">
                    <a class="close" href="#" data-div="fcat_ec"><img src="<?php echo BASE ?>media/img/close.png" alt="Cerrar" /></a>
                </div>
                <div class="right">
                    <div id="" class="btn btn_show_e" data-s="ec">Ocultar eventos</div>
                    <div id="btn_fcat_ec" class="btn btn_fcat" data-inst="ec">Filtrar mapa</div>
                </div>
                <div class="fcat_list">
                    <?php 
                    foreach($cats_f['ec'] as $_cp => $_cts) { ?>
                        <div>
                            <ul class="cats">
                                <li class="p">
                                    <?php
                                    $chk = 'checked';
                                    if (strpos($_cp, 'Complementa')) {
                                            $chk = '';
                                    }
                                    ?>
                                    
                                    <input type="checkbox" id="<?php echo $_cp ?>" value="" class="cp" <?php echo $chk ?> />
                                    <label for="<?php echo $_cp ?>"><?php echo $_cp ?></label>
                                </li>
                                <?php
                                //$_h = count($_ch);
                                foreach($_cts as $_idh => $_ch) { 
                                    $_id = "cat_ec_$_idh";
                                    $chk = 'checked';
                                    if (!in_array($_idh, $cats_u) || in_array($_idh, $cats_hide['ec'])) {
                                            $chk = '';
                                    }
                                    ?>
                                    <li class="h">
                                        <input type="checkbox" id="<?php echo $_id ?>" name="<?php echo $_id ?>" value="<?php echo $_idh; ?>" <?php echo $chk ?> class="ch" />
                                        <label for="<?php echo $_id ?>"><?php echo $_ch ?></label>
                                    </li>
                                <?php
                                }
                                ?>
                            </ul>
                        </div>

                    <?php
                    } 
                    ?>
                </div>
            </div>
            <!-- Filtro categorias Violencia :: Fin -->

            <!-- Filtro categorias Desastres -->
            <div id="fcat_dn" class="filtro fcat" data-index="1">
                <div class="left">
                     <h2 class="dosis">Categorias Desastres</h2>
                     <br />
                    <div class="inline linko">
                        <a href="https://wiki.salahumanitaria.co/wiki/Sistema_de_categor%C3%ADas_del_m%C3%B3dulo_de_eventos_de_conflicto" target="_blank">&nbsp;Definici&oacute;n de categorias</a>
                    </div>
                    <div class="inline">
                    |&nbsp;<a class="tn_fcat" href="#">Seleccionar todas/ninguna</a>
                    </div>
                </div>
                <div class="right">
                    <a class="close" href="#" data-div="fcat_dn"><img src="<?php echo BASE ?>media/img/close.png" alt="Cerrar" /></a>
                </div>
                <div class="right">
                    <div id="btn_show_dn" class="btn btn_show_e" data-s="dn">Ocultar eventos</div>
                    <div id="btn_fcat_dn" class="btn btn_fcat" data-inst="dn">Filtrar mapa</div>
                </div>
                <div class="fcat_list">
                    <?php 
                    foreach($cats_f['dn'] as $_cp => $_cts) { ?>
                        <div>
                            <ul class="cats">
                                
                                <li class="p">
                                    <input type="checkbox" id="<?php echo $_cp ?>" value="" class="cp" checked />
                                    <label for="<?php echo $_cp ?>"><?php echo $_cp ?></label>
                                </li>

                                <?php
                                //$_h = count($_ch);
                                foreach($_cts as $_idh => $_ch) { 
                                    $_id = "cat_dn_$_idh";
                                    $chk = 'checked';
                                    if (!in_array($_idh, $cats_u) || in_array($_idh, $cats_hide['dn'])) {
                                            $chk = '';
                                        
                                    }
                                    ?>
                                    <li class="h">
                                        <input type="checkbox" id="<?php echo $_id ?>" name="<?php echo $_id ?>" value="<?php echo $_idh; ?>" <?php echo $chk ?> class="ch" />
                                        <label for="<?php echo $_id ?>"><?php echo $_ch ?></label>
                                    </li>
                                <?php
                                }
                                ?>
                            </ul>
                        </div>

                    <?php
                    } 
                    ?>
                </div>
            </div>
            <!-- Filtro categorias Desastres :: Fin -->

            <!-- Filtro fecha -->
            <div id="ini_fin" class="filtro fcat" data-index="0">
                <div class="left">
                     <h2 class="dosis">Filtrar monitor por periodo</h2>
                </div>
                <div class="right">
                    <a class="close" href="#" data-div="ini_fin"><img src="<?php echo BASE ?>media/img/close.png" alt="Cerrar" /></a>
                </div>
                <div class="right">
                    <div id="filter_period" class="btn btn_fcat">Filtrar mapa</div>
                </div>
                <div class="clear"></div>
                <div>
                    <fieldset class="left">
                        <legend>Periodo</legend>
                        <div class="r">
                            <label>Desde</label>
                            <?php 
                            $fecha_html = '<input type="text" id="q_val_text" class="fecha select" data-div="q_val_div" readonly />
                                <div class="filtro_fecha" id="q_val_div" data-if="if_val">
                                <div class="left">Seleccione a&ntilde;o, mes y d&iacute;a</div>
                                <div class="right close"></div>
                                <div class="clear"></div>
                                <div class="inline yyyy l">
                                    <p><b>A&ntilde;o</b></p>
                                    <ul class="yyyy">';
                                foreach($totalxy as $_a) {
                                    $fecha_html .= "<li data-val='$_a' data-q='q_val'>$_a</li>";
                                }
                                $fecha_html .= '
                                    </ul>
                                </div>
                                <div class="inline mes l">
                                    <p><b>Mes</b></p>
                                    <ul class="mes">';
                                foreach ($meses as $m => $mes) { 
                                    $fecha_html .= "<li data-val='".($m+1)."' data-q='q_val'>$mes</li>"; 
                                }
                                
                                $fecha_html .= '
                                    </ul>
                                </div>
                                <div class="inline dia l">
                                    <p><b>D&iacute;a</b></p>
                                    <ul class="dia">';
                                    for ($i=1;$i<32;$i++) { 
                                        $fecha_html .= "<li data-val='$i' data-q='q_val'>$i</li>";
                                    }
                                $fecha_html .= '
                                    </ul>
                                </div>
                                <input type="hidden" id="id_hidden_date" value="">
                            </div>';
                                
                            echo str_replace(array('q_val','if_val','id_hidden'),array('ini','ini','ini'),$fecha_html);
                            ?>
                        </div>
                        <div class="r">
                            <label>Hasta</label>
                            <?php
                            echo str_replace(array('q_val','if_val','id_hidden'),array('fin','fin','fin'),$fecha_html);
                            ?>
                        </div>
                    </fieldset>
                    <fieldset class="left">
                        <legend>A&ntilde;os</legend>
                        <div>
                            <?php
                            foreach($totalxy as $_a) { ?>
                                <div class="radio">
                                    <input type="radio" id="a_<?php echo $_a ?>" value="<?php echo $_a ?>" name="rap" />
                                    <label for="a_<?php echo $_a ?>"><?php echo $_a ?></label>
                                </div>
                            <?php
                            }
                            ?> 
                        </div>
                    </fieldset>
                    <fieldset class="left">
                        <legend>Otros</legend>
                        <div id="time" class="r left">
                            <div class="radio">
                                <input type="radio" id="hoy" value="h" name="rap" />
                                <label for="hoy">Hoy</label>
                            </div>
                            <div class="radio">
                                <input type="radio" id="ayer-y-hoy" value="ay" name="rap" />
                                <label for="ayer-y-hoy">Ayer y hoy</label>
                            </div>
                            <div class="radio">
                                <input type="radio" id="semana" value="s" name="rap" checked />
                                <label for="semana">Ultima semana</label>
                            </div>
                            <div class="radio">
                                <input type="radio" id="mes" value="m" name="rap" />
                                <label for="mes">Ultimo mes</label>
                            </div>
                            <div class="radio">
                                <input type="radio" id="acumulado" value="acum" name="rap" />
                                <label for="acumulado">Acumulado del año</label>
                            </div>
                    </fieldset>
                </div>
            </div>
            <!-- Filtro fecha :: FIN-->

            <!-- Filtro categorias acceso
            <div id="fcat_acceso" class="filtro fcat" data-index="2">
                <div class="left">
                     <h2 class="dosis">Posible restriccion al acceso humanitario</h2>
                     <br />
                    <div class="inline linko">
                        <a href="https://wiki.salahumanitaria.co/wiki/Restricción_al_acceso_humanitario" target="_blank">&nbsp;Definici&oacute;n de categorias</a>
                    </div>
                    <div class="inline">
                    |&nbsp;<a class="tn_fcat" href="#">Seleccionar todas/ninguna</a>
                    </div>
                </div>
                <div class="right">
                    <a class="close" href="#" data-div="fcat_acceso"><img src="<?php echo BASE ?>media/img/close.png" alt="Cerrar" /></a>
                </div>
                <div class="right">
                    <div id="btn_fcat_acceso" class="acceso btn btn_fcat">Filtrar mapa</div>
                </div>
                <div class="fcat_list">
                            <ul id="fcat_list_acceso">
                                <li>
                                    <input type="checkbox" id="acceso_cat_3" name="acceso_cat" value="cat_3" checked />
                                    <label for="acceso_cat_3">
                                        Restricciones o interferencia con el paso de agencias, 
                                        personal o bienes en el pa&iacute;s
                                    </label>
                                </li>
                                <li>
                                    <input type="checkbox" id="acceso_cat_4" name="acceso_cat" value="cat_4" checked />
                                    <label for="acceso_cat_4">
                                    Operaciones militares y hostilidades continuas impidiendo 
                                    a las operaciones humanitarias
                                    </label>
                                </li>
                                <li>
                                    <input type="checkbox" id="acceso_cat_5" name="acceso_cat" value="cat_5" checked />
                                    <label for="acceso_cat_5">
                                        Amenazas y violaciones encontra el personal humanitario
                                        y sus instalaciones
                                    </label>
                                </li>
                                <li>
                                    <input type="checkbox" id="acceso_cat_7" name="acceso_cat" value="cat_7" checked />
                                    <label for="acceso_cat_7">
                                        Presencia de minas (MAP) y ordenanza no explotada (MUSE)
                                    </label>
                                </li>
                                <li>
                                    <input type="checkbox" id="acceso_cat_9" name="acceso_cat" value="cat_9" checked />
                                    <label for="acceso_cat_9">
                                        Restricciones sobre, o obstrucción de, acceso a 
                                        servicios y asistencia por parte de las poblaciones
                                    </label>
                                </li>
                            </ul>
                </div>
            </div>
            Filtro Acceso :: Fin -->

            <!-- Descargar eventos -->
            <div id="descargar" class="filtro fcat" data-index="4">
                <div class="right">
                    <a class="close" href="#" data-div="descargar"><img src="<?php echo BASE ?>media/img/close.png" alt="Cerrar" /></a>
                </div>
                <div class="clear"></div>
                <div class="left step">
                    <div class="w">
                        <h2 class="dosis">Generar reporte</h2>
                    </div>
                    <p class="w">
                        Esta opci&oacute;n le permite descargar el listado de eventos que est&aacute;
                        viendo en el mapa, es decir, los eventos con los filtros aplicados. <br /> <br /> El tiempo
                        de generaci&oacute;n del reporte depende del n&uacute;mero de eventos
                    </p>
                    <div>
                        <div class="radio">
                            <input type="radio" value="v" name="descargar_v_d" checked />
                            <label for="acumulado">Eventos de Violencia Armada</label>
                        </div>
                        <div class="radio">
                            <input type="radio" value="d" name="descargar_v_d" />
                            <label for="acumulado">Eventos de Desastres</label>
                        </div>
                        <br /><br />
                        <div class="btn" id="download_incidents">Comenzar con la descarga....</div>
                    </div>
                </div>
                <div class="left w step zips">
                    <div>
                        <h2 class="dosis">Descarga directa</h2>
                    </div>
                    <div class="of">
                        <table>
                            <tr><th></th><th align="center">Violencia</th><th align="center">Desastres</th></tr>
                            <?php 
                            foreach($totalxy as $_a) {
                                echo "<tr><td>$_a</td>";
                                foreach(array('violencia','desastres') as $b) {
                                    $file = "monitor-eventos-$_a-$b.xls";

                                    $size = filesize_formatted($config['cache_reportes'].'/'.$file);
                                    echo "<td><a href='z/".$file."'>Descargar ~ $size</a></td>"; 
                                }
                                echo "</tr>";
                            } 
                            ?>
                        </table>
                    </div>
                    <p class="note">Reportes generados el: <b><?php echo $ayer ?></b></p>
                </div>
            </div>
            <!-- Descargar eventos :: FIN-->

            <!-- Totales por año -->
            <?php $t1 = '<tr><th></th><th align="center">Afectados</th><th align="center">Eventos</th></tr>'; ?>
            <div id="totales" class="filtro fcat" data-index="3">
                <div class="left">
                     <h2 class="dosis inline w">Totales</h2>
                     <select id="total_periodo_yyyy">
                         <?php foreach($totalxy as $_a) {echo "<option val='$_a'>$_a</<option>"; } ?>
                     </select>
                </div>
                <div class="right">
                    <a class="close" href="#" data-div="ini_fin"><img src="<?php echo BASE ?>media/img/close.png" alt="Cerrar" /></a>
                </div>
                <div class="clear"><br /></div>
                <div class="left w step zips">
                    <div class="left">
                        <h2 class="dosis w">Violencia</h2>
                    </div>
                    <div class="right">
                        <i class="fa fa-download"></i> <a href=#'' id="total_descarga_v">Descargar info</a>
                    </div>
                    <div class="clear"></div>
                    <div class="of">
                        <table id="totales_data_violencia">
                            <?php echo $t1; ?>
                        </table>
                    </div>
                </div>
                <div class="left w step zips">
                    <div class="left">
                        <h2 class="dosis w">Desastres</h2>
                    </div>
                    <div class="right">
                        <i class="fa fa-download"></i> <a href='z/monitor-totales-' id="total_descarga_d">Descargar info</a>
                    </div>
                    <div class="clear"></div>
                    <div class="of">
                        <table id="totales_data_desastres">
                            <?php echo $t1; ?>
                        </table>
                    </div>
                </div>
            </div>
            <!-- Totales por año :: FIN-->

            <!-- Variación -->
            <div id="variacion" class="filtro fcat" data-index="4">
                <div class="left">
                    <h2 class="dosis w">Cálculo de variación</h2>
                </div>
                <div class="right">
                    <a class="close" href="#" data-div="variacion"><img src="<?php echo BASE ?>media/img/close.png" alt="Cerrar" /></a>
                </div>
                <div class="right">
                    <div id="btn_variacion" class="btn">Calcular variación</div>
                </div>
                <div class="clear"></div>
                <div class="w">
                    Esta funcionalidad le permite realizar análisis temporal a través de la comparación del <b>NUMERO DE EVENTOS</b>
                    en 2 periodos de tiempo. Obtendrá una visualización rápida de si existe un aumento o una disminución
                    de valores. Recuerde seleccionar las categorias correspondientes<br />&nbsp;
                </div>
                <div class="clear"></div>
                <div>
                    <fieldset class="left">
                        <legend>Consultar para</legend>
                            <div>
                            <div class="radio">
                                <input type="radio" value="v" name="variacion_v_d" checked />
                                <label for="acumulado">Violencia armada</label>
                            </div>
                            <div class="radio">
                                <input type="radio" value="d" name="variacion_v_d" />
                                <label for="acumulado">Desastres</label>
                            </div>
                    </fieldset>
                    <fieldset class="left">
                        <legend>Periodo 1</legend>
                        <div class="r">
                            <?php $pre = "variacion_p1_"; ?>
                            <label>Desde</label>
                            <?php
                            echo str_replace(array('q_val','if_val','id_hidden'),array($pre.'ini','ini',$pre.'ini'),$fecha_html);
                            ?>
                        </div>
                        <div class="r">
                            <label>Hasta</label>
                            <?php
                            echo str_replace(array('q_val','if_val','id_hidden'),array($pre.'fin','fin',$pre.'fin'),$fecha_html);
                            ?>
                        </div>
                    </fieldset>
                    <fieldset class="left">
                        <legend>Periodo 2</legend>
                        <div class="r">
                            <?php $pre = "variacion_p2_"; ?>
                            <label>Desde</label>
                            <?php
                            echo str_replace(array('q_val','if_val','id_hidden'),array($pre.'ini','ini',$pre.'ini'),$fecha_html);
                            ?>
                        </div>
                        <div class="r">
                            <label>Hasta</label>
                            <?php
                            echo str_replace(array('q_val','if_val','id_hidden'),array($pre.'fin','fin',$pre.'fin'),$fecha_html);
                            ?>
                        </div>
                    </fieldset>
                </div>
            </div>
            <!-- Variación :: FIN-->
        </div>
        <!-- /Content -->
    </div>
    <div class="clear"></div>
    <div id="footer"></div>

    <?php
    if ($dev) { ?>
        <!--[if lt IE 9]>
        <script src="https://unpkg.com/respond.js@1.4.2/dest/respond.min.js"></script>
        <![endif]-->
        <script type="text/javascript" src="https://unpkg.com/jquery@3/dist/jquery.min.js"></script>
        <script type="text/javascript" src="https://unpkg.com/jquery-migrate@1/dist/jquery-migrate.min.js"></script>
        <script type="text/javascript" src="https://unpkg.com/jquery-migrate@3/dist/jquery-migrate.min.js"></script>
        <script type="text/javascript" src="https://ajax.aspnetcdn.com/ajax/jquery.ui/1.12.1/jquery-ui.min.js"></script>
        <script type="text/javascript" src="<?php echo BASE ?>media/js/url_tools.min.js"></script>
        <script type="text/javascript" src="https://unpkg.com/highcharts@7/highcharts.js"></script>
        <script type="text/javascript" src="https://unpkg.com/icheck@1/icheck.min.js"></script>
        <script type="text/javascript" src="https://unpkg.com/geostats@1/lib/geostats.min.js"></script>
        <script type="text/javascript" src="https://unpkg.com/datatables@1/media/js/jquery.dataTables.min.js"></script>
        <script type="text/javascript" src="<?php echo BASE ?>media/js/tooltip.js"></script>
        <!-- script type="text/javascript" src="https://unpkg.com/bootstrap-tooltip@3/index.js"></script -->
        <script type="text/javascript" src="<?php echo BASE ?>media/js/popover.js"></script>
        <script type="text/javascript" src="https://unpkg.com/openlayers@3/dist/ol.js"></script>
        <script type="text/javascript" src="<?php echo BASE ?>media/js/fe.js"></script>
        <script type="text/javascript" src="<?php echo BASE ?>media/js/map.js?v={random number/string}"></script>
        <script type="text/javascript" src="<?php echo BASE ?>media/js/focus-element-overlay.min.js"></script>
        <script type="text/javascript" src="<?php echo BASE ?>media/js/nouislider.min.js"></script>
        <script type="text/javascript" src="<?php echo BASE ?>media/js/HoldOn.min.js"></script>

    <?php
    }
    else {
    ?>
    <script type="text/javascript" src="<?php echo BASE ?>media/js/m.js?v={random number/string}"></script>
    <?php
    }
    ?>

    <script type="text/javascript">
     (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
      (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
      m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
      })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

      ga('create', 'UA-48294292-6', 'auto');
      ga('send', 'pageview');

    </script>
</body>
</html>


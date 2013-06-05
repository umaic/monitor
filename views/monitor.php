<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="shortcut icon" href="favicon.ico" />
<title>Monitor Humanitario :: Colombia</title>
<link rel="stylesheet" type="text/css" href="http://monitor.colombiassh.org/media/css/brand.css">
<link type="text/css" rel="stylesheet" href="<?php echo BASE ?>media/css/fe.min.css">
<link type="text/css" rel="stylesheet" href="<?php echo BASE ?>media/css/jquery-ui-1.8.22.custom.min.css" />
</head>

<?php 
$meses = array('Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre');
?>

<body>
    <!--<div id="brand">Colombia<b>SSH</b></div>-->
    <div id="header">
        <div id="logo"></div>
        <div id="b">
            Monitor es una herramienta puesta a disposici&oacute;n de la comunidad 
            humanitaria en Colombia para poder visualizar la situaci&oacute;n 
            humanitaria en el pa&iacute;s de manera georeferenciada. <a href="#" id="lmh">Leer m&aacute;s</a>
            <div id="qlmh" class="hide">
                <div id="lmm">
                    <img src="<?php echo BASE ?>media/img/logo.png" />
                </div>
                <div id="qmm">
                    Monitor es una herramienta puesta a disposici&oacute;n de la comunidad 
                    humanitaria en Colombia para poder visualizar la situaci&oacute;n 
                    humanitaria en el pa&iacute;s de manera georeferenciada. <br /><br />
                    Permite la visualizaci&oacute;n de multiples fuentes de informaci&oacute;n 
                    tanto de desastres naturales como de emergencia compleja.  
                    OCHA Colombia provee esta plataforma como un servicio com&uacute;n humanitario 
                    para el Equipo Humanitario del Pa&iacute;s (EHP) y los miembros de los respetivos Clusters
                </div>
            </div>
        </div>
    </div>
    <!--
    <div id="filtros">
        <div class="opf">Restricci&oacute;n al acceso humanitario</div>
        <div class="filtro">
            <div class="right">
                <a href="http://www.colombiassh.org/gtmi/wiki/index.php/Restricci%C3%B3n_al_acceso_humanitario" target="_blank">? Wiki Acceso Humanitario</a>
            </div>
            <div class="i f clear">Mostrar eventos que posiblemente
            restringen el acceso humanitario</div>
            <div class="i">Restricciones o interferencia con el paso
            de agencias, personal o bienes en el país</div>
            <div class="i">Operaciones militares y hostilidades 
            continuas impidiendo a las operaciones 
            humanitarias</div>
            <div class="i">Amenazas y violaciones encontra el personal
            humanitario y sus instalaciones</div>
            <div class="i">Presencia de minas (MAP) y ordenanza
            no explotada (MUSE)</div>
        </div>
        <div class="opf">Resoluciones del Consejo de Seguridad</div>
    </div>
    -->
        <div id="tec" class="tecdn">
            <a href="http://www.colombiassh.org/emergenciacompleja/" target="_blank">
                <img src="<?php echo BASE ?>media/img/logo_ec.png" />
            </a>
            <div class="it tot">Total Eventos: <b><?php echo number_format($tec) ?></b></div>
            <div class="cat it">
                <div class="inline">Categorias</div>
                <div class="inline arrow-down"></div>
            </div>
            <div id="fcat_ec" class="filtro fcat">
                <div class="left">
                     <h2>Categorias Violencia Armada</h2>
                     <br />
                    <div class="inline linko">
                        <a href="http://www.colombiassh.org/gtmi/wiki/index.php/Sistema_de_categor%C3%ADas_del_m%C3%B3dulo_de_eventos_de_conflicto" target="_blank">Definici&oacute;n de categorias</a>
                    </div>
                    <div class="inline">
                    <a class="tn_fcat" href="#">|&nbsp;Seleccionar todas/ninguna</a>
                    </div>
                </div>
                <div class="right">
                    <a class="close" href="#"><img src="<?php echo BASE ?>media/img/close.png" alt="Cerrar" /></a>
                </div>
                <div class="right">
                    <div id="btn_fcat_ec" class="ec btn btn_fcat">Filtrar mapa</div>
                </div>
                <div class="fcat_list">
                    <?php 
                    foreach($cats_f['ec'] as $_cp => $_cts) { ?>
                        <div>
                            <ul>
                                <li class="p">
                                    <?php echo $_cp ?>
                                </li>
                                <?php
                                //$_h = count($_ch);
                                foreach($_cts as $_idh => $_ch) { 
                                    $_id = "cat_$_idh";
                                    $chk = 'checked';
                                    if (!in_array($_idh, $cats_u) || in_array($_idh, $cats_hide['ec'])) {
                                            $chk = '';
                                        
                                    }
                                    ?>
                                    <li class="h">
                                        <input type="checkbox" id="<?php echo $_id ?>" name="<?php echo $_id ?>" value="<?php echo $_idh; ?>" <?php echo $chk ?> />
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
        </div>
        <div id="tdn" class="tecdn">
            <a href="http://inundaciones.colombiassh.org" target="_blank">
                <img src="<?php echo BASE ?>media/img/logo_dn.png" />
            </a>
            <div class="it tot">Total Eventos: <b><?php echo number_format($tdn) ?></b></div>
            <div class="it cat">
                <div class="inline">Categorias</div>
                <div class="inline arrow-down"></div>
            </div>
            <div id="fcat_dn" class="filtro fcat">
                <div class="left">
                     <h2>Categorias Desastres Naturales</h2>
                     <br />
                    <div class="inline linko">
                        <a href="http://www.colombiassh.org/gtmi/wiki/index.php/Sistema_de_categor%C3%ADas_del_m%C3%B3dulo_de_eventos_de_conflicto" target="_blank">Definici&oacute;n de categorias</a>
                    </div>
                    <div class="inline">
                    <a class="tn_fcat" href="#">|&nbsp;Seleccionar todas/ninguna</a>
                    </div>
                </div>
                <div class="right">
                    <a class="close" href="#"><img src="<?php echo BASE ?>media/img/close.png" alt="Cerrar" /></a>
                </div>
                <div class="right">
                    <div id="btn_fcat_ec" class="dn btn btn_fcat">Filtrar mapa</div>
                </div>
                <div class="fcat_list">
                    <?php 
                    foreach($cats_f['dn'] as $_cp => $_cts) { ?>
                        <div>
                            <ul>
                                <li class="p"><?php echo $_cp ?></li>
                                <?php
                                //$_h = count($_ch);
                                foreach($_cts as $_idh => $_ch) { 
                                    $_id = "cat_$_idh";
                                    $chk = 'checked';
                                    if (!in_array($_idh, $cats_u) || in_array($_idh, $cats_hide['dn'])) {
                                            $chk = '';
                                        
                                    }
                                    ?>
                                    <li class="h">
                                        <input type="checkbox" id="<?php echo $_id ?>" name="<?php echo $_id ?>" value="<?php echo $_idh; ?>" <?php echo $chk ?> />
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
        </div>
    <div id="menu">
        <div id="aaaa">
            <input type="hidden" id="currentCatE" value="0">
            <input type="hidden" id="currentCatD" value="0">
            <input type="hidden" id="startDate" value="">
            <input type="hidden" id="endDate" value="">
            <input type="hidden" id="yyyy_ini" value="">
            <input type="hidden" id="yyyy_fin" value="">
            <?php 
            foreach($totalxy as $_a => $_t) { ?>
                <div class="v">
                    <div class="a">
                        <?php echo $_a ?>
                    </div>
                    <!--
                    <div>
                        <div class="circle ec"></div><div class="n"><?php echo $_t['ec'] ?></div>
                        <div class="circle dn clear"></div><div class="n"><?php echo $_t['dn'] ?></div>
                    </div>
                    -->
                </div>
            <?php
            } 
            ?>
        </div>
    </div>
        <div id="loading" class="alpha60">
            <img src="<?php echo BASE ?>media/img/ajax-loader.png" />
        </div>
    <div id="content">
        <!--<div id="ys"></div>-->
        <div id="map"></div>
        <div id="totalxd" class="shadow">
            <div>
                <a href="http://inundaciones.colombiassh.org/reports/submit" target="_blank">
                    <div id="new_event">
                        Reportar un evento de desastre natural
                    </div>
                </a>
            </div>
            <div id="data">
                <h1 class="inline">Eventos por</h1>
                <select class="select">
                    <option value="d">Departamento</option>
                    <!--<option value="r">Regi&oacute;n</option>-->
                </select>
                
                <!--<div id="dslider"></div>
                <div class="note">Seleccione el periodo usando la barra o la lista</div>-->
                <div class="r">Seleccione un periodo de tiempo mas exacto</div>
                <div id="ini_fin" class="inline">
                    <div class="r">
                        Desde:&nbsp;<input type="text" id="ini_text" class="fecha select" dv="ini_div" readonly />
                        <div class="filtro_fecha" id="ini_div">
                            <div class="left">Seleccione a&ntilde;o, mes y d&iacute;a</div>
                            <div class="right close"></div>
                            <div class="clear"></div>
                            <div class="inline yyyy l">
                                <p><b>A&ntilde;o</b></p>
                                <ul>
                                    <?php foreach($totalxy as $_a => $_t) {echo "<li val='$_a' q='ini' y='yyyy'>$_a</li>"; } ?>
                                </ul>
                            </div>
                            <div class="inline mes l">
                                <p><b>Mes</b></p>
                                <ul>
                                    <?php foreach ($meses as $m => $mes) { echo "<li val='".($m+1)."' q='ini' y='mes'>$mes</li>"; } ?>
                                </ul>
                            </div>
                            <div class="inline dia l">
                                <p><b>D&iacute;a</b></p>
                                <ul>
                                    <?php for ($i=1;$i<32;$i++) { echo "<li val='$i' q='ini' y='dia'>$i</li>"; } ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="r">
                        Hasta:&nbsp;&nbsp;<input type="text" id="fin_text" class="fecha select" dv="fin_div" readonly />
                        <div class="filtro_fecha" id="fin_div">
                            <div class="left">Seleccione a&ntilde;o, mes y d&iacute;a</div>
                            <div class="right close"></div>
                            <div class="clear"></div>
                            <div class="inline yyyy l">
                                <p><b>A&ntilde;o</b></p>
                                <ul>
                                    <?php foreach($totalxy as $_a => $_t) {echo "<li val='$_a' q='fin' y='yyyy'>$_a</li>"; } ?>
                                </ul>
                            </div>
                            <div class="inline mes l">
                                <p><b>Mes</b></p>
                                <ul>
                                    <?php foreach ($meses as $m => $mes) { echo "<li val='".($m+1)."' q='fin' y='mes'>$mes</li>"; } ?>
                                </ul>
                            </div>
                            <div class="inline dia l">
                                <p><b>D&iacute;a</b></p>
                                <ul>
                                    <?php for ($i=1;$i<32;$i++) { echo "<li val='$i' q='fin' y='dia'>$i</li>"; } ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <!--<div class="inline"><img src="<?php echo BASE ?>media/img/calendar.png" width="18" height="18" /><a href="#" id="lff">Aplicar filtro de fechas</a></div>-->
                    <div id="time" class="inline">
                        <label class="inline">o consultar:</label>
                        <select id="stime">
                            <option value=0>----</option>
                            <option value="a">Todo el a&ntilde;o</option>
                            <option value="m" selected="selected">Ultimo mes</option>
                            <option value="s">Ultima semana</option>
                            <option value="ay">Ayer y hoy</option>
                            <option value="h">Hoy</option>
                        </select>
                    </div>
                </div>
                <!--
                <div class="inline" id="totalxd_y">
                    <?php //echo date('Y') ?>
                </div>
                -->
                <div id="div_table_totalxd">
                    <table id="table_totalxd">
                        <thead>
                            <tr>
                                <td id="totalxd_menu" colspan="4">
                                    <div id="filter_states" class="filter">Filtrar mapa</div>
                                    <div id="download_incidents" class="xls">Descargar eventos</div>
                                </td>
                            </tr>
                            <tr><th><input type="checkbox" id="totalxd_all_chk" checked></th><th class="d"></th><th class="ec">Violencia</th><th class="dn">Desastres</th></tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="clear"></div>
    </div>
    <div id="footer">
    </div>
    <script type="text/javascript" src="<?php echo BASE ?>media/js/jquery-1.7.2.min.js"></script>
    <script type="text/javascript" src="<?php echo BASE ?>media/js/jquery-ui-1.8.21.custom.min.js"></script>
    <script type="text/javascript" src="<?php echo BASE ?>media/js/openlayers/OpenLayers.min.js"></script>
    <script type="text/javascript" src="<?php echo BASE ?>media/js/openlayers/LoadingPanel.min.js"></script>
    <script type="text/javascript" src="<?php echo BASE ?>media/js/fe.js"></script>
    <script type="text/javascript" src="<?php echo BASE ?>media/js/map.js"></script>
    <script type="text/javascript" src="<?php echo BASE ?>media/js/jquery.tablesorter.min.js"></script>
    <script type="text/javascript" src="<?php echo BASE ?>media/js/url_tools.min.js"></script>
    <script type="text/javascript">
    var _gaq = _gaq || [];
    _gaq.push(['_setAccount', 'UA-138811-20']);
   _gaq.push(['_setDomainName', 'colombiassh.org']);
   _gaq.push(['_trackPageview']);

   (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
    })();

    </script>
</body>
</html>


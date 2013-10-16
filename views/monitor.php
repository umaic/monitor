<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="shortcut icon" href="favicon.ico" />
<title>Monitor Humanitario :: Colombia</title>
<link type="text/css" rel="stylesheet" href="<?php echo BASE ?>media/css/fe.min.css">
<link type="text/css" rel="stylesheet" href="<?php echo BASE ?>media/css/jquery-ui-1.8.22.custom.css" />
</head>

<?php 
$meses = array('Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre');
?>

<body>
    <div id="content">
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
            <a href="http://violenciaarmada.colombiassh.org/reports/submit" target="_blank">
                <img src="<?php echo BASE ?>media/img/logo_ec.png" border="0" />
            </a>
            <div class="it tot">Total Eventos: <b><?php echo number_format($tec) ?></b></div>
            <div class="cat it">
                <div class="inline">Filtrar categor&iacute;as</div>
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
                    <div id="" class="ec btn btn_show_e">Ocultar eventos</div>
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
            <div id="new_event">
                <a href="http://violenciaarmada.colombiassh.org/reports/submit" target="_blank">
                    Reportar un evento
                </a>
            </div>
        </div>
        <div id="tdn" class="tecdn">
            <a href="http://desastres.colombiassh.org/reports/submit" target="_blank">
                <img src="<?php echo BASE ?>media/img/logo_dn.png" border="0" />
            </a>
            <div class="it tot">Total Eventos: <b><?php echo number_format($tdn) ?></b></div>
            <div class="it cat">
                <div class="inline">Filtrar categor&iacute;as</div>
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
                    <div id="" class="dn btn btn_show_e">Ocultar eventos</div>
                    <div id="btn_fcat_dn" class="dn btn btn_fcat">Filtrar mapa</div>
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
            <div>
                <a href="http://desastres.colombiassh.org/reports/submit" target="_blank">
                    <div id="new_event">
                        Reportar un evento
                    </div>
                </a>
            </div>
        </div>
        <div id="loading" class="alpha60">
            <img src="<?php echo BASE ?>media/img/ajax-loader.png" />
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
            /*        
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
             */
            ?>
        <!--<div id="ys"></div>-->
        <!--
        <h1 class="inline">Eventos por</h1>
        <select class="select">
            <option value="d">Departamento</option>
            <option value="r">Regi&oacute;n</option>
        </select>-->
        <div id="ini_fin" class="inline">
            <div class="r left">
                <label>Desde</label><input type="text" id="ini_text" class="fecha select" dv="ini_div" readonly />
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
            <div class="r left">
                <label>Hasta</label><input type="text" id="fin_text" class="fecha select" dv="fin_div" readonly />
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
            <div id="time" class="r left">
                <label class="inline">O consultar:</label>
                <select id="stime">
                    <option value=0>----</option>
                    <option value="h">Hoy</option>
                    <option value="ay">Ayer y hoy</option>
                    <option value="s" selected="selected">Ultima semana</option>
                    <option value="m">Ultimo mes</option>
                    <optgroup label="A&ntilde;os">
                    <?php
                    foreach($totalxy as $_a => $_t) { ?>
                        <option value="<?php echo $_a ?>">Todo <?php echo $_a ?></option>
                    <?php
                    }
                    ?> 
                    </optgroup>
                </select>
            </div>
            <div id="filter_states" class="r btn left"><img src="media/img/filter.png" />&nbsp;&nbsp;Filtrar mapa</div>
            <div id="download_incidents" class="r btn left"><img src="media/img/xls.png" />&nbsp;&nbsp;Descargar eventos</div>
        </div>
        </div>
        </div>
                <?php
                   /* 
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
                    */
            ?>
    <div id="mapas_tipos">
        <div class="mapa_tipo activo inline" data-tipo="afectacion"><img src="<?php echo BASE ?>media/img/people_affected_population_64px_icon.png" /><h1>Afectados</h1></div>
        <div class="mapa_tipo inline" data-tipo="eventos"><img src="<?php echo BASE ?>media/img/activity_scale_operation_64px_icon.png" /><h1>Eventos</h1></div>
        
        <div id="group_fts" class="ungroup"><h1>Desagrupar mapa</h1></div>
    </div>
        <!--<div id="ys"></div>-->
        <div id="map"></div>
        <div id="totalxd" class="">
            <div id="data">
                <div id="minmax_total" class="minimize"></div>
                <div id="tabs">
                  <ul>
                    <li><a href="#resumen">Resumen</a></li>
                    <li><a href="#departamentos">Departamentos</a></li>
                    <li><a href="#tendencia">Tendencia</a></li>
                  </ul>
                  <div id="resumen">
                    <div class="data_title">
                        <h2>Personas afectadas</h2>
                    </div>
                    <div class="div_table_totalxd">
                        <div id="resumen_ec" class="hide">
                            <div id="resumen_total_ec" class="ec resumen_total">
                                <div id="resumen_total_ec_num" class="num"></div>
                                <div class="cat">Total</div>
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
                                <div class="cat">Total</div>
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
                    <div class="data_title">
                        <h2>Personas afectadas</h2>
                    </div>
                    <table id="table_totalxd">
                        <thead>
                            <tr><th><input type="checkbox" id="totalxd_all_chk" value="0" checked></th><th class="d"></th><th class="ec">Violencia</th><th class="dn">Desastres</th></tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
              </div>
              <div id="tendencia">
                Proximamente --- :)
              </div>
            <div class="clear"></div>
            </div>
        </div>
        <div class="clear"></div>
    </div>
    <div id="footer">
    </div>
    <!--<script type="text/javascript" src="<?php echo BASE ?>media/js/jquery.min.js,jquery-ui.min.js,openlayers/OpenLayers.min.js,fe.min.js,map.min.js,/url_tools.min.js"></script>-->
    <script type="text/javascript" src="<?php echo BASE ?>media/js/jquery.min.js"></script>
    <script type="text/javascript" src="<?php echo BASE ?>media/js/jquery-ui.min.js"></script>
    <script type="text/javascript" src="<?php echo BASE ?>media/js/openlayers/OpenLayers.min.js"></script>
    <script type="text/javascript" src="<?php echo BASE ?>media/js/openlayers/LoadingPanel.min.js"></script>
    <script type="text/javascript" src="<?php echo BASE ?>media/js/fe.min.js"></script>
    <script type="text/javascript" src="<?php echo BASE ?>media/js/map.min.js"></script>
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


<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="shortcut icon" href="favicon.ico" />
<title>Monitor Humanitario :: Colombia</title>
<link type="text/css" rel="stylesheet" href="<?php echo BASE ?>media/css/fe.css">
<link type="text/css" rel="stylesheet" href="<?php echo BASE ?>media/css/orange.css">
<link type="text/css" rel="stylesheet" href="<?php echo BASE ?>media/css/jquery-ui-1.8.22.custom.css" />
</head>

<?php
$sala = 'salahumanitaria.co';
$meses = array('Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre');
$url_violencia = "http://violenciaarmada.".$sala."/reports/submit";
$url_desastres = "http://desastres.".$sala."/reports/submit";

// Test geonode server
$geonode = true;
?>

<body>
    <div id="loading" class="alpha60">
        <img src="<?php echo BASE ?>media/img/ajax-loader.png" />
    </div>
    <!--<div id="brand">Colombia<b>SSH</b></div>-->
    <div id="header">
        <div id="i" class="left">
            <div id="logo" class="left"></div>
        </div>
            <div id="b" class="left">
                    <ul>
                        <li><a id="lmh" href="#">Que es monitor?</a></li>
                        <li><a href="http://www.salahumanitaria.co" target="_blank">Sala Humanitaria</a></li>
                        <li><a href="http://sidih.salahumanitaria.co" target="_blank">Sidih</a></li>
                        <li><a href="http://geonode.salahumanitaria.co" target="_blank">Geonode</a></li>
                        <li><a href="http://www.colombiassh.org/gtmi/wiki/" target="_blank">Wiki</a></li>
                    </ul>
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
        <div id="tec" class="tecdn right">
            <a href="<?php echo $url_violencia ?>" target="_blank">
                <img src="<?php echo BASE ?>media/img/logo_ec.png" border="0" />
            </a>
            <div class="it tot">Total Eventos: <b><?php echo number_format($tec) ?></b></div>
            <!--
            <div class="cat it">
                <div class="inline">Filtrar categor&iacute;as</div>
                <div class="inline arrow-down"></div>
            </div>
            -->
            <div class="new_event nev">
                <a href="<?php echo $url_violencia ?>" target="_blank">
                    Reportar un evento
                </a>
            </div>
        </div>
        <div id="tdn" class="tecdn right">
            <a href="<?php echo $url_desastres ?>" target="_blank">
                <img src="<?php echo BASE ?>media/img/logo_dn.png" border="0" />
            </a>
            <div class="it tot">Total Eventos: <b><?php echo number_format($tdn) ?></b></div>
            <!--
            <div class="it cat">
                <div class="inline">Filtrar categor&iacute;as</div>
                <div class="inline arrow-down"></div>
            </div>
            -->
            <div class="new_event ned">
                <a href="<?php echo $url_desastres ?>" target="_blank">
                    Reportar un evento
                </a>
            </div>
        </div>
        <div class="clear"></div>
    </div>
    <div id="menua" class="hide">
        <div id="aaaa">
            <input type="hidden" id="currentCatE" value="0">
            <input type="hidden" id="currentCatD" value="0">
            <input type="hidden" id="startDate" value="">
            <input type="hidden" id="endDate" value="">
            <input type="hidden" id="yyyy_ini" value="">
            <input type="hidden" id="yyyy_fin" value="">
            <?php 
            ?>
        </div>
    </div>
    <div id="content">
        <div id="menu" class="left">
            <ul>
                <li class="sub" data-div="ini_fin"><span class="menu_fecha">Filtrar por fecha</span></li>
                <li class="sub" data-div="fcat_ec"><span class="menu_violencia">Categorias violencia</span></li>
                <li class="sub" data-div="fcat_dn"><span class="menu_desastres">Categorias desastres</span></li>
                <li class="sub" data-div="fcat_acceso"><span class="menu_acceso">Restricci&oacute;n al acceso</span></li>
                <li class="sub hide" data-div="fcat_1612"><span class="menu_1612">Menores en conflicto</span></li>
                <li class="sub" data-div="descargar"><span class="menu_descargar">Descargar eventos</span></li>
            </ul>
        </div>
        <!-- Filtro categorias Violencia -->
        <div id="fcat_ec" class="filtro fcat" data-index="1">
            <div class="left">
                 <h2 class="dosis">Categorias Violencia Armada</h2>
                 <br />
                <div class="inline linko">
                    <a href="http://www.colombiassh.org/gtmi/wiki/index.php/Sistema_de_categor%C3%ADas_del_m%C3%B3dulo_de_eventos_de_conflicto" target="_blank">&nbsp;Definici&oacute;n de categorias</a>
                </div>
                <div class="inline">
                |&nbsp;<a class="tn_fcat" href="#">Seleccionar todas/ninguna</a>
                </div>
            </div>
            <div class="right">
                <a class="close" href="#" data-div="fcat_ec"><img src="<?php echo BASE ?>media/img/close.png" alt="Cerrar" /></a>
            </div>
            <div class="right">
                <div id="" class="ec btn btn_show_e">Ocultar eventos</div>
                <div id="btn_fcat_ec" class="ec btn btn_fcat">Filtrar mapa</div>
            </div>
            <div class="fcat_list">
                <?php 
                foreach($cats_f['ec'] as $_cp => $_cts) { ?>
                    <div>
                        <ul class="cats">
                            <li class="p">
                                <?php echo $_cp ?>
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
        <!-- Filtro categorias Violencia :: Fin -->
        
        <!-- Filtro categorias Desastres -->
        <div id="fcat_dn" class="filtro fcat" data-index="2">
            <div class="left">
                 <h2 class="dosis">Categorias Desastres</h2>
                 <br />
                <div class="inline linko">
                    <a href="http://www.colombiassh.org/gtmi/wiki/index.php/Sistema_de_categor%C3%ADas_del_m%C3%B3dulo_de_eventos_de_conflicto" target="_blank">&nbsp;Definici&oacute;n de categorias</a>
                </div>
                <div class="inline">
                |&nbsp;<a class="tn_fcat" href="#">Seleccionar todas/ninguna</a>
                </div>
            </div>
            <div class="right">
                <a class="close" href="#" data-div="fcat_dn"><img src="<?php echo BASE ?>media/img/close.png" alt="Cerrar" /></a>
            </div>
            <div class="right">
                <div id="btn_show_dn" class="dn btn btn_show_e">Ocultar eventos</div>
                <div id="btn_fcat_dn" class="dn btn btn_fcat">Filtrar mapa</div>
            </div>
            <div class="fcat_list">
                <?php 
                foreach($cats_f['dn'] as $_cp => $_cts) { ?>
                    <div>
                        <ul class="cats">
                            <li class="p"><?php echo $_cp ?></li>
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
                <div id="filter_states" class="dn btn btn_fcat">Filtrar mapa</div>
            </div>
            <div class="clear"></div>
            <div>
                <fieldset class="left">
                    <legend>Periodo</legend>
                    <div class="r">
                        <label>Desde</label><input type="text" id="ini_text" class="fecha select" dv="ini_div" readonly />
                        <div class="filtro_fecha" id="ini_div">
                            <div class="left">Seleccione a&ntilde;o, mes y d&iacute;a</div>
                            <div class="right close"></div>
                            <div class="clear"></div>
                            <div class="inline yyyy l">
                                <p><b>A&ntilde;o</b></p>
                                <ul>
                                    <?php foreach($totalxy as $_a) {echo "<li val='$_a' q='ini' y='yyyy'>$_a</li>"; } ?>
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
                        <label>Hasta</label><input type="text" id="fin_text" class="fecha select" dv="fin_div" readonly />
                        <div class="filtro_fecha" id="fin_div">
                            <div class="left">Seleccione a&ntilde;o, mes y d&iacute;a</div>
                            <div class="right close"></div>
                            <div class="clear"></div>
                            <div class="inline yyyy l">
                                <p><b>A&ntilde;o</b></p>
                                <ul>
                                    <?php foreach($totalxy as $_a) {echo "<li val='$_a' q='fin' y='yyyy'>$_a</li>"; } ?>
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
                            <input type="radio" id="semana" value="s" name="rap" />
                            <label for="semana">Ultima semana</label>
                        </div>
                        <div class="radio">
                            <input type="radio" id="mes" value="m" name="rap" />
                            <label for="mes">Ultimo mes</label>
                        </div>
                        <div class="radio">
                            <input type="radio" id="acumulado" value="acum" name="rap" checked />
                            <label for="acumulado">Acumulado del año</label>
                        </div>
                </fieldset>
                
            </div>
        </div>
        <!-- Filtro fecha :: FIN-->
        
        <!-- Filtro categorias acceso -->
        <div id="fcat_acceso" class="filtro fcat" data-index="2">
            <div class="left">
                 <h2 class="dosis">Posible restriccion al acceso humanitario</h2>
                 <br />
                <div class="inline linko">
                    <a href="http://www.colombiassh.org/gtmi/wiki/index.php/Restricción_al_acceso_humanitario" target="_blank">&nbsp;Definici&oacute;n de categorias</a>
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
        <!-- Filtro Acceso :: Fin -->
        
        <!-- Descargar eventos -->
        <div id="descargar" class="filtro fcat" data-index="2">
            <div class="left">
                 <h2 class="dosis w">Descargar eventos</h2>
            </div>
            <div class="right">
                <a class="close" href="#" data-div="ini_fin"><img src="<?php echo BASE ?>media/img/close.png" alt="Cerrar" /></a>
            </div>
            <div class="clear"></div>
            <div class="w"><br />
                Esta opci&oacute;n le permite descargar el listado de eventos que est&aacute;
                viendo en el mapa, es decir, los eventos con los filtros aplicados. <br /> <br /> El tiempo
                de generaci&oacute;n del reporte depende del n&uacute;mero de eventos
            </div>
            <div>
                <br /><br />
                <div class="btn" id="download_incidents">Comenzar con la descarga....</div>
            </div>
        </div>
        <!-- Descargar eventos :: FIN-->
        
        <div id="map" class="map_monitor left"></div>
        <div id="featured" class="hide">
            <div><b>Eventos destacados por:</b></div>
            <div id="t">- Movilizaci&oacute;n social <br />- Paro</div>
             
        </div>
        <div id="submenu">
            <div id="titulo_general" class="left">
                <div id="tgt" class="dosis"></div>
                <div id="tgc"></div>
            </div>
            <div id="mapa_tipo" class="left">
                <div class="mapa_tipo menu_activo left op" data-tipo="afectacion">
                    <span class="menu_victimas">Afectados</span>
                </div>
                <div class="mapa_tipo left op" data-tipo="eventos">
                    <span class="menu_eventos">Eventos</span>
                </div>
                <div id="group_fts" class="mapa_tipo left op">
                    <span class="menu_desagrupar">Desagrupar</span>
                </div>
                <div id="layers" class="left op">
                    <span class="menu_layers">+ Capas</span>
                </div>
            </div>
        </div>
        <div id="layers_div" class="filtro">
            <div class="left">
                <h1 class="dosis">Adicionar capas al mapa</h1>
            </div>
            <div class="right">
                <a data-div="layers_div" href="#" class="close"><img alt="Cerrar" src="media/img/close.png"></a>
            </div>
            <div class="clear"></div>
            <?php 
            if ($geonode) { ?>
                <p>
                    Presentamos el listado de capas disponibles en el sistema <a href="http://geonode.salahumanitaria.co" target="_blank">GEONODE</a>
                    de Sala Humanitaria</a>, las cuales pueden ser visualizadas en monitor
                </p>
                <div id="layers_loading" class="hide">
                    <img src="media/img/ajax-loader-mini.gif" /> Cargando capa....
                </div>
                <ul id="layers_ul">
                    <li>
                        <div class="left chk">
                            <input type="checkbox" data-n="División Departamental de Colombia - SIGOT, IGAC" value="division_departamental_de_colombia_sigot_igac" />
                        </div>
                        <div class="left">
                            <h3>División Departamental de Colombia - SIGOT, IGAC</h3>
                            <p class="nota">Abstract</p>
                        </div>
                        <div class="clear"></div>
                    </li>
                    <li>
                        <div class="left chk">
                            <input type="checkbox" data-n="División Municipal de Colombia - SIGOT, IGAC" value="municipio_sigot" />
                        </div>
                        <div class="left">
                            <h3>División Municipal de Colombia - SIGOT, IGAC</h3>
                            <p class="nota">Abstract</p>
                        </div>
                        <div class="clear"></div>
                    </li>
                </ul>
            <?php
            }
            else { ?>
                <p>El servicio: <a href="http://geonode.<?php echo $sala ?>" target="_blank">http://geonode.<?php echo $sala ?></a> no se encuentra disponible</p>
            <?php
            }
            ?>
        </div>
        <div id="totalxd" class="relative">
            <div id="loading_data" class="alpha60">
                <img src="<?php echo BASE ?>media/img/ajax-loader.png" />
                    &nbsp;Cargando datos....
            </div>
            <div id="data">
                <div id="minmax_total" class="minimize"></div>
                <div id="tabs">
                  <ul>
                    <li><a href="#tendencia">Tendencia</a></li>
                    <li><a href="#resumen">Resumen</a></li>
                    <li><a href="#departamentos">Departamentos</a></li>
                  </ul>
                  <div id="tendencia">
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
    <script type="text/javascript" src="<?php echo BASE ?>media/js/fe.js"></script>
    <script type="text/javascript" src="<?php echo BASE ?>media/js/map.js"></script>
    <script type="text/javascript" src="<?php echo BASE ?>media/js/url_tools.min.js"></script>
    <script type="text/javascript" src="<?php echo BASE ?>media/js/highcharts.js"></script>
    <script type="text/javascript" src="<?php echo BASE ?>media/js/icheck.min.js"></script>
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


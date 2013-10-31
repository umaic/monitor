<?php
if ($_SERVER['SERVER_NAME'] == 'localhost') {
    define ('BASE_PORTAL', '/monitor/');  // Comienza con slash, se usa al incluir los assets
} 
else {
    define ('BASE_PORTAL', '/');  // Comienza con slash, se usa al incluir los assets
}
?>
<link type="text/css" rel="stylesheet" href="<?php echo BASE_PORTAL ?>media/css/portal.css">
<link type="text/css" rel="stylesheet" href="<?php echo BASE_PORTAL ?>media/css/jquery-ui-1.8.22.custom.css" />
<link type="text/css" rel="stylesheet" href="<?php echo BASE_PORTAL ?>media/css/perfect-scrollbar.min.css" />

<?php 
$meses = array('Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre');
?>
        <div id="tec" class="tecdn hide">
            <a href="http://www.colombiassh.org/emergenciacompleja/" target="_blank">
                <img src="<?php echo BASE_PORTAL ?>media/img/logo_ec.png" />
            </a>
            <div class="it tot">Total Eventos: <b><?php echo number_format($tec) ?></b></div>
            <div class="cat it">
                <div class="inline">Categorias</div>
                <div class="inline arrow-down"></div>
            </div>
            <div id="fcat_ec" class="filtro fcat">
                <div class="left">
                     <h2>Categorias Conflicto Armado</h2>
                     <br />
                    <div class="inline linko">
                        <a href="http://www.colombiassh.org/gtmi/wiki/index.php/Sistema_de_categor%C3%ADas_del_m%C3%B3dulo_de_eventos_de_conflicto" target="_blank">Definici&oacute;n de categorias</a>
                    </div>
                    <div class="inline">
                    <a class="tn_fcat" href="#">|&nbsp;Seleccionar todas/ninguna</a>
                    </div>
                </div>
                <div class="right">
                    <a class="close" href="#"><img src="<?php echo BASE_PORTAL ?>media/img/close.png" alt="Cerrar" /></a>
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
        <div id="tdn" class="tecdn hide">
            <a href="http://inundaciones.colombiassh.org" target="_blank">
                <img src="<?php echo BASE_PORTAL ?>media/img/logo_dn.png" />
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
                    <a class="close" href="#"><img src="<?php echo BASE_PORTAL ?>media/img/close.png" alt="Cerrar" /></a>
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
    <div id="loading" class="alpha60">
        <img src="<?php echo BASE_PORTAL ?>media/img/ajax-loader.png" />
    </div>
    <div id="content" class="clear">
            <div id="menu">
                <div id="aaaa" class="left">
                    <input type="hidden" id="currentCatE" value="0">
                    <input type="hidden" id="currentCatD" value="0">
                    <input type="hidden" id="startDate" value="">
                    <input type="hidden" id="endDate" value="">
                    <input type="hidden" id="yyyy_ini" value="">
                    <input type="hidden" id="yyyy_fin" value="">
                    <div id="time" class="">
                        <label class="inline">Consultar:</label>
                        <select id="stime">
                            <option value="s" selected>Ultima semana</option>
                            <option value="m">Ultimo mes</option>
                            <option value="a">Todo el a&ntilde;o</option>
                        </select>
                        &nbsp;
                        <div class="inline" id="periodo_texto"></div>
                    </div>
                </div>
                <div id="mapas_tipos" class="right">
                    <div class="mapa_tipo activo inline" data-tipo="afectacion">
                        <img src="<?php echo BASE ?>media/img/people_affected_population_24px_icon.png" class="left" />
                        Mapa de personas afectadas
                    </div>
                    <div class="mapa_tipo inline" data-tipo="eventos">
                        <img src="<?php echo BASE ?>media/img/activity_scale_operation_24px_icon.png" class="left" />
                        Mapa de n&uacute;mero de eventos
                    </div>
                    
                    <div id="group_fts" class="inline ungroup">Desagrupar mapa por categoria</div>
                </div>
            </div>
        <div class="clear"></div>
        <div id="content_map_tabs">
            <div class="left">
                <div id="map"></div>
            </div>
            <div id="featured" class="">
                <div><b>Eventos destacados por:</b></div>
                <div id="t">- Movilizaci&oacute;n social <br />- Paro</div>
                 
            </div>
            <div class="left" id="tabs">
                <div id="loading_data" class="alpha60">
                    <img src="<?php echo BASE ?>media/img/ajax-loader.png" />
                    &nbsp;Cargando datos....
                </div>
                  <ul>
                    <li><a href="#resumen">Resumen por categorias</a></li>
                    <li><a href="#lista">Lista de eventos</a></li>
                  </ul>
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
                  <div id="lista">
                    <div id="totales" class="right">
                        <div class="total">
                            <div id="num_total" class="inline">Total: <span id="num_total_span"></span></div>
                            <div id="num_total_ec" class="inline violencia">Violencia: <span id="num_total_ec_span"></span></div>
                            <div id="num_total_dn" class="inline desastres">Desastres: <span id="num_total_dn_span"></span></div>
                        </div>
                    </div>
                    <div id="incidentes" class=""></div>
                </div>
            </div>
        </div>
        <div class="clear"></div>
    </div>
    <script type="text/javascript" src="<?php echo BASE_PORTAL ?>media/js/jquery.min.js"></script>
    <script type="text/javascript" src="<?php echo BASE_PORTAL ?>media/js/jquery-ui.min.js"></script>
    <script type="text/javascript" src="<?php echo BASE_PORTAL ?>media/js/openlayers/OpenLayers.min.js"></script>
    <script type="text/javascript" src="<?php echo BASE_PORTAL ?>media/js/url_tools.min.js"></script>
    <script type="text/javascript" src="<?php echo BASE_PORTAL ?>media/js/fe.js"></script>
    <script type="text/javascript" src="<?php echo BASE_PORTAL ?>media/js/map.js"></script>
    <script type="text/javascript" src="<?php echo BASE_PORTAL ?>media/js/highcharts.js"></script>
    <script type="text/javascript">
        var portal = 1;
        $(function() {
            /*
            $('.tab').click(function() {
                $('.tab_data').hide();
                $('.tab').removeClass('active');
                $('#' + $(this).attr('show')).show();
                $(this).addClass('active');
            });
            */
            
            // Row events
            $('#incidentes').on("click",".t",function() {
                $(this).parent('div').find('.hide').toggle();
            });
            
        });
    </script>
    <!--  Para simular la tabla de deptos y que funcione getStateChecked() en fe.js-->
    <div id="table_totalxd" class="hide">
        <input type="hidden" id="state" centroid="<?php echo $centroid ?>" />
        <input type="checkbox" value="0" /> <!-- Se deja el primero vacio simulando el Cheeck all/ -->
        <input type="checkbox" value="<?php echo $state_id ?>" checked />
    </div>
    
</body>
</html>

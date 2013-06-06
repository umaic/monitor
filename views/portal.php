<?php define('BASE_PORTAL', '/monitor/'); ?>
<link type="text/css" rel="stylesheet" href="<?php echo BASE_PORTAL ?>media/css/portal.min.css">
<link type="text/css" rel="stylesheet" href="<?php echo BASE_PORTAL ?>media/css/jquery-ui-1.8.22.custom.min.css" />

<?php 
$meses = array('Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre');
?>

<body>
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
        <div class="left">
            <div id="menu">
                <div id="aaaa">
                    <input type="hidden" id="currentCatE" value="0">
                    <input type="hidden" id="currentCatD" value="0">
                    <input type="hidden" id="startDate" value="">
                    <input type="hidden" id="endDate" value="">
                    <input type="hidden" id="yyyy_ini" value="">
                    <input type="hidden" id="yyyy_fin" value="">
                    <div id="time" class="inline">
                        <label class="inline">Consultar:</label>
                        <select id="stime">
                            <option value="a">Todo el a&ntilde;o</option>
                            <option value="m" selected="selected">Ultimo mes</option>
                            <option value="s">Ultima semana</option>
                        </select>
                    </div>
                </div>
                <div class="clear"></div>
            </div>
            <div id="map"></div>
        </div>
        <div id="incidentes" class="right">
        </div>
        <div class="clear"></div>
    </div>
    <script type="text/javascript" src="<?php echo BASE_PORTAL ?>media/js/jquery-1.7.2.min.js"></script>
    <script type="text/javascript" src="<?php echo BASE_PORTAL ?>media/js/jquery-ui-1.8.21.custom.min.js"></script>
    <script type="text/javascript" src="<?php echo BASE_PORTAL ?>media/js/openlayers/OpenLayers.min.js"></script>
    <script type="text/javascript" src="<?php echo BASE_PORTAL ?>media/js/openlayers/LoadingPanel.min.js"></script>
    <script type="text/javascript" src="<?php echo BASE_PORTAL ?>media/js/jquery.tablesorter.min.js"></script>
    <script type="text/javascript" src="<?php echo BASE_PORTAL ?>media/js/url_tools.min.js"></script>
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
    <script type="text/javascript" src="<?php echo BASE_PORTAL ?>media/js/fe.min.js"></script>
    <script type="text/javascript" src="<?php echo BASE_PORTAL ?>media/js/map.min.js"></script>
    <!--  Para simular la tabla de deptos y que funcione getStateChecked() en fe.js-->
    <div id="table_totalxd" class="hide">
        <input type="hidden" id="state" centroid="<?php echo $centroid ?>" />
        <input type="checkbox" value="0" /> <!-- Se deja el primero vacio simulando el Cheeck all/ -->
        <input type="checkbox" value="<?php echo $state_id ?>" checked />
    </div>
    
</body>
</html>


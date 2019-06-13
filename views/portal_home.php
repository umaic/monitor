<?php
if ($_SERVER['SERVER_NAME'] == 'localhost') {
    define ('BASE_PORTAL', '/monitor/');  // Comienza con slash, se usa al incluir los assets
}
else {
    define ('BASE_PORTAL', '/');  // Comienza con slash, se usa al incluir los assets
}
?>
<link href="https://fonts.googleapis.com/css?family=Roboto&display=swap" rel="stylesheet">
<link type="text/css" rel="stylesheet" href="<?php echo BASE_PORTAL ?>media/css/m_portal_home.css" />

<?php
$meses = array('Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre');
?>
    <div id="content" class="clear">
        <div class="">
            <div id="menu_portal_home">
                <div id="aaaa">
                    <input type="hidden" id="currentCatE" value="0">
                    <input type="hidden" id="currentCatD" value="0">
                    <input type="hidden" id="startDate" value="">
                    <input type="hidden" id="endDate" value="">
                    <input type="hidden" id="yyyy_ini" value="">
                    <input type="hidden" id="yyyy_fin" value="">
                    <div id="time" class="">
                        <select id="stime">
                            <option value="s" selected>Ultima semana</option>
                            <option value="m">Ultimo mes</option>
                            <option value="acum">Acumulado <?php echo $totalxy[0] ?></option>
                            <optgroup label="Años">
                            <!--<option value="a">Todo el a&ntilde;o</option>-->
                            <?php
                            foreach($totalxy as $_a) { ?>
                                <option value="<?php echo $_a ?>">Todo <?php echo $_a ?></option>
                            <?php
                            }
                            ?>
                            </optgroup>
                        </select>
                        &nbsp;
                        <div class="inline" id="periodo_texto"></div>
                    </div>
                </div>
            </div>
        </div>
        <div id="brand">
            Visualización de # de afectados en eventos georeferenciados de <span style="color:#7FB92F;">desastres naturales</span> y <span style="color:#E56A54;">violencia armada</span>
        </div>
        <div id="map" class="map_portal_home"></div>
        <div>
            <div id="resumen">
                <div id="loading" class="alpha60">
                    <img src="<?php echo BASE_PORTAL ?>media/img/ajax-loader.png" />
                </div>
                <div id="des_div" class="r">
                    <h4 class="left">Desplazamientos</h4><h3 id="des" class="right"></h3>
                    <div class="clear nota">
                        <b><u>No. de personas</u></b> desplazadas en eventos masivos
                        (50 o más personas, o 10 o más familias desplazadas en un mismo evento). Información</div>
                </div>
                <div id="con_div" class="r">
                    <h4 class="left">Confinados</h4><h3 id="con" class="right"></h3>
                    <div class="clear nota">
                        <b><u>No. personas</u></b> de confinadas, se refiere a personas afectadas por limitaciones a
                        la movilidad que sufren al mismo tiempo restricciones para acceder a por lo menos
                        tres bienes y/o servicios básicos durante un período mínimo de una semana</div>
                </div>
                <div id="acc_div" class="r">
                    <h4 class="left">Acciones b&eacute;licas</h4><h3 id="acc" class="right"></h3>
                    <div class="clear nota">
                         <b><u>No. de eventos</u></b> de violencia entre grupos insurgentes que luchan contra el Estado
                         o contra el orden social vigente, ajustándose a las leyes o costumbres de la guerra, con el fin de mantener, modificar, sustituir o destruir un modelo de Estado o de sociedad. (CINEP, y Justicia y Paz; 1996, pág. 3)</div>
                </div>
                <div id="ataq_div" class="r">
                    <h4 class="left">Ataques a obj. il&iacute;citos de guerra</h4><h3 id="ataq" class="right"></h3>
                    <div class="clear nota"><b><u>No. de eventos</u></b> de Ataques a Bienes culturales y religiosos, Infraestructura vial, misión humanitaria, misión médica, bienes indispensables para la supervivencia de la población civil, misión religiosa</div>
                </div>
                <div id="hom_div" class="r">
                    <h4 class="left">Homicidios en Persona Protegida</h4><h3 id="hom" class="right"></h3>
                    <div class="clear nota"><b><u>No. de personas</u></b> civiles, combatientes que quedan por fuera de la posibilidad de combatir por su condición de heridas, enfermedad, naufragio o que se encuentran privadas de la libertad a causa del conflicto armado interno, y personas que gozan de una protección especial debajo de las normas del DIH.</div>
                </div>
                <div id="ame_div" class="r">
                    <h4 class="left">Amenazas</h4><h3 id="ame" class="right"></h3>
                    <div class="clear nota"></div>
                </div>
            </div>
        </div>
        <div class="clear"></div>
    </div>
    <script type="text/javascript" src="https://unpkg.com/jquery@1/dist/jquery.min.js"></script>
    <script type="text/javascript" src="https://unpkg.com/jquery-migrate@1/dist/jquery-migrate.min.js"></script>
    <script type="text/javascript" src="https://ajax.aspnetcdn.com/ajax/jquery.ui/1.12.1/jquery-ui.min.js"></script>
    <script type="text/javascript" src="<?php echo BASE_PORTAL ?>media/js/m_portal_home.js"></script>
    <script type="text/javascript">
        var portal = 1;
        var layout = 'portal_home';

        $(function(){
            var h = $('div.r').css('height');

            $('div.r').toggle(function(){
                $(this).css('height', 'auto');
            },
            function(){ $(this).css('height', h) });
        });
    </script>

</body>
</html>

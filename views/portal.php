<?php define('BASE_PORTAL', '/monitor/'); ?>
<link type="text/css" rel="stylesheet" href="<?php echo BASE_PORTAL ?>media/css/portal.css">
<link type="text/css" rel="stylesheet" href="<?php echo BASE_PORTAL ?>media/css/jquery-ui-1.8.22.custom.min.css" />
<link type="text/css" rel="stylesheet" href="<?php echo BASE_PORTAL ?>media/css/perfect-scrollbar.min.css" />

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
            <div id="menu">
                <div id="aaaa" class="left">
                    <input type="hidden" id="currentCatE" value="0">
                    <input type="hidden" id="currentCatD" value="0">
                    <input type="hidden" id="startDate" value="">
                    <input type="hidden" id="endDate" value="">
                    <input type="hidden" id="yyyy_ini" value="">
                    <input type="hidden" id="yyyy_fin" value="">
                    <div id="time" class="inline">
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
                <div id="totales" class="right">
                    <div class="total">
                        <div id="num_total" class="inline">Total: <span id="num_total_span"></span></div>
                        <div id="num_total_ec" class="inline violencia">Violencia: <span id="num_total_ec_span"></span></div>
                        <div id="num_total_dn" class="inline desastres">Desastres: <span id="num_total_dn_span"></span></div>
                    </div>
                </div>
            </div>
            <div class="clear"></div>
        <div class="left">
            <div id="map"></div>
        </div>
        <div id="incidentes" class="">
<div class="clear report_list_map report_list_map_desastres"> <div class="l desastres"> D</div> <div class="t clear">Fuerte aguacero en el sur del Atlántico</div> <div class="hide"><div class="date detail">2013-06-13 13:45:00</div> <div class="loc detail">Santa Lucía , Atlántico <span class="pdf opt"> <a href="http://sidih.colombiassh.org/sissh/download_pdf.php?c=2&amp;id_depto=2&amp;id_mun=" target="_blank">Perfil Atlántico</a></span></div> <!--<div--></div><div class="clear hide"><div class="left"><b>Categorias</b></div> <div class="opt right linko"><a href="http://www.colombiassh.org/gtmi/wiki/index.php/Sistema_de_categor%C3%ADas_del_m%C3%B3dulo_de_eventos_de_conflicto" target="_blank">Definición</a></div><div class="clear cat">»&nbsp;Ataque a objetivos ilícitos de guerra: Ataque a infraestructura y/o bienes civiles</div></div><div class="clear"></div> <div class="f hide"><div class="ft">Fuente de noticia</div><div class="fc"><div class="fct">Radio :: Caracol</div><div class="fcc"><b>Descripción tomada de la fuente</b>: "&nbsp;Guerrilleros de las Farc, dinamitaron un tramo del Oleoducto Petronorte, en Norte de Santander,
al oriente del país.
Las autoridades reportaron que la acción violenta se produjo en el sector conocido como
Filogringo, zona rural de Tibú, en la zona del Catatumbo, que desde hace varios días atrás sufre
una arremetida guerrillera.
Las cargas explosivas fueron activadas entre los kilómetros 80 y 90 de la infraestructura
petrolera, según las informaciones preliminares que entregan las autoridades municipales de la
zona.
Petronorte, es una empresa contratista de Ecopetrol en la zona norte de esa región, y anuncia la
activación de un plan de contingencia orientado desde el corregimiento de La Gabarra, para
evitar una emergencia ambiental sobre el Río Catatumbo, que desemboca en el vecino país, en Venezuela.
La fuerza pública en Norte de Santander se encuentra en alerta máxima ante las acciones
violentas que han regresado a esa zona del país y que recientemente cobro la vida de siete
personas, miembros de la policía nacional y civiles"</div><div class="fcc"><a href=" http://www.caracol.com.co/nota.aspx?id=1442645" target="_blank">&nbsp;http://www.caracol.com.co/nota.aspx?id=1442645</a></div></div> </div></div><div class="clear report_list_map report_list_map_violencia"> <div class="l violencia"> V</div> <div class="t clear">MAP. Guerrillas. Cartagena Del Chairá, Caquetá. 13 de Junio de 2013</div> <div class="hide"><div class="date detail">2013-06-13 00:00:00</div> <div class="loc detail">Cartagena del Chairá  <span class="pdf opt"> <a href="http://sidih.colombiassh.org/sissh/download_pdf.php?c=2&amp;id_depto=7&amp;id_mun=" target="_blank">Perfil Caquetá</a></span></div> <!--<div--></div><div class="clear hide"><div class="left"><b>Categorias</b></div> <div class="opt right linko"><a href="http://www.colombiassh.org/gtmi/wiki/index.php/Sistema_de_categor%C3%ADas_del_m%C3%B3dulo_de_eventos_de_conflicto" target="_blank">Definición</a></div><div class="clear cat">»&nbsp;Uso de explosivos remanentes de guerra: MAP</div></div><div class="clear"></div> <div class="f hide"><div class="ft">Fuente de noticia</div><div class="fc"><div class="fct">Prensa :: El Lider</div><div class="fcc"><b>Descripción tomada de la fuente</b>: "&nbsp;Cuando adelantaban una operación ofensiva, en contra de guerrilleros al mando de Javier Yanguma Muñoz, alias Orlando Porcelana, tropas adscritas a la Fuerza de tarea Conjunta Omega, hallaron dos campos minados. Según el general Jaime Alfonso Lasprilla Villamizar, comandante de esa unidad militar; el hallazgo de los puntos, en los que los rebeldes habían diseminado de manera indiscriminada los artefactos explosivos improvisados, tuvo lugar en la vereda Los Comuneros, jurisdicción de Cartagena del Chairá. Hasta ahí llegaron los castrenses y previo a la ofensiva, iniciaron una operación de reconocimiento militar de área y con la ayuda de caninos expertos en detección de explosivos, dieron con las trampas mortales. De acuerdo con el general Lasprilla Villamizar, las cargas estaban compuestas por pentolita y habían sido colocadas por los rebeldes en un sitio que las tropas utilizan como helipuerto para el embarque y desembarco de hombres. Los elementos bélicos fueron destruidos de manera controlada por unidades del grupo de Explosivos y Demoliciones, Exde, de la Fuerza de Tarea Conjunta Omega. Fuente: El Líder."</div></div></div></div><div class="clear report_list_map report_list_map_violencia"> <div class="l violencia"> V</div> <div class="t clear">Ataque a infraestructura y/o bienes civiles. Guerrillas. San José Del Guaviare, Guaviare. 13 de Junio de 2013</div> <div class="hide"><div class="date detail">2013-06-13 00:00:00</div> <div class="loc detail">San José del Guaviare  <span class="pdf opt"> <a href="http://sidih.colombiassh.org/sissh/download_pdf.php?c=2&amp;id_depto=31&amp;id_mun=" target="_blank">Perfil Guaviare</a></span></div> <!--<div--></div><div class="clear hide"><div class="left"><b>Categorias</b></div> <div class="opt right linko"><a href="http://www.colombiassh.org/gtmi/wiki/index.php/Sistema_de_categor%C3%ADas_del_m%C3%B3dulo_de_eventos_de_conflicto" target="_blank">Definición</a></div><div class="clear cat">»&nbsp;Ataque a objetivos ilícitos de guerra: Ataque a infraestructura y/o bienes civiles</div></div><div class="clear"></div> <div class="f hide"><div class="ft">Fuente de noticia</div><div class="fc"><div class="fct">Prensa :: El Colombiano</div><div class="fcc"><b>Descripción tomada de la fuente</b>: "&nbsp;Un bus de que cubría la ruta Granada- San José del Guaviare fue quemado este jueves por guerrilleros del frente séptimo de las Farc, según confirmaron las autoridades. El hecho ocurrió en el sector de El Pororio, lugar donde los subversivos hicieron bajar a los pasajeros y le prendieron fuego al vehículo. El conductor del bus y los pasajeros resultaron ilesos de este atentado de las Farc, indicaron las autoridades. Fuente: El Colombiano."</div></div></div></div><div class="clear report_list_map report_list_map_desastres"> <div class="l desastres"> D</div> <div class="t clear">Casi los deja sin colegio en San Onofre</div> <div class="hide"><div class="date detail">2013-06-12 12:00:00</div> <div class="loc detail">San Onofre , Sucre, Corregimiento San Onofre <span class="pdf opt"> <a href="http://sidih.colombiassh.org/sissh/download_pdf.php?c=2&amp;id_depto=22&amp;id_mun=" target="_blank">Perfil Sucre</a></span></div> <!--<div--></div><div class="clear hide"><div class="left"><b>Categorias</b></div> <div class="opt right linko"><a href="http://www.colombiassh.org/gtmi/wiki/index.php/Sistema_de_categor%C3%ADas_del_m%C3%B3dulo_de_eventos_de_conflicto" target="_blank">Definición</a></div><div class="clear cat">»&nbsp;Ataques contra la población civil: Homicidio intencional en persona protegida</div></div><div class="clear"></div> <div class="f hide"><div class="ft">Fuente de noticia</div><div class="fc"><div class="fct">Radio :: Caracol</div><div class="fcc"><b>Descripción tomada de la fuente</b>: "&nbsp;La juez Penal Especializada, Gloria Constanza Gaona Rodríguez, que llevaba el caso de los tres menores asesinados en el municipio de Tame, fue ultimada por varios impactos de arma de fuego en el municipio de Saravena, en el departamento de Arauca.

Los hechos se registraron en la calle 29 con carrera 16 esquina, pleno centro del municipio de Saravena, en momentos en que la funcionaria de la rama judicial descendía de un vehículo de transporte público, cuando fue agredida con arma de fuego por el sicario que le disparó a quemarropa.

Gaona Rodríguez descendía de un vehículo de la empresa de transporte Cootransaraucana, luego de pasar el puente festivo en una población de Boyacá.

A esta hora las autoridades en Saravena adelantan las labores para el levantamiento del cadáver, mientras que la fuerza pública hizo un despliegue del personal para dar con los responsables.

A este municipio del departamento de Arauca viajó el comandante del Departamento de Policía Arauca, coronel William Javier Guevara Meyer, quien se pondrá al frente de la investigación de este asesinato.

La juez Gaona Rodríguez era la encargada de adelantar las investigaciones por la violación y posterior asesinato de tres menores de edad en Tame, Arauca, al parecer a manos de un subteniente del Ejército.

A finales de febrero, durante la audiencia preparatoria de juicio contra el subteniente Raúl Muñoz, la juez denunció maniobras dilatorias por parte de la defensa del militar."</div><div class="fcc"><a href=" http://www.caracol.com.co/nota.aspx?id=1442749" target="_blank">&nbsp;http://www.caracol.com.co/nota.aspx?id=1442749</a></div></div> </div></div><div class="clear report_list_map report_list_map_violencia"> <div class="l violencia"> V</div> <div class="t clear">Combates. Fuerzas Armadas Estatales Vs Guerrillas. El Litoral Del San Juan, Chocó. 12 de Junio de 2013</div> <div class="hide"><div class="date detail">2013-06-12 00:00:00</div> <div class="loc detail">El Litoral de San Juan <span class="pdf opt"> <a href="http://sidih.colombiassh.org/sissh/download_pdf.php?c=2&amp;id_depto=12&amp;id_mun=" target="_blank">Perfil Chocó</a></span></div> <!--<div--></div><div class="clear hide"><div class="left"><b>Categorias</b></div> <div class="opt right linko"><a href="http://www.colombiassh.org/gtmi/wiki/index.php/Sistema_de_categor%C3%ADas_del_m%C3%B3dulo_de_eventos_de_conflicto" target="_blank">Definición</a></div><div class="clear cat">»&nbsp;Acciones Bélicas: Combates</div></div><div class="clear"></div> <div class="f hide"><div class="ft">Fuente de noticia</div><div class="fc"><div class="fct">Prensa :: El País</div><div class="fcc"><b>Descripción tomada de la fuente</b>: "&nbsp;Al menos cuatro guerrilleros de la estructura de seguridad de la columna móvil 'Libardo García' de las Farc, habrían muerto durante una acción conjunta de las fuerzas militares en el departamento del Chocó. Con información de inteligencia proporcionada por el Ejército, aeronaves de combate y reconocimiento de la Fuerza Aérea Colombiana, FAC, lograron la ubicación y posterior neutralización de un campamento ubicada en cercanías del río Munguidó, litoral del Bajo San Juan. Como resultado parcial, las autoridades reportaron cuatro guerrilleros abatidos pendientes de ser identificados por pruebas dactilares. Versiones extraoficiales dan cuenta de que serían nueve los guerrilleros dados de baja. En la operación, los uniformados decomisaron nueve fusiles y una pistola, material para la inteligencia militar, así como la incautación de dos embarcaciones con motores fuera de borda, que fueron puestas a órdenes de las autoridades competentes. Fuente: El País."</div></div></div></div><div class="clear report_list_map report_list_map_violencia"> <div class="l violencia"> V</div> <div class="t clear">MAP. Guerrillas. Tumaco, Nariño. 12 de Junio de 2013</div> <div class="hide"><div class="date detail">2013-06-12 00:00:00</div> <div class="loc detail">Tumaco  <span class="pdf opt"> <a href="http://sidih.colombiassh.org/sissh/download_pdf.php?c=2&amp;id_depto=17&amp;id_mun=" target="_blank">Perfil Nariño</a></span></div> <!--<div--></div><div class="clear hide"><div class="left"><b>Categorias</b></div> <div class="opt right linko"><a href="http://www.colombiassh.org/gtmi/wiki/index.php/Sistema_de_categor%C3%ADas_del_m%C3%B3dulo_de_eventos_de_conflicto" target="_blank">Definición</a></div><div class="clear cat">»&nbsp;Uso de explosivos remanentes de guerra: MAP</div></div><div class="clear"></div> </div><div class="clear report_list_map report_list_map_violencia"> <div class="l violencia"> V</div> <div class="t clear">Artefacto Explosivo Improvisado - AEI. Guerrillas. La Montañita, Caquetá. 12 de Junio de 2013</div> <div class="hide"><div class="date detail">2013-06-12 00:00:00</div> <div class="loc detail">La Montañita  <span class="pdf opt"> <a href="http://sidih.colombiassh.org/sissh/download_pdf.php?c=2&amp;id_depto=7&amp;id_mun=" target="_blank">Perfil Caquetá</a></span></div> <!--<div--></div><div class="clear hide"><div class="left"><b>Categorias</b></div> <div class="opt right linko"><a href="http://www.colombiassh.org/gtmi/wiki/index.php/Sistema_de_categor%C3%ADas_del_m%C3%B3dulo_de_eventos_de_conflicto" target="_blank">Definición</a></div><div class="clear cat">»&nbsp;Uso de explosivos remanentes de guerra: Artefacto Explosivo Improvisado - AEI</div></div><div class="clear"></div> <div class="f hide"><div class="ft">Fuente de noticia</div><div class="fc"><div class="fct">Entidad Pública :: Ejército</div><div class="fcc"><b>Descripción tomada de la fuente</b>: "&nbsp;Tropas del Batallón de Infantería No.35, de la Décima Segunda Brigada, neutralizaron un ataque terrorista al localizar y destruir un cilindro bomba de 20 libras, compuesto por metralla, TNT y pólvora negra, con sistema de activación por telemando, listo para ser activado por parte de integrantes de la cuadrilla 15 de las Farc, en la inspección de la Unión Peneya, municipio de la Montañita, Caquetá. Fuente: Ejército Nacional"</div></div></div></div><div class="clear report_list_map report_list_map_violencia"> <div class="l violencia"> V</div> <div class="t clear">Desplazamiento masivo Intermunicipal. Guerrillas. Sipí, Chocó. 12 de Junio de 2013</div> <div class="hide"><div class="date detail">2013-06-12 00:00:00</div> <div class="loc detail">Sipí  <span class="pdf opt"> <a href="http://sidih.colombiassh.org/sissh/download_pdf.php?c=2&amp;id_depto=12&amp;id_mun=" target="_blank">Perfil Chocó</a></span></div> <!--<div--></div><div class="clear hide"><div class="left"><b>Categorias</b></div> <div class="opt right linko"><a href="http://www.colombiassh.org/gtmi/wiki/index.php/Sistema_de_categor%C3%ADas_del_m%C3%B3dulo_de_eventos_de_conflicto" target="_blank">Definición</a></div><div class="clear cat">»&nbsp;Desplazamiento: Desplazamiento masivo Intermunicipal</div></div><div class="clear"></div> </div><div class="clear report_list_map report_list_map_violencia"> <div class="l violencia"> V</div> <div class="t clear">MAP. Guerrillas. Puerto Rico, Caquetá. 12 de Junio de 2013</div> <div class="hide"><div class="date detail">2013-06-12 00:00:00</div> <div class="loc detail">Puerto Rico  <span class="pdf opt"> <a href="http://sidih.colombiassh.org/sissh/download_pdf.php?c=2&amp;id_depto=7&amp;id_mun=" target="_blank">Perfil Caquetá</a></span></div> <!--<div--></div><div class="clear hide"><div class="left"><b>Categorias</b></div> <div class="opt right linko"><a href="http://www.colombiassh.org/gtmi/wiki/index.php/Sistema_de_categor%C3%ADas_del_m%C3%B3dulo_de_eventos_de_conflicto" target="_blank">Definición</a></div><div class="clear cat">»&nbsp;Uso de explosivos remanentes de guerra: MAP</div></div><div class="clear"></div> <div class="f hide"><div class="ft">Fuente de noticia</div><div class="fc"><div class="fct">Entidad Pública :: Ejército</div><div class="fcc"><b>Descripción tomada de la fuente</b>: "&nbsp;En la vereda La Campana del municipio de Puerto Rico, Caquetá, unidades del Comando Operativo No.6 localizaron un campo minado instalado por la compañía Sonia la Pilosa de la Columna Móvil Teófilo Forero Castro. El campo minado estaba integrado por un cilindro bomba de 40 libras y 40 artefactos explosivos tipo mina antipersonal, compuestos por explosivo tipo pentolita, con baterías de 9 voltios y cable de cobre, en tubos de PVC. De acuerdo con las informaciones de las tropas, el campo minado tenía una extensión aproximada de 300 metros y estaba ubicado en un potrero, con el propósito de ser activado controladamente al paso de una unidad de la Fuerza Pública. Fuente: Ejército Nacional."</div></div></div></div><div class="clear report_list_map report_list_map_violencia"> <div class="l violencia"> V</div> <div class="t clear">Amenazas (individuales/colectivas). Sin determinar. Samaniego, Nariño. 12 de Junio de 2013</div> <div class="hide"><div class="date detail">2013-06-12 00:00:00</div> <div class="loc detail">Samaniego  <span class="pdf opt"> <a href="http://sidih.colombiassh.org/sissh/download_pdf.php?c=2&amp;id_depto=17&amp;id_mun=" target="_blank">Perfil Nariño</a></span></div> <!--<div--></div><div class="clear hide"><div class="left"><b>Categorias</b></div> <div class="opt right linko"><a href="http://www.colombiassh.org/gtmi/wiki/index.php/Sistema_de_categor%C3%ADas_del_m%C3%B3dulo_de_eventos_de_conflicto" target="_blank">Definición</a></div><div class="clear cat">»&nbsp;Ataques contra la población civil: Amenazas (individuales/colectivas)</div></div><div class="clear"></div> </div><div class="clear report_list_map report_list_map_violencia"> <div class="l violencia"> V</div> <div class="t clear">Emboscada. Fuerzas Armadas Estatales. El Litoral Del San Juan, Chocó. 12 de Junio de 2013</div> <div class="hide"><div class="date detail">2013-06-12 00:00:00</div> <div class="loc detail">El Litoral de San Juan <span class="pdf opt"> <a href="http://sidih.colombiassh.org/sissh/download_pdf.php?c=2&amp;id_depto=12&amp;id_mun=" target="_blank">Perfil Chocó</a></span></div> <!--<div--></div><div class="clear hide"><div class="left"><b>Categorias</b></div> <div class="opt right linko"><a href="http://www.colombiassh.org/gtmi/wiki/index.php/Sistema_de_categor%C3%ADas_del_m%C3%B3dulo_de_eventos_de_conflicto" target="_blank">Definición</a></div><div class="clear cat">»&nbsp;Acciones Bélicas: Emboscada</div></div><div class="clear"></div> <div class="f hide"><div class="ft">Fuente de noticia</div><div class="fc"><div class="fct">Prensa :: El Espectador</div><div class="fcc"><b>Descripción tomada de la fuente</b>: "Al menos cuatro guerrilleros de la estructura de seguridad de la columna móvil 'Libardo García' de las Farc, habrían muerto durante una acción conjunta de las fuerzas militares en el departamento del Chocó.

Con información de inteligencia proporcionada por el Ejército, aeronaves de combate y reconocimiento de la Fuerza Aérea Colombiana, FAC, lograron la ubicación y posterior neutralización de un campamento ubicada en cercanías del río Munguidó, litoral del Bajo San Juan.&nbsp;"</div><div class="fcc"><a href="http://www.elpais.com.co/elpais/judicial/noticias/ejercito-abatio-cuatro-guerrilleros-farc-zona-rural-choco " target="_blank">http://www.elpais.com.co/elpais/judicial/noticias/ejercito-abatio-cuatro-guerrilleros-farc-zona-rural-choco&nbsp;</a></div></div> </div></div><div class="clear report_list_map report_list_map_violencia"> <div class="l violencia"> V</div> <div class="t clear">Amenazas (individuales/colectivas). Sin determinar. Valledupar, Cesar. 12 de Junio de 2013</div> <div class="hide"><div class="date detail">2013-06-12 00:00:00</div> <div class="loc detail">Valledupar  <span class="pdf opt"> <a href="http://sidih.colombiassh.org/sissh/download_pdf.php?c=2&amp;id_depto=9&amp;id_mun=" target="_blank">Perfil Cesar</a></span></div> <!--<div--></div><div class="clear hide"><div class="left"><b>Categorias</b></div> <div class="opt right linko"><a href="http://www.colombiassh.org/gtmi/wiki/index.php/Sistema_de_categor%C3%ADas_del_m%C3%B3dulo_de_eventos_de_conflicto" target="_blank">Definición</a></div><div class="clear cat">»&nbsp;Ataques contra la población civil: Amenazas (individuales/colectivas)</div></div><div class="clear"></div> <div class="f hide"><div class="ft">Fuente de noticia</div><div class="fc"><div class="fct">Prensa :: El Pilón</div><div class="fcc"><b>Descripción tomada de la fuente</b>: "&nbsp;Los corregidores e inspectores de Policías de los corregimientos de Valledupar, expresaron su preocupación por las amenazas de muerte que se ciernen contra el titular del corregimiento de Río Seco, Luis Eduardo Vega Daza, a quien lo han sentenciado de manera directa. De acuerdo con la denuncia, Vega Daza se dirigía hacia Valledupar en su vehículo y fue interceptado por dos hombres que se transportaban en una motocicleta. El Corregidor indicó que esos hombres me dijeron que si había visto un encargo que me dejaron en mi escritorio. Les respondí que era un sapo muerto y me advirtieron que así me iba a pasar a mí, que no iba a saber por dónde me iban a entrar los tiros. Otros inspectores de la zona, también recibieron amenazas. Fuente: El Pilón."</div></div></div></div><div class="clear report_list_map report_list_map_violencia"> <div class="l violencia"> V</div> <div class="t clear">Artefacto Explosivo Improvisado - AEI. Guerrillas. La Salina, Casanare. 12 de Junio de 2013</div> <div class="hide"><div class="date detail">2013-06-12 00:00:00</div> <div class="loc detail">La Salina  <span class="pdf opt"> <a href="http://sidih.colombiassh.org/sissh/download_pdf.php?c=2&amp;id_depto=26&amp;id_mun=" target="_blank">Perfil Casanare</a></span></div> <!--<div--></div><div class="clear hide"><div class="left"><b>Categorias</b></div> <div class="opt right linko"><a href="http://www.colombiassh.org/gtmi/wiki/index.php/Sistema_de_categor%C3%ADas_del_m%C3%B3dulo_de_eventos_de_conflicto" target="_blank">Definición</a></div><div class="clear cat">»&nbsp;Uso de explosivos remanentes de guerra: Artefacto Explosivo Improvisado - AEI</div></div><div class="clear"></div> </div><div class="clear report_list_map report_list_map_desastres"> <div class="l desastres"> D</div> <div class="t clear">Asamblea sesionó en Ayapel</div> <div class="hide"><div class="date detail">2013-06-11 13:36:00</div> <div class="loc detail">Ayapel , Córdoba <span class="pdf opt"> <a href="http://sidih.colombiassh.org/sissh/download_pdf.php?c=2&amp;id_depto=10&amp;id_mun=" target="_blank">Perfil Córdoba</a></span></div> <!--<div--></div><div class="clear hide"><div class="left"><b>Categorias</b></div> <div class="opt right linko"><a href="http://www.colombiassh.org/gtmi/wiki/index.php/Sistema_de_categor%C3%ADas_del_m%C3%B3dulo_de_eventos_de_conflicto" target="_blank">Definición</a></div><div class="clear cat">»&nbsp;Acciones Bélicas: Bloqueo de vías/Retén ilegal</div></div><div class="clear"></div> <div class="f hide"><div class="ft">Fuente de noticia</div><div class="fc"><div class="fct">Prensa :: Diario del Sur</div><div class="fcc"><b>Descripción tomada de la fuente</b>: "&nbsp;Luego de que la guerrilla de las
Farc amenazara con quemar los vehículos que transitaran por la vía que une a
los corregimientos de Puerto Vega y Teteyé, el Ejército recuperó la seguridad y
el restablecimiento del transporte entre los sectores. Según se conoció, varios
guerrilleros de las Farc a través de un retén anunciaban a los conductores que
transitaban por la zona que se abstuvieran de regresar porque iban a quemar
sus vehículos. Sin embargo, horas más tarde el Ejército asumió el control de la
zona para garantizar el normal transporte de carga y pasajeros entre estas dos
zonas del municipio de Puerto Asís."</div></div></div></div><div class="clear report_list_map report_list_map_desastres"> <div class="l desastres"> D</div> <div class="t clear">Vendaval en Santa Lucía dejó 43 casas destechadas</div> <div class="hide"><div class="date detail">2013-06-11 12:00:00</div> <div class="loc detail">Santa Lucía , Atlántico <span class="pdf opt"> <a href="http://sidih.colombiassh.org/sissh/download_pdf.php?c=2&amp;id_depto=2&amp;id_mun=" target="_blank">Perfil Atlántico</a></span></div> <!--<div--></div><div class="clear hide"><div class="left"><b>Categorias</b></div> <div class="opt right linko"><a href="http://www.colombiassh.org/gtmi/wiki/index.php/Sistema_de_categor%C3%ADas_del_m%C3%B3dulo_de_eventos_de_conflicto" target="_blank">Definición</a></div><div class="clear cat">»&nbsp;Ataques contra la población civil: Desaparición forzada</div></div><div class="clear"></div> <div class="f hide"><div class="ft">Fuente de noticia</div><div class="fc"><div class="fct">Prensa :: El País</div><div class="fcc"><b>Descripción tomada de la fuente</b>: "&nbsp;El pasado miércoles 2
de marzo llegaron tres hombres a la casa de la líder Ana Julia Renteria,
presidenta de la junta del Consejo Comunitario del río Cajambre y la
desaparecieron. Según testigos, a la líder le dijeron que la invitaban a una
reunión y que fuera con ellos, la cual se negó, ante la insistencia, ella
respondió que iría en su propia lancha y se hizo acompañar de su esposo
Miguel Santos Rentería Caicedo y hasta la fecha no han regresado. Cabe
resaltar que estos hechos son posteriores a la aparición de panfletos
amenazantes que fueron distribuidos a mediados del año pasado, donde son
amenazados los integrantes de las organizaciones sociales."</div></div></div></div><div class="clear report_list_map report_list_map_violencia"> <div class="l violencia"> V</div> <div class="t clear">Artefacto Explosivo Improvisado - AEI. Guerrillas. Jambaló, Cauca. 11 de Junio de 2013</div> <div class="hide"><div class="date detail">2013-06-11 00:00:00</div> <div class="loc detail">Jambaló  <span class="pdf opt"> <a href="http://sidih.colombiassh.org/sissh/download_pdf.php?c=2&amp;id_depto=8&amp;id_mun=" target="_blank">Perfil Cauca</a></span></div> <!--<div--></div><div class="clear hide"><div class="left"><b>Categorias</b></div> <div class="opt right linko"><a href="http://www.colombiassh.org/gtmi/wiki/index.php/Sistema_de_categor%C3%ADas_del_m%C3%B3dulo_de_eventos_de_conflicto" target="_blank">Definición</a></div><div class="clear cat">»&nbsp;Uso de explosivos remanentes de guerra: Artefacto Explosivo Improvisado - AEI</div></div><div class="clear"></div> </div><div class="clear report_list_map report_list_map_violencia"> <div class="l violencia"> V</div> <div class="t clear">MAP. Guerrillas. Suárez, Cauca. 11 de Junio de 2013</div> <div class="hide"><div class="date detail">2013-06-11 00:00:00</div> <div class="loc detail">Suárez  <span class="pdf opt"> <a href="http://sidih.colombiassh.org/sissh/download_pdf.php?c=2&amp;id_depto=8&amp;id_mun=" target="_blank">Perfil Cauca</a></span></div> <!--<div--></div><div class="clear hide"><div class="left"><b>Categorias</b></div> <div class="opt right linko"><a href="http://www.colombiassh.org/gtmi/wiki/index.php/Sistema_de_categor%C3%ADas_del_m%C3%B3dulo_de_eventos_de_conflicto" target="_blank">Definición</a></div><div class="clear cat">»&nbsp;Uso de explosivos remanentes de guerra: MAP</div></div><div class="clear"></div> </div><div class="clear report_list_map report_list_map_violencia"> <div class="l violencia"> V</div> <div class="t clear">Artefacto Explosivo Improvisado - AEI. Sin determinar. San Vicente Del Caguán, Caquetá. 11 de Junio de 2013</div> <div class="hide"><div class="date detail">2013-06-11 00:00:00</div> <div class="loc detail">San Vicente del Caguán  <span class="pdf opt"> <a href="http://sidih.colombiassh.org/sissh/download_pdf.php?c=2&amp;id_depto=7&amp;id_mun=" target="_blank">Perfil Caquetá</a></span></div> <!--<div--></div><div class="clear hide"><div class="left"><b>Categorias</b></div> <div class="opt right linko"><a href="http://www.colombiassh.org/gtmi/wiki/index.php/Sistema_de_categor%C3%ADas_del_m%C3%B3dulo_de_eventos_de_conflicto" target="_blank">Definición</a></div><div class="clear cat">»&nbsp;Uso de explosivos remanentes de guerra: Artefacto Explosivo Improvisado - AEI</div></div><div class="clear"></div> </div><div class="clear report_list_map report_list_map_violencia"> <div class="l violencia"> V</div> <div class="t clear">Ataque a infraestructura y/o bienes civiles. Sin determinar. Espinal, Tolima. 11 de Junio de 2013</div> <div class="hide"><div class="date detail">2013-06-11 00:00:00</div> <div class="loc detail">Espinal  <span class="pdf opt"> <a href="http://sidih.colombiassh.org/sissh/download_pdf.php?c=2&amp;id_depto=23&amp;id_mun=" target="_blank">Perfil Tolima</a></span></div> <!--<div--></div><div class="clear hide"><div class="left"><b>Categorias</b></div> <div class="opt right linko"><a href="http://www.colombiassh.org/gtmi/wiki/index.php/Sistema_de_categor%C3%ADas_del_m%C3%B3dulo_de_eventos_de_conflicto" target="_blank">Definición</a></div><div class="clear cat">»&nbsp;Ataque a objetivos ilícitos de guerra: Ataque a infraestructura y/o bienes civiles</div></div><div class="clear"></div> <div class="f hide"><div class="ft">Fuente de noticia</div><div class="fc"><div class="fct">Agencia UN :: UNDSS</div><div class="fcc"><b>Descripción tomada de la fuente</b>: "&nbsp;UNDSS recibió información de fuentes abiertas sobre, una  explosión la cual  se produjo en el Municipio de Espinal  departamento del Tolima contra  las instalaciones de la empresa de transporte Cootranstol,  la 
la onda explosiva afecto el área administrativa y los talleres de la empresa.  Por la acción una persona resulto herida. 
  
En este momento las autoridades tratan de establecer   los móviles del  atentado."</div></div></div></div><div class="clear report_list_map report_list_map_violencia"> <div class="l violencia"> V</div> <div class="t clear">Hostigamiento. Guerrillas. Barrancas, La Guajira. 11 de Junio de 2013</div> <div class="hide"><div class="date detail">2013-06-11 00:00:00</div> <div class="loc detail">Barrancas  <span class="pdf opt"> <a href="http://sidih.colombiassh.org/sissh/download_pdf.php?c=2&amp;id_depto=14&amp;id_mun=" target="_blank">Perfil La Guajira</a></span></div> <!--<div--></div><div class="clear hide"><div class="left"><b>Categorias</b></div> <div class="opt right linko"><a href="http://www.colombiassh.org/gtmi/wiki/index.php/Sistema_de_categor%C3%ADas_del_m%C3%B3dulo_de_eventos_de_conflicto" target="_blank">Definición</a></div><div class="clear cat">»&nbsp;Acciones Bélicas: Hostigamiento</div></div><div class="clear"></div> <div class="f hide"><div class="ft">Fuente de noticia</div><div class="fc"><div class="fct">Prensa :: El Heraldo</div><div class="fcc"><b>Descripción tomada de la fuente</b>: "&nbsp;Un soldado adscrito a la Décima Brigada del Ejército Nacional que estaba prestando sus servicios en la base militar del corregimiento de San Pedro en la jurisdicción de Barrancas (La Guajira), resultó asesinado ayer al medio día al enfrentar a un grupo de guerrilleros.

De acuerdo con la información entregada por la institución armada, los guerrilleros trataban de tomarse por asalto la base militar de esta población enclavada en las colinas de la serranía del Perijá, a siete kilómetros de la cabecera municipal.

La oficina de comunicaciones de la Décima Brigada dijo a periodistas que no poseían mayor información del caso y que al conocerla la tenían que suministrar primero a la familia de la víctima y no desmintió el asalto cometido contra los militares de la base de San Pedro."</div><div class="fcc"><a href=" http://www.elheraldo.co/noticias/nacional/en-ataque-de-la-guerrilla-muere-soldado-113582" target="_blank">&nbsp;http://www.elheraldo.co/noticias/nacional/en-ataque-de-la-guerrilla-muere-soldado-113582</a></div></div> </div></div><div class="clear report_list_map report_list_map_violencia"> <div class="l violencia"> V</div> <div class="t clear">Emboscada. Guerrillas. Puerto Tejada, Cauca. 11 de Junio de 2013</div> <div class="hide"><div class="date detail">2013-06-11 00:00:00</div> <div class="loc detail">Puerto Tejada  <span class="pdf opt"> <a href="http://sidih.colombiassh.org/sissh/download_pdf.php?c=2&amp;id_depto=8&amp;id_mun=" target="_blank">Perfil Cauca</a></span></div> <!--<div--></div><div class="clear hide"><div class="left"><b>Categorias</b></div> <div class="opt right linko"><a href="http://www.colombiassh.org/gtmi/wiki/index.php/Sistema_de_categor%C3%ADas_del_m%C3%B3dulo_de_eventos_de_conflicto" target="_blank">Definición</a></div><div class="clear cat">»&nbsp;Acciones Bélicas: Emboscada</div></div><div class="clear"></div> <div class="f hide"><div class="ft">Fuente de noticia</div><div class="fc"><div class="fct">Prensa :: El Colombiano</div><div class="fcc"><b>Descripción tomada de la fuente</b>: "&nbsp;Cuatro policías salieron heridos luego de un atentado con explosivos a una patrulla de la Policía este martes en el barrio Alto de París del municipio de Puerto Tejada, Cauca.

Según indican las primeras versiones, las cargas explosivas fueron activadas al paso de la patrulla policial a la 1:30 de la tarde este martes.

Las primeras investigaciones apuntan a que guerrilleros de las Farc estarían detrás de este atentado.

Dos de los policías que se encuentran en más grave estado fueron trasladados a la clínica Fundación Valle del Lili en la ciudad de Cali."</div><div class="fcc"><a href=" http://www.elcolombiano.com/BancoConocimiento/C/cuatro_policias_heridos_en_atentado_de_las_farc_contra_patrulla/cuatro_policias_heridos_en_atentado_de_las_farc_contra_patrulla.asp" target="_blank">&nbsp;http://www.elcolombiano.com/BancoConocimiento/C/cuatro_policias_heridos_en_atentado_de_las_farc_contra_patrulla/cuatro_policias_heridos_en_atentado_de_las_farc_contra_patrulla.asp</a></div></div> </div></div><div class="clear report_list_map report_list_map_violencia"> <div class="l violencia"> V</div> <div class="t clear">Artefacto Explosivo Improvisado - AEI. Guerrillas. Morales, Cauca. 11 de Junio de 2013</div> <div class="hide"><div class="date detail">2013-06-11 00:00:00</div> <div class="loc detail">Morales  <span class="pdf opt"> <a href="http://sidih.colombiassh.org/sissh/download_pdf.php?c=2&amp;id_depto=8&amp;id_mun=" target="_blank">Perfil Cauca</a></span></div> <!--<div--></div><div class="clear hide"><div class="left"><b>Categorias</b></div> <div class="opt right linko"><a href="http://www.colombiassh.org/gtmi/wiki/index.php/Sistema_de_categor%C3%ADas_del_m%C3%B3dulo_de_eventos_de_conflicto" target="_blank">Definición</a></div><div class="clear cat">»&nbsp;Uso de explosivos remanentes de guerra: Artefacto Explosivo Improvisado - AEI</div></div><div class="clear"></div> <div class="f hide"><div class="ft">Fuente de noticia</div><div class="fc"><div class="fct">Prensa :: El Espectador</div><div class="fcc"><b>Descripción tomada de la fuente</b>: "&nbsp;De acuerdo a la información oficial, el primer hallazgo se registró en zona rural de la vereda el Mesón del municipio de Morales, donde los guerrilleros columna móvil Jacobo Arenas de la mantenían ocultos 25 tatucos."</div><div class="fcc"><a href=" http://www.elespectador.com/noticias/judicial/articulo-427011-incautan-31-explosivos-de-farc-cauca" target="_blank">&nbsp;http://www.elespectador.com/noticias/judicial/articulo-427011-incautan-31-explosivos-de-farc-cauca</a></div></div> </div></div><div class="clear report_list_map report_list_map_violencia"> <div class="l violencia"> V</div> <div class="t clear">Atentado. Sin determinar. Cartagena, Bolivar. 11 de Junio de 2013</div> <div class="hide"><div class="date detail">2013-06-11 00:00:00</div> <div class="loc detail">Cartagena  <span class="pdf opt"> <a href="http://sidih.colombiassh.org/sissh/download_pdf.php?c=2&amp;id_depto=4&amp;id_mun=" target="_blank">Perfil Bolivar</a></span></div> <!--<div--></div><div class="clear hide"><div class="left"><b>Categorias</b></div> <div class="opt right linko"><a href="http://www.colombiassh.org/gtmi/wiki/index.php/Sistema_de_categor%C3%ADas_del_m%C3%B3dulo_de_eventos_de_conflicto" target="_blank">Definición</a></div><div class="clear cat">»&nbsp;Ataques contra la población civil: Atentado</div></div><div class="clear"></div> <div class="f hide"><div class="ft">Fuente de noticia</div><div class="fc"><div class="fct">Prensa :: El Universal</div><div class="fcc"><b>Descripción tomada de la fuente</b>: "&nbsp;Henry Guizamano Vivas, presidente de la Asociación de Consejos Comunitarios de Cartagena, se encuentra preocupado después de que un habitante de la comunidad de Tierra Baja atentara contra su vida. Los hechos sucedieron en la mañana de este lunes en Tierra Baja (zona rural de Cartagena), cuando Guizamano se disponía a salir a una reunión. Antes de salir, el joven agresor, quien fue identificado como un trabajador de varios terratenientes de la zona de relleno de algunos mangles, se acercó con un arma blanca de espaldas al agredido e intentó hacerle daño. Sin embargo, antes de que lo alcanzara con el arma, Guizamano logró esquivar el golpe que llegó hasta la motocicleta en que se iba a transportar. Según vecinos de la comunidad, antes de escabullirse, el agresor le gritó a Guizamano que eso era una advertencia y que ese no sería el último ataque. El hecho se encuentra en instancias de la Fiscalía. El agresor, que fue identificado por la comunidad como Damián Meléndez Ospino, se dio a la fuga. Guizamano adelanta la solicitud de Titulación Colectiva de Tierra Baja como representante de la Asociación de Consejos Comunitarios de Cartagena (Asococ) Mi Tambó y del Consejo Comunitario de Tierra Baja. Con esta titulación se busca salvaguardar los mangles, fuentes hídricas y bosques de la región, de los cuales la comunidad negra tierrabajera obtiene su sustento. El presidente de Asococ ya está adelantando la solicitud a la Unidad de Protección de Víctimas para que se proteja su integridad y la de su familia, mientras que el joven agresor es buscado por la Policía Metropolitana. Fuente: El Universal."</div></div></div></div><div class="clear report_list_map report_list_map_violencia"> <div class="l violencia"> V</div> <div class="t clear">Artefacto Explosivo Improvisado - AEI-MAP. Guerrillas. Anorí, Antioquia. 11 de Junio de 2013</div> <div class="hide"><div class="date detail">2013-06-11 00:00:00</div> <div class="loc detail">Anorí  <span class="pdf opt"> <a href="http://sidih.colombiassh.org/sissh/download_pdf.php?c=2&amp;id_depto=1&amp;id_mun=" target="_blank">Perfil Antioquia</a></span></div> <!--<div--></div><div class="clear hide"><div class="left"><b>Categorias</b></div> <div class="opt right linko"><a href="http://www.colombiassh.org/gtmi/wiki/index.php/Sistema_de_categor%C3%ADas_del_m%C3%B3dulo_de_eventos_de_conflicto" target="_blank">Definición</a></div><div class="clear cat">»&nbsp;Uso de explosivos remanentes de guerra: Artefacto Explosivo Improvisado - AEI, MAP</div></div><div class="clear"></div> </div><div id="cargar_mas"><div class="btn cargar_mas">Cargar mas eventos</div></div>
</div>
        <div class="clear"></div>
    </div>
    <script type="text/javascript" src="<?php echo BASE_PORTAL ?>media/js/jquery.min.js"></script>
    <script type="text/javascript" src="<?php echo BASE_PORTAL ?>media/js/jquery.mousewheel.js"></script>
    <script type="text/javascript" src="<?php echo BASE_PORTAL ?>media/js/perfect-scrollbar.min.js"></script>
    <script type="text/javascript" src="<?php echo BASE_PORTAL ?>media/js/openlayers/OpenLayers.min.js"></script>
    <script type="text/javascript" src="<?php echo BASE_PORTAL ?>media/js/openlayers/LoadingPanel.min.js"></script>
    <script type="text/javascript" src="<?php echo BASE_PORTAL ?>media/js/jquery.tablesorter.min.js"></script>
    <script type="text/javascript" src="<?php echo BASE_PORTAL ?>media/js/url_tools.min.js"></script>
    <script type="text/javascript" src="<?php echo BASE_PORTAL ?>media/js/fe.js"></script>
    <script type="text/javascript" src="<?php echo BASE_PORTAL ?>media/js/map.min.js"></script>
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
                
            $('#incidentes').perfectScrollbar();
            
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


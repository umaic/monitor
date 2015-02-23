var map;
var pL;
var fromProjection;
var toProjection;
var features_ec;
var features_dn;
var markerRadius = 7;
var Style;
var mtipo;
var clear = true;
var request = false;
var filtro = id_filtro = '';
var nump_list = 15;
var id_depto = '00';
var rm_id_depto = false;
var id_tema = id_org = 0;
var maximo = 0; // Maximo count en cluster
var jenks = [];
var jenks_cl = 5; // Estratos

var url_xd = '/json/cluster/?m=0&v=0';


if (window.location.hostname == 'monitor.local') {
    var subdomain_dn = 'desastres';
    var subdomain_ec = 'violencia_armada';
    var url_ec = 'http://localhost/violencia_armada';
    var url_dn = 'http://localhost/desastres';
}
else {
    var subdomain_dn = 'desastres';
    var subdomain_ec = 'violenciaarmada';
    var url_ec = 'http://'+subdomain_ec + '.salahumanitaria.co';

    var url_dn = 'http://'+subdomain_dn+'.salahumanitaria.co';

}

// Verificados se usan como destacados
var url_ft_ec = url_ec + '/json/index/?m=0';
var url_ft_dn = url_dn + '/json/index/?m=0';

url_ec += url_xd;
url_dn += url_xd;


var markerOpacity = 0.8;
var selectCtrl;
var l_ec, l_dn;
var mapLoad = 0;
var _zoomOffset = 6;
var lym;
var lytmp;
var resolutions= [
                  2445.9849047851562, 1222.9924523925781,
                  611.4962261962891, 305.74811309814453, 152.87405654907226,
                  76.43702827453613, 38.218514137268066, 19.109257068634033,
                  9.554628534317017, 4.777314267158508, 2.388657133579254,
                 ];


/*
    var ly = new OpenLayers.Layer.OSM("Openstreetmap","",
           {
                zoomOffset: _zoomOffset,
                resolutions: [
                                //156543.03390625, 78271.516953125,
                                //39135.7584765625, 19567.87923828125, 9783.939619140625,
                              //4891.9698095703125,
                              2445.9849047851562, 1222.9924523925781,
                              611.4962261962891, 305.74811309814453, 152.87405654907226,
                              76.43702827453613, 38.218514137268066, 19.109257068634033,
                              9.554628534317017, 4.777314267158508, 2.388657133579254,
                             ]
            });
*/

var show = [];
show['desc'] = false; // Descripcion del evento de sidih
show['fuente'] = true;

function addWMSLayer(n,l,v) {
    
    var u = 'http://geonode.salahumanitaria.co/geoserver/wms';
        
    if (map.getLayersByName(n).length > 0) {
        var ly = map.getLayersByName(n)[0];

        ly.setVisibility(v);
    }
    else {
        ly = new OpenLayers.Layer.WMS(n, 
                                  u,
                                  {
                                  layers: l,
                                  transparent: true,
                                  },
                                  {
                                    opacity: 1,
                                    visibility: true,
                                    singleTile: true
                                  }
                              );

        var $lo = $('#layers_loading');

        ly.events.register("loadstart", ly, function() {
            $lo.show();
        });
        
        ly.events.register("loadend", ly, function() {
            $lo.hide();
        });

        map.addLayer(ly);
    }
}

function selDepto(centroide) {
    var _c = centroide.split(',');
    map.setCenter(new OpenLayers.LonLat(_c[0], _c[1]), 1);
}
    
function resetMap() {
    map.setCenter(map.maxExtent.getCenterLonLat(), 0);
}

function mapRender() {
   
    // Portal
    if (is_portal) {
        _zoomOffset = 5;
        var r1 = [4891.9698095703125];
        resolutions = r1.concat(resolutions);
    }
    
    fromProjection = new OpenLayers.Projection('EPSG:4326'); // World Geodetic System 1984 projection (lon/lat) 
    toProjection = new OpenLayers.Projection('EPSG:900913'); // WGS84 OSM/Google Mercator projection (meters) 
    OpenLayers.ImgPath = base_ol + "/media/js/openlayers/img/";
    
    var maxE = new OpenLayers.Bounds(
			-8599122, -471155, -7441396, 1505171  // Colombia con San Andrés
    );
    
    map = new OpenLayers.Map({
        div: "map",
        displayProjection: toProjection, 
        theme: base_ol + '/media/js/openlayers/theme/default/style.min.css',
        maxExtent: maxE,
        //restrictedExtent: maxE,
        eventListeners: {
            "zoomend": mapMove
        },
        /*
        controls: [
            new OpenLayers.Control.PanZoomBar()
        ]*/
    });

    var ly = new OpenLayers.Layer.XYZ(
            "OpenStreetMap", 
            [
                "http://otile1.mqcdn.com/tiles/1.0.0/map/${z}/${x}/${y}.png",
                "http://otile2.mqcdn.com/tiles/1.0.0/map/${z}/${x}/${y}.png",
                "http://otile3.mqcdn.com/tiles/1.0.0/map/${z}/${x}/${y}.png",
                "http://otile4.mqcdn.com/tiles/1.0.0/map/${z}/${x}/${y}.png"
            ],
            {
                attribution: "Ver mas detalles en &nbsp;<img src='http://monitor.salahumanitaria.co/favicon.ico'> <a href='http://monitor.salahumanitaria.co' target='_blank'>monitor.salahumanitaria.co</a>",
                transitionEffect: "resize",
                zoomOffset: _zoomOffset,
                sphericalMercator: true,
                resolutions: resolutions
            }
        );
    map.addLayer(ly);
    
    //map.zoomTo(6);
    map.setCenter(map.maxExtent.getCenterLonLat(), 0);

    //map.zoomToMaxExtent();

    defStyle();
    addFeaturesFirstTime();
}

function addFeaturesFirstTime() {
    //addFeatures($("#currentCat").val(),$("#startDate").val(),$("#endDate").val(),map.getZoom() + _zoomOffset,map.getCenter());
    addFeatures();
    mapLoad = 1;
}

//function addFeatures(cat, start, end, zoom, center) {
function addFeatures(inst) {

    if (inst == undefined) {
        inst = 'ecdn';
    }
    
    if (!_cluster) {
        url_ec = url_ec.replace('cluster','index');
        url_dn = url_dn.replace('cluster','index');
    }
    else {
        url_ec = url_ec.replace('index','cluster');
        url_dn = url_dn.replace('index','cluster');
    }
   
    var start = $("#startDate").val();
    var end = $("#endDate").val();
    var zoom = map.getZoom() + _zoomOffset;
    var center = map.getCenter();

    var uparams = [['s', start], ['e', end], ['z', zoom]];

    if (inst == 'ecdn' || inst == 'dn') {
        var uparams_dn = uparams.concat([['c', $('#currentCatD').val()]]);
        if (map.getLayersByName('Desastres Naturales').length > 0) {
            l_dn = map.getLayersByName('Desastres Naturales')[0];
            l_dn.removeFeatures(l_dn.features);
        }
        else {
            l_dn = new OpenLayers.Layer.Vector('Desastres Naturales', 
                { styleMap: Styles });

            map.addLayer(l_dn);
        }
        
        var _udn = addURLParameter(url_dn, uparams_dn);
        
        // States filter
        _udn = addURLParameter(_udn, [['states', getStatesChecked()]]); // getStatesChcked in fe.js
        
        // Tipo mapa
        _udn = addURLParameter(_udn, [['afectacion', getMapaAfectacion()]]); // getMapaAfectacion in fe.js
        
        ajaxFeatures(_udn, l_dn);
    }
    
    if (inst == 'ecdn' || inst == 'ec') {
    
        var uparams_ec = uparams.concat([['c', $('#currentCatE').val()]]);

        if (map.getLayersByName('Emergencia Compleja').length > 0) {
            l_ec = map.getLayersByName('Emergencia Compleja')[0];
            l_ec.removeFeatures(l_ec.features);
        }
        else {
            l_ec = new OpenLayers.Layer.Vector('Emergencia Compleja', 
                { styleMap: Styles 
                });
            //l_ec.styleMap.styles.default.defaultStyle.fillColor = '#cc0000';
            map.addLayer(l_ec);
        }
    
        var _uec = addURLParameter(url_ec, uparams_ec);
        
        // States filter
        _uec = addURLParameter(_uec, [['states', getStatesChecked()]]); // getStatesChcked in fe.js
        
        // Tipo mapa
        _uec = addURLParameter(_uec, [['afectacion', getMapaAfectacion()]]); // getMapaAfectacion in fe.js
        
        ajaxFeatures(_uec, l_ec);
    }
    
    // Destacados, ft=fetured
    if (inst == 'ecdn' || inst == 'ft') {
        
        var uparams_ft = uparams.concat([['v', 1]]);

        if (map.getLayersByName('Destacados').length > 0) {
            l_ft = map.getLayersByName('Destacados')[0];
            l_ft.removeFeatures(l_ft.features);
        }
        else {
            l_ft = new OpenLayers.Layer.Vector('Destacados', 
                { styleMap: Styles });

            map.addLayer(l_ft);
        }
        
        var _uft_dn = addURLParameter(url_ft_dn, uparams_ft);
        
        // States filter
        _uft_dn = addURLParameter(_uft_dn, [['states', getStatesChecked()]]); // getStatesChcked in fe.js
        
        ajaxFeatures(_uft_dn, l_ft);
        
        var _uft_ec = addURLParameter(url_ft_ec, uparams_ft);
        
        _uft_ec = addURLParameter(_uft_ec, [['states', getStatesChecked()]]); // getStatesChcked in fe.js
        
        ajaxFeatures(_uft_ec, l_ft);

    }

    
    // Acceso
    if (inst == 'acceso') {
        
        var uparams_acceso = uparams.concat([['acceso', 1]]);
        uparams_acceso = uparams_acceso.concat([['acceso_cats', getAccesoCats()]]);

        if (map.getLayersByName('Emergencia Compleja').length > 0) {
            l_ec = map.getLayersByName('Emergencia Compleja')[0];
            l_ec.removeFeatures(l_ec.features);
        }
        else {
            l_ec = new OpenLayers.Layer.Vector('Emergencia Compleja', 
                { styleMap: Styles });

            map.addLayer(l_ec);
        }
            
        // Oculta capa desastres y featured
        if (map.getLayersByName('Desastres Naturales').length > 0) {
            l_dn = map.getLayersByName('Desastres Naturales')[0];
            l_dn.setVisibility(false);
        }
        if (map.getLayersByName('Destacados').length > 0) {
            l_ft = map.getLayersByName('Destacados')[0];
            l_ft.setVisibility(false);
        }
        
        // States filter
        _uft_ec = addURLParameter(url_ft_ec, uparams_acceso);
        ajaxFeatures(_uft_ec, l_ec);

    }

    selectCtrl = new OpenLayers.Control.SelectFeature([l_ec, l_dn, l_ft],
        { 
            clickout: true,
            onSelect: function(feature) { onFeatureSelect(feature.attributes)  }
        }
    );

    map.addControl(selectCtrl);
    selectCtrl.activate();
}

/**
* Display popup when feature selected
*/
function onFeatureSelect(attrs) {

    var _html = '';
    var max_e = 20; // Numero de eventos en la lista, el mismo en /eocmpleja/plugins/monitor/controllers/monitor.php, linea=41
    
    // Link when is cluster: /reports/index/
    if (attrs.link.indexOf('index') > 0) {
        
        var _url = attrs.link.replace('reports', 'monitor').replace('index', 'reports_list_map');
        var _cats;
        
        if (_url.indexOf(subdomain_ec) > 0) {
            _cats = $('#currentCatE').val()
        } 
        else {
            _cats = $('#currentCatD').val();
        }
        
        _url += '&c=' + _cats;
    }
    // Link when is single feature: /reports/view/incident_id
    else {
        var _url = attrs.link.replace('reports', 'monitor').replace('view', 'single_report_map');
    }

    $.ajax({
        url: _url,
        dataType: 'jsonp',
        beforeSend: function(){ $('#loading').show() },
        success: function(json){
            
            $('#loading').hide();

            for(var i=0, j=json.length; i < j; i+=1) {
                
                _js = json[i];
                
                // Click en el titulo: funcion fe.js:673
                _html += '<div class="report_list_map from_map"> ' +
                    '<div class="t"><a href="#" onclick="return false;" title="' + _js.id + '">'+ _js.t +'</a></div> ' +
                    '<div>' +
                        '<div class="date detail">'+ _js.d +'</div> ' +
                        '<div class="loc detail">'+ _js.ln + ' <span class="pdf opt"> ' +
                            '<a href="http://sidih.colombiassh.org/sissh/download_pdf.php?c=2&id_depto='+_js.ld+'&id_mun=" target="_blank">' +
                            'Consulte el perfil de '+ _js.ldn +'</a></span>' +
                        '</div> ' +
                    '</div>';
                    
                    _html += '<div class="clear"></div><div class="left"><b>Categorias</b></div> ' +
                             '<div class="opt right linko">' +
                                '<a href="http://www.colombiassh.org/gtmi/wiki/index.php/Sistema_de_categor%C3%ADas_del_m%C3%B3dulo_de_eventos_de_conflicto" target="_blank">Definici&oacute;n de categorias</a>' +
                             '</div>';

                    for (c in _js.c) {
                        _html += '<div class="clear detail"> &raquo;' + c;
                        
                        if (_js.c[c].length > 0) {
                            _html += ': ';
                        }

                        for (d in _js.c[c]) {
                            
                            if (d > 0) {
                                _html += ', ';
                            } 
                            
                            _html += _js.c[c][d];
                        }
                        _html += '</div>';
                    }
                    
                    //_html += '</div>';
                    _html += '<div class="clear"></div> ';
        
                // Victimas
                if (_js.q == 'violencia') {
                 
                    if (_js.v.length > 0) {
                        _html += '<div><b>Víctimas</b></div> ';
                        
                        for (v in _js.v) {
                            
                            _v = _js.v[v];
                            
                            _html += '<div class="victim"><div class="right">' + _v['category'] + '</div><table><tr>';
                            
                            _html += '<td valign="top">';
                            
                            _html += '<div><b>Cantidad</b>: ' + _v['cant'] + '</div> ';

                            
                            if (_v['gender'] != '') {
                                _html += '<div> <b>Género:</b> ' + _v['gender'] + '</div> ';
                            }
                            
                            if (_v['status'] != '') {
                                _html += '<div> <b>Estado:</b> ' + _v['status'] + '</div> ';
                            }
                            
                            _html += '</td>';
                            _html += '<td valign="top">';
                            
                            if (_v['age'] != '') {
                                _html += '<div> <b>Edad:</b> ' + _v['age'];
                            
                                if (_v['age_group'] != '') {
                                    _html += ' / ' + _v['age_group'];
                                }
                            
                                _html += '</div> ';
                                
                            }
                            
                            
                            if (_v['condition'] != '') {
                                _html += '<div><b>Condición:</b> ' + _v['condition'];
                            
                                if (_v['sub_condition'] != '') {
                                    _html += ' / ' + _v['sub_condition'];
                                }
                                _html += '</div> ';
                                
                            }
                            
                            if (_v['ethnic_group'] != '') {
                                _html += '<div> <b>Grupo poblacional:</b> ' + _v['ethnic_group'];
                            
                                if (_v['sub_ethnic_group'] != '') {
                                    _html += ' / ' + _v['sub_ethnic_group'];
                                }
                            
                                _html += '</div> ';
                                
                            }
                            
                            _html += '</td>';
                            
                            _html += '</table></div> ';
                        }
                    }
                }
                else {

                    if (_js.v.length > 0) {
                        _html += '<div class="victim"><div><b>Afectación</b></div> ';
                        _html += '<div><table><tr>';
                        
                        var p = 0;
                        for (var k in _js.v[0]) {

                            _v = _js.v[0];

                            if (_v != '') {
                                tdo = (p == 0 || p == 4 || p == 8 || p == 12) ? true : false;
                                tdc = (p == 3 || p == 7 || p == 11) ? true : false;

                                if (_v[k] != '') {
                                    if (tdo) {
                                        _html += '<td>';
                                    }

                                    _html += '<div><b>' + k + '</b>: ' + _v[k] + '</div>';
                                    
                                    if (tdc) {
                                        _html += '</td>';
                                    }

                                    p += 1;
                                }
                            }
                        }
                        
                        _html += '</tr></table></div></div>';
                    }
                }

                _html += '<div class="clear"></div> ';
                
                if (show['desc'] || _js.f.length == 0) {
                    _html += '<div class="desc"><b>Descripci&oacute;n</b>: '+ _js.desc +'</div> ';
                } 

                if (show['fuente']) {
                    if (_js.f != '') {
                        _html += '<div class="f">' +
                        '<div class=""><b>Fuente de noticia</b></div>'; 
                        for(var k=0, l=_js.f.length; k< l; k += 1) {
                            _html += '<div class="fc">';
                            
                            // Source type :: source name
                            if ((_js.f[k][0] != '' && _js.f[k][1] != '') || _js.f[k][2] != '') {
                                _html += '<div class="detail">&raquo; '+_js.f[k][1]+' ( '+_js.f[k][0]+' )';
                            
                            
                                // Source refer
                                if (_js.f[k][2].indexOf('http') != -1) {
                                    //_js.f[k][2] = url
                                    _html += '&nbsp;<img src="media/img/pdf.gif" />&nbsp;<a href="ss/?x=232fdsfwwppo&q=' + _js.q[0] + _js.id + '" target="_blank">Ver noticia original</a>';
                                         
                                }

                                // Source desc
                                if (_js.f[k][3] != undefined && _js.f[k][3] != '') {
                                    _html += ' | <a href="#" class="d" onclick="return false;">Leer descripci\u00f3n de la fuente</a>';
                                }
                                
                                _html += '</div>';
                                
                                if (_js.f[k][3] != undefined && _js.f[k][3] != '') {
                                    _html += '<div class="hide detail">' + _js.f[k][3] + '"';

                                    _html += '<br /><br />Tomado de: <a href="' + _js.f[k][2] + '" target="_blank">' + _js.f[k][2] + '</a></div>';
                                }
                                
                            }
                            
                        }
                        _html += '</div>';
                    }
                }
            
                _html += '</div></div>';

            }
                
            if (json.length > max_e) {
                _html += '<div id="mase"><div class="btn"><a href="'+attrs.link+'" target="_blank">Ir al listado completo de eventos</a></div></div>';
            }
            

            // Portal EHP
            if (is_portal) {
                $('#incidentes').html(_html).show();
                $('#tabs').tabs("select", 2);
                $('#volver').show();
            }
            else {
                // Modal window, in fe.js
                numr = (attrs.id > max_e) ? max_e : attrs.id;
                m({
                    //t: 'Monitor - ColombiaSSH :: Listado de eventos [ ' + numr + ' registros ]',
                    t: 'Monitor - ColombiaSSH :: Listado de eventos',
                    html: _html,
                    w: 800,
                    h: 500,
                    funOpen: listReportsEvents,
                });
            }
        },
    });
    
}

function ajaxFeatures(u, l) {
    
    var geojson = new OpenLayers.Format.GeoJSON({
        'internalProjection': toProjection,
        'externalProjection': fromProjection});
    $.ajax({
        url: u,
        dataType: 'jsonp',
        success: function(json){
                var _f = geojson.read(json);

                // Calcula el numero máximo de features en un cluster
                var fts = json.features;
                var arr = [];
                for(j in fts) {
                    c = fts[j].properties.count;

                    if (c > maximo) {
                        maximo = c;
                    } 

                    arr[j] = c;
                }

                // calcula jenks
                var len = arr.length
                if (len > 1) {
                    if (len <= jenks_cl) {
                        jenks_cl = len + 1;
                    }

                    serie = new geostats(arr);

                    cl = (len < jenks_cl) ? len -1 : jenks_cl;
                    jenks = serie.getClassJenks(cl);
                }

                //map.addLayer(l);
                l.addFeatures(_f);
                $('#loading').hide();
	
                // Show/Hide icono de destacados
                showHideFeaturedIcon(); // funcion en este archivo
            },
        beforeSend: function(){ $('#loading').show() }
    });
}

function showHideFeaturedIcon() {
    var $f = $('#featured');
    if (map.getLayersByName('Destacados')[0].features.length > 0){
        $f.show();
    }
    else {
        $f.hide();
    }
}

function mapMove(event)
{
    // Prevent this event from running on the first load
    if (mapLoad > 0)
    {
        // Refresh Map
        addFeatures();

        // Municipios en zoom 3
        var _vd = true;
        var _vm = false;

        if (map.getZoom() >= 2) {
            _vd = false;
            _vm = true;
        }
    }
}

function defStyle(){
                
	var	style = new OpenLayers.Style({
				'externalGraphic': "${icon}",
				'graphicTitle': "${cluster_count}",
                graphicWidth: 24,
                graphicHeight: 24,
                cursor: 'pointer',
				pointRadius: "${radius}",
				fillColor: "${color}",
				fillOpacity: "${opacity}",
				strokeColor: "${strokeColor}",
				strokeWidth: 4,
				strokeOpacity: "${strokeOpacity}",
				label:"${clusterCount}",
				title:"${clusterCount}",
				//labelAlign: "${labelalign}", // IE doesn't like this for some reason
				fontWeight: "${fontweight}",
				fontColor: "${fontColor}",
				fontSize: "${fontsize}"
			},
            {
				context:
				{
					fontsize: function(feature)
					{
						feature_icon = feature.attributes.icon;
						if (feature_icon!=="")
						{
							return "9px";
						}
						else
						{

                            return "11px";
						}
					},
					fontweight: function(feature)
					{
						feature_icon = feature.attributes.icon;
						if (feature_icon!=="")
						{
							return "normal";
						}
						else
						{
							return "normal";
						}
					},
					radius: function(feature)
					{

						feature_count = feature.attributes.count;
                        
                        var r = markerRadius;

                        for (var i=1;i<jenks.length;i++) {
                            if (feature_count <= jenks[i]) {
                                r = markerRadius * i;
                                return r;
                            }
                        }

                        return r;
					},
					strokeWidth: function(feature)
					{
						if ( typeof(feature.attributes.strokewidth) != 'undefined' && 
							feature.attributes.strokewidth != '')
						{
							return feature.attributes.strokewidth;
						}
						else
						{
							feature_count = feature.attributes.count;
							if (feature_count > 10000)
							{
								return 18;
							}
							else if (feature_count > 5000)
							{
								return 16;
							}
							else if (feature_count > 1000)
							{
								return 14;
							}
							else if (feature_count > 100)
							{
								return 12;
							}
							else if (feature_count > 10)
							{
								return 10;
							}
							else if (feature_count >= 2)
							{
								return 5;
							}
							else
							{
								return 1;
							}
						}
					},
					color: function(feature)
					{
                        // Se coloca color fijo pq json/cluster devuelve color de categoria en cada feature
                        if (_cluster) {
                            if (String(feature.attributes.link).indexOf(subdomain_ec) == -1) {
                                return '#2ca02c';
                            }
                            else {
                                return '#cc0000';
                            }
                        }
                        else {
                            return '#ffffff';
                            return '#' + feature.attributes.color;
                        }
					},
					strokeColor: function(feature)
					{
                        // Se coloca color fijo pq json/cluster devuelve color de categoria en cada feature
                        if (_cluster) {
                            if (String(feature.attributes.link).indexOf(subdomain_ec) == -1) {
                                return '#2ca02c';
                            }
                            else {
                                return '#cc0000';
                            }
                        }
                        else {
                            return '#' + feature.attributes.color;
                        }
					},
					fontColor: function(feature)
					{
                        return (_cluster) ? '#ffffff' : '#000000';
					},
					strokeOpacity: function(feature)
					{
                        return (_cluster) ? '0.5' : '1';
					},
					clusterCount: function(feature)
					{
						if (feature.attributes.count > 1)
						{
							if($.browser.msie && $.browser.version=="6.0")
							{ // IE6 Bug with Labels
								return "";
							}
							
							return feature.attributes.count;
						}
						else
						{
							return "1";
						}
					},
					opacity: function(feature)
					{

						feature_icon = feature.attributes.icon;
						
                        if (feature_icon!=="")
						{
							return 1;
						}
						else
						{
							return markerOpacity;
						}
					},
					labelalign: function(feature)
					{
						feature_icon = feature.attributes.icon;
						if (feature_icon!=="")
						{
							return "c";
						}
						else
						{
							return "c";
						}
					}
				}
			}
    );
    
    Styles = new OpenLayers.StyleMap({
        "default": style,
        "select":{
            fillColor: "#86ABD9",
            strokeColor: "#32a8a9"
           } 
    });
}



function onFeatureUnselect(event) {
    // Safety check
    if (event.feature.popup != null)
    {
      map.removePopup(event.feature.popup);
      event.feature.popup.destroy();
      event.feature.popup = null;
    }
}
/**
 * Close Popup
 */
function onPopupClose(event)
{
    selectCtrl.unselect(selectedFeature);
    selectedFeature = null;
};

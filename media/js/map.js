
var map;
var pL;
var fromProjection;
var toProjection;
var features_ec;
var features_dn;
var markerRadius = 5;
var Style;
var mtipo;
var clear = true;
var request = false;
var filtro = id_filtro = '';
var nump_list = 15;
var id_depto = '00';
var rm_id_depto = false;
var id_tema = id_org = 0;

var url_xd = '/json/cluster/?m=0&v=0';


if (window.location.hostname == 'localhost') {
    var subdomain_dn = 'desastres';
    var subdomain_ec = 'violencia_armada';
    var url_ec = 'http://localhost/violencia_armada';
    var url_dn = 'http://localhost/desastres';
    var url_ft = url_dn + '/json/index/?m=0&v=1';
}
else {
    var subdomain_dn = 'desastres';
    var subdomain_ec = 'violenciaarmada';
    var url_ec = 'http://'+subdomain_ec + '.colombiassh.org';

    var url_dn = 'http://'+subdomain_dn+'.colombiassh.org';

    // Verificados se usan como destacados
    var url_ft = url_dn + '/json/index/?m=0&v=1';
}

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

var show = [];
show['desc'] = false; // Descripcion del evento de sidih
show['fuente'] = true;

var wms = [];
wms = [
          {'n': 'Departamentos',
          'u' : "http://geonode.openstreetmap.co/geoserver/wms",
          'l' : 'geonode:division_departamental_de_colombia_sigot_igac',
          'v' : false,
          'op' : 1
          },
          {'n': 'Municipios',
          'u' : "http://geonode.openstreetmap.co/geoserver/wms",
          'l' : 'geonode:municipio_sigot',
          'v': false,
          'op': 1 
           },
           ];

function selDepto(centroide) {
    var _c = centroide.split(',');
    map.setCenter(new OpenLayers.LonLat(_c[0], _c[1]), 2);
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
                attribution: "<a href='http://www.mapquest.com/'  target='_blank'><img src='http://developer.mapquest.com/content/osm/mq_logo.png' border='0' /></a>, <a href='http://www.openstreetmap.org/' target='_blank'>OSM</a>",
                transitionEffect: "resize",
                zoomOffset: _zoomOffset,
                sphericalMercator: true,
                resolutions: resolutions
            }
        );
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
    map.addLayer(ly);
    
    // Limites muncipales
    var ca;
    for (var _w in wms) {
        ca = wms[_w];
        lytmp = new OpenLayers.Layer.WMS(ca.n, 
                                      ca.u,
                                      {
                                      layers: ca.l,
                                      transparent: true,
                                      },
                                      {
                                        opacity: ca.op,
                                        visibility: ca.v
                                      }
                                      );
        map.addLayer(lytmp);

    }

    //map.zoomTo(6);
    map.setCenter(map.maxExtent.getCenterLonLat(), 0);
    //map.zoomToMaxExtent();

    defStyle();
    addFeaturesFirstTime();
    /*
    var loadingpanel = new OpenLayers.Control.LoadingPanel();
    map.addControl(loadingpanel);
    */
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
        
        var _uft = addURLParameter(url_ft, uparams_ft);
        
        // States filter
        //_uft = addURLParameter(_uft, [['states', getStatesChecked()]]); // getStatesChcked in fe.js
        
        ajaxFeatures(_uft, l_ft);
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
                
                _html += '<div class="report_list_map from_map"> ' +
                    '<div class="t">'+ _js.t +'</div> ' +
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
                        _html += '<div class="clear cat detail">'+ c;
                        
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
                            
                            _html += '<div class="victim"><table><tr>';
                            
                            _html += '<td valign="top">';
                            
                            _v = _js.v[v];
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
                        _html += '<div class="f hide">' +
                        '<div class="ft">Fuente de noticia</div>'; 
                        for(var k=0, l=_js.f.length; k< l; k += 1) {
                            _html += '<div class="fc">';
                            
                            // Source type :: source name
                            if (_js.f[k][0] != '' && _js.f[k][1] != '') {
                                _html += '<div class="fct">'+_js.f[k][0]+' :: '+_js.f[k][1]+'</div>';
                            }
                            
                            // Source desc
                            if (_js.f[k][3] != undefined && _js.f[k][3] != '') {
                                _html += '<div class="fcc"><b>Descripci\u00f3n tomada de la fuente</b>: "' + _js.f[k][3] + '"</div>';
                            }
                            
                            // Source refer
                            if (_js.f[k][2].indexOf('http') != -1) {
                                _html += '<div class="fcc"><a href="'+_js.f[k][2]+'" target="_blank">'+_js.f[k][2]+'</a></div>' + 
                                     '</div> ';
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
        /*
        // Get Current Category
        currCat = $("#currentCat").val();

        // Get Current Start Date
        currStartDate = $("#startDate").val();

        // Get Current End Date
        currEndDate = $("#endDate").val();

        // Get Current Zoom
        currZoom = map.getZoom() + _zoomOffset;

        // Get Current Center
        currCenter = map.getCenter();
        
        // Refresh Map
        addFeatures(currCat, currStartDate, currEndDate, currZoom, currCenter);
        */
        
        // Refresh Map
        addFeatures();

        // Municipios en zoom 3
        var _vd = true;
        var _vm = false;

        if (map.getZoom() >= 2) {
            _vd = false;
            _vm = true;
        }
        
        //map.getLayersByName('Departamentos')[0].setVisibility(_vd);
        //map.getLayersByName('Municipios')[0].setVisibility(_vm);
    }
}

function defStyle(){
                
	var	style = new OpenLayers.Style({
				'externalGraphic': "${icon}",
				'graphicTitle': "${cluster_count}",
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
					count: function(feature)
					{
						if (feature.attributes.count < 2)
						{
							return 2 * markerRadius;
						} 
						else if (feature.attributes.count == 2)
						{
							return (Math.min(feature.attributes.count, 7) + 1) *
							(markerRadius * 0.8);
						}
						else
						{
							return (Math.min(feature.attributes.count, 7) + 1) *
							(markerRadius * 0.6);
						}
					},
					fontsize: function(feature)
					{
						feature_icon = feature.attributes.icon;
						if (feature_icon!=="")
						{
							return "9px";
						}
						else
						{
							feature_count = feature.attributes.count;
							if (feature_count > 1000)
							{
								return "20px";
							}
							else if (feature_count > 500)
							{
								return "18px";
							}
							else if (feature_count > 100)
							{
								return "14px";
							}
							else if (feature_count > 10)
							{
								return "12px";
							}
							else if (feature_count >= 2)
							{
								return "10px";
							}
							else
							{
								return "";
							}
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
						if (feature_count > 1000) {
							return markerRadius * 7;
						}
						else if (feature_count > 900) {
							return markerRadius * 6.8;
						}
						else if (feature_count > 800) {
							return markerRadius * 6.6;
						}
						else if (feature_count > 700) {
							return markerRadius * 6.4;
						}
						else if (feature_count > 600) {
							return markerRadius * 6.2;
						}
						else if (feature_count > 500) {
							return markerRadius * 6;
						}
						else if (feature_count > 400) {
							return markerRadius * 5.7;
						}
						else if (feature_count > 300) {
							return markerRadius * 5.2;
						}
						else if (feature_count > 200) {
							return markerRadius * 4.7;
						}
						else if (feature_count > 100) {
							return markerRadius * 4.2;
						}
						else if (feature_count > 90) {
							return markerRadius * 4;
						}
						else if (feature_count > 80) {
							return markerRadius * 3.8;
						}
						else if (feature_count > 70) {
							return markerRadius * 3.6;
						}
						else if (feature_count > 60) {
							return markerRadius * 3.4;
						}
						else if (feature_count > 50) {
							return markerRadius * 3.2;
						}
						else if (feature_count > 40) {
							return markerRadius * 3;
						}
						else if (feature_count > 30) {
							return markerRadius * 2.8;
						}
						else if (feature_count > 20) {
							return markerRadius * 2.6;
						}
						else if (feature_count > 10) {
							return markerRadius * 2.4;
						}
						else {
							return markerRadius * 2;
						}
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

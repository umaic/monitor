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
var maximo = 0; // Maximo count en cluster
var jenks = [];
var jenks_cl = 5; // Estratos
var zoom = 6;
var layer_dn_exists = false;
var layer_ec_exists = false;
var layer_ft_exists = false;
var layer_variacion_exists = false;
var l_dn;
var l_ec;
var l_ft;
var l_variacion;

var centroColombia = ol.proj.transform(
[-70.963384, 4.370786], 'EPSG:4326', 'EPSG:3857');
var extensionColombia = ol.proj.transformExtent(
    [-82.836, -5.004, -65.552, 14.29], 'EPSG:4326', 'EPSG:3857');

if (window.location.hostname == 'monitor.local') {
    /*
    var subdomain_dn = 'desastres';
    var subdomain_ec = 'violencia';

    var domain = '.local';
    */
    // Probar local consultado eventos online
    var subdomain_dn = 'desastres';
    var subdomain_ec = 'violenciaarmada';
    var domain = '.salahumanitaria.co';

}
else {
    var subdomain_dn = 'monitor.salahumanitaria.co/desastres';
    var subdomain_ec = 'monitor.salahumanitaria.co/violenciaarmada';
    var domain = '';
}

var url_ec = '&server=' + subdomain_ec + domain;
var url_dn = '&server=' + subdomain_dn + domain;

// Verificados se usan como destacados
var _u = '/json/index/?m=0';

var url_ft_ec = _u + url_ec;
var url_ft_dn = _u + url_dn;

var url_xd = '/json/cluster/?m=0&v=0';

url_ec = url_xd + url_ec;
url_dn = url_xd + url_dn;

var markerOpacity = 0.8;
var selectCtrl;
var l_ec, l_dn;
var mapLoad = 0;
var _zoomOffset = 6;
var lym;
var lytmp;
var resolutions= [
                  4891.9698095703125,
                  2445.9849047851562, 1222.9924523925781,
                  611.4962261962891, 305.74811309814453, 152.87405654907226,
                  76.43702827453613, 38.218514137268066, 19.109257068634033,
                 ];

var show = [];
show['desc'] = false; // Descripcion del evento de sidih
show['fuente'] = true;


function getLayerByName(n) {

    var lys = [];

    map.getLayers().forEach(function(layer, i){
        if (layer.get('title') == n) {
            lys = [layer];
        }
    });

    return lys;
}

function addWMSLayer(n,l,v) {

    var u = 'https://geonode.salahumanitaria.co/geoserver/wms';

    var lys = getLayerByName(n);

    if (lys.length > 0) {
        var ly = lys[0];

        ly.setVisible(v);
    }
    else {

        var source = new ol.source.TileWMS({
                    url: u,
                    params: {'LAYERS': l, 'TILED': true, 'TRANSPARENT': true},
                    serverType: 'geoserver',
                });


        ly = new ol.layer.Tile({
                title: n,
                source: source,
        });

        /*
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
        */

        var $lo = $('#layers_loading');

        source.on('tileloadstart', function(event) {
            $lo.show();
        });

        source.on('tileloadend', function(event) {
            $lo.hide();
        });

        map.addLayer(ly);

    }
}

function selDepto(centroide) {
    var _c = centroide.split(',');
    var v = map.getView();

    v.setCenter([_c[0]*1, _c[1]*1]);
    v.setZoom(3);

}

function resetMap() {
    var v = map.getView();

    v.setCenter(centroColombia);
    v.setZoom(0);
}

function mapRender() {

    var view = new ol.View({
        center: centroColombia,
        zoom: 1,
        resolutions: resolutions,
        extent: extensionColombia

    });

    map = new ol.Map({
        layers: [
            new ol.layer.Tile({
                title: 'OSM',
                //source: new ol.source.OSM()
                source: new ol.source.XYZ({
                    // url: 'https://api.tiles.mapbox.com/v4/mapbox.streets/{z}/{x}/{y}.png?access_token=pk.eyJ1IjoicmF0YmlrZXIiLCJhIjoiY2loejFyM3B4MDQwcHRnbTF5MWlmOHJuNCJ9.H5A3WGVx60EdqY0hMzIMKg'
                    // url: 'https://tiles.wmflabs.org/bw-mapnik/{z}/{x}/{y}.png'
                    // url: 'https://api.maptiler.com/maps/positron/{z}/{x}/{y}.png?key=NRrAO36GS5hDL58vTiCT'
                    url: 'https://cartodb-basemaps-{s}.global.ssl.fastly.net/light_all/{z}/{x}/{y}.png'
                })
            })
        ],
        target: document.getElementById('map'),
        controls: ol.control.defaults({
            attributionOptions: /** @type {olx.control.AttributionOptions} */ ({
                collapsible: false
            })
        }),
        view: view
    });


    var styleVariacion = [new ol.style.Style({
        fill: new ol.style.Fill({
            color: 'rgba(255, 255, 255, 0.6)'
        }),
        stroke: new ol.style.Stroke({
            color: '#319FD3',
            width: 1
        })
    })];

    var element = document.getElementById('popup');

    var popup = new ol.Overlay({
      element: element,
      positioning: 'bottom-center',
      stopEvent: false
    });
    map.addOverlay(popup);

    // Eventos
    map.getView().on('change:resolution', mapMove);

    map.on('click', function(evt) {

        var feature = map.forEachFeatureAtPixel(evt.pixel,
                function(feature, layer) {
                    return feature;
                });

        $(element).popover('destroy');

        if (feature !== undefined && feature.get('variacion') !== undefined) {

            var geometry = feature.getGeometry();
            var coord = geometry.getFirstCoordinate();

            popup.setPosition(coord);

            $(element).popover({
                'placement': 'top',
                'html': true,
                'title': feature.get('MUNNAME'),
                'content': '<div><b>Variación:</b> ' + feature.get('variacion') +
                    '% <br /><b>Periodo 1:</b> ' + feature.get('p1') +
                    '<br /><b>Periodo 2:</b> ' + feature.get('p2') + '</div>'
            });

            $(element).popover('show');
        }
        else {
            onFeatureSelect(feature.getProperties());
        }
    });

    // change mouse cursor when over marker
    $(map.getViewport()).on('mousemove', function(e) {
        var pixel = map.getEventPixel(e.originalEvent);
        var hit = map.forEachFeatureAtPixel(pixel, function(feature, layer) {
            return true;
        });

        if (hit) {
            map.getTarget().style.cursor = 'pointer';
        } else {
            map.getTarget().style.cursor = '';
        }
    });

    // /Eventos

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

    var start = $("#ini_date").val();
    var end = $("#fin_date").val();
    var zoom = map.getView().getZoom() + _zoomOffset;
    var group_level = $('#group_level').val();

    var uparams = [['s', start], ['e', end], ['z', zoom], ['gl', group_level]];

    // Destacados, ft=fetured
    if (inst == 'ecdn' || inst == 'ft') {

        var uparams_ft = uparams.concat([['v', 1]]);  ////1
        var l_ft_source = new ol.source.Vector();

        if (!layer_ft_exists) {
            l_ft = new ol.layer.Vector({
                title: 'Destacados',
                source: l_ft_source,
                style: styleFtFunction
            });

            map.addLayer(l_ft);

            layer_ft_exists = true;
        }
        else {
            l_ft.getSource().clear();
            showHideLayers('ft');
        }

        var _uft_dn = addURLParameter(url_ft_dn, uparams_ft);

        // States filter
        _uft_dn = addURLParameter(_uft_dn, [['states', getStatesChecked()]]); // getStatesChcked in fe.js

        ajaxFeatures(_uft_dn, l_ft, false);

        var _uft_ec = addURLParameter(url_ft_ec, uparams_ft);

        _uft_ec = addURLParameter(_uft_ec, [['states', getStatesChecked()]]); // getStatesChcked in fe.js

        ajaxFeatures(_uft_ec, l_ft, false);

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

        ajaxFeatures(_uft_ec, l_ec, false);

    }

    if (inst == 'ecdn' || inst == 'dn') {
        var uparams_dn = uparams.concat([['c', $('#currentCatD').val()]]);
        var l_dn_source = new ol.source.Vector({});

        if (!layer_dn_exists) {
            l_dn = new ol.layer.Vector({
                title: 'Desastres Naturales',
                style: styleFunction,
                source: l_dn_source
                });

            map.addLayer(l_dn);

            layer_dn_exists = true;
        }
        else {
            l_dn.getSource().clear();
            showHideLayers('dn');
        }

        if (l_dn.getVisible()) {

            var _udn = addURLParameter(url_dn, uparams_dn);

            // States filter
            _udn = addURLParameter(_udn, [['states', getStatesChecked()]]); // getStatesChcked in fe.js

            // Tipo mapa
            _udn = addURLParameter(_udn, [['afectacion', getMapaAfectacion()]]); // getMapaAfectacion in fe.js
//console.log('4');
            ajaxFeatures(_udn, l_dn, true);
        }
    }


    if (inst == 'ecdn' || inst == 'ec') {

        var uparams_ec = uparams.concat([['c', $('#currentCatE').val()]]);
        var l_ec_source = new ol.source.Vector();

        if (!layer_ec_exists) {
            l_ec = new ol.layer.Vector({
                title: 'Violencia',
                source: l_ec_source,
                style: styleFunction
                });

            map.addLayer(l_ec);

            layer_ec_exists = true;
        }
        else {
            l_ec.getSource().clear();
            showHideLayers('ec');
        }

        if (l_ec.getVisible()) {
            var _uec = addURLParameter(url_ec, uparams_ec);

            // States filter
            _uec = addURLParameter(_uec, [['states', getStatesChecked()]]); // getStatesChcked in fe.js

            // Tipo mapa
            _uec = addURLParameter(_uec, [['afectacion', getMapaAfectacion()]]); // getMapaAfectacion in fe.js
            //console.log('5');
            ajaxFeatures(_uec, l_ec, true);
        }

    }


    /*
    selectCtrl = new OpenLayers.Control.SelectFeature([l_ec, l_dn, l_ft],
        {
            clickout: true,
            onSelect: function(feature) { onFeatureSelect(feature.attributes)  }
        }
    );

    map.addControl(selectCtrl);
    selectCtrl.activate();
    */
}

function ajaxFeatures(u, l, doJenks) {
//console.log(u+'f:'+l);
    $.ajax({
        url: u,
        dataType: 'jsonp',
        success: function(json){

            if (json.features.length > 0) {

                var _f = (new ol.format.GeoJSON()).readFeatures(json,{ dataProjection: 'EPSG:4326',
                                                                      featureProjection: 'EPSG:3857'});

                // Calcula el numero máximo de features en un cluster
                if (doJenks) {
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
                }

                l.getSource().addFeatures(_f);

                $('#loading').hide();

                // Show/Hide icono de destacados
                showHideFeaturedIcon(); // funcion en este archivo
            }
        },
        beforeSend: function(){ $('#loading').show() }
    });
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
                            '<a href="https://legacy.sidi.salahumanitaria.co/sissh/download_pdf.php?c=2&id_depto='+_js.ld+'&id_mun=" target="_blank">' +
                            'Consulte el perfil de '+ _js.ldn +'</a></span>' +
                        '</div> ' +
                    '</div>';

                    _html += '<div class="clear"></div><div class="left"><b>Categorias</b></div> ' +
                             '<div class="opt right linko">' +
                                '<a href="https://wiki.salahumanitaria.co/wiki/Sistema_de_categor%C3%ADas_del_m%C3%B3dulo_de_eventos_de_conflicto" target="_blank">Definici&oacute;n de categorias</a>' +
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

                                _html += '<div class="detail">';

                                if ((_js.f[k][0] != '' && _js.f[k][1] != '')) {
                                    _html += '&raquo; '+_js.f[k][1]+' ( '+_js.f[k][0]+' )';
                                }


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
                            _html += '</div>';
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
                    t: ' OCHA Colombia Monitor - Listado de eventos',
                    html: _html,
                    w: 800,
                    h: 500,
                    funOpen: listReportsEvents,
                });
            }
        },
    });
}

function showHideFeaturedIcon() {
    var $f = $('#featured');
    if (layer_ft_exists){
        //$f.show();
          $f.hide();
    }
    else {
        $f.hide();
    }
}

function mapMove(event)
{
    // Prevent this event from running on the first load
    if (mapLoad > 0 || (l_variacion && !l_variacion.getVisible()))
    {
        // Refresh Map
        addFeatures();

        // Municipios en zoom 3
        var _vd = true;
        var _vm = false;

        if (map.getView().getZoom() >= 2) {
            _vd = false;
            _vm = true;
        }
    }
}


function styleFunction(feature, resolution) {
    var size = feature.getProperties().count;

    if (_cluster) {
        if (String(feature.getProperties().link).indexOf(subdomain_ec) == -1) {
            colorFill = '#7FB92F';
            colorStroke = '#7FB92F';
        }
        else {
            colorFill = '#E56A54';
            colorStroke = '#E56A54';
        }
    }
    else {
        colorFill = '#FFFFFF';
        colorStroke = '#' + feature.getProperties().color;
    }

    var r = markerRadius;
    for (var i=1;i<jenks.length;i++) {
        if (size <= jenks[i]) {
            r = (i==1) ? 7 : markerRadius * i;
            break;
        }
    }

    var textFill = new ol.style.Fill({
        color: '#ffffff'
    });
    var textStroke = new ol.style.Stroke({
        color: '#ffffff',
        width: 2
    });

    style = [new ol.style.Style({
      image: new ol.style.Circle({
        radius: r,
        fill: new ol.style.Fill({
          color: colorFill
        }),
        stroke: new ol.style.Stroke({
            color: colorStroke,
            width: 4,
            strokeOpacity: 0.5
          }),
      }),
      text: new ol.style.Text({
        text: size.toString(),
        fill: textFill,
        // stroke: textStroke
      })
    })];
  return style;
}

function styleFtFunction(feature, resolution) {

 var size = feature.getProperties().count;
  colorFill = 'rgba(252,255,0,1)';
  colorStroke = 'rgba(252,255,0,0.5)';
  
     var r = markerRadius;
    for (var i=1;i<jenks.length;i++) {
        if (size <= jenks[i]) {
            r = (i==1) ? 7 : markerRadius * i;
            break;
        }
    }
	
	    var textFill = new ol.style.Fill({
        color: '#fff'
    });
    var textStroke = new ol.style.Stroke({
        color: 'rgba(0, 0, 0, 0.6)',
        width: 2
    });
	
	    style = [new ol.style.Style({
      image: new ol.style.Circle({
        radius: r,
        fill: new ol.style.Fill({
          color: colorFill
        }),
        stroke: new ol.style.Stroke({
            color: colorStroke,
            width: 3
          }),
      }),
      text: new ol.style.Text({
        text: size.toString(),
        fill: textFill,
        stroke: textStroke
      })
    })];
  return style;

/*
    return [new ol.style.Style({
        image: new ol.style.Icon(({
            src: feature.get('icon'),
            size: [24,24]
        }))
    })]


*/
}

function showHideLayers(c) {

    var $vd = $('#variacion_data');
    var $vdl = $('#variacion_legend');
    var $tabs = $('#tabs');
    var $slide_cluster = $('#slide_cluster');

    if (c == 'variacion') {
        l_ec.setVisible(false);
        l_dn.setVisible(false);
        l_ft.setVisible(false);

        l_variacion.setVisible(true);

        $vd.show();
        $vdl.show();
        $tabs.hide();
        $slide_cluster.hide();
    }
    else {

        if (l_variacion !== undefined) {
            l_variacion.setVisible(false);
        }

        $vd.hide();
        $vdl.hide();
        $tabs.show();
        $slide_cluster.show();

        /*
        var _l;

        switch(c) {
            case 'ec':
                _l = l_ec;
            break;
            case 'dn':
                _l = l_dn;
            break;
            case 'ft':
                _l = l_ft;
            break;
        }
        */

        var _l = eval('l_' + c);

        if (_l.getVisible()) {
            l_ft.setVisible(true);
        }
    }
}

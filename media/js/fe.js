var _mes = ['','Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];

var base = '';
var base_ol = '';
var is_portal = false;
var layout;
var num_carga = 20;
var cargar_mas = 0;  // Cuenta las veces que se hace click en cargar mas
var _cluster = true;  // Mapa en cluster, parametro para ushahidi json/cluster, json/index
var resetLimit = false;
var acceso = false;
var _year, _month, _day, _today;
var ocultar = '';

$(function(){

    _today = new Date();
    _year = _today.getFullYear();
    _month = _today.getMonth();
    _day = _today.getDate();
    
    // Este bloque cuando el periodo inicial era una semana
    var _ms = 2;  // Meses hacia atras
    var _ds = 7; // Dias iniciales hacia atras
    var _ii = new Date(_year,_month,_day,0,0).getTime();
    var _iniObj = new Date(_ii - daysToMiliseconds(_ds));
    var _iniD = _iniObj.getDate();
    var _iniM = _iniObj.getMonth();
    var _iniY = _iniObj.getFullYear();

    // Ahora periodo inicial es el acumulado del año
    /*
    var _iniObj = new Date(_year,0,1);
    var _iniD = 1;
    var _iniM = 0;
    var _iniY = _year;
    */

    var _ini = _iniObj.getTime(); // milisecs
    var _fin = new Date().getTime();

    // Para ushahidi va en segundos
    $('#startDate').val(_ini/1000);
    $('#endDate').val(_fin/1000);

    markIniFin(_iniD,_iniM,_iniY,_day,_month,_year);

    setYear('ini',_iniY);
    setYear('fin',_year);


    if (window.location.hostname == 'localhost'
        || window.location.hostname == '190.66.6.168') {
        base = '/monitor';
        base_ol = '/monitor';
    }
    else {
        base = 'http://' + window.location.hostname;
        base_ol = '';
    }

    if (typeof portal !== "undefined") {
        is_portal = true;
    }
    
    if (typeof layout === "undefined") {
        layout = 'monitor';
    }
    
    set100Height();
    //setMapWidth();

    $(window).resize(function(){ 
        set100Height();
        //setMapWidth();
    });

    $(document).ajaxStart(function(){ $('#loading').show(); });
    $(document).ajaxStop(function(){ $('#loading').hide(); });
    
    // Intro text
    $('#lmh').click(function() {
        m({
            t: 'Monitor - ColombisSSH',
            hid: 'qlmh',
            w: 600,
            h: 400
        });
		
        // prevent the default action
		return false;
	});

    // Menu
    $menu_li = $('#menu').find('li.sub');
    $menu_li.click(function() {

        $('.filtro').fadeOut(100);
        
        // Top del filtro
        var $div = $('#' + $(this).data('div'));
        var topx = 100*$div.data('index') + 'px';

        $div.css('top', topx);
        $div.fadeIn(100);
        
        $menu_li.removeClass('menu_activo');
        $(this).addClass('menu_activo');
    });
    // Filtro cats
    /*
    $('.cat').click(function() {
        $('div.filtro').hide();
        $(this).siblings('div.filtro').slideDown();
    });
    */
    
    if (layout == 'monitor') {
        $('input').iCheck({
            checkboxClass: 'icheckbox_square-orange',
            radioClass: 'iradio_square-orange'
        });

    
        $('.btn_fcat').click(function() {

            setCatsHidden();

            var inst = $(this).attr("class").split(' ')[0];
            
            // Si es filtrar mapa desde acceso, cambia el texto de ocultar
            // eventos en desastres
            acceso = false;
            if (inst == 'acceso') {
                $('#btn_show_dn').html('Mostrar eventos');

                acceso = true;
            }
            
            addFeatures(inst);
            totalesxDepto();
            
            $(this).closest('.filtro').hide();

        });

        // Oculta eventos
        $('.btn_show_e').click(function() {

            var cs = $(this).attr('class').split(' ');

            ocultarViolenciaDesastres(cs[0]);
            
            
            $(this).closest('.filtro').hide();
            
        });

        //  Categorias : Todas/ninguna
        var td_ec = false;
        $('.tn_fcat').click(function() { 
            $(this).closest('.filtro').find('input:checkbox').each(function() {
                $(this).iCheck('toggle');
                $(this).attr('checked', td_ec);
            });
            
            td_ec = !td_ec;
            
            return false;
        });
        
        // Minimize - Maximize total
        $('#minmax_total').toggle(
            function() { 
                //$('.ui-tabs-panel, #mapas_tipos').hide(); 
                $('.ui-tabs-panel').hide(); 
                $(this).removeClass('minimize'); 
                $(this).addClass('maximize'); 
            }, 
            function() { 
                //$('.ui-tabs-panel, #mapas_tipos').show(); 
                $('.ui-tabs-panel').show(); 
                $(this).addClass('minimize'); 
                $(this).removeClass('maximize'); 
            }
        );

        // Deptos: Todos/ninguno
        var td_st = false;
        $('#totalxd_all_chk').click(function() { 
            $('#table_totalxd').find(':checkbox:not(:first)').each(function() {
                $(this).attr('checked', td_st);
            });
            
            td_st = !td_st;
        });

        // Filtrar deptos
        $('#filter_states').click(function(){ 
            addFeaturesFirstTime();
            totalesxDepto();
        });

        // Descargar inicidentes
        $('#download_incidents').click(function() {
            $('#loading').show();
            $.ajax({
                    url: 'download_incidents/' + $('input[name="descargar_v_d"]:checked').val(),
                    success: function() {
                        $('#loading').hide();
                        location.href = base + '/export/xls/incidentes/Monitor-Incidentes';            
                    }
                });
        });

        // Cerrar filtro
        $('a.close').click(function() { 
            $(this).closest('.filtro').hide();
            $('li[data-div="' + $(this).attr('data-div') + '"]').removeClass('menu_activo');
        });
        
        // Cerrar opciones fecha
        $('.filtro_fecha').find('div.close').click(function() { 
            $(this).closest('.filtro_fecha').hide();
        });
    

        $('input[name=rap]').on('ifClicked', function(event){
            applyPeriod($(this).val());
        });
    }
    
    // Tipo de mapa
    $('.mapa_tipo:not(.active)').click(function() {
        var that = this;
        
        $.ajax({
            url: 'session_var/mapa_tipo/' + $(that).data('tipo'),
            success: function() {
                $('.mapa_tipo').removeClass('menu_activo');
                $(that).addClass('menu_activo');
                addFeaturesFirstTime();
                totalesxDepto();
            }
        });
    });
    
    // Group - Ungroup
    $('#group_fts').click(function() {

        // Muestra colors de cats
        $('.cat_color').toggle();

        _cluster = !_cluster;

        var dgt = (_cluster) ? 'Desagrupado' : 'Agrupado'; 

        $(this).find('span').html(dgt);

        addFeaturesFirstTime();
        totalesxDepto();
        
        // Activa resumen
        $('#tabs').tabs("select", 1);
    });
    
    $('#layers').click(function() {
        $('#layers_div').toggle();
    });

    $('#layers_div').on('ifClicked', ':checkbox', function(event){
    //$('#layers_div').on('click', ':checkbox', function() {

        var $t = $(this);
        var $li = $t.closest('li');
        var v = true;

        // evalua esto antes de ponerse checked
        if ($t.is(':checked')) {
            v = false;
            $li.removeClass('selected');
        }
        else {
            $li.addClass('selected');
        }

        addWMSLayer($t.val(),$t.val(),v);
    });
    
    $('#depto').click(function() {
        $('#depto_dropdown').toggle();
    });
    
    $('#depto_dropdown').on('click','li', function() {

        $li = $(this);
        $chks = $('#table_totalxd').find(':checkbox');

        mapLoad = 0; // Variable para no recargar features en mapMove()

        // Colombia
        if ($li.data('value') == 0) {
            $chks.prop('checked', true);
            resetMap(); // map.js
        } 
        else {
            $chks.each(function(){
                chk = ($(this).val() == $li.data('value')) ? true : false;
                $(this).prop('checked', chk);
            });
            
            selDepto($li.data('centroid'));   // in map.js
        }
       
        var html = ($li.html() != 'Todos') ? $li.html() : 'Colombia';

        $('#depto_t').html(html);

        addFeatures();
        
        totalesxDepto();

    });
    
    $('#collapse').click(function() {
        $('.op').toggle();
        $(this).toggleClass('expand');
    });

    if (layout != 'monitor') {
        // Dropdown de periodos 
        $('#stime').change(function() {
            applyPeriod($(this).val());
            addFeatures();
            totalesxDepto();
        });
    }

    // Click categorias en resumen
    $('.resumen_row', '#resumen_ec').live('click', function(){ 

        $('#fcat_ec').find('input:checkbox').attr('checked', false);
        $('#fcat_ec').find('input:checkbox[value='+$(this).attr('id')+']').attr('checked', true);
        
        setCatsHidden();
        //addFeatures($(this).attr("class").split(' ')[0]);
        addFeatures('ec');
        totalesxDepto();
    });
    
    if (layout != 'portal_home') {
        
        // Colocar categorias en inputs hidden para envio a json/cluster
        setCatsHidden();
    
        //Tabs
        if ($('#tabs').length > 0) {
            $('#tabs').tabs();
        }
    
        // Fecha inicio - Fecha fin
        $('.fecha').click(function (){
            $('div.filtro_fecha:not(#' + $(this).attr('dv') + ')').hide();
            $('#' + $(this).attr('dv')).slideToggle();
        });
    
        $('div.filtro_fecha').each(function() {
        
            var that = this;

            $(this).find('li').click(function() { 
                
                $(this).closest('div').find('li').removeClass('selected');
                $(this).addClass('selected');
                var q = $(this).attr('q');
                var y = $(this).attr('y');

                var $input = $('#' + $(this).attr('q') + '_text');
                var $div = $('#' + q + '_div');

                
                if ($div.find('li.selected').length == 3) {

                    var _ini = new
                    Date($('li.selected[q=ini][y=yyyy]').attr('val'),$('li.selected[q=ini][y=mes]').attr('val')-1,$('li.selected[q=ini][y=dia]').attr('val')).getTime();

                    var _fin = new
                    Date($('li.selected[q=fin][y=yyyy]').attr('val'),$('li.selected[q=fin][y=mes]').attr('val')-1,$('li.selected[q=fin][y=dia]').attr('val')).getTime();
                    
                    if (_ini > _fin) {
                        alert('Desde debe ser menor que Hasta');
                        $input.val('');
                        $div.find('li').removeClass('selected');
                        //$('stime').val(0);
                    }
                    else {
                    
                        $input.val($('li.selected[q='+q+'][y=dia]').text() + ' de ' +
                        $('li.selected[q='+q+'][y=mes]').text()+' '+ $('li.selected[q='+q+'][y=yyyy]').text());
                        
                        $('#startDate').val(_ini / 1000);
                        
                        $('#endDate').val(_fin / 1000);

                        //$('#stime').val(0);
                    }
                }
            });
        });
    
        $('div.filtro_fecha').find('.close').click(function() {  $('div.filtro_fecha').slideUp(); });
    
        $('#lff').click(function() {
            
            var _ini = getStartEnd('ini');
            var _fin = getStartEnd('fin');
            
            if (_ini > _fin) {
                alert('La fecha Desde debe ser menor que la fecha Hasta');
            }
            else {
                addFeaturesFirstTime();
                totalesxDepto();
            }

            return false;
        
        });
    }

    totalesxDepto();

    mapRender();
    
    // Click en el departamento en la lista derecha
    var $table = $('#table_totalxd');

    $table.find(':checkbox').live('click', function() {
        if ($(this).is(':checked')) {
            $(this).closest('tr').removeClass('unselected');
        }
        else {
            $(this).closest('tr').addClass('unselected');
        }
    });
                
    // Row events
    //$table.find('tr:not(:first) td.n, tr:not(:last) td.n').live('click', function() {
    //});

    // Carga el listado de capas de geonode del archivo geonode_layers.html el
    // cual es creado por el script geonode_get_layers.sh
    $.ajax({
        url: 'geonode_layers.html',
        success: function(html){

            $ul = $('#layers_ul');
        
            $ul.append(html);
                
            // Ordena por nombre
            $ul.append($ul.find('li').sort(function(a, b) { 

                aa = $(a).find('h3').text();
                bb = $(b).find('h3').text();

                return aa == bb ? 0 : aa < bb ? -1 : 1
             }));
            
            $ul.find('input').iCheck({
                checkboxClass: 'icheckbox_square-orange',
                radioClass: 'iradio_square-orange'
            });
        }
    });
            
    // Busca layers en el listado de geonode
    $('#layers_search').keyup(function(e) {
        clearTimeout($.data(this, 'timer'));

        if (e.keyCode == 13)
          search(true);
        else
          $(this).data('timer', setTimeout(search, 500));
    });

    // Reset de buscar layers

});

function search(force) {
    var $i = $("#layers_search");
    var existingString = $i.val();
    
    if (!force && existingString.length < 3) return;

    var $l = $('#layers_ul li');

    $l.each(function(){ 

        var re = new RegExp(existingString, 'i');

        $(this).show();
        var match = re.exec($(this).html());
        if (match === null) {
            $(this).hide();
        }
    });


    $i.focusout(function(){ $l.fadeOut(100); });

}

function ocultarViolenciaDesastres(cs) {

    var v_chart_serie;

    if (cs == 'ec') {
        var ly = map.getLayersByName('Emergencia Compleja')[0];

        $r_hide = $('#resumen_ec');
        $r_show = $('#resumen_dn');
    }
    else {
        var ly = map.getLayersByName('Desastres Naturales')[0];
        
        $r_hide = $('#resumen_dn');
        $r_show = $('#resumen_ec');

        $eventos_desastres = $('#report_list_map_desastres');
        
    }
    
    if (ly.getVisibility()) {
        $(this).html('Mostrar eventos'); 
        ly.setVisibility(false);
        v_chart_serie = false;

        $r_hide.hide();
        $r_show.removeClass('left half');
        $eventos_desastres.hide();
    
        ocultar = cs;
    }
    else {
        $(this).html('Ocultar eventos'); 
        ly.setVisibility(true);
        v_chart_serie = true;
        
        $r_hide.show();
        $r_show.addClass('left half');
        $eventos_desastres.show();

        ocultar = '';
    }

    var ly_ft = map.getLayersByName('Destacados')[0];
    ly_ft.setVisibility(!ly_ft.getVisibility());
    
    $('#chart_1').highcharts().get(cs).setVisible(v_chart_serie,true); 
    

}
function applyPeriod(val) {
    if (val != 0) {
        var _ini = getStartEnd('ini');
        var _fin = getStartEnd('fin');
        
        var _iiObj = new Date(_year,_month,_day,0,0);
        var _ii = _iiObj.getTime();

        switch(val) {
            // Acumulado
            case 'acum':
                _ini = new Date(_year,0,1);
                _fin = _today;

                showGroupUngroup('show');
            break;
            // Mes
            case 'm':
                _ini = new Date(_ii - daysToMiliseconds(30)); // milisecs
                _fin = _today;

                showGroupUngroup('show');
            break;
            // Semana
            case 's':
                _ini = new Date(_ii - daysToMiliseconds(7)); // milisecs
                _fin = _today;
                showGroupUngroup('show');
            break;
            // Ayer
            case 'ay':
                _ini = new Date(_ii - daysToMiliseconds(1)); // milisecs
                _fin = _today;
                showGroupUngroup('show');
            break;
            // Hoy
            case 'h':
                _ini = new Date(_ii); // milisecs
                _fin = _today;
                showGroupUngroup('show');
            break;
            // Año
            default:
                _ini = new Date(val,0,1);
                _fin = new Date(val,11,31);
                showGroupUngroup('false');
            break;
        }

        //$('#dslider').dateRangeSlider('values', _ini,_fin);
        $('#startDate').val(_ini/1000); // Segundos para ushahidi
        $('#endDate').val(_fin/1000); // Segundos para ushahidi
        
        var _iniY = _ini.getFullYear();
        var _finY = _fin.getFullYear();

        setYear('ini', _iniY);
        setYear('fin', _finY);

        markIniFin(_ini.getDate(),_ini.getMonth(),_iniY,
        _fin.getDate(),_fin.getMonth(),_finY);

        //totalesxDepto();
        //addFeaturesFirstTime();

        resetLimit = true;
        cargar_mas = 0;
    }
}

m = function(o){	
	
    if($('#dialog').size()){
		$('#dialog').dialog('destroy');
	}
    else { 
		$('body').append('<div id="dialog"></div>')
    }

	//$('#dialog').html('<img src="img/loading.gif">&nbsp;Loading....');

	if(o.hid){
		 $('#dialog').html($('#' + o.hid).html());
	}
    else if (o.html) {
        $('#dialog').html(o.html);
    }
    else if(o.u){
		$('#dialog').load(o.u, function(r, s, x){
			 if (s == "error" &&  x.status == 403) window.location.href  = '/';		 
		});
	}
     
    sm(o);
}

// it shows a modal dialog
sm = function(o){
	$('#dialog').dialog({
		height: (o.h ? o.h : 500),
		width:  (o.w ? o.w : 900),
		modal: true,
		title: o.t,
        open: o.funOpen ? o.funOpen : null,
		close: function(){ $(this).dialog('destroy'); $(this).remove()}
	});
}

listReportsEvents = function() {
    $('.report_list_map').each(function() {
        $(this).hover(function() { $(this).find('.opt').show(); },
            function() {$(this).find('.opt').hide();   } );
        });
        $(this).find('.t').click(function() {
            $(this).parent('div').find('.hide').slideToggle();
        });
}

totalesxDepto = function(more) {
    
    var _ini = getStartEnd('ini');
    var _fin = getStartEnd('fin');

    var _cats = $('#currentCatE').val() + '|' + $("#currentCatD").val();

    var num;
    var num_total;

    _states = getStatesChecked();
    
    // Portal EHP
    //if (is_portal) {
    if (layout == 'portal_home') {

        $.ajax({
            url: base + '/getResumenPortalHome/' + _ini + '/' + _fin,
            dataType: 'jsonp',
            success: function(json){
                for (j in json) {
                    
                    $('#' + j).html(json[j]['t']);
                    $('#' + j + '_div').data('index', json[j]['v']);
                }

                var $r = $('#resumen'); // your parent ul element
                
                $r.append($('div.r').sort(function(a, b) { return $(b).data('index') - $(a).data('index'); }));

                for (j in json) {

                    if (json[j]['v'] == 0) {
                        $('#' + j).html('--');
                    }
                }

            }
        });
    }
    else if (layout == 'portal') {
        
        if (typeof more === 'undefined') {
            $('.tab_data').html('');
        } 

        var limiti;
        if (resetLimit) {
            limiti = 0;
        }
        else {
            //limiti = $('.report_list_map:not(.from_map)').length;
            limiti = cargar_mas * num_carga;
        }

        var term = ['ec', 'dn'];
        
        if (limiti == 0) {
            $('.tab_data').html('<div>&nbsp;&nbsp;<img src="' + base + '/media/img/ajax-loader-mini.gif" />&nbsp;Cargando datos...</div>');
        }
        
        $.ajax({
            url: base + '/getIncidentesPortal/' + _ini + '/' + _fin + '/' + _cats + '/' + limiti + '/' + _states ,
            dataType: 'jsonp',
            beforeSend: function(){ $('#loading').show() },
            success: function(json){

                //for(jj in json) {
                    num_total = json['t'];
                    num_e = json['t_e'];
                    num_d = json['t_d'];
                    num = json['e'].length;

                    $('#num_total_span').html(num_total);
                    $('#num_total_ec_span').html(num_e);
                    $('#num_total_dn_span').html(num_d);

                    var _html = '';
                    if (num > 0) {
                        for(var i=0, j=num; i < j; i+=1) {
                            _js = json['e'][i];
                            _dt = _js.d.split(/\W+/);
                            _date = [_dt[2],_mes[_dt[1]*1],_dt[0]].join(' ');
                            _html += ''+
                                '<div class="clear report_list_map report_list_map_' + _js.sys + '"> ' +
                                '<div class="l ' + _js.sys + '"> ' + _js.sys.substring(0,1).toUpperCase() + '</div> ' +
                                //'<div class="date detail">'+ _date +'</div> ' +
                                '<div class="t clear">' + _js.t +'</div> ' +
                                '<div class="hide">' +
                                    '<div class="date detail">'+ _js.d +'</div> ' +
                                    '<div class="loc detail">'+ _js.ln + ' <span class="pdf opt"> ' +
                                    '<a href="http://sidih.salahumanitaria.co/sissh/download_pdf.php?c=2&id_depto='+_js.ld+'&id_mun=" target="_blank">' +
                                    'Perfil '+ _js.ldn +'</a></span></div> ' +
                                '</<div></div>';
                                
                                _html += '<div class="clear hide"><div class="left"><b>Categorias</b></div> ' +
                                         '<div class="opt right linko">' +
                                         '<a href="http://www.salahumanitaria.co/gtmi/wiki/index.php/Sistema_de_categor%C3%ADas_del_m%C3%B3dulo_de_eventos_de_conflicto" target="_blank">Definici&oacute;n</a></div>';
                                for (c in _js.c) {
                                    _html += '<div class="clear cat">&raquo;&nbsp;'+ c;
                                    
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
                                _html += '</div>';
                            
                            _html += '<div class="clear"></div> ';
                            
                            if (show['desc']) {
                                _html += '<div class="desc hide"><b>Descripci&oacute;n</b>: '+ _js.desc +'</div> ';
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
                                        if (_js.f[k][3] != '') {
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
                            
                        // Cargar mas
                        if ((limiti + num_carga) < num_total) {
                            _html += '<div id="cargar_mas"><div class="btn cargar_mas">Cargar mas eventos</div></div>';
                        } 
                    }
                    else {
                        _html = '<div class="no">No hay eventos registrados</div>';
                    }
                    
                    $div = $('#incidentes');
                    if (limiti == 0) {
                        $div.html(_html);
                    }
                    else {
                        $div.find('.cargar_mas').remove();
                        $div.append(_html);
                    }
                //}
                

                // Cargar mas evento
                $('.cargar_mas').click(function() {
                    cargar_mas += 1;
                    totalesxDepto(true);
                    return false;
                });

                //listReportsEvents();
                
                // Zoom state
                if (_states != 0){
                    selDepto($('#state').attr('centroid'));   // in map.js
                }
                
                $('#loading').hide();
                
                resumenAfectacion(json);

                charts(json.charts);
                
                $('#loading_data').hide();
            
            }
        });
    
    }
    else {
        
        // Afectacion
        var titulo = (getMapaAfectacion() == 1) ? 'afectados' : 'eventos';
            
        // Acceso
        if (acceso) {
            titulo = 'restricción <br /> al acceso';
        }
        // Titulo derecha
        var t_ini = $('#ini_text').val();
        var t_fin = $('#fin_text').val();

        $('#titulo_general > #tgt').html('Mapa de ' + titulo);
        $('#titulo_general > #tgc').html(t_ini + ' - ' + t_fin);
        
        $('#table_totalxd tbody').html('<tr><td colspan="4"><img src="media/img/ajax-loader-mini.gif" />&nbsp;Actualizando datos...</td></tr>');
        
        $.ajax({
            url: base + '/totalxd/' + _ini + '/' + _fin + '/' + _cats + '/' + _states,
            dataType: 'json',
            success: function(data) {
                
                $('#table_totalxd tbody tr').remove();
                //$('#table_totalxd').trigger('update');
                
                // Totales
                var _t = data.t;
                var checked;
                var no_e = true;

                var $table = $('#table_totalxd tbody');
                var $depto_dropdown = $('#depto_dropdown');
                
                var html = $table.html();
                var dd_options = '<li data-value=0>Todos</li>';

                html += '<tr class="totalxd"><td class="left">Total</td><td class="ec">' + _t.ec + '</td><td class="dn">' + _t.dn + '</td></tr>';

                for (var d in data.r) {
                    _i = data.r[d];
                    checked = (_i.css == '') ? 'checked' : '';

                    if (no_e && (_i.ec > 0 || _i.dn > 0)) {
                        no_e = false;
                    } 
                    html += '<tr class="f ' + _i.css + ' ' + _i.hide + '"><td class="hide"><input type="checkbox" name="deptos[]" value="' + _i.state_id + '" '+checked+' /></td><td class="n left">'+_i.d+'</td><td class="ec">'+_i.ec+'</td><td class="dn">'+_i.dn+'</td><td class="hide centroid">'+_i.c+'</td></tr>';

                    dd_options += '<li class="' + _i.hide + '" data-value="' + _i.state_id + '" data-centroid="' +_i.c + '"> ' +_i.d + '</li>';
                }
                
                // Aviso de no eventos
                //if ($table.find('.f:not(.hide)').length == 0) {
                if (no_e) {
                    html += '<tr><td colspan="4"><br />No existen eventos</td></tr>';
                }

                $table.html(html);
                $depto_dropdown.html(dd_options);

                // Ordena tabla
                //forceSortTable();
                
                resumenAfectacion(data);
                
                charts(data.charts);
                
                $('#loading_data').hide();
            }
        });

    }
}

resumenAfectacion = function(data) {
    
    // Afectacion
    var titulo = (getMapaAfectacion() == 1) ? 'personas afectadas' : 'eventos';
    var total_ec = 0;
        
    $('#resumen_ec, #resumen_dn').find('.resumen_row:not(:first)').remove();

    $resumen_ec = $('#resumen_ec');

    for (var d in data.rsms_ec) {
        $div = $('.resumen_row:first').clone();
        $div.removeClass('hide');
        $div.addClass('ect');
        
        rsm = data.rsms_ec[d];
        
        $div.attr('id', rsm.cat_id);
        $div.find('.num').html(numberWithCommas(rsm.n));
        $div.find('.cat').html(rsm.t);
        $div.find('.cat_color').css('background-color', '#' + rsm.c);;

        //total_ec += rsm.n*1;
        
        if (rsm.n > 0) {
            $resumen_ec.append($div);
        } 
    }
        
    // El total se toma de la lista de departamentos, porque este total
    // sumado no corresponde, dado que un evento puede tener varias categorias
    total_ec = data.t.ec;
    
    if (total_ec > 0) {
        $('#resumen_total_ec_num').html(numberWithCommas(total_ec));
        $resumen_ec.show();
    }
    else {
        $resumen_ec.hide();
    }
    
    $resumen_dn = $('#resumen_dn');
    var total_dn = 0;
    for (var d in data.rsms_dn) {
        $div = $('.resumen_row:first').clone();
        $div.removeClass('hide');
        $div.addClass('dnt');
        
        rsm = data.rsms_dn[d];
        
        $div.find('.num').html(numberWithCommas(rsm.n));
        $div.find('.cat').html(rsm.t);
        
        //total_dn += rsm.n*1;

        if (rsm.n > 0) {
            $resumen_dn.append($div);
        }
    }
    
    // El total se toma de la lista de departamentos, porque este total
    // sumado no corresponde, dado que un evento puede tener varias categorias
    total_dn = data.t.dn;
    
    if (total_dn > 0) {
        $('#resumen_total_dn_num').html(numberWithCommas(total_dn));
        $resumen_dn.show();
    }
    else {
        $resumen_dn.hide();
    }

    if (total_ec > 0 && total_dn > 0) {
        $('#resumen_ec, #resumen_dn').addClass('left half');
    }
    else {
        $('#resumen_ec, #resumen_dn').removeClass('left half');
    }

    $('.data_title').html(titulo);

    // Mantiene oculto resumen despues de ocultar eventos y dar click en categoria de resumen
    if (ocultar != '') {
        $r = $('#resumen_' + ocultar);
        $r.hide();

        if (ocultar == 'ec') {
            $('#resumen_dn').removeClass('left half');
        }
        else {
            $('#resumen_ec').removeClass('left half');
        }
    }

}

charts = function(data_charts) {
    
    Highcharts.setOptions({
        chart: {
            style: {
                fontFamily: 'Arial',
                fontSize: '11px'
            }
        },
        yAxis: { min: 0}

    });
    
    var title_style = { fontSize: '14px', margin: 0 }

    // No info
    if (data_charts[0].data.length == 0 && 
         data_charts[1].data.length == 0 && 
         data_charts[2].data.length == 0
        )
    {
        $('#tendencia').html('<h2>No hay información</h2>');

        return;
    }

    var s = data_charts[0];
    $('#chart_1').highcharts({
        chart: {
            type: 'line',
            width: 350,
            height: 250,
            style: {
            }
        },
        plotOptions: {
            series: {
                marker: {
                    radius: 3,
                }
            }
        },
        title: {
            text: s.title,
            style: title_style
        },
        //xAxis: s.xAxis,
        xAxis: {
            type: 'datetime',
            dateTimeLabelFormats: { // don't display the dummy year
                month: '%e. %b',
                year: '%b'
            },
            gridLineWidth: 1,
            lineColor: '#000',
            tickColor: '#000',
        },
        yAxis: s.yAxis,
        series: s.data
    });
    
    if (data_charts.length > 2) {
        
        $('#charts_pie').show();
        
        var marginPie = [30,30,10,30];
        var pie_h = 180;
        var pie_w = 350;

        var pie_plot_options = {
                        allowPointSelect: true,
                        cursor: 'pointer',
                        dataLabels: {
                            enabled: true,
                            distance: 20,
                            color: '#000000',
                            connectorColor: '#000000',
                            format: '<b>{point.name}</b>: {point.percentage:.1f} %'
                        }
                    };

        var column_plot_options = {
            stacking: 'normal',
        }

        var s = data_charts[1];
        $('#chart_2').highcharts({
            chart: {
                type: 'pie',
                width: pie_w,
                height: pie_h,
                margin: marginPie,
                style: {
                }
            },
            plotOptions: {
                    pie: pie_plot_options
                },
            title: {
                text: s.title,
                style: title_style
            },
            series: [{ data: s.data }]
        });
        
        var s = data_charts[2];
        $('#chart_3').highcharts({
            chart: {
                type: 'pie',
                width: pie_w,
                height: pie_h,
                margin: marginPie,
                style: {
                }
            },
            plotOptions: {
                    pie: pie_plot_options 
                },
            title: {
                text: s.title,
                style: title_style
            },
            series: [{ data: s.data }]
        });
        
        // columna g. poblacional
        /*
        var s = data_charts[2];
        $('#chart_4').highcharts({
            chart: {
                type: 'column',
                width: pie_w,
                height: pie_h,
                margin: marginPie,
                style: {
                }
            },
            plotOptions: {
                    column: column_plot_options 
                },
            title: {
                text: s.title,
                style: title_style
            },
            series: [{ data: s.data }]
        });
        */
    }
    else {
        $('#charts_pie').hide();
    }
}

forceSortTable = function() {
    var sorting = [[1,1]]
    
    $('#table_totalxd').trigger('update');
    $('#table_totalxd').trigger("sorton", [sorting]);
}

setCatsHidden = function() {
    
    var _ids = [];
    
    $('#fcat_ec').find('input:checked').each(function() {
        _ids.push($(this).val());
    });

    $('#currentCatE').val(_ids);
    
    _ids = [];
    $('#fcat_dn').find('input:checked').each(function() {
        _ids.push($(this).val());
    });

    $('#currentCatD').val(_ids);
    
}

daysToMiliseconds = function(d) {
    return d * 24 * 3600 * 1000;
}

getStartEnd = function(c) {
    var v = [];

    v['ini'] = $('#startDate').val();
    v['fin'] = $('#endDate').val();

    return v[c];
}

getYear = function(c) { 
    //return $('#totalxd_y').text();
    return $('#yyyy_' + c).val();
}

setYear = function(c,y) { 
    $('#yyyy_' + c).val(y);
    
    var se = getStartEnd();

    var ini_t = new Date(getStartEnd('ini')*1000);
    var fin_t = new Date(getStartEnd('fin')*1000);

    $('#periodo_texto').html(ini_t.getDate() + '-' + _mes[1 + ini_t.getMonth()] + ' ' +ini_t.getFullYear() + ' al ' + fin_t.getDate() + '-' + _mes[1 + fin_t.getMonth()] + ' ' +fin_t.getFullYear());

}

markIniFin = function(id,im,iy,fd,fm,fy) { 
    
    var c = 'selected';

    im = im + 1;
    fm = fm + 1;

    $('#ini_fin').find('li').removeClass('selected');

    $('li[y=dia][q=ini][val='+id+']').addClass(c);
    $('li[y=mes][q=ini][val='+im+']').addClass(c);
    $('li[y=yyyy][q=ini][val='+iy+']').addClass(c);
    
    $('li[y=dia][q=fin][val='+fd+']').addClass(c);
    $('li[y=mes][q=fin][val='+fm+']').addClass(c);
    $('li[y=yyyy][q=fin][val='+fy+']').addClass(c);
    
    $('#ini_text').val( id + ' de ' + _mes[im] + ' ' + iy);
    $('#fin_text').val( fd + ' de ' + _mes[fm] + ' ' + fy);
}

getStatesChecked = function(){ 

    var _sts = [];
    var $t = $('#table_totalxd');

    if ($t.find(':not(:checked)').length > 0) {
        
        $t.find(':checked').each(function() {
            
            if ($(this).val() != 0) {
                _sts.push($(this).val());
            }
        });
    }

    return _sts.join(',');
}

getMapaAfectacion = function(){ 
    
    return ($('.mapa_tipo.menu_activo').data('tipo') == 'eventos') ? 0 : 1;

}

getAccesoCats = function(){  
    
    var acceso = [];

    // Filtro acceso
    $('#fcat_list_acceso').find(':checked').each(function(){ 
        acceso.push($(this).val()); 
    });

    return acceso.join(',');
}

showGroupUngroup = function(s) {

    $btn = $('#group_fts');
    
    if (s == 'show') {
        $btn.show();
    }
    else {
        $btn.hide();
    }
}

function numberWithCommas(n) {
    var parts=n.toString().split(".");
    return parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",") + (parts[1] ? "." + parts[1] : "");
}

String.prototype.capitalize = function() {
    return this.charAt(0).toUpperCase() + this.slice(1);
}

set100Height = function(){ 
    $('.map_monitor, #menu').css('height', $(document).height());
}
setMapWidth = function(){ 
    //$('#map').css('width', $(document).width() - $('#menu').css('width'));
    $('.map_monitor').css('width', $(document).width());
}

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
var cp_ecs = {};
var titulo;
var totales_ini = true;
var id_start_date = 'ini_date';
var id_end_date = 'fin_date';
var subtotales;

// Temporal para fenomeno del niño
// Datos de afectación desagregado sigpad, almacenado en subtotales cuando se hace totalesxd
function setSubtotales() {
    
    var sub = subtotales.dn;
    $bt = $('#chart_subtotal');

    var html = '';
    for (s in sub) {
        var n = sub[s];

        if (n > 0) {
            html += '<div style="background-color: #f2f2f2;margin:1px;padding: 3px">' +
                    '<div style="float:left">' + s + '</div>' + 
                    '<div id="hectareas" class="right">'+numberWithCommas(sub[s]) + '</div>' +
                    '<div class="clear"></div>' + 
                    '</div>';
        }
    }

    $bt.html(html);
}

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
    var _fin = new Date(_year,_month,_day,11,59).getTime();

    // Para ushahidi va en segundos
    $('#' + id_start_date).val(_ini/1000);
    $('#' + id_end_date).val(_fin/1000);

    // Textos de fechas iniciales
    markIniFin(_iniD,_iniM,_iniY,_day,_month,_year);

    setYear('ini',_iniY);
    setYear('fin',_year);
    
    // ******** Fenomeno del niño
    $('#fen_nino').click(function(){ 
    
        filterByPeriod(1,2,2015,_day,_month,_year);
        
        // Desmarca todas las categorias de desastres
        $('#fcat_dn').find('.tn_fcat').click();
       
        // Incendios, inundaciones, sequias
        $.each([1,3,8], function(index, value) {
            
            var $o = $('#cat_dn_' + value);

            $o.iCheck('check');
            $o.attr('checked', td_ec);
        });

        setCatsHidden();
        
        addFeatures('dn');
        totalesxDepto();

        setTimeout(function(){ 
            // Titulo y fechas
            $('#tgt').html('Fenómeno del niño');
            $('#chart_total').find('.ec').hide();
            
            $t = $('#chart_total');

            $t.css('width', '100%');

            var $div_titulo_dn = $('#chart_total').find('.dn').find('.total_t');
            var titulo_f_n_af = 'total personas afectadas fenómeno del niño';
            
            $div_titulo_dn.html(titulo_f_n_af);
            
            $bt = $('#chart_subtotal');
            $bt.removeClass('left').removeClass('ec');
            
            setSubtotales();


            // Oculta pies
            //$('#chart_1').hide();
            
            // Oculta pies
            setTimeout(function(){ 
                $('#chart_1, #charts_pie').hide();
            }, 500);
                    
            ocultarViolenciaDesastres('ec');

            // Si selecciona Eventos cambia titulos
            $('div.mapa_tipo').click(function() {
                if ($(this).data('tipo') == 'eventos') {
                    $div_titulo_dn.html('total eventos fenómeno del niño');
                    $bt.hide();
                }
                else {
                    $div_titulo_dn.html(titulo_f_n_af);
                    $bt.show();
                    
                }

                setTimeout(function(){ 
                    $('#chart_1, #charts_pie').hide();
                }, 300);

            });
            
            // Actualiza subotales sigpad del depto seleccionado
            $('#depto_dropdown').on('click','li', function() {
                setTimeout(setSubtotales, 500);
                
                setTimeout(function(){ 
                    $('#chart_1, #charts_pie').hide();
                }, 300);
            });

        }, 1000);

    });
    // ******** Fenomeno del niño
    
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

    $(document).ajaxStart(function(){ 
        HoldOn.open({theme:"sk-dot"})
    }).ajaxStop(function(){ 
        HoldOn.close();
    });
    
    // Intro text
    $('#lmh').click(function() {
        m({
            t: 'Monitor - SalaHumanitaria',
            hid: 'qlmh',
            w: 850,
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
        var topx = 30*$div.data('index') + 'px';

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
        
        // [ INICIO Promocionar variacion ]
        // Resalta funcionalidad
        /*
        var options = {
            fadeDuration: 700,
            hideOnClick: true,
            hideOnESC: true,
            findOnResize: true
        };
        
        setTimeout(function(){$('li[data-div="variacion"]').click(); Focusable.setFocus($('div#variacion'), options) }, 1000);
        
        setTimeout(function(){ Focusable.hide(); $('div#variacion').hide(); }, 7000);
        */
        
        // [FIN Promocionar variacion]

        $('input').iCheck({
            checkboxClass: 'icheckbox_square-orange',
            radioClass: 'iradio_square-orange'
        });

    
        $('.btn_fcat').click(function() {

            setCatsHidden();

            var inst = $(this).data('inst');
            
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

            var cs = $(this).data('s');

            ocultarViolenciaDesastres(cs);
            
            
            $(this).closest('.filtro').hide();
            
        });

        //  Categorias : Todas/ninguna
        var td_ec = false;
        $('.tn_fcat').click(function() { 
            $(this).closest('.filtro').find('input:checkbox').each(function() {
                
                if (td_ec === false) {
                    $(this).iCheck('uncheck');
                }
                else {
                    $(this).iCheck('check');
                }
                
                $(this).attr('checked', td_ec);
            });
            
            td_ec = !td_ec;
            
            return false;
        });

        // Categorias : Click en categoria papa
        $('input.cp:checkbox').on('ifClicked', function(){ 

            var chkp = $(this).attr('checked');
            
            if (chkp) {
                $(this).iCheck('uncheck');
            }
            else {
                $(this).iCheck('check');
            }
            
            $(this).attr('checked', !$(this).attr('checked'));
           
            $(this).closest('ul.cats').find('input.ch:checkbox').each(function(){ 
                
                if (chkp) {
                    $(this).iCheck('uncheck');
                }
                else {
                    $(this).iCheck('check');
                }
            });
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

        // Total por años inicio
        $('.menu_totales').click(function(){ 
            if (totales_ini) {
                totalPeriodo('y', _year);
                totales_ini = false;
            }
        });
        
        // Total por años select
        $('#total_periodo_yyyy').change(function(){ 
            totalPeriodo('y', $(this).val());
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
        
        // Total por años
        $('li.menu_totales').click(function() { 
            if (totales_inicio) {
                totalPeriodo(_year);
                totales_inicio = false;
            } 
        });
        
        // Variacion
        $('#btn_variacion').click(function() {
            
            /*
            if ($('#variacion_p1_ini_text').val() == '' || $('#variacion_p1_fin_text').val() == ''
                || $('#variacion_p2_ini_text').val() == '' || $('#variacion_p2_fin_text').val() == ''   
                    ) {

                alert('Todas las fechas son obligatorias');

                return false;
            } 
            */

            variacion();
            
            $(this).closest('.filtro').hide();

        });


        // Nivel de agrupamiento cluster

        var slider = document.getElementById('slide_cluster_bar');
        var slider_max = 4;

        noUiSlider.create(slider, {
            start: [ slider_max/2 ], // Handle start position
            step: 1, // Slider moves in increments of '10'
            connect: 'lower',
            range: { // Slider can select '0' to '100'
                'min': 0,
                'max': slider_max
            },
        });

        slider.noUiSlider.on('change', function(values, handle){ 

            $('#group_level').val(Math.round(values[handle]) - (slider_max/2));
            
            // Refresh Map
            addFeatures();
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
            var div = $(this).data('div');

            $('div.filtro_fecha:not(#' + div + ')').hide();
            $('#' + div).slideToggle();
        });
    
        $('div.filtro_fecha').each(function() {
        
            var that = this;

            $(this).find('li').click(function() { 
                
                $(this).closest('div').find('li').removeClass('selected');
                $(this).addClass('selected');
                
                var q = $(this).data('q');
                var $div = $('#' + q + '_div');
                
                if ($div.find('li.selected').length == 3) {
                
                    var $div_ini = $(this).closest('fieldset').find('div.filtro_fecha[data-if="ini"]');
                    var $div_fin = $(this).closest('fieldset').find('div.filtro_fecha[data-if="fin"]');

                    var $input = $('#' + q + '_text');

                    var _ini = new
                    Date($div_ini.find('ul.yyyy > li.selected').data('val'),$div_ini.find('ul.mes > li.selected').data('val')-1,$div_ini.find('ul.dia > li.selected').data('val'),23,59).getTime();

                    var _fin = new
                    Date($div_fin.find('ul.yyyy > li.selected').data('val'),$div_fin.find('ul.mes > li.selected').data('val')-1,$div_fin.find('ul.dia > li.selected').data('val'),23,59).getTime();
                    
                    if (_ini > _fin) {
                        alert('Desde debe ser menor que Hasta');
                        $input.val('');
                        $div.find('li').removeClass('selected');
                        //$('stime').val(0);
                    }
                    else {
                        $input.val($div.find('ul.dia > li.selected').text() + ' de ' +
                        $div.find('ul.mes > li.selected').text() + ' ' + $div.find('ul.yyyy > li.selected').text());
                        
                        $div_ini.find('input:hidden').val(_ini / 1000);
                        $div_fin.find('input:hidden').val(_fin / 1000);
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
    
    // Check settings.monitor_cache_json para ver si borra
    // archivos
    checkCacheJson();
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
        ly = l_ec;

        $r_hide = $('#resumen_ec');
        $r_show = $('#resumen_dn');

    }
    else {
        
        ly = l_dn;
        
        $r_hide = $('#resumen_dn');
        $r_show = $('#resumen_ec');

        //$eventos_desastres = $('#report_list_map_desastres');
        
        
    }

    var $btn = $('div[data-s="' + cs + '"]');
    
    if (ly.getVisible()) {
        $btn.html('Mostrar eventos'); 
        ly.setVisible(false);
        v_chart_serie = false;

        $r_hide.hide();
        $r_show.removeClass('left half');
        //$eventos_desastres.hide();
    
        ocultar = cs;
    }
    else {
        $btn.html('Ocultar eventos'); 
        ly.setVisible(true);
        v_chart_serie = true;
        
        $r_hide.show();
        $r_show.addClass('left half');
        //$eventos_desastres.show();

        ocultar = '';
    }

    // Capa destacados
    l_ft.setVisible(!l_ft.getVisible());
    
    // Gráfica de linea de tiempos
    if ($('#chart_1').highcharts() !== undefined) {
        $('#chart_1').highcharts().get(cs).setVisible(v_chart_serie,true); 
    }

    if (cs == 'ec') {
        $('#charts_pie').toggle();
    } 

    // En tabla por departamentos
    $('#table_totalxd').find('.' + cs).hide();
    

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
        $('#' + id_start_date).val(_ini/1000); // Segundos para ushahidi
        $('#' + id_end_date).val(_fin/1000); // Segundos para ushahidi
        
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
        $(this).find('.d').click(function() {
            $(this).closest('div.fc').find('.hide').slideToggle();
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
        titulo = (getMapaAfectacion() == 1) ? 'afectados' : 'eventos';
            
        // Acceso
        if (acceso) {
            titulo = 'restricción <br /> al acceso';
        }
        // Titulo derecha
        var t_ini = $('#ini_text').val();
        var t_fin = $('#fin_text').val();

        $('#titulo_general').find('#tgt').html('Mapa de ' + titulo);
        $('#titulo_general').find('#tgc').html(t_ini + ' - ' + t_fin);
        
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

                // Almacena subotales en variable
                subtotales = data.subtotales;
                
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
    total_ec = numberWithCommas(data.t.ec);
    
    $('#chart_total_v').html(total_ec);
    
    if (data.t.ec > 0) {
        $('#resumen_total_ec_num').html(total_ec);
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
    total_dn = numberWithCommas(data.t.dn);
    
    $('#chart_total_d').html(total_dn);
    
    if (data.t.dn > 0) {
        $('#resumen_total_dn_num').html(total_dn);
        $resumen_dn.show();
    }
    else {
        $resumen_dn.hide();
    }

    if (data.t.ec > 0 && data.t.dn > 0) {
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

    // Subtotales violencia
    if (data.subtotales.ec !== undefined) {
        
        var sub = data.subtotales.ec;
        
        $('#civiles').html((sub.civiles === undefined ) ? '---' : numberWithCommas(sub.civiles));
        $('#hombres').html((sub.hombres === undefined ) ? '---' : numberWithCommas(sub.hombres));
        $('#mujeres').html((sub.mujeres === undefined ) ? '---' : numberWithCommas(sub.mujeres));
        $('#menores').html((sub.menores === undefined ) ? '---' : numberWithCommas(sub.menores));
        $('#afros').html((sub.afros === undefined ) ? '---' : numberWithCommas(sub.afros));
        $('#indigenas').html((sub.indigenas === undefined ) ? '---' : numberWithCommas(sub.indigenas));
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
    if (data_charts[0].data.length == 0 )
    {
        $('#chart_1').html('<h2>No hay información</h2>');

        return;
    }

    var s = data_charts[0];
    $('#chart_1').highcharts({
        chart: {
            type: 'line',
            width: 350,
            height: 200,
            style: {
            }
        },
        legend: {
            enabled: false
        },
        plotOptions: {
            series: {
                marker: {
                    radius: 3,
                }
            }
        },
        title: {
            text: titulo.toUpperCase(),
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
    
    $('#fcat_ec').find('input.ch:checked').each(function() {
        _ids.push($(this).val());
    });

    $('#currentCatE').val(_ids);
    
    _ids = [];
    $('#fcat_dn').find('input.ch:checked').each(function() {
        _ids.push($(this).val());
    });

    $('#currentCatD').val(_ids);
    
}

daysToMiliseconds = function(d) {
    return d * 24 * 3600 * 1000;
}

getStartEnd = function(c) {
    var v = [];

    v['ini'] = $('#' + id_start_date).val();
    v['fin'] = $('#' + id_end_date).val();

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

    $('#ini_div,#fin_div').find('li').removeClass('selected');

    var $div_ini = $('#ini_div');
    var $div_fin = $('#fin_div');

    $div_ini.find('ul.dia > li[data-val='+id+']').addClass(c);
    $div_ini.find('ul.mes > li[data-val='+im+']').addClass(c);
    $div_ini.find('ul.yyyy > li[data-val='+iy+']').addClass(c);
    
    $div_fin.find('ul.dia > li[data-val='+fd+']').addClass(c);
    $div_fin.find('ul.mes > li[data-val='+fm+']').addClass(c);
    $div_fin.find('ul.yyyy > li[data-val='+fy+']').addClass(c);
    
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
    
    var h = $(document).height() - $('#brand').height() - 20;

    $('.map_monitor').css('height', h - 5 - $('#header').height());
    $('#menu').css('height', h);
}
setMapWidth = function(){ 
    //$('#map').css('width', $(document).width() - $('#menu').css('width'));
    $('.map_monitor').css('width', $(document).width());
}

/* Retorna el contenido html de total en el periodo
 *
 * var vd violencia o desastres
 * var periodo y=Año, s=Semestre, t=Trimestre
 * var valor 
*/
totalPeriodo = function(periodo, valor) {
    $.ajax({
        url: 'totalPeriodo/violencia/' + periodo + '/' + valor,
        success: function(html){ 

            $o = $('#totales_data_violencia');
            
            $o.find('tr:not(:first)').remove(); 
            $o.append(html); 
        }
    });
    
    $.ajax({
        url: 'totalPeriodo/desastres/' + periodo + '/' + valor,
        success: function(html){ 
            
            $o = $('#totales_data_desastres');
            
            $o.find('tr:not(:first)').remove(); 
            $o.append(html); 
        }
    });

    // Link descarga
    var path = 'z/monitor-totales';

    $('#total_descarga_v').attr('href', path + '-' + valor + '-violencia.xls');
    
    $('#total_descarga_d').attr('href', path + '-' + valor + '-desastres.xls');
}

/* Realiza check de settings.monitor_cache_json
*/
checkCacheJson = function() {
    $.ajax({
        url: 'checkCacheJson'
    });
}

variacion = function() {

    var p1_ini = $('#variacion_p1_ini_date').val();
    var p1_fin = $('#variacion_p1_fin_date').val();
    
    var p2_ini = $('#variacion_p2_ini_date').val();
    var p2_fin = $('#variacion_p2_fin_date').val();
    
    var ecdn = $('input[name="variacion_v_d"]:checked').val();

    if (ecdn == 'v') {
        var _cats = $('#currentCatE').val();
    }
    else {
        var _cats = $("#currentCatD").val();
    }

    var _states = getStatesChecked();

    $('#loading').show();
    $.ajax({
            url: 'variacion/' + p1_ini + '|' + p1_fin + '/' + p2_ini + '|' + p2_fin + '/' + ecdn + '/' + _cats + '/' + _states,
            success: function(data) {
                
                $('#loading').hide();

                addLayerVariacion(data.values);
                
                var $vd = $('#variacion_data');
                var ecdn = ($('input[name="variacion_v_d"]:checked').val() == 'v') ? 'violencia armada' : 'vesastres';
                
                var html = '<h2 class="ac">Variación ' + ecdn + '</h2> ' +
                           '<div class="variacion_periodo"><b>Periodo 1</b><br /> ' + 
                            $('#variacion_p1_ini_text').val() + 
                            ' al <br /> ' + $('#variacion_p1_fin_text').val() +
                            '</div>' +
                            '<div class="variacion_periodo"><b>Periodo 2</b><br /> ' + 
                             $('#variacion_p2_ini_text').val() + 
                             ' al <br /> ' + $('#variacion_p2_fin_text').val() +
                             '</div>' +
                             '<div class="clear"></div>' + 
                             '<h2 class="ac"><br />Datos municipales</h2><div class="ac">La variación se calcula sobre el # de eventos<br />&nbsp;</div>'
                            ;

                html += data.html;

                $vd.html(html);

                // Titulo variacion
                
                // Datatables
                $vd.find('table').DataTable({
                 "order": [[ 2, 'desc']]   ,
                 "pageLength": 30,
                 "scrollY": "400px",
                 "lengthChange" : false,
                 "language": {
                     "searchPlaceholder": "Buscar municipio",
                     "search": ""
                 },
                 "columnDefs": [
                    {
                        "targets": [ 0 ],
                        "visible": false,
                    },
                ]
                });
                
                var $filter = $('div.dataTables_filter');

                $filter.append('<i class="fa fa-download"></i> <a href="z/monitor-variacion.xls">Descargar a excel</a>');
                 
            }
        });
}


function addLayerVariacion(dataJson) {

    /****** Temporal desarrollo
     console.log($('#variacion_p1_ini_date').val());
     console.log($('#variacion_p1_fin_date').val());
     console.log($('#variacion_p2_ini_date').val());
     console.log($('#variacion_p2_fin_date').val());

     $('#variacion_p1_ini_date').val(1430542740);
     $('#variacion_p1_fin_date').val(1433134740);
     
     $('#variacion_p2_ini_date').val(1433221140);
     $('#variacion_p2_fin_date').val(1435813140);

     variacion();
     *////

    var serie7 = new geostats(dataJson);
    serie7.setPrecision(6);
    //var a = serie7.getClassQuantile(5);
    //var a = serie7.getClassEqInterval(5);
    var a = serie7.getClassJenks(5);

    var ranges = serie7.ranges;

    var color_x  = new Array('#36B446', '#F0E62C', '#E59322', '#EF3326', '#700909');

    serie7.setColors(color_x);
                
    var class_x = ranges;
    
    serie7.setPrecision(2);
    serie7.legendSeparator = ' ⇔ ';
    
    var content = serie7.getHtmlLegend(null, 'Variación %');
    $('#variacion_legend').html(content + '<p>Mapa: puede consultar la información de un municipio haciendo click sobre él</p>');
    
    if (!layer_variacion_exists) {

        l_variacion = new ol.layer.Vector({
            source: new ol.source.Vector({
                url: 'static/variacion-topo.json',
                format: new ol.format.TopoJSON()
            }),
            style: function(feature, resolution) {

                if (feature.get('variacion') !== undefined) {
                    styleObj = {
                        fill: new ol.style.Fill({color: color_x[serie7.getClass(feature.get('variacion'))]}),
                        stroke: new ol.style.Stroke({color: 'gray', width: 1})
                    }
                }
                else {
                    styleObj = {
                        stroke: new ol.style.Stroke({color: 'gray', width: 1})
                    }
                }
                return [new ol.style.Style(styleObj)]
              }
        });
        
        map.addLayer(l_variacion);
        
        layer_variacion_exists = true;
    }
    else {
        l_variacion.getSource().changed();
    }

    showHideLayers('variacion');
    

}

function filterByPeriod(id,im,iy,fd,fm,fy) {

    _ini = new Date(iy,im,id);
    _fin = new Date(fy,fm,fd);

    $('#' + id_start_date).val(_ini/1000); // Segundos para ushahidi
    $('#' + id_end_date).val(_fin/1000); // Segundos para ushahidi
    
    markIniFin(id,im,iy,fd,fm,fy);
}

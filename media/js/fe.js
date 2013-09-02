var _mes =
['','Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];

var base = '';
var base_ol = '';
var is_portal = false;
var num_carga = 20;
var cargar_mas = 0;  // Cuenta las veces que se hace click en cargar mas
var _afectacion = 1;

var resetLimit = false;

$(function(){

    if (window.location.hostname == 'localhost' || window.location.hostname == '190.66.6.168') {
        base = '/monitor';
        base_ol = '/monitor';
    }
    else {
        base = 'http://monitor.colombiassh.org';
        base_ol = '';
    }

    if (typeof portal !== "undefined") {
        is_portal = true;
    }
    
    // Ajax loader
    $(document)
    .ajaxStart(function(){ $('#loading').show(); })
    .ajaxStop(function(){ $('#loading').hide(); });
    
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

    // Filtro cats
    $('.cat').click(function() {
        $('div.filtro').hide();
        $(this).siblings('div.filtro').slideDown();
    });
    
    $('.btn_fcat').click(function() {
        setCatsHidden();
        addFeatures($(this).attr("class").split(' ')[0]);
        totalesxDepto();
        $(this).closest('.filtro').slideUp();
    });

    // Oculta eventos
    $('.btn_show_e').click(function() {

        var cs = $(this).attr('class').split(' ');
        
        if (cs[0] == 'ec') {
            var ly = map.getLayersByName('Emergencia Compleja')[0];
        }
        else {
            var ly = map.getLayersByName('Desastres Naturales')[0];
        }
        
        if (ly.getVisibility()) {
            $(this).html('Mostrar eventos'); 
            ly.setVisibility(false);
        }
        else {
            $(this).html('Ocultar eventos'); 
            ly.setVisibility(true);
        }
        
        $(this).closest('.filtro').slideUp();
        
    });

    // Click outside menu
    $(document).click(function(e) {
        if (!$(e.target).closest('.cat, .filtro').length) {
            $('.filtro').slideUp();
        }
    });

    //  Categorias : Todas/ninguna
    var td_ec = false;
    $('.tn_fcat').click(function() { 
        $(this).closest('.filtro').find('input:checkbox').each(function() {
            $(this).attr('checked', td_ec);
        });
        
        td_ec = !td_ec;
        
        return false;
    });
    
    // Colocar categorias en inputs hidden para envio a json/cluster
    setCatsHidden();
    
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
        //$('#loading').show();
        $.ajax({
            url: 'download_incidents',
            success: function() {
                //$('#loading').hide();
                location.href = base + '/export/xls/incidentes/Monitor-Incidentes';            
            }
        });
    });

    $('.close').click(function() { 
        $(this).closest('.filtro').slideUp();
    });

    // Tipo de mapa
    $('.mapa_tipo:not(.active)').click(function() {
        var that = this;

        $.ajax({
            url: 'mapa_tipo/' + $(that).data('tipo'),
            success: function() {
                $('.mapa_tipo').removeClass('activo');
                $(that).addClass('activo');
                addFeaturesFirstTime();
                totalesxDepto();
            }
        });
    });

    // Date events
    var _ms = 2;  // Meses hacia atras
    var _ds = 7; // Dias iniciales hacia atras

    var _today = new Date();
    var _year = _today.getFullYear();
    var _month = _today.getMonth();
    var _day = _today.getDate();
    var _ii = new Date(_year,_month,_day,0,0).getTime();
    var _iniObj = new Date(_ii - daysToMiliseconds(_ds));
    var _iniD = _iniObj.getDate();
    var _iniM = _iniObj.getMonth();
    var _iniY = _iniObj.getFullYear();
    var _ini = _iniObj.getTime(); // milisecs
    var _fin = new Date().getTime();

    // Para ushahidi va en segundos
    $('#startDate').val(_ini/1000);
    $('#endDate').val(_fin/1000);

    markIniFin(_iniD,_iniM,_iniY,_day,_month,_year);

    setYear('ini',_iniY);
    setYear('fin',_year);

    $('#time').find('select').change(function() {
        
        if ($(this).val() != 0) {
            var _ini = getStartEnd('ini');
            var _fin = getStartEnd('fin');
            
            var _iiObj = new Date(_year,_month,_day,0,0);
            var _ii = _iiObj.getTime();

            switch($(this).val()) {
                // Año
                case 'a':
                    _ini = new Date(_year,0,1);
                    _fin = new Date(_year,11,31);
                break;
                // Mes
                case 'm':
                    _ini = new Date(_ii - daysToMiliseconds(30)); // milisecs
                    _fin = _today;
                break;
                // Semana
                case 's':
                    _ini = new Date(_ii - daysToMiliseconds(7)); // milisecs
                    _fin = _today;
                break;
                // Ayer
                case 'ay':
                    _ini = new Date(_ii - daysToMiliseconds(1)); // milisecs
                    _fin = _today;
                break;
                // Hoy
                case 'h':
                    _ini = _ii; // milisecs
                    _fin = _today;
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

            totalesxDepto();
            addFeaturesFirstTime();

            resetLimit = true;
            cargar_mas = 0;
        }
    });

    // Years menu
    $('#aaaa div.v').click(function() { 
        var _y = parseInt($(this).find('div.a').text());
        $('#startDate').val(new Date(_y,0,1,0,0,0,0).getTime()/1000);
        $('#endDate').val(new Date(_y,11,31).getTime()/1000);

        addFeaturesFirstTime();

        //$('#totalxd_y').html(_y);
        setYear('ini',_y);
        setYear('fin',_y);

        $('#fin_text').val('31 de Diciembre');
        markIniFin(1,0,_y,31,11,_y);

        $('#time').hide();

        // Totales por departamento
        totalesxDepto();
        
        /*
        var bmin = new Date(_y,0,1);
        var bmax = (_y == new Date().getFullYear()) ? new Date() : new Date(_y,11,31);
        
        $('#dslider').dateRangeSlider('bounds', bmin, bmax);
        $('#dslider').dateRangeSlider('values', bmin, bmax);
        */

    });

    // Table sorter
    var sorting = [[1,1]];
    //$("#table_totalxd").tablesorter({sortList: [sorting] });
    
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
                    $('stime').val(0);
                }
                else {
                
                    $input.val($('li.selected[q='+q+'][y=dia]').text() + ' de ' +
                    $('li.selected[q='+q+'][y=mes]').text()+' '+ $('li.selected[q='+q+'][y=yyyy]').text());
                    
                    $('#startDate').val(_ini / 1000);
                    
                    $('#endDate').val(_fin / 1000);

                    $('#stime').val(0);
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

    totalesxDepto();

    map();
    
    // Click en el departamento en la lista derecha
    //
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
    $table.find('tr:not(:first) td.n, tr:not(:last) td.n').live('click', function() {
        selDepto($(this).closest('tr').find('td.centroid').html());   // in map.js
    });
});

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
    if (is_portal) {
        
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
            //beforeSend: function(){ $('#loading').show() },
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
                                    '<a href="http://sidih.colombiassh.org/sissh/download_pdf.php?c=2&id_depto='+_js.ld+'&id_mun=" target="_blank">' +
                                    'Perfil '+ _js.ldn +'</a></span></div> ' +
                                '</<div></div>';
                                
                                _html += '<div class="clear hide"><div class="left"><b>Categorias</b></div> ' +
                                         '<div class="opt right linko">' +
                                         '<a href="http://www.colombiassh.org/gtmi/wiki/index.php/Sistema_de_categor%C3%ADas_del_m%C3%B3dulo_de_eventos_de_conflicto" target="_blank">Definici&oacute;n</a></div>';
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
                
                //$('#loading').hide();
                
            }
        });
    
    }
    else {
        
        $('#table_totalxd tbody').html('<tr><td colspan="4"><img src="media/img/ajax-loader-mini.gif" />&nbsp;Actualizando datos...</td></tr>');

        $.ajax({
            url: base + '/totalxd/' + _ini + '/' + _fin + '/' + _cats + '/' + _states,
            dataType: 'json',
            success: function(data) {
                
                $('#table_totalxd tbody tr').remove();
                $('#table_totalxd').trigger('update');
                
                // Totales
                var _t = data.t;
                var checked;

                var $table = $('#table_totalxd tbody');

                $table.append('<tr class="totalxd"><td></td><td class="left">Total</td><td class="ec">' + _t.ec + '</td><td class="dn">' + _t.dn + '</td></tr>');

                for (var d in data.r) {
                    _i = data.r[d];
                    checked = (_i.css == '') ? 'checked' : '';
                    $table.append('<tr class="f ' + _i.css + ' ' + _i.hide + '"><td><input type="checkbox" name="deptos[]" value="'+_i.state_id+'" '+checked+' /></td><td class="n left">'+_i.d+'</td><td class="ec">'+_i.ec+'</td><td class="dn">'+_i.dn+'</td><td class="hide centroid">'+_i.c+'</td></tr>');
                }
                
                
                // Aviso de no eventos
                if ($table.find('.f:not(.hide)').length == 0) {
                    $table.append('<tr><td colspan="4"><br />No existen eventos</td></tr>');
                }

                // Ordena tabla
                //forceSortTable();
            }
        });

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
    
    $('#table_totalxd').find(':checked').each(function() {
        
        if ($(this).val() != 0) {
            _sts.push($(this).val());
        }
    });

    return _sts.join(',');
}

getMapaAfectacion = function(){ 
    
    return ($('.mapa_tipo.activo').data('tipo') == 'eventos') ? 0 : 1;

}

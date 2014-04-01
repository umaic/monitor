<?php
/**
 * Main controller
 *
 * @package     Monitor
 * @link 		http://monitor.colombiashh.org/api
 */
class MonitorController {
    
    private $db_dn;
    private $db;
    private $dbs;
    private $meses;

    function __construct() {
        $this->root = dirname( __FILE__ ).'/../'; 
        
        require $this->root.'libraries/factory.php';
        $this->db = Factory::create('mysql');
        $this->db_dn = 'desastres';    
        $this->dbs = array('', $this->db_dn.'.');
        $this->meses = array('','Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic');

    }

    /**
     * Display Home
     *
     */ 
    public function index() {

        require 'libraries/factory.php';
        
        $f = Factory::create('file');

        if ($ecj = @file_get_contents('http://colombiassh.org/emergenciacompleja/ec.json')) {
            
            $fp = $f->open('data/ec.json', 'w+');
            $f->write($fp, $ecj);
            $f->close($fp);

            return true;
        }

       return false; 

    }
    
    /**
     * Get total of incidents per year to top menu
     *
     * @param array $cats_hide Categories to avoid
     */ 
    public function total($cats_hide) {
        
        // Se usaba este para mostrar el total por años
        $_sql = 'SELECT COUNT(i.id) AS n FROM %sincident i 
                 JOIN %sincident_category AS ic ON i.id = ic.incident_id
                 JOIN %scategory AS c ON ic.category_id = c.id
                 WHERE YEAR(incident_date) = %d 
                       AND incident_active = 1
                       AND c.category_visible = 1
                       AND category_id NOT IN(%s)';
        $yi = 2008;
        $yf = date('Y');
        $t = array();
        for($_y=$yf;$_y>=$yi;$_y--){
            
            $_rse = $this->db->open(sprintf($_sql, '','','', $_y,implode($cats_hide['ec'])));
            $_ec = $this->db->FO($_rse);

            $_db = $this->db_dn.'.';
            $_rsd = $this->db->open(sprintf($_sql,$_db,$_db,$_db, $_y,implode($cats_hide['dn'])));
            $_dn = $this->db->FO($_rsd);

            $t[$_y] = array('ec' => $_ec->n, 'dn' => ($_dn->n == 0) ? 'N/A' : $_dn->n);
        }

        return $t;

    }

    /**
     * Get parametros para incidentes en mapa y portal
     *
     * @param int $ini Fecha inicio milisegundos
     * @param int $fin Fecha inicio milisegundos
     * @param string $cats Categorias separadas por ',' filtradas para ec y dn formato ec1,ec2|dn1,dn2
     * @param string $states States separados por ','
     */ 
    public function getConditions($ini, $fin, $cats, $states) {
    
        date_default_timezone_set('UTC');  // Igual a emergencia compleja
        
        $ini = date('Y-m-d H:i:s', intval($ini));  // Se usa intval para que quede igual que ushahidi/application/helper/reports/ :740
        $fin = date('Y-m-d H:i:s', intval($fin));

        $_t = explode('|', $cats);
        $cond_cats_ec = '';
        if (!empty($_t[0])) {
            $cond_cats_ec = ' category_id IN ('.$_t[0].')';
        }
        
        $cond_cats_dn = '';
        if (!empty($_t[1])) {
            $cond_cats_dn = ' category_id IN ('.$_t[1].')';
        }
        $cond_tmp = "
                 incident_date >= '$ini' AND incident_date <= '$fin' 
                 AND incident_active = 1 AND %s";
        
        // Para totales
        $cond = "state_id = '%s' AND $cond_tmp";

        $cond_csv = $cond_tmp;
        
        if (!empty($states)) {
            $cond_csv .= ' AND state_id IN ('.$states.')';
        }

        return array($ini,$fin,$cond_cats_ec,$cond_cats_dn,$cond_tmp,$cond_csv);
    }

    /**
     * Get total of incidents per depto
     *
     * @param int $ini Fecha inicio yyyy-mm-dd H:i:s
     * @param int $fin Fecha inicio yyyy-mm-dd H:i:s
     * @param string $cats Categorias separadas por ',' filtradas para ec y dn formato ec1,ec2|dn1,dn2
     * @param string $states States separados por ','
     */ 
    public function totalxd($ini, $fin, $cats, $states='') {
        
        $r = array();
        $t = array('ec' => 0, 'dn' => 0);
        $afectacion = ($_SESSION['mapa_tipo'] == 'afectacion') ? true : false;
        $acceso = ($_SESSION['acceso'] == 1) ? true : false;

        list($ini,$fin,$cond_cats_ec,$cond_cats_dn,$cond_tmp,$cond_csv) = $this->getConditions($ini, $fin, $cats, $states);
        
        //echo $cond_tmp;

        $_SESSION['cond_csv'] = $cond_csv;
        $_SESSION['cond_cats_ec'] = $cond_cats_ec;
        $_SESSION['cond_cats_dn'] = $cond_cats_dn;

        list($rsms_ec, $rsms_dn, $charts) = $this->getAfeEveChart($ini,$fin,$cond_cats_ec,$cond_cats_dn,$cond_tmp,$cond_csv);

        $_db = $this->db_dn.'.';

        if ($afectacion) {
            $_sqle = "SELECT SUM(victim_cant) AS n
                FROM victim v
                JOIN incident_category ic ON v.incident_category_id = ic.id
                JOIN incident AS i ON ic.incident_id = i.id
                JOIN location AS l ON l.id = i.location_id";

            if ($acceso) {
                $_sqle .= ' JOIN form_response '; 
            }

            $_sqle .= " WHERE $cond_tmp";
            
            $_sqld = "SELECT SUM(REPLACE(REPLACE(form_response,'.',''),',','')) AS n
                FROM %sform_response f
                JOIN %sincident AS i ON f.incident_id = i.id
                JOIN %slocation AS l ON l.id = i.location_id
                JOIN %sincident_category ic USING(incident_id)
                WHERE $cond_tmp AND form_field_id = 4";
            
            $_sqliec = sprintf($_sqle,$cond_cats_ec);
            $_sqlidn = sprintf($_sqld,$_db,$_db,$_db,$_db,$cond_cats_dn);
        }
        else {
            $_sql = "SELECT COUNT(DISTINCT(l.id)) AS n FROM %slocation AS l
                 JOIN %sincident AS i ON l.id = i.location_id
                 JOIN %sincident_category AS ic ON i.id = ic.incident_id
                 WHERE $cond_tmp";
        
            $_sqliec = sprintf($_sql,'','','',$cond_cats_ec);
            $_sqlidn = sprintf($_sql,$_db,$_db,$_db,$cond_cats_dn);
        }

        //echo $_sqliec;
        //echo $_sqlidn;
        
        $_ss = " AND state_id = '%s' LIMIT 1";
        $_sqliec .= $_ss;
        $_sqlidn .= $_ss;

        $cond_states = false;
        if (!empty($states)) {
            $cond_states = true;
            $cond_csv = $cond_tmp." AND l.state_id IN ($states)";
            $states = explode(',', $states);
        }

        $sql_states = "SELECT id,state,centroid FROM state ORDER BY state";
        $_rs = $this->db->open($sql_states);
        while($_row = $this->db->FO($_rs)) {
            
            $_sqlec = sprintf($_sqliec,$_row->id);
            //echo $_sqlec;
            $_rse = $this->db->open($_sqlec);
            $_ec = $this->db->FO($_rse);
            $_nec = (empty($_ec->n)) ? '' : $_ec->n;

            if (!$acceso) {
                $_sqldn = sprintf($_sqlidn,$_row->id);
                $_rsd = $this->db->open($_sqldn);
                //echo $_sqldn;
                $_dn = $this->db->FO($_rsd);
            }
            
            $_ndn = (empty($_dn->n)) ? '' : $_dn->n;

            $class = 'unselected';
            if ($cond_states) {
                if (in_array($_row->id,$states)) {
                    $t['ec'] += $_ec->n;
                    $t['dn'] += $_dn->n;
                    $class = '';
                }
            }
            else {
                $t['ec'] += $_ec->n;
                $t['dn'] += $_dn->n;
                $class = '';

            }

            $hide = (empty($_nec) && empty($_ndn)) ? 'hide' : '';
             
            $r[] = array('d' => $_row->state,
                         'ec' => $_nec,
                         'dn' => $_ndn,
                         'c' => $_row->centroid,
                         'state_id' => $_row->id,
                         'css' => $class,
                         'hide' => $hide
                        );
        }
        
        return compact('r', 't','rsms_ec', 'rsms_dn','charts');
    }
    
    /**
     * Total Ec y Dn
     *
     */ 
    public function totalecdn() {
        
        $_sql = "SELECT COUNT(id) AS n FROM incident LIMIT 1";
        $_rs = $this->db->Open($_sql);
        $_row = $this->db->FO($_rs);
        $r['ec'] = $_row->n;
        
        $_sqld = "SELECT COUNT(id) AS n FROM $this->db_dn.incident LIMIT 1";
        $_rsd = $this->db->Open($_sqld);
        $_rowd = $this->db->FO($_rsd);
        $r['dn'] = $_rowd->n;
        
        return $r;
    }
    
    /**
     * Categorias
     *
     */ 
    public function getCats() {
        
        $tree = $h = array();
        $inst = array('ec', 'dn');
        foreach($this->dbs as $d => $db) {
            
            // Oculta categoria monitoreo ieh en desastre
            $cond = ($db != '') ? ' AND id NOT IN (104)' : '';

            $_sql = "SELECT id, category_title AS t FROM ".$db."category WHERE parent_id = 0 AND category_visible = 1 $cond ORDER BY category_title";
            $_rs = $this->db->Open($_sql);
            while ($_row = $this->db->FO($_rs)) {
                $tree[$inst[$d]][$_row->t] = array();
                $_sqlh = "SELECT id, category_title AS t FROM ".$db."category WHERE parent_id = ".$_row->id." AND category_visible = 1 ORDER BY category_title ";
                $_rsh = $this->db->Open($_sqlh);
                
                $nh = 0;
                while ($_rowh = $this->db->FO($_rsh)) {
                    $tree[$inst[$d]][$_row->t][$_rowh->id] = $_rowh->t;
                    $h[] = $_rowh->id;
                    $nh++;
                }

                // No tiene hijos, se agrega una categoria hija con el mismo id del papa
                if ($nh == 0) {
                    $tree[$inst[$d]][$_row->t][$_row->id] = $_row->t;
                    $h[] = $_row->id;
                }
            }
        }
        
        return compact('tree', 'h');
    }
    
    /**
     * 
     * Genera el csv de incidentes
     *
     */ 
    public function downloadIncidents() {
        $_db = $this->db_dn.'.';
        $cond_csv = $_SESSION['cond_csv']; // Se crea en $this->totalxd, fila 152 
        $cond_cats_ec = $_SESSION['cond_cats_ec'];
        $cond_cats_dn = $_SESSION['cond_cats_dn'];

        $limi = '~';
        $nl = "\r\n";

        $csv = '"Tipo"'.$limi.'"Fecha Evento"'.$limi.'"Título evento"'.$limi.'"Resumen evento"'.$limi.
                '"Categorias (Subcategorias)"'.$limi.
                '"Acceso"'.$limi.'"Resoluciones"'.$limi.
                '"Fuente"'.$limi.'"Descripcion de la fuente"'.$limi.'"Referecia"'.$limi.
                '"Departamento"'.$limi.'"Municipio"'.$limi.'"Lugar"'.$limi.
                '"# Total Víctimas (Violencia armada) / Personas Afectadas (Desastres)"'.$limi.'"# Víctimas civiles"'.$limi.'"Víctimas militares"'.$limi.
                '"# Víctimas menores 18 años"'.$limi.'"Víctimas mujeres"'.$limi.
                '"# Víctimas afro"'.$limi.'"Víctimas indígenas"'.$limi.'"Víctimas otros"'.$limi.
                $nl;

        $_sql_csv = "SELECT i.id AS id, i.incident_date AS date, i.incident_title AS title,
                    i.incident_description AS des, GROUP_CONCAT(c.category_title) AS cats,
                    l.location_name AS ln, city_id, state_id, l.location_name AS loc
                    FROM %slocation AS l
                 INNER JOIN %sincident AS i ON l.id = i.location_id
                 INNER JOIN %sincident_category AS ic ON i.id = ic.incident_id
                 INNER JOIN %scategory AS c ON ic.category_id = c.id
                 WHERE $cond_csv 
                 GROUP BY i.id
                 ";
        
        //$_sql_csv_ec = sprintf($_sql_csv,'','','', $cond_cats_ec);
        //$_sql_csv_dn = sprintf($_sql_csv,$_db,$_db,$_db, $cond_cats_dn);

        $ushas = array(array('t' => 'Violencia armada', 'db' => '', 'cc' => $cond_cats_ec),
                      array( 't' => 'Desastres', 'db' => $_db, 'cc' => $cond_cats_dn));

        foreach($ushas as $u => $usha) {

            $_dbu = $usha['db'];
            $_sql_csv_ecdn = sprintf($_sql_csv,$_dbu,$_dbu,$_dbu,$_dbu,$usha['cc']);

            $_rs = $this->db->open($_sql_csv_ecdn);
            while($_r = $this->db->FO($_rs)) {

                $des = $source = $desc = $ref = $cats = '';
                $iid = $_r->id;

                // Inicializa num victimas
                for ($v=0; $v<7; $v++) {
                    $num_v[$v] = '';
                }

                $acceso = '';
                $res_1612 = '';

                if ($u == 0) {
                    
                    $title = $_r->title;
                    $des = $_r->des;

                    // Fuentes
                    // Todo: Ojo, revisar el tema que no se pueden mostrar bitacoras
                    $_sql_s = "SELECT source_desc AS descr, source_reference AS ref, source 
                    FROM source_detail sd
                    INNER JOIN source s ON sd.source_id = s.id
                    WHERE incident_id = $iid LIMIT 1";

                    $_rss = $this->db->open($_sql_s);
                    $_row_s = $this->db->FO($_rss);
                    
                    $desc = (empty($_row_s->descr)) ? '' : str_replace('"','',$_row_s->descr);
                    $ref = (empty($_row_s->ref)) ? '' : $_row_s->ref;
                    $source = (empty($_row_s->source)) ? '' : $_row_s->source;

                    // # victimas por condicion,age,gender,ethnic
                    $vcn = array('1' => array(1),
                                 'victim_condition_id' => array(2,4),
                                 'victim_age_id' => array(3),
                                 'victim_gender_id' => array(1),
                                 'victim_ethnic_group_id' => array(2,1,6),
                             );

                    $v = 0;
                    foreach($vcn as $col => $vc) {
                        foreach($vc as $cid) {
                        
                            $_sql_v = "SELECT SUM(victim_cant) AS num FROM victim WHERE $col = $cid AND incident_id = $iid GROUP BY incident_id";
                            $_rsv = $this->db->open($_sql_v);
                            $_row_v = $this->db->FO($_rsv);

                            $num_v[$v] = (!empty($_row_v->num)) ? $_row_v->num : '';

                            $v++;
                        }
                    }

                    // Acceso y 1612
                    $form_field_id_acc = 1; // Preguntas de acceso

                    $_sql_acc_1612 = "SELECT form_response AS r
                        FROM ".$_dbu."form_response AS fr
                        INNER JOIN incident AS i ON fr.incident_id = i.id
                    WHERE form_field_id = %s AND incident_id = $iid AND restricting_access = 1";

                    $_sql_acc = sprintf($_sql_acc_1612,$form_field_id_acc);

                    if ($iid == 71757) {
                        //echo $_sql_acc.'<br />';
                    } 

                    $_rsv = $this->db->open($_sql_acc);
                    $acceso = array();
                    while ($_row_a = $this->db->FO($_rsv)) {
                        $acceso[] = $_row_a->r;
                    }

                    $acceso = implode(',', $acceso);
                    
                    $form_field_id_1612 = 2; // Preguntas de 1612

                    $_sql_1612 = sprintf($_sql_acc_1612,$form_field_id_1612);

                    $_rsv = $this->db->open($_sql_1612);
                    $res_1612 = array();
                    while ($_row_a = $this->db->FO($_rsv)) {
                        //$res_1612[] = $_row_a->r;
                        $res_1612[] = 'NAN';
                    }

                    $res_1612 = implode(',', $res_1612);

                }
                else {
                    $title = $_r->title;

                    $_sql_s = "SELECT media_link AS descr 
                    FROM ".$_dbu."media m
                    WHERE media_type = 4 AND incident_id = $iid";

                    $_rss = $this->db->open($_sql_s);
                    $_row_s = $this->db->FO($_rss);
                    
                    $source = (empty($_row_s->descr)) ? '' : $_row_s->descr;
                    
                    // Victimas, personas
                    $_sql_v = "SELECT SUM(REPLACE(REPLACE(form_response,'.',''),',','')) AS num 
                    FROM ".$_dbu."form_response
                    WHERE form_field_id = 4 AND incident_id = $iid";

                    $_rsv = $this->db->open($_sql_v);
                    $_row_v = $this->db->FO($_rsv);
                    
                    $num_v[0] = $_row_v->num;

                }
                
                $_sql_c = "SELECT city from city WHERE id = ".$_r->city_id." LIMIT 1";
                $_rss_c = $this->db->open($_sql_c);
                $_row_c = $this->db->FO($_rss_c);
                
                $city = (empty($_row_c->city)) ? '' : $_row_c->city;
                
                $_sql_s = "SELECT state from state WHERE id = ".$_r->state_id." LIMIT 1";
                $_rss_s = $this->db->open($_sql_s);
                $_row_s = $this->db->FO($_rss_s);

                $state = (empty($_row_s->state)) ? '' : $_row_s->state;
                $csv .= '"'.$usha['t'].'"'.$limi.'"'.$_r->date.'"'.$limi.'"'.$title.'"'.$limi.'"'.$des.'"'.$limi.
                        '"'.$_r->cats.'"'.$limi.
                        '"'.$acceso.'"'.$limi.'"'.$res_1612.'"'.$limi.
                        '"'.$source.'"'.$limi.'"'.$desc.'"'.$limi.'"'.$ref.'"'.$limi.
                        '"'.$state.'"'.$limi.'"'.$city.'"'.$limi.'"'.$_r->loc.'"'.$limi;

                // Victimas
                foreach($num_v as $nv) {
                    $csv .= $nv.$limi;
                }

                $csv .= $nl;

            }
        }

        //echo $csv;
        $f = Factory::create('file');
        $nomf = 'incidentes'; 

        $fp = $f->open("data/$nomf.csv", 'w+');
        $f->write($fp, $csv);
        $f->close($fp);
        
        echo "1";

    }
    
    /**
     * 
     * Incidentes para portal
     *
     */ 
    public function getIncidentesPortal($ini, $fin, $cats, $states, $limiti) {
        
        $_db_dn = $this->db_dn.'.';
        $evs = array();
        //$desas = array();
        $limit = 20;
        $total = 0;
        $total_ec = 0;
        $total_dn = 0;
        $sys = array('violencia','desastres');
        
        list($ini,$fin,$cond_cats_ec,$cond_cats_dn,$cond_tmp,$cond_csv) = $this->getConditions($ini, $fin, $cats, $states);
        
        $cats_parent_id = array();
        
        $conds = array($cond_cats_ec, $cond_cats_dn);
        
        $_from = "FROM %slocation AS l
                 INNER JOIN %sincident AS i ON l.id = i.location_id
                 INNER JOIN %sincident_category AS ic ON i.id = ic.incident_id
                 WHERE $cond_csv";

        $_order = " ORDER BY date DESC";

        $_limit = "LIMIT $limiti, $limit";

        $_selet_csv = "SELECT DISTINCT i.id AS id, i.incident_title AS t, i.incident_date AS date, l.location_name AS ln, state_id ";
        $_sql_csv = "$_selet_csv $_from $_order $_limit";
        
        $_sql_total = "SELECT COUNT(DISTINCT i.id) AS n $_from LIMIT 1";
        
        // Total ec
        $_sql = sprintf($_sql_total,'','','',$conds[0]);
        $_rs = $this->db->open($_sql);
        $_rt = $this->db->FO($_rs);
        $total_ec = (!empty($_rt)) ? $_rt->n : 0;
        
        // Total dn
        $_sql = sprintf($_sql_total,$_db_dn,$_db_dn,$_db_dn, $conds[1]);
        $_rs = $this->db->open($_sql);
        $_rt = $this->db->FO($_rs);
        $total_dn = (!empty($_rt)) ? $_rt->n : 0;

        $total = $total_ec + $total_dn;
        

        foreach($this->dbs as $_d => $_db) {
            
            $conf =array();

            $_sql = sprintf($_sql_csv,$_db,$_db,$_db, $conds[$_d]);
            $_rs = $this->db->open($_sql);

            while($_r = $this->db->FO($_rs)) {
                
                $iid = $_r->id;
                $state_id = $_r->state_id;

                $cats_tree = array();
                $_sql_cat = "SELECT category_title AS title, category_visible AS v, parent_id
                            FROM incident_category ic
                            INNER JOIN category c ON ic.category_id = c.id
                     WHERE incident_id = $iid";
                
                $_rs_cat = $this->db->open($_sql_cat);
                while($_r_cat = $this->db->FO($_rs_cat)) {
                     
                    // don't show hidden categoies
                    if($_r_cat->v == 0) continue;
                    $parent_id = $_r_cat->parent_id;
                    if ($parent_id == 0) {
                        $cats_tree[$_r_cat->title] = array();
                    }
                    else {
                        if (!in_array($parent_id, $cats_parent_id)) {
                            $sql_pa = "SELECT category_title AS t FROM category WHERE id = $parent_id";
                            $_rs_pa = $this->db->open($sql_pa);
                            $_pa = $this->db->FO($_rs_pa);
                            $cat_parent[$parent_id] = $_pa->t;
                        } 

                        $cats_tree[$cat_parent[$parent_id]][] = $_r_cat->title;
                    }
                }

                $_sql_s = "SELECT source_desc AS descr, source_reference AS ref, source, source_type
                    FROM source_detail sd
                    INNER JOIN source s ON sd.source_id = s.id
                    INNER JOIN source_type st ON s.source_type_id = st.id
                    WHERE incident_id = $iid LIMIT 1";

                $_rss = $this->db->open($_sql_s);
                $desc = array();
                $ref = array();
                $source = array();
                while($_row_s = $this->db->FO($_rss)){
                    $desc = (empty($_row_s->descr)) ? '' : str_replace('"','',$_row_s->descr);
                    $ref = (empty($_row_s->ref)) ? '' : $_row_s->ref;
                    $sn = (empty($_row_s->source)) ? '' : $_row_s->source;
                    $st = (empty($_row_s->source_type)) ? '' : $_row_s->source_type;

                    $source[] = array($st,$sn,$ref,$desc);
                }
                
                /*
                $_sql_c = "SELECT city from city WHERE id = ".$_r->city_id." LIMIT 1";
                $_rss_c = $this->db->open($_sql_c);
                $_row_c = $this->db->FO($_rss_c);
                 */
                
                //$city = (empty($_row_c->city)) ? '' : $_row_c->city;
                
                $_sql_s = "SELECT state from state WHERE id = ".$state_id." LIMIT 1";
                $_rss_s = $this->db->open($_sql_s);
                $_row_s = $this->db->FO($_rss_s);
                 
                $state = (empty($_row_s->state)) ? '' : $_row_s->state;
                
                // Ocultamos actores en titulo
                $_ti = explode('.', $_r->t);
                if (count($_ti) == 4) {
                    $_titulo = $_ti[0].'.'.$_ti[2].'.'.$_ti[3];
                }
                else {
                    $_titulo = $_r->t;
                }

                $_conf = array(
                't' => $_titulo,
                'd' => $_r->date,
                'c' => $cats_tree,
                'f' => $source,
                'desc' => $desc,
                'ref' => $ref,
                'ln' => $_r->ln,
                'ld' => $state_id,
                'ldn' => $state,
                'sys' => $sys[$_d]
                );

                $conf[] = $_conf;
            }

            $evs = array_merge($evs, $conf);

            //$evs = $conf;  

            //$evs[] = array('e' => $conf, 't' => $_rt->n);
        }

        list($rsms_ec, $rsms_dn, $charts) = $this->getAfeEveChart($ini,$fin,$cond_cats_ec,$cond_cats_dn,$cond_tmp,$cond_csv);
 
        // Ordena por fecha desc los eventos para mezclarlos
        usort($evs, array('MonitorController', 'orderArrayByDate'));

        $e = $evs;
        $t = $total;
        $t_e = $total_ec;
        $t_dn = $total_dn;

        return compact('e','t','t_e','t_d','rsms_ec','rsms_dn','charts');

    }
    
    /**
     * 
     * Resumen para el home del portal
     *
     */ 
    public function getResumenPortalHome($ini, $fin) {

        list($ini,$fin,$cond_cats_ec,$cond_cats_dn,$cond_tmp,$cond_csv) = $this->getConditions($ini, $fin, '', '');
        
        $resumen = array();
        
        $cond = " WHERE category_id IN (%s) AND $cond_tmp";
        
        $sqle = "SELECT COUNT(DISTINCT i.id) AS n
                FROM incident AS i
                JOIN incident_category ic ON ic.incident_id = i.id 
                $cond";
        
        $sqlv = "SELECT SUM(victim_cant) AS n
                FROM victim v
                JOIN incident_category ic ON v.incident_category_id = ic.id
                JOIN incident AS i ON ic.incident_id = i.id
                $cond AND victim_cant IS NOT NULL";

        // Desplazamiento Masivo
        $cats['des'] = '42, 44, 46, 41, 43, 45';
        $sql['des'] = $sqlv;
        
        // Confinamiento 13
        $cats['con'] = 13;
        $sql['con'] = $sqlv;
        
        // Acciones Bélicas 
        $cats['acc'] = '2, 3, 4, 5, 6, 7, 8';
        $sql['acc'] = $sqle;
        
        // Ataques a objetivos ilícitos de guerra
        $cats['ataq'] = '28, 29, 30, 31, 32, 33, 34';
        $sql['ataq'] = $sqle;
        
        // Amenazas
        $cats['ame'] = 11;
        $sql['ame'] = $sqle;
        
        // Homicidios en persona protegida 17
        $cats['hom'] = 17;
        $sql['hom'] = $sqlv;
        
        foreach($cats as $c => $cat) {
            $_sql = sprintf($sql[$c],$cat,'1=1'); 
            $_rs = $this->db->open($_sql);
            $row = $this->db->FO($_rs);

            if (!empty($row->n)) {
                $resumen[$c]['v'] = $row->n;
            }
            else {
                $resumen[$c]['v'] = 0;
            }
        }

        arsort($resumen);

        foreach($resumen as $c => $v) {
            $resumen[$c]['t'] = number_format($v['v'],0,'',',');
        }
        
        return $resumen;

    }

    private function getAfeEveChart($ini,$fin,$cond_cats_ec,$cond_cats_dn,$cond_tmp,$cond_csv){

        $afectacion = ($_SESSION['mapa_tipo'] == 'afectacion') ? true : false;
        
        $_db = $this->db_dn.'.';
        
        //echo $cond_tmp;
        
        if ($afectacion) {
            $_sqle = "SELECT SUM(victim_cant) AS n
                FROM %svictim v
                JOIN %sincident_category ic ON v.incident_category_id = ic.id
                JOIN %sincident AS i ON ic.incident_id = i.id
                JOIN %slocation AS l ON l.id = i.location_id
                WHERE $cond_tmp";
            
            $_sqld = "SELECT SUM(REPLACE(REPLACE(form_response,'.',''),',','')) AS n
                FROM %sform_response f
                JOIN %sincident AS i ON f.incident_id = i.id
                JOIN %slocation AS l ON l.id = i.location_id
                JOIN %sincident_category ic USING(incident_id)
                WHERE $cond_tmp AND form_field_id = 4";
            
            $_sqliec = sprintf($_sqle,'','','','',$cond_cats_ec);
            $_sqlidn = sprintf($_sqld,$_db,$_db,$_db,$_db,$cond_cats_dn);
        }
        else {
            $_sql = "SELECT COUNT(DISTINCT(l.id)) AS n FROM %slocation AS l
                 JOIN %sincident AS i ON l.id = i.location_id
                 JOIN %sincident_category AS ic ON i.id = ic.incident_id
                 WHERE $cond_tmp";
        
            $_sqliec = sprintf($_sql,'','','',$cond_cats_ec);
            $_sqlidn = sprintf($_sql,$_db,$_db,$_db,$cond_cats_dn);
        }
        
        // Resumen violencia
        if ($afectacion) {
            $_sqlr = "SELECT SUM(victim_cant) AS sum, 
                category_title AS cat, category_color AS color, c.id AS cat_id,
                DAY(incident_date) AS dia, MONTH(incident_date) AS mes, YEAR(incident_date) AS year,
                victim_ethnic_group_id, victim_gender_id
                FROM victim v
                JOIN incident_category ic ON v.incident_category_id = ic.id
                JOIN category c ON ic.category_id = c.id
                JOIN incident i ON ic.incident_id = i.id
                WHERE $cond_tmp";

            $_sql = $_sqlr ." GROUP BY category_id
                              ORDER BY sum DESC";

            $_sql_chart_ec = $_sqlr ." GROUP BY %s ORDER BY year,mes,dia";

        }
        else {
            $_sqlr = "SELECT COUNT(i.id) AS sum, 
                category_title AS cat, category_color AS color, c.id AS cat_id,
                DAY(incident_date) AS dia, MONTH(incident_date) AS mes, YEAR(incident_date) AS year
                FROM incident i
                JOIN incident_category ic ON i.id = ic.incident_id
                JOIN category c ON ic.category_id = c.id
                WHERE $cond_tmp";
            
            $_sql = $_sqlr ." GROUP BY category_id
                              ORDER BY sum DESC";

            $_sql_chart_ec = $_sqlr ." GROUP BY %s ORDER BY year,mes,dia";
        }
        
        $_sqliec = sprintf($_sql,$cond_cats_ec);
        //echo $_sqliec;

        $rsms_ec = array();
        $_rs = $this->db->open($_sqliec);
        while($_row = $this->db->fo($_rs)) {
            $rsms_ec[] = array('t' => $_row->cat, 'n' => $_row->sum, 'cat_id' => $_row->cat_id, 'c' => $_row->color);
        }
        
        // Resumen desastres
        if ($afectacion) {

            // Form id = 4, # personas
            $_sqlr = "SELECT SUM(REPLACE(REPLACE(form_response,'.',''),',','')) AS sum, category_title AS cat, category_color AS color,
                DAY(incident_date) AS dia, MONTH(incident_date) AS mes, YEAR(incident_date) AS year
                FROM ".$_db."form_response f
                JOIN %sincident i ON f.incident_id = i.id
                JOIN %sincident_category ic ON ic.incident_id = i.id
                JOIN %scategory c ON ic.category_id = c.id
                WHERE $cond_tmp AND form_field_id = 4";
            
            $_sql = $_sqlr ." GROUP BY category_id
                              ORDER BY sum DESC";

            $_sql_chart_dn = $_sqlr ." GROUP BY %s ORDER BY year,mes,dia";

        }
        else {
            $_sqlr = "SELECT COUNT(i.id) AS sum, category_title AS cat, category_color AS color,
                DAY(incident_date) AS dia, MONTH(incident_date) AS mes, YEAR(incident_date) AS year
                FROM %sincident i
                JOIN %sincident_category ic ON i.id = ic.incident_id
                JOIN %scategory c ON ic.category_id = c.id
                WHERE $cond_tmp";
            
            $_sql = $_sqlr ." GROUP BY category_id
                              ORDER BY sum DESC";

            $_sql_chart_dn = $_sqlr ." GROUP BY %s ORDER BY year,mes,dia";
        }
        
        $_sqlidn = sprintf($_sql,$_db,$_db,$_db,$cond_cats_dn);

        //echo $_sqlidn;
        
        $rsms_dn = array();
        $_rs = $this->db->open($_sqlidn);
        while($_row = $this->db->FO($_rs)) {
            $rsms_dn[] = array('t' => $_row->cat, 'n' => $_row->sum, 'c' => $_row->color);
        }
        
        // Charts
        // Calcula si eje x en dias o meses
        $ini_segundos = strtotime($ini);
        $fin_segundos = strtotime($fin);

        $segundos = abs($fin_segundos - $ini_segundos);

        //echo $segundos;
        
        // Eje x meses, 12
        $periodo = '';
        if ($segundos > 60*60*24*31) {
            $group_by = 'MONTH(incident_date)';
            $titlex = 'Meses';
            $periodo = 'y';
        }
        else if ($segundos > 60*60*24*8 && empty($periodo)) {
            $group_by = 'DAY(incident_date), MONTH(incident_date)';
            $titlex = 'Dias';
            $periodo = 'm';
        }
        else { // Eje x dias, 15 o 30
            $group_by = 'DAY(incident_date), MONTH(incident_date)';
            $titlex = 'Dias';
            $periodo = 'd';
        }
        
        $_sqliecc = sprintf($_sql_chart_ec,$cond_cats_ec, $group_by);

        $data_lines = array();
        $data = array();
        $ejex = array();
        $color_v = '#d40000';
        $color_d = '#2CA02C'; 
        $_rs = $this->db->open($_sqliecc);
        while($_row = $this->db->FO($_rs)) {
            $date = strtotime($_row->year.'-'.$_row->mes.'-'.$_row->dia) * 1000; // Tiene que ser en milisegundos
            $data_lines[] = array($date ,$_row->sum*1);
        }

        if (!empty($data_lines)) {
            $data[] = array('name' => 'Violencia', 
                                                   'data' => $data_lines,
                                                   'color' => $color_v,
                                                    );
        }


        // Desastres
        $_sqlidnc = sprintf($_sql_chart_dn,$_db,$_db,$_db,$cond_cats_dn, $group_by);
        //echo $_sqlidnc;

        $data_lines = array();
        $_rs = $this->db->open($_sqlidnc);
        while($_row = $this->db->FO($_rs)) {

            $date = strtotime($_row->year.'-'.$_row->mes.'-'.$_row->dia) * 1000; // Tiene que ser en milisegundos
            $data_lines[] = array($date ,$_row->sum*1);
        }
        
        if (!empty($data_lines)) {
            $data[] = array('name' => 'Desastres', 
                                                   'data' => $data_lines,
                                                   'color' => $color_d,
                                                   'yAxis' => 1
                                                    );
        }

        $chart_line_yaxis_title = ($afectacion) ? 'Personas' : 'Eventos';
        $charts[0] = array('title' => 'Conteo en el tiempo', 
                             //'xAxis' => array('title' => array('text' => $titlex), 'categories' => $ejex),
            'yAxis' => array(
                                array('title' => array('text' => $chart_line_yaxis_title, 'style' => array('color' => $color_v)),
                                      'labels' => array('style' => array('color' => $color_v)),
                                ),
                                array('title' => array('text' => $chart_line_yaxis_title, 'style' => array('color' => $color_d)),
                                      'labels' => array('style' => array('color' => $color_d)),
                                      'opposite' => true,
                                )
                            ),
                            'data' => $data
                         );

        if ($afectacion) {
            // Pie de grupo etnico
            $group_by = 'victim_ethnic_group_id';
            $_sqliecc = sprintf($_sql_chart_ec,$cond_cats_ec, $group_by);

            //echo $_sqliecc;

            // Consulta ethnic groups
            $_sql = "SELECT * FROM victim_ethnic_group";
            $_rs = $this->db->open($_sql);
            while($_row = $this->db->FO($_rs)) {
                $ethnic_groups[$_row->id] = str_replace(array('Sin información'), array('Sin info.'), $_row->ethnic_group);
            }
            
            $data_pie_ethnic = array();
            $_rs = $this->db->open($_sqliecc);
            while($_row = $this->db->FO($_rs)) {
                if (!empty($_row->victim_ethnic_group_id)) {
                    $data_pie_ethnic[] = array($ethnic_groups[$_row->victim_ethnic_group_id],$_row->sum*1);
                }
            }
            
            // Pie de genero
            $group_by = 'victim_gender_id';
            $_sqliecc = sprintf($_sql_chart_ec,$cond_cats_ec, $group_by);

            // Consulta genders
            $_sql = "SELECT * FROM victim_gender";
            $_rs = $this->db->open($_sql);
            while($_row = $this->db->FO($_rs)) {
                $genders[$_row->id] = $_row->gender;
            }
            
            $data_pie_gender = array();
            $_rs = $this->db->open($_sqliecc);
            while($_row = $this->db->FO($_rs)) {
                if (!empty($_row->victim_gender_id)) {
                    $data_pie_gender[] = array($genders[$_row->victim_gender_id],$_row->sum*1);
                }
            }
            
            $charts[1] = array('title' => 'Víctimas por grupo poblacional', 
                'data' => $data_pie_ethnic
                             );
            
            $charts[2] = array('title' => 'Víctimas por género', 
                'data' => $data_pie_gender
                             );
        }  

        return array($rsms_ec,$rsms_dn,$charts);
    }

    /*
     * Función para usar con usor y ordenar por la llave 'd' (fecha) de un arreglo
     * @param array $a
     * @param array $b
     */
    private static function orderArrayByDate($a, $b) {
        return strtotime($b['d']) - strtotime($a['d']);
    }

    /**
     * Export data
     *
     * @param string $t xls,pdf
     * @param string $csv Nombre del archivo csv
     * @param string $nom Nombre del archivo con el que se exporta
     */ 
    public function export($t,$csv,$nom) {
        require 'libraries/phpexcel/PHPExcel/IOFactory.php';

        $objReader = PHPExcel_IOFactory::createReader('CSV');

        // If the files uses a delimiter other than a comma (e.g. a tab), then tell the reader
        $objReader->setDelimiter("~");
        //$objReader->setEnclosure('""');
        $objReader->setLineEnding("\r\n");
        // If the files uses an encoding other than UTF-8 or ASCII, then tell the reader
        //$objReader->setInputEncoding('ISO-8859-1');

        $objPHPExcel = $objReader->load('data/'.$csv.'.csv');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        header("Content-type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=\"".$nom.".xls\"");
        header("Cache-Control: max-age=0");
        $objWriter->save('php://output');   
    }
    
    /**
     * Get state centroide
     *
     * @param string $divipola
     */ 
    public function getStateCentroid($divipola) {    
        $sql = "SELECT id,centroid FROM state WHERE divipola = '$divipola'";
        $_rs = $this->db->open($sql);
        $_r = $this->db->FO($_rs);
        
        return array($_r->id, $_r->centroid);
    }
    
    /*
     * Coloca variable en sesión
     * @param string $var
     * @param string $valor
     */
    public function setSessionVar($var, $valor) {
        
        $_SESSION[$var] = $valor;

    }
}

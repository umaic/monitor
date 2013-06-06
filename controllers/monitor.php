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

    function __construct() {
        $this->root = dirname( __FILE__ ).'/../'; 
        
        require $this->root.'libraries/factory.php';
        $this->db = Factory::create('mysql');
        $this->db_dn = 'inundacionesv2_1';    
        $this->dbs = array('', $this->db_dn.'.');
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
        
        /*
        require 'libraries/factory.php';
        
        $f = Factory::create('file');
        $p = 'data/monitor.json';

        $fp = $f->open($p, 'r');
        $j = $f->read($fp, $p);
        $i = json_decode($j, true);

        //echo json_encode(array('h' => array(2012,2011,2013)));

        return $i['totales'];
         */
        
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
     * @param int $ini Fecha inicio milisegundos
     * @param int $fin Fecha inicio milisegundos
     * @param string $cats Categorias separadas por ',' filtradas para ec y dn formato ec1,ec2|dn1,dn2
     * @param string $states States separados por ','
     */ 
    public function totalxd($ini, $fin, $cats, $states) {
        
        $r = array();
        $t = array('ec' => 0, 'dn' => 0);
        
        list($ini,$fin,$cond_cats_ec,$cond_cats_dn,$cond_tmp,$cond_csv) = $this->getConditions($ini, $fin, $cats, $states);

        $_SESSION['cond_csv'] = $cond_csv;
        $_SESSION['cond_cats_ec'] = $cond_cats_ec;
        $_SESSION['cond_cats_dn'] = $cond_cats_dn;

        $_db = $this->db_dn.'.';
        $_sql = "SELECT COUNT(DISTINCT(l.id)) AS n FROM %slocation AS l
                 JOIN %sincident AS i ON l.id = i.location_id
                 JOIN %sincident_category AS ic ON i.id = ic.incident_id
                 WHERE $cond_tmp";

        $_sqliec = sprintf($_sql,'','','',$cond_cats_ec);
        $_sqlidn = sprintf($_sql,$_db,$_db,$_db,$cond_cats_dn);

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
            
            //$_sqlec = sprintf($_sql,'','','','',$_row->id, $ini, $fin, $cond_cats_ec);
            $_sqlec = sprintf($_sqliec,$_row->id);
            //echo $_sqlec;
            $_rse = $this->db->open($_sqlec);
            $_ec = $this->db->FO($_rse);
            $_nec = (empty($_ec->n)) ? '' : $_ec->n;
            
            //$_sqldn = sprintf($_sql,$_db,$_db,$_db,$_db,$_row->id, $ini, $fin, $cond_cats_dn);
            $_sqldn = sprintf($_sqlidn,$_row->id);
            $_rsd = $this->db->open($_sqldn);
            //echo $_sqldn;
            $_dn = $this->db->FO($_rsd);
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

            if (!empty($_nec) || !empty($_ndn)) {
             
                $r[] = array('d' => $_row->state,
                             'ec' => $_nec,
                             'dn' => $_ndn,
                             'c' => $_row->centroid,
                             'state_id' => $_row->id,
                             'css' => $class
                            );
            }
        }

        
        return compact('r', 't');
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
        
            $_sql = "SELECT id, category_title AS t FROM ".$db."category WHERE parent_id = 0 AND category_visible = 1 ORDER BY category_title";
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
        /*
        $_sql_csv = "SELECT i.incident_date AS date, s.source, sd.source_desc AS descr,
            sd.source_reference AS ref, state, city, l.location_name AS ln, l.latitude, l.longitude
            FROM %slocation AS l
                 INNER JOIN %sincident AS i ON l.id = i.location_id
                 INNER JOIN %sincident_category AS ic ON i.id = ic.incident_id
                 INNER JOIN %scategory AS c ON ic.category_id = c.id
                 INNER JOIN %ssource_detail AS sd ON i.id = sd.incident_id
                 INNER JOIN %ssource AS s ON sd.source_id = s.id
                 INNER JOIN %sstate AS st ON l.state_id = st.id
                 INNER JOIN %scity AS ct ON l.city_id = ct.id
                 WHERE $cond_csv 
                 ";

        $_sql_csv_ec = sprintf($_sql_csv,'','','','','','','','', $cond_cats_ec);
        echo $_sql_csv_ec;
         */
        $_db = $this->db_dn.'.';
        $cond_csv = $_SESSION['cond_csv'];
        $cond_cats_ec = $_SESSION['cond_cats_ec'];
        $cond_cats_dn = $_SESSION['cond_cats_dn'];
        
        $limi = '~';
        $nl = "\r\n";
        $csv = '"Tipo"'.$limi.'"Fecha Evento"'.$limi.'"Fuente"'.$limi.'"Descripcion"'.$limi.'"Referecia"'.$limi.'"Departamento"'.$limi.'"Municipio"'.$nl;

        

        $_sql_csv = "SELECT DISTINCT(i.id) AS id, i.incident_date AS date, l.location_name AS ln, city_id, state_id
            FROM %slocation AS l
                 INNER JOIN %sincident AS i ON l.id = i.location_id
                 INNER JOIN %sincident_category AS ic ON i.id = ic.incident_id
                 WHERE $cond_csv 
                 ";
        $_sql_csv_ec = sprintf($_sql_csv,'','','', $cond_cats_ec);

        $_rs = $this->db->open($_sql_csv_ec);
        while($_r = $this->db->FO($_rs)) {

            $_sql_s = "SELECT source_desc AS descr, source_reference AS ref, source 
                FROM source_detail sd
                INNER JOIN source s ON sd.source_id = s.id
                WHERE incident_id = ".$_r->id." LIMIT 1";

            $_rss = $this->db->open($_sql_s);
            $_row_s = $this->db->FO($_rss);
            
            $desc = (empty($_row_s->descr)) ? '' : str_replace('"','',$_row_s->descr);
            $ref = (empty($_row_s->ref)) ? '' : $_row_s->ref;
            $source = (empty($_row_s->source)) ? '' : $_row_s->source;
            
            $_sql_c = "SELECT city from city WHERE id = ".$_r->city_id." LIMIT 1";
            $_rss_c = $this->db->open($_sql_c);
            $_row_c = $this->db->FO($_rss_c);
            
            $city = (empty($_row_c->city)) ? '' : $_row_c->city;
            
            $_sql_s = "SELECT state from state WHERE id = ".$_r->state_id." LIMIT 1";
            $_rss_s = $this->db->open($_sql_s);
            $_row_s = $this->db->FO($_rss_s);
             
            $state = (empty($_row_s->state)) ? '' : $_row_s->state;
            $csv .= '"Violencia armada"'.$limi.'"'.$_r->date.'"'.$limi.'"'.$source.'"'.$limi.'"'.$desc.'"'.$limi;
            $csv .= '"'.$ref.'"'.$limi.'"'.$state.'"'.$limi.'"'.$city.'"'.$nl;

        }

        $_sql_csv_dn = sprintf($_sql_csv,$_db,$_db,$_db, $cond_cats_dn);

        $_rs = $this->db->open($_sql_csv_dn);
        while($_r = $this->db->FO($_rs)) {

            $_sql_s = "SELECT source_desc AS descr, source_reference AS ref, source 
                FROM source_detail sd
                INNER JOIN source s ON sd.source_id = s.id
                WHERE incident_id = ".$_r->id." LIMIT 1";

            $_rss = $this->db->open($_sql_s);
            $_row_s = $this->db->FO($_rss);
            
            $desc = (empty($_row_s->descr)) ? '' : $_row_s->descr;
            $ref = (empty($_row_s->ref)) ? '' : $_row_s->ref;
            $source = (empty($_row_s->source)) ? '' : $_row_s->source;
            
            $desc = str_replace('"','',$_r->descr);
            
            $_sql_c = "SELECT city from city WHERE id = ".$_r->city_id." LIMIT 1";
            $_rss_c = $this->db->open($_sql_c);
            $_row_c = $this->db->FO($_rss_c);
            
            $city = (empty($_row_c->city)) ? '' : $_row_c->city;
            
            $_sql_s = "SELECT state from state WHERE id = ".$_r->state_id." LIMIT 1";
            $_rss_s = $this->db->open($_sql_s);
            $_row_s = $this->db->FO($_rss_s);
             
            $state = (empty($_row_s->state)) ? '' : $_row_s->state;
            $csv .= '"Desastre"'.$limi.'"'.$_r->date.'"'.$limi.'"'.$source.'"'.$limi.'"'.$desc.'"'.$limi;
            $csv .= '"'.$ref.'"'.$limi.'"'.$state.'"'.$limi.'"'.$city.'"'.$nl;

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
        
        $_db = $this->db_dn.'.';
        $evs = array();
        //$desas = array();
        $limit = 20;
        $total = 0;
        $sys = array('violencia','desastres');
        
        list($ini,$fin,$cond_cats_ec,$cond_cats_dn,$cond_tmp,$cond_csv) = $this->getConditions($ini, $fin, $cats, $states);
        
        $cats_parent_id = array();
        $_sql_csv = "SELECT DISTINCT i.id AS id, i.incident_title AS t, i.incident_date AS date, l.location_name AS ln, state_id
            FROM %slocation AS l
                 INNER JOIN %sincident AS i ON l.id = i.location_id
                 INNER JOIN %sincident_category AS ic ON i.id = ic.incident_id
                 WHERE $cond_csv
                 ORDER BY date DESC
                 LIMIT $limiti, $limit
                 ";
        
        $_sql_total = "SELECT COUNT(i.id) AS n
            FROM %slocation AS l
                 INNER JOIN %sincident AS i ON l.id = i.location_id
                 INNER JOIN %sincident_category AS ic ON i.id = ic.incident_id
                 WHERE $cond_csv
                 LIMIT 1
                 ";
        
        $conds = array($cond_cats_ec, $cond_cats_dn);

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
                
                $_conf = array(
                't' => $_r->t,
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

            $_sqlt = sprintf($_sql_total,$_db,$_db,$_db, $conds[$_d]);
            $_rst = $this->db->open($_sqlt);
            $_rt = $this->db->FO($_rst);


            //$evs = $conf;  
            $total += $_rt->n;

            //$evs[] = array('e' => $conf, 't' => $_rt->n);
        }
        
        // Ordena por fecha desc los eventos para mezclarlos
        usort($evs, function($a, $b){
            return strtotime($b['d']) - strtotime($a['d']);
        });
        
        return array('e' => $evs, 't' => $total);

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
}

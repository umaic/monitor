<?php
/**
 * Main controller
 *
 * @category Controller
 * @package Monitor
 * @author Ruben Rojas
 * @link http://monitor.colombiashh.org/api
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GNU General Public License
 * @autor   Ruben Rojas C.
 */
class MonitorController
{

    private $db_dn;
    private $db;
    private $dbs;
    private $meses;
    private $config;
    private $lib_dir;

    function __construct() {

        require dirname( __FILE__ ).'/../config.php';

        $this->lib_dir = $config['libraries'];

        require $this->lib_dir."/factory.php";

        $this->config = $config;
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

        date_default_timezone_set('America/Bogota');  // Igual a USHAHIDI, violencia armada

        $ini = date('Y-m-d H:i:s', intval($ini));  // Se usa intval para que quede igual que ushahidi/application/helper/reports/ :740
        $fin = date('Y-m-d H:i:s', intval($fin));

        $_t = explode('|', $cats);
        $cond_cats_ec = '1=1';
        if (!empty($_t[0])) {
            $cond_cats_ec = ' category_id IN ('.$_t[0].')';
        }

        $cond_cats_dn = '1=1';
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

        $afectacion = ($_SESSION['mapa_tipo'] == 'afectacion') ? true : false;
        $acceso = ($_SESSION['acceso'] == 1) ? true : false;

        $n = md5(str_replace(array('/',','),array('-','-'),$ini.'-'.$fin.'-'.$cats.'-'.$states.'-'.$afectacion.'-'.$acceso));
        $file = $this->config['cache_json']['path'].'/'.$n;

        if (!file_exists($file)) {
            $r = array();
            $t = array('ec' => 0, 'dn' => 0);

            list($ini,$fin,$cond_cats_ec,$cond_cats_dn,$cond_tmp,$cond_csv) = $this->getConditions($ini, $fin, $cats, $states);

            $_SESSION['cond_csv'] = $cond_csv;
            $_SESSION['cond_cats_ec'] = $cond_cats_ec;
            $_SESSION['cond_cats_dn'] = $cond_cats_dn;

            list($rsms_ec, $rsms_dn, $charts, $subtotales) = $this->getAfeEveChart($ini,$fin,$cond_cats_ec,$cond_cats_dn,$cond_tmp,$cond_csv);

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

                $_sqld = "SELECT SUM(n) AS n FROM (SELECT REPLACE(REPLACE(form_response,'.',''),',','') AS n
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

           // echo $_sqliec;
           // echo $_sqlidn;


	        if ($afectacion) {
		        $_ss = " AND state_id = '%s'";
	        } else
	        {
		        $_ss = " AND state_id = '%s' LIMIT 1";
	        }
	        $_sqliec .= $_ss;
	        $_sqlidn .= $_ss;
	        if ($afectacion) $_sqlidn .= " GROUP BY i.id) sqld";


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

            $json = json_encode(compact('r', 't','rsms_ec', 'rsms_dn','charts','subtotales'));

            file_put_contents($file, $json);

            return $json;
        }
        else {
            return file_get_contents($file);
        }
    }

    /**
     * Variacion
     *
     * @param string $periodo_1  f_ini|f_fin yyyy-mm-dd H:i:s
     * @param string $periodo_2  f_ini|f_fin yyyy-mm-dd H:i:s
     * @param string $ecdn Violencia (v) o Desastres (d)
     * @param string $cats Categorias separadas por ',' filtradas para ec y dn formato ec1,ec2|dn1,dn2
     * @param string $states States separados por ','
     */
    public function variacion($periodo_1, $periodo_2, $ecdn, $cats, $states='') {

        //$afectacion = ($_SESSION['mapa_tipo'] == 'afectacion') ? true : false;
        // Variacion solo para # eventos
        $afectacion = false;
        $acceso = ($_SESSION['acceso'] == 1) ? true : false;
        $violencia = ($ecdn == 'v') ? true : false;
        $usar_temporal = true;
        $values = array();

        $n = md5($periodo_1.'-'.$periodo_2.'-'.$ecdn.'-'.$cats.'-'.$states);

        $path_static = $this->config['cache_json']['path'].'/';
        $path2file = $path_static.$n;

        $r = array();
        $t = array('ec' => 0, 'dn' => 0);

        if (!$usar_temporal || !file_exists($path2file)) {
            $p1 = explode('|', $periodo_1);
            $p2 = explode('|', $periodo_2);

            foreach (array($p1,$p2) as $p12 => $d) {
                $ini = $d[0];
                $fin = $d[1];

                list($ini,$fin,$cond_cats_ec,$cond_cats_dn,$cond_tmp,$cond_csv) = $this->getConditions($ini, $fin, $cats, $states);

                $_SESSION['cond_csv'] = $cond_csv;
                $_SESSION['cond_cats_ec'] = $cond_cats_ec;
                $_SESSION['cond_cats_dn'] = $cond_cats_dn;

                //list($rsms_ec, $rsms_dn, $charts, $subtotales) = $this->getAfeEveChart($ini,$fin,$cond_cats_ec,$cond_cats_dn,$cond_tmp,$cond_csv);

                $_db = $this->db_dn.'.';

                if ($afectacion) {

                    if ($violencia) {
                        $_sql = "SELECT SUM(victim_cant) AS n
                            FROM victim v
                            JOIN incident_category ic ON v.incident_category_id = ic.id
                            JOIN incident AS i ON ic.incident_id = i.id
                            JOIN location AS l ON l.id = i.location_id";

                        $_sql .= " WHERE $cond_tmp";
                        $_sqli = sprintf($_sql,$cond_cats_ec);
                    }
                    else {
                        $_sql = "SELECT SUM(REPLACE(REPLACE(form_response,'.',''),',','')) AS n
                            FROM %sform_response f
                            JOIN %sincident AS i ON f.incident_id = i.id
                            JOIN %slocation AS l ON l.id = i.location_id
                            JOIN %sincident_category ic USING(incident_id)
                            WHERE $cond_tmp AND form_field_id = 4";

                        $_sqli = sprintf($_sqld,$_db,$_db,$_db,$_db,$cond_cats_dn);
                    }

                }
                else {
                    $_sql = "SELECT COUNT(DISTINCT(l.id)) AS n FROM %slocation AS l
                         JOIN %sincident AS i ON l.id = i.location_id
                         JOIN %sincident_category AS ic ON i.id = ic.incident_id
                         WHERE $cond_tmp";

                    if ($violencia) {
                        $_sqli = sprintf($_sql,'','','',$cond_cats_ec);
                    }
                    else {
                        $_sqli = sprintf($_sql,$_db,$_db,$_db,$cond_cats_dn);
                    }
                }

                $_ss = " AND city_id = %s LIMIT 1";

                $_sqli .= $_ss;

                //echo $_sqli;

                $sql_cities = "SELECT id,divipola,city FROM city";

                if (!empty($states)) {
                    $sql_cities .= " WHERE state_id IN ($states)";
                }

                $_rs = $this->db->open($sql_cities);
                while($_row = $this->db->FO($_rs)) {

                    if ($violencia) {
                        $_sqln = sprintf($_sqli,$_row->id);
                        $_rsn = $this->db->open($_sqln);
                        $_ec = $this->db->FO($_rsn);

                        $num = (empty($_ec->n)) ? 0 : $_ec->n;
                    }
                    else {
                        $_sqln = sprintf($_sqli,$_row->id);
                        $_rsn = $this->db->open($_sqln);
                        //echo $_sqldn;
                        $_dn = $this->db->FO($_rsn);
                        $num = (empty($_dn->n)) ? 0 : $_dn->n;
                    }

                    //echo $_sql;

                    $r[$_row->divipola.'|'.$_row->city][$p12] = $num;
                }
            }

            // Calcula variacion
            $headers = array('Divipola','Municipio','Variación','P1','P2');
            $html = '<table class="display"><thead><tr>';
            $csv = implode('~',$headers)."\n";

            foreach($headers as $h) {
                $html .= "<th>$h</th>";
            }
            $html .= '</tr></thead><tbody>';

            foreach($r as $mun => $v) {

                list($divipola,$name) = explode('|', $mun);

                $p1 = $v[0];
                $p2 = $v[1];

                //echo "$div - ".$v[0]." <br />";
                if ($p1 > 0) {

                    $val = (($p2 - $p1) / $p1) * 100;

                    $values[$divipola] = 1*number_format($val,2,".","");
                    $p1s[$divipola] = number_format($p1,2,".","");
                    $p2s[$divipola] = number_format($p2,2,".","");

                    $val_html = number_format($val,2);

                    $html .= "<tr><td>$divipola</td><td>$name</td><td>$val_html%</td><td>$p1</td><td>$p2</td></tr>";
                    $csv .= "$divipola~$name~$val~$p1~$p2\n";
                }
            }

            $html .= '</tbody></table>';

            file_put_contents($path2file,json_encode(compact('values','p1s','p2s','html','csv')));

        }
        else {
            extract(json_decode(file_get_contents($path2file),true));
        }

        $topojson = json_decode(file_get_contents('data/mpios_topo.json'), true);

        $geometries = $topojson['objects']['mpios_geonode']['geometries'];

        // Agrega propiedad variacion a la capa topoJSON
        foreach($geometries as $g => $geom) {
            $divipola = $geom['id'];

            if (isset($values[$divipola])) {
                $geom['properties']['variacion'] = $values[$divipola];
                $geom['properties']['p1'] = $p1s[$divipola];
                $geom['properties']['p2'] = $p2s[$divipola];
            }

            $geometries[$g] = $geom;
        }

        $topojson['objects']['mpios_geonode']['geometries'] = $geometries;

        file_put_contents($path_static.'/variacion-topo.json',json_encode($topojson));

        // Excel file
        $nom = 'monitor-variacion';
        $csv_path = $this->config['cache_reportes']."/$nom.csv";
        $xls_path = $this->config['cache_reportes']."/$nom.xls";

        file_put_contents($csv_path,$csv);
        $this->export('xls',$nom, $xls_path ,'static');

            // Serie de datos para Jenks
        $values = array_values(array_unique($values));

        return json_encode(compact('values','html'));

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
     * @param string $tipo, v|d
     * @param array $conds
     *
     */
    public function downloadIncidents($tipo, $conds=array()) {
        $_db = $this->db_dn.'.';

        if (isset($_SESSION['cond_csv'])) {
            $cond_csv = $_SESSION['cond_csv']; // Se crea en $this->totalxd, fila 152
            $cond_cats_ec = $_SESSION['cond_cats_ec'];
            $cond_cats_dn = $_SESSION['cond_cats_dn'];
        }
        else {
            extract($conds);
        }

        $_sql_f = "SELECT id, field_name AS n FROM ".$_db."form_field WHERE form_id = 1 AND field_type = 1";
        $_rsf = $this->db->open($_sql_f);
        while ($_row_f = $this->db->FO($_rsf)) {
            $ids_form_afectacion_dn[] = $_row_f->id;
            $name_form_afectacion_dn[] = $_row_f->n;
        }

        $limi = '~';
        $nl = "\r\n";

        $csv = '"Id"'.$limi.'"Tipo"'.$limi.'"Fecha Evento"'.$limi.'"Fecha Fin Evento"'.$limi.'"# días evento"'.$limi.'"Título evento"'.$limi.'"Resumen evento"'.$limi.
                '"Categorias (Subcategorias)"'.$limi.
                '"Acceso (para desastres)"'.$limi.
                '"Resoluciones"'.$limi.
                '"Fuente"'.$limi.'"Descripcion de la fuente"'.$limi.'"Referecia"'.$limi.
				'"Divipola"'.$limi.'"Departamento"'.$limi.'"Divipola"'.$limi.'"Municipio"'.$limi.'"Lugar"';


        $_sql_csv = "SELECT i.id AS id, i.incident_date AS date, i.incident_date_end AS date_end,
                    DATEDIFF(i.incident_date_end, i.incident_date) AS dias, i.incident_title AS title,
                    i.incident_description AS des, GROUP_CONCAT(c.category_title) AS cats,GROUP_CONCAT(DISTINCT c.parent_id) AS parents_id,
                    l.location_name AS ln, city_id, state_id, l.location_name AS loc
                    FROM %slocation AS l
                 INNER JOIN %sincident AS i ON l.id = i.location_id
                 INNER JOIN %sincident_category AS ic ON i.id = ic.incident_id
                 INNER JOIN %scategory AS c ON ic.category_id = c.id
                 WHERE $cond_csv
                 GROUP BY i.id
                 ";

        $ushas = array('v' => array('t' => 'Violencia armada', 'db' => '', 'cc' => $cond_cats_ec),
                       'd' => array( 't' => 'Desastres', 'db' => $_db, 'cc' => $cond_cats_dn));

        if ($tipo == 'v') {
            $csv .= $limi.'"# Total Víctimas"'.$limi.'"# Víctimas civiles"'.$limi.'"Víctimas militares"'.$limi.
                '"# Víctimas menores 18 años"'.$limi.'"Víctimas mujeres"'.$limi.
                '"# Víctimas afro"'.$limi.'"Víctimas indígenas"'.$limi.'"Víctimas otros"'.$limi.
                '"Incidente o Accidente MAP/MUSE"';
        }
        else {
            // Formulario de afectacion desastres
            foreach ($name_form_afectacion_dn as $name) {
                // code...
                $csv .= $limi.'"'.$name.'"';
            }
        }

        $csv .= $nl;

        $usha = $ushas[$tipo];
        $_dbu = $usha['db'];
        $_sql_csv_ecdn = sprintf($_sql_csv,$_dbu,$_dbu,$_dbu,$_dbu,$usha['cc']);

		//echo $_sql_csv_ecdn."</br>";

        $_rs = $this->db->open($_sql_csv_ecdn);
        while($_r = $this->db->FO($_rs)) {

		//echo "entro</br>";

            $des = $source = $desc = $ref = $cats = '';
            $iid = $_r->id;

            // Inicializa num victimas
            for ($v=0; $v<7; $v++) {
                $num_v[$v] = '';
            }

            $acceso = '';
            $resoluciones = array();

            // Respuesta a formularios
            $_sql_acc_1612 = "SELECT form_response AS r
                FROM ".$_dbu."form_response AS fr
            WHERE form_field_id = %s AND incident_id = $iid";

            if ($tipo == 'v') {

                $title = $_r->title;
                $des = $this->cleanText($_r->des);

                // Fuentes
                // Todo: Ojo, revisar el tema que no se pueden mostrar bitacoras
                $_sql_s = "SELECT source_desc AS descr, source_reference AS ref, source
                FROM source_detail sd
                INNER JOIN source s ON sd.source_id = s.id
                WHERE incident_id = $iid LIMIT 1";

                $_rss = $this->db->open($_sql_s);
                $_row_s = $this->db->FO($_rss);

                $desc = (isset($_row_s->descr)) ? $this->cleanText($_row_s->descr) : '';
                $ref = (isset($_row_s->ref)) ? $this->cleanText($_row_s->ref) : '';

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

                // Resoluciones
                $form_field_id_1612 = 2; // Preguntas de 1612

                $_sql_1612 = sprintf($_sql_acc_1612,$form_field_id_1612);

                $_rsv = $this->db->open($_sql_1612);
                while ($_row_a = $this->db->FO($_rsv)) {

                    $r = $_row_a->r;

                    if (strpos($r,'res_1612') !== false) {
                        $resoluciones[] = 'NAN';
                    }

                    if (strpos($r,'res_1820')) {
                        $resoluciones[] = 'VSBG';
                    }
                }


            }
            else {
                $title = $_r->title;
                $des = $this->cleanText($_r->des);

                $_sql_s = "SELECT media_link AS descr
                FROM ".$_dbu."media m
                WHERE media_type = 4 AND incident_id = $iid";

                $_rss = $this->db->open($_sql_s);
                $_row_s = $this->db->FO($_rss);

                $source = (empty($_row_s->descr)) ? '' : $_row_s->descr;

                // Acceso desastres
                $form_field_id_acc = 27; // Preguntas de acceso en desastres

                $_sql_acc = sprintf($_sql_acc_1612,$form_field_id_acc);

                $_rsv = $this->db->open($_sql_acc);
                $_row_a = $this->db->FO($_rsv);
                if (isset($_row_a->r)) {
                    $acceso = $_row_a->r;
                }
            }

            $_sql_c = "SELECT city,divipola from city WHERE id = ".$_r->city_id." LIMIT 1";
            $_rss_c = $this->db->open($_sql_c);
            $_row_c = $this->db->FO($_rss_c);

            $city = (empty($_row_c->city)) ? '' : $_row_c->city;
            $city_divipola = (empty($_row_c->divipola)) ? '' : $_row_c->divipola;

            $_sql_s = "SELECT state,divipola from state WHERE id = ".$_r->state_id." LIMIT 1";
            $_rss_s = $this->db->open($_sql_s);
            $_row_s = $this->db->FO($_rss_s);

            $state = (empty($_row_s->state)) ? '' : $_row_s->state;
            $state_divipola = (empty($_row_s->divipola)) ? '' : $_row_s->divipola;

            // Incidente o Accidente con Uso de explosivos remanentes de guerra ?
            $_cat_uerg = "35";
            $inc_acc = '';
            if (strpos($_r->parents_id, $_cat_uerg) !== false) {
                $inc_acc = ($num_v[0] > 0) ? 'Accidente' : 'Incidente';
            }

            $resoluciones = implode(',', $resoluciones);

            $csv .= '"'.$iid.'"'.$limi.'"'.$usha['t'].'"'.$limi.'"'.$_r->date.'"'.$limi.'"'.$_r->date_end.'"'.$limi.'"'.$_r->dias.'"'.$limi.
                    '"'.$title.'"'.$limi.'"'.$des.'"'.$limi.
                    '"'.$_r->cats.'"'.$limi.
                    '"'.$acceso.'"'.$limi.
                    '"'.$resoluciones.'"'.$limi.
                    '"'.$source.'"'.$limi.'"'.$desc.'"'.$limi.'"'.$ref.'"'.$limi.
                    '"'.$state_divipola.'"'.$limi.'"'.$state.'"'.$limi.'"'.$city_divipola.'"'.$limi.'"'.$city.'"'.$limi.'"'.$_r->loc.'"'.$limi;

            if ($tipo == 'v') {
                // Victimas
                foreach($num_v as $nv) {
                    $csv .= $nv.$limi;
                }
            }
            else {
                // Afectados
                foreach ($ids_form_afectacion_dn as $idf) {
                    $_sql_v = "SELECT SUM(REPLACE(REPLACE(form_response,'.',''),',','')) AS num
                    FROM ".$_dbu."form_response
                    WHERE form_field_id = $idf AND incident_id = $iid";

                    $_rsv = $this->db->open($_sql_v);
                    $_row_v = $this->db->FO($_rsv);

                    $csv .= $_row_v->num.$limi;
                }
            }

            $csv .= '"'.$inc_acc.'"'.$nl;

        }
        //}

       // echo $csv;

		if(strlen($csv)>0){

		$f = Factory::create('file');



        $fp = $f->open($this->config['reporte_csv'], 'w+');
        $f->write($fp, $csv);
        $f->close($fp);



		chmod($this->config['reporte_csv'], 0777);

        }
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

        $t = array('ec' => 0, 'dn' => 0);

        foreach($rsms_ec as $r) {
            $t['ec'] += $r['n'];
        }

        foreach($rsms_dn as $r) {
            $t['dn'] += $r['n'];
        }

        $e = $evs;
        //$t = $total;
        //$t_e = $total_ec;
        //$t_dn = $total_dn;

        return compact('e','t','rsms_ec','rsms_dn','charts');

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

    private function getAfeEveChart($ini,$fin,$cond_cats_ec,$cond_cats_dn,$cond_tmp,$cond_csv) {

        $afectacion = ($_SESSION['mapa_tipo'] == 'afectacion') ? true : false;
        $subtotales = array();

        $_db = $this->db_dn.'.';

        // Resumen violencia
        if ($afectacion) {
            $_sqlr = "SELECT SUM(victim_cant) AS sum,
                category_title AS cat, category_color AS color, c.id AS cat_id,
                DAY(incident_date) AS dia, MONTH(incident_date) AS mes, YEAR(incident_date) AS year,
                victim_ethnic_group_id, victim_gender_id, victim_age_id, victim_condition_id
                FROM victim v
                JOIN incident_category ic ON v.incident_category_id = ic.id
                JOIN category c ON ic.category_id = c.id
                JOIN incident i ON ic.incident_id = i.id
                JOIN location AS l ON l.id = i.location_id
                WHERE $cond_csv";

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
                JOIN location AS l ON l.id = i.location_id
                WHERE $cond_csv";

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
                JOIN %slocation AS l ON l.id = i.location_id";

            $_cond_tmp = " WHERE $cond_csv";

            // Datos extras de afectacion sigpad
            $_sqliex = "SELECT SUM(REPLACE(REPLACE(form_response,'.',''),',','')) AS sum, ff.field_name
                FROM ".$_db."form_response f
                JOIN %sform_field ff ON ff.id = f.form_field_id
                JOIN %sincident i ON f.incident_id = i.id
                JOIN %sincident_category ic ON ic.incident_id = i.id
                JOIN %scategory c ON ic.category_id = c.id
                JOIN %slocation AS l ON l.id = i.location_id
                ".$_cond_tmp." GROUP BY form_field_id ";

            $_sqliex = sprintf($_sqliex,$_db,$_db,$_db,$_db,$_db,$cond_cats_dn);

            $_rsex = $this->db->open($_sqliex);
            $subtotales = array();
            while($_rowex = $this->db->FO($_rsex)) {
                $subtotales['dn'][$_rowex->field_name] = $_rowex->sum*1;
            }

            $_sqlr .= $_cond_tmp;

            $_sql = $_sqlr." AND form_field_id = 4 GROUP BY category_id
                              ORDER BY sum DESC";

            $_sql_chart_dn = $_sqlr ." GROUP BY %s ORDER BY year,mes,dia";

        }
        else {
            $_sqlr = "SELECT COUNT(i.id) AS sum, category_title AS cat, category_color AS color,
                DAY(incident_date) AS dia, MONTH(incident_date) AS mes, YEAR(incident_date) AS year
                FROM %sincident i
                JOIN %sincident_category ic ON i.id = ic.incident_id
                JOIN %scategory c ON ic.category_id = c.id
                JOIN %slocation AS l ON l.id = i.location_id
                WHERE $cond_csv";

            $_sql = $_sqlr ." GROUP BY category_id
                              ORDER BY sum DESC";

            $_sql_chart_dn = $_sqlr ." GROUP BY %s ORDER BY year,mes,dia";
        }

        $_sqlidn = sprintf($_sql,$_db,$_db,$_db,$_db,$cond_cats_dn);

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
            $data[] = array('id' => 'ec', 'name' => 'Violencia',
                                                   'data' => $data_lines,
                                                   'color' => $color_v,
                                                    );
        }


        // Desastres
        $_sqlidnc = sprintf($_sql_chart_dn,$_db,$_db,$_db,$_db,$cond_cats_dn, $group_by);
        //echo $_sqlidnc;

        $data_lines = array();
        $_rs = $this->db->open($_sqlidnc);
        while($_row = $this->db->FO($_rs)) {

            $date = strtotime($_row->year.'-'.$_row->mes.'-'.$_row->dia) * 1000; // Tiene que ser en milisegundos
            $data_lines[] = array($date ,$_row->sum*1);
        }

        if (!empty($data_lines)) {
            $data[] = array('id' => 'dn',
                                   'name' => 'Desastres',
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
                    $_num = $_row->sum*1;
                    $data_pie_ethnic[] = array($ethnic_groups[$_row->victim_ethnic_group_id],$_num);

                    if ($_row->victim_ethnic_group_id == 1) {
                        $subtotales['ec']['indigenas'] = $_num;
                    }
                    else if ($_row->victim_ethnic_group_id == 2) {
                        $subtotales['ec']['afros'] = $_num;
                    }
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

                    $_num = $_row->sum*1;
                    $data_pie_gender[] = array($genders[$_row->victim_gender_id],$_num);

                    if ($_row->victim_gender_id == 1) {
                        $subtotales['ec']['mujeres'] = $_num;
                    }
                    else if ($_row->victim_gender_id == 2) {
                        $subtotales['ec']['hombres'] = $_num;
                    }
                }
            }

            // Pie de edad
            $group_by = 'victim_age_id';
            $_sqliecc = sprintf($_sql_chart_ec,$cond_cats_ec, $group_by);

            // Consulta age
            $_sql = "SELECT * FROM victim_age";
            $_rs = $this->db->open($_sql);
            while($_row = $this->db->FO($_rs)) {
                $ages[$_row->id] = $_row->age;
            }

            $data_pie_age = array();
            $_rs = $this->db->open($_sqliecc);
            while($_row = $this->db->FO($_rs)) {
                if (!empty($_row->victim_age_id)) {

                    $_num = $_row->sum*1;
                    $data_pie_age[] = array($ages[$_row->victim_age_id],$_num);

                    if ($_row->victim_age_id == 3) {
                        $subtotales['ec']['menores'] = $_num;
                    }
                }
            }

            // Pie de condicion
            $group_by = 'victim_condition_id';
            $_sqliecc = sprintf($_sql_chart_ec,$cond_cats_ec, $group_by);

            // Consulta conditions
            $_sql = "SELECT * FROM victim_condition";
            $_rs = $this->db->open($_sql);
            while($_row = $this->db->FO($_rs)) {
                $conditions[$_row->id] = $_row->condition;
            }

            $data_pie_condition = array();
            $_rs = $this->db->open($_sqliecc);
            while($_row = $this->db->FO($_rs)) {
                if (!empty($_row->victim_condition_id)) {
                    $_num = $_row->sum*1;
                    $data_pie_condition[] = array($conditions[$_row->victim_condition_id],$_num);

                    if ($_row->victim_condition_id == 2) {
                        $subtotales['ec']['civiles'] = $_num;
                    }
                }
            }

            $charts[1] = array('title' => 'Victimas por grupo poblacional',
                'data' => $data_pie_ethnic
                             );

            $charts[2] = array('title' => 'Victimas por genero',
                'data' => $data_pie_gender
                             );
            /*
            $charts[3] = array('title' => 'Victimas por edad',
                'data' => $data_pie_age
                                              );

            $charts[4] = array('title' => 'Victimas por condicion',
                'data' => $data_pie_condition
            );
             */
        }

        return array($rsms_ec,$rsms_dn,$charts,$subtotales);
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
     * @param string $csv Path al archivo csv temporal
     * @param string $nom Nombre del archivo con el que se exporta
     * @param string $output Output way
     */
    public function export($t,$csv,$nom,$output='web') {

        require_once $this->lib_dir.'/phpexcel/PHPExcel/IOFactory.php';

        $objReader = PHPExcel_IOFactory::createReader('CSV');

        // If the files uses a delimiter other than a comma (e.g. a tab), then tell the reader
        $objReader->setDelimiter("~");
        $objReader->setLineEnding("\r\n");
        // If the files uses an encoding other than UTF-8 or ASCII, then tell the reader
        //$objReader->setInputEncoding('ISO-8859-1');

        $objPHPExcel = $objReader->load($this->config['cache_reportes'].'/'.$csv.'.csv');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

        switch($output) {
            case 'web':
                header("Content-type: application/vnd.ms-excel");
                header("Content-Disposition: attachment; filename=\"".$nom.".xls\"");
                header("Cache-Control: max-age=0");
                $objWriter->save('php://output');
            break;
            case 'static':
                $objWriter->save($nom);
            break;
        }

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
     * Consulta url de eventos creados o modificados en el dia
     *
     * @return array $eventos
     */
    public function genCachePdfDiario() {

        ini_set('max_execution_time', 0);

        $ch = curl_init();
        $cond_date = 'DATE(incident_dateadd) = DATE(NOW() - INTERVAL 1 DAY)';

        // Violencia armada
        $sql = "SELECT i.id, source_reference AS url
                FROM incident AS i
                JOIN source_detail AS s
                ON i.id = s.incident_id
                WHERE $cond_date
                AND source_reference LIKE 'http:%'";

        $rs = $this->db->open($sql);
        while ( $row = $this->db->FO($rs)) {
            $this->getPDF($ch, $row->id, $row->url, 'v');
            sleep(10);
        }

        // Desatres
        $db = $this->db_dn;
        $sql = "SELECT i.id, media_link AS url
                FROM $db.incident AS i
                JOIN $db.media AS m
                ON i.id = m.incident_id
                WHERE $cond_date
                AND media_type = 4 AND media_link LIKE 'http:%'";

        $rs = $this->db->open($sql);
        while ( $row = $this->db->FO($rs)) {
            $this->getPDF($ch, $row->id, $row->url, 'd');
            sleep(10);
        }

        curl_close($ch);
    }

    /*
     * Crea conexion a servidor desarrollo y guarda pdf
     *
     * @param object $ch
     * @param int $id
     * @param string $url
     * @param string f Nombre de la carpeta en /ss donde se guarda el pdf, 'v', 'd'
     *
     */
    private function getPDF($ch, $id, $url, $f) {

        $w3hx = 1;
        $vars = compact('w3hx','id','u');

        $h2pdf = "https://monitor.salahumanitaria.co/html2pdf/index.php?w3hx=1&id=$id&u=$url";

        //echo $h2pdf;

        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //Set curl to return the data instead of printing it to the browser.
        curl_setopt($ch, CURLOPT_URL, $h2pdf);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  2);

        $pdf = curl_exec($ch);

        if (False !== $pdf && !empty($pdf)) {
            // Guarda pdf
            file_put_contents($this->config['cache_pdf']."/$f/$id.pdf", $pdf);
        }

    }

    /*
     * Genera cache de reportes
     *
     */
    public function genCacheReportesDiario() {

        $reporte_tmp = $this->config['reporte_csv'];
        $ayer = 'DATE(NOW() - INTERVAL 1 DAY) ';
        $cond_date = "DATE(incident_datemodify) = $ayer OR DATE(incident_dateadd) = $ayer";
        $yyyy = date('Y');

        $dbs = array('violencia' => '', 'desastres' => $this->db_dn.'.');

        for ($a=$yyyy;$a>=$this->config['yyyy_ini'];$a--) {

            foreach($dbs as $d => $db) {

                // Check if there are modificated incidents at the year
                $sql = "SELECT COUNT(id) AS n FROM ".$db."incident WHERE YEAR(incident_date) = $a AND incident_active = 1 AND ($cond_date)";

                //echo "$sql\n";

                $rs = $this->db->open($sql);
                $row = $this->db->FO($rs);

                $reporte = $this->config['cache_reportes'].'/'."monitor-eventos-$a-$d.xls";

                if (!empty($row->n) || file_exists($reporte) === false) {

                    $ini = mktime(0,0,0,1,1,$a);
                    $fin = mktime(0,0,0,12,31,$a);
                    $cats = '';
                    $states = '';

					//echo $a."</br>";
					//echo $ini."</br>";
					//echo $fin."</br>";

                    list($ini,$fin,$cond_cats_ec,$cond_cats_dn,$cond_tmp,$cond_csv) = $this->getConditions($ini, $fin, $cats, $states);

                    $this->downloadIncidents($d[0],compact('cond_csv','cond_cats_dn','cond_cats_ec'));
                   $this->export('xls','incidentes', $reporte,'static');

                    //echo "Listo = $a - $d \n";

                }
            }
        }
    }

    /*
     * Genera cache de totales por año-categoria
     *
     */
    public function genCacheTotalesDiario() {

        $totales_csv = $this->config['cache_reportes'].'/totales.csv';


		//echo $totales_csv."</br>";

        $ayer = 'DATE(NOW() - INTERVAL 1 DAY) ';
        $cond_date = "DATE(incident_datemodify) = $ayer OR DATE(incident_dateadd) = $ayer";
        $yyyy = date('Y');
        $limi = '~';
        $nl = "\r\n";

        $dbs = array('violencia' => '', 'desastres' => $this->db_dn.'.');

        // Resumen violencia
        // Afectacion
        $_sql['violencia']['af'] = "SELECT SUM(victim_cant) AS sum, category_title AS cat
            FROM %svictim v
            JOIN %sincident_category ic ON v.incident_category_id = ic.id
            JOIN %scategory c ON ic.category_id = c.id
            JOIN %sincident i ON ic.incident_id = i.id
            WHERE %s";

        // Incidentes
        $_sql['violencia']['e'] = "SELECT COUNT(i.id) AS sum, category_title AS cat
            FROM %sincident i
            JOIN %sincident_category ic ON i.id = ic.incident_id
            JOIN %s.category c ON ic.category_id = c.id
            JOIN %slocation AS l ON l.id = i.location_id
            WHERE %s";

        //echo $_sqliec;

        // Resumen desastres
        // Afectacion
        // Form id = 4, # personas
        $_sql['desastres']['af'] = "SELECT SUM(REPLACE(REPLACE(form_response,'.',''),',','')) AS sum, category_title AS cat
            FROM %sform_response f
            JOIN %sincident i ON f.incident_id = i.id
            JOIN %sincident_category ic ON ic.incident_id = i.id
            JOIN %scategory c ON ic.category_id = c.id
            WHERE form_field_id = 4 AND %s";

        $_sql['desastres']['e'] = "SELECT COUNT(i.id) AS sum, category_title AS cat
            FROM %sincident i
            JOIN %sincident_category ic ON i.id = ic.incident_id
            JOIN %scategory c ON ic.category_id = c.id
            JOIN %slocation AS l ON l.id = i.location_id
            WHERE %s";

        //echo $_sqlidn;

		//echo "y:".$yyyy."</br>".$this->config['yyyy_ini']."</br>";

        for ($a=$yyyy;$a>=$this->config['yyyy_ini'];$a--) {
            foreach($dbs as $d => $db) {

                $json = array();

                // Check if there are modificated incidents at the year
                $sql = "SELECT COUNT(id) AS n FROM ".$db."incident WHERE YEAR(incident_date) = $a AND incident_active = 1 AND ($cond_date)";

			//	echo $sql."</br>";

                $rs = $this->db->open($sql);



				$row = $this->db->FO($rs);

                $totales_html = $this->config['cache_reportes']."/totales-$a-$d.html";
                $reporte = $this->config['cache_reportes'].'/'."monitor-totales-$a-$d.xls";

                // Borrar al terminar el desarrollo
                //unlink($reporte);

                if (!empty($row->n) || file_exists($reporte) === false) {

                    $ini = date('Y-m-d', mktime(0,0,0,1,1,$a));
                    $fin = date('Y-m-d', mktime(0,0,0,12,31,$a));

                    $csv = "$a$nl";
                    $csv .= $limi."Afectados".$limi."Eventos".$nl;

                    $cond_if = "incident_date >= '$ini' AND incident_date <= '$fin' AND category_visible = 1";

                    foreach(array('af','e') as $afv) {

                        $_sqlafv = $_sql[$d][$afv];

                        // Total
                        $_sqlv = sprintf($_sqlafv,$db,$db,$db,$db,$cond_if);
                        //echo $_sqlv;
                        $_rs = $this->db->open($_sqlv);
                        $_row = $this->db->FO($_rs);

                        $json['total'][$afv] = (isset($_row->sum)) ? number_format($_row->sum,0,'',',') : '';

                        // Total por categoria
                        $_sqlv = sprintf($_sqlafv,$db,$db,$db,$db,$cond_if." GROUP BY category_id ORDER BY sum DESC");
                        //echo $_sqlv;

                        $_rs = $this->db->open($_sqlv);

                        while($_row = $this->db->FO($_rs)) {
                            $json['categorias'][$_row->cat][$afv] = number_format($_row->sum,0,'',',');
                        }
                    }

                    $total_af = $json['total']['af'];
                    $total_e = $json['total']['e'];

                    // Popula csv
                    $csv .= "Total".$limi.$total_af.$limi.$total_e.$nl;
                    $html = "<tr><td><b>Total</b></td><td><b>".$total_af."</b></td><td><b>".$total_e."</b></td></tr>";

                    if (isset($json['categorias'])) {
                        foreach($json['categorias'] as $cat => $v) {
                            $af = (isset($v['af'])) ? $v['af'] : '';
                            $e = (isset($v['e'])) ? $v['e'] : '';

                            $csv .= $cat.$limi.$af.$limi.$e.$nl;
                            $html .= "<tr><td>$cat</td><td>$af</td><td>$e</td></tr>";

                        }
                    }

                    file_put_contents($totales_html, $html);
                    file_put_contents($totales_csv, $csv);

					//echo "ht:".$totales_html."</br>";
					//echo "cs:".$totales_csv."</br>";

					chmod($totales_html, 0777);
					chmod($totales_csv, 0777);

                    $this->export('xls','totales', $reporte,'static');

                    echo "Listo = $a - $d \n";

                }

            }

        }
    }

    /*
     * Consulta totales por periodo
     *
     * @param string $vd violecia o desatres
     * @param string $periodo y=año, s=semestre, t=trimestre
     * @param int $valor
     *
     */
    public function totalPeriodo($vd, $periodo, $valor) {

        $totales_html = $this->config['cache_reportes']."/totales-$valor-$vd.html";

        echo file_get_contents($totales_html);
    }

    /*
     * Coloca variable en sesión
     * @param string $var
     * @param string $valor
     */
    public function setSessionVar($var, $valor) {

        $_SESSION[$var] = $valor;

    }

    /*
     * Retorna un json de cache o de fuente original
     * @param string $url_base Url para json en ushahidi
     * @param array $qs Querystring
     *
     * @return string $json
     */
    public function genJson($url_base, $qs) {

        $callback = $qs['callback'];

        // Para archivo se eliminan el callback
        // para que el archivo no cambie de nombre
        unset($qs['callback']);
        unset($qs['_']);

        $url = $url_base.http_build_query($qs);

        $file = $url_base.http_build_query($qs);

        $n = md5(str_replace('/','-',$file));
        //echo $n;
        $path2file = $this->config['cache_json']['path'].'/'.$n;

        if (!file_exists($path2file)) {

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //Set curl to return the data instead of printing it to the browser.
            curl_setopt($ch, CURLOPT_URL, 'https://' . $url);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  2);

            //echo 'http://'.$url;

            $json = curl_exec($ch);
            curl_close($ch);

            file_put_contents($path2file, $json);

        }
        else {
            $json = file_get_contents($path2file);
        }

        // El callback
        return $callback. '(' . $json . ');';
    }

    /*
     * Comprueba si se debe generar el json
     *
     * @param string $file absolute path to cached file
     *
     * @return boolean
     */
    public function checkCacheJson() {

        $rsv = $this->db->open("SELECT `value` AS v FROM settings WHERE `key` = 'monitor_cache_json'");
        $rsd = $this->db->open("SELECT `value` AS v FROM ".$this->db_dn.".settings WHERE `key` = 'monitor_cache_json'");

        $rowv = $this->db->FO($rsv);
        $rowd = $this->db->FO($rsd);

        if ($rowv->v == '1' || $rowd->v == '1') {

            // Borra archivos estaticos
            array_map('unlink', glob($this->config['cache_json']['path'].'/*'));

            // Coloca en 0 el flag en las 2 dbs
            $this->db->Execute("UPDATE settings SET `value` = 0 WHERE `key` = 'monitor_cache_json'");
            $this->db->Execute("UPDATE ".$this->db_dn.".settings SET `value` = 0 WHERE `key` = 'monitor_cache_json'");
        }
    }

    /*
     * Limpia texto para exportar
     *
     * @param string $txt
     *
     * @return text
     */
    public function cleanText($txt) {

        return (empty($txt)) ? '' : preg_replace( "/\r\n|\r|\n|\"/", "", $txt);

    }


}

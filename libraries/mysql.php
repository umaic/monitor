<?php
//class DBConn {
class Mysql {

	private $conn;
	private $user;
	private $pass;
	private $db;
	private $debug = 0;
	private static $instance;

	private function __construct() {

        $this->host = '192.168.1.60';
        $this->user = 'monitor';
        $this->pass = 'Myp4$M0';
        $this->db = "violencia_armada";

        //$this->conn = mysqli_connect($this->host,$this->user,$this->pass,$this->db);

        try {
            $this->conn = new PDO("mysql:host=$this->host;dbname=$this->db", $this->user, $this->pass);
            $this->conn->exec("SET CHARACTER SET utf8");
        } catch (PDOException $e) {
            print "Error!: " . $e->getMessage() . "<br/>";
            die();
        }

	}

	// Singleton Design Pattern
	public static function getInstance(){

        if (!isset(self::$instance))	
            self::$instance = new self;

		return self::$instance;
	}

	public function open($sql){
        $_rs = $this->conn->prepare($sql);
		
		if ($this->debug == 1)
			file_put_contents ('/tmp/monitor_sql.txt' , "-Open->".$sql."\r\n", FILE_APPEND);  
        $_rs->execute();
        
        return $_rs;

	}
    
    public function FO($result) {
        return $result->fetch(PDO::FETCH_OBJ);
	}

	public function RowCount($result) {
	}


	public function FetchAssoc($Result) {
	}

	public function Execute($query) {
        $this->conn->exec($query);
		
		if ($this->debug == 1)
			file_put_contents ('/tmp/monitor_sql.txt' , "-Exec->".$query."\r\n", FILE_APPEND);  
	}

	public function GetGeneratedID() {
	}

	public function FreeMemory($Result) {
	}

}

?>

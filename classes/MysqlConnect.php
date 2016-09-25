<?
require_once('dbparams.php');

class mysqlConnect{
	static public $connect_inst;
	
	function __construct($hostname, $username, $password, $dbname){
		if(empty(self::$connect_inst)){ //!self::$connect_inst instanceof MySQLi) {
			
			self::$connect_inst=new MySQLi($hostname, $username, $password, $dbname);
			
			if (self::$connect_inst->connect_error) {
   				echo "Ќевозможно соединитьс€ с базой данных!";
				die();
			}
			
			self::$connect_inst->query("set names cp1251");
		}
	}
	
	

	public function GetConnection(){
		return mysqlConnect::$connect_inst;
	}
	
}
?>
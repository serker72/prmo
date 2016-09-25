<?
require_once('global.php');
require_once('dbparams.php');
require_once('MysqlConnect.php');
//require_once('MysqlSet.php');

//класс для запроса к бд
class nonSet{
	protected $query='';
	protected $result=NULL;
	protected $id=0;

	static public $inst_count;
	
	function __construct($query){
		$con = new mysqlConnect(HostName,UserName,Password,DBName);
		$this->query=$query;
		$connection=$con->GetConnection();
		$this->result=$connection->query($query);
		//echo $query; 
		if(DEBUG_INFO) {
			
			echo mysqli_error();
		}
		if(strlen(mysql_error())>0){
			echo $query; 
			echo mysqli_error();
		}
		
		 
		$this->id=$connection->insert_id; //  mysql_insert_id();	
		
		nonSet::$inst_count=nonSet::$inst_count+1;
	}
	
	
	public function getResult(){
		return $this->id;
	}
	
	function __destruct(){
		//unset($con);
	}
	
	public function ShowInstCount(){
		return nonSet::$inst_count;
	}
};
?>
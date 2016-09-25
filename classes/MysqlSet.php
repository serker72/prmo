<?
require_once('global.php');
require_once('dbparams.php');
require_once('MysqlConnect.php');

//класс набор данных
class mysqlSet{
	protected $rec_no=0;
	protected $result=NULL;
	protected $rec_no_unfiltered=0;
	static public $inst_count;
	
	
	function __construct($query,$up_to=NULL,$from=NULL,$query_count_all=NULL){
		$con = new mysqlConnect(HostName,UserName,Password,DBName);
		
		if($up_to!==NULL){
			if($from!==NULL){
				$query.=' limit '.$from.', '.$up_to;
			}else{
				$query.=' limit '.$up_to;
			}
		}

		$connection=$con->GetConnection();
		
		
		
		if(DEBUG_INFO) {
			if($connection->errno>0){
				//echo mysqlSet::$inst_count.') '.$query.'<BR>';
				echo $query;
				echo $connection->error;	
			}
		}
		$this->result=$connection->query($query); //mysql_query($query);
		$this->rec_no=$this->result->num_rows;
	//	echo $query;
		
		//подсчет числа всех-всех записей
		if($query_count_all!=NULL){
			$someres=$connection->query($query_count_all);
				echo $connection->error;	
		//	if(DEBUG_INFO) {
				if($connection->errno>0){
					echo $query_count_all;
					echo $connection->error;	
				}
			//}
			$r=$someres->fetch_array();
			$this->rec_no_unfiltered=$r[0];
			//if($someres instanceof MySQLi_Result) $someres->free();
		}
		mysqlSet::$inst_count=mysqlSet::$inst_count+1;
		
	}
	
	
	public function ShowInstCount(){
		return mysqlSet::$inst_count;
	}
	
	//вернуть результат запроса
	public function getResult(){
		return $this->result;
	}
	
	//вернуть кол-во рядов в результате
	public function getResultNumRows(){
		return $this->rec_no;
	}
	
	//вернуть кол-во рядов в результате нефильтованном
	public function getResultNumRowsUnf(){
		return $this->rec_no_unfiltered;
	}
	
	function __destruct(){
		//if($this->result instanceof MySQLi_Result) $this->result->free();
	}
};
?>
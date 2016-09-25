<?
 
require_once('abstractitem.php');

//абстрактный итем (без языковых таблиц)
class ProgramItem extends AbstractItem{
	 
	
	//установка всех имен
	protected function init(){
		$this->tablename='program';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_active';	
		//$this->subkeyname='mid';	
	}
	
	
	//получить по айди и коду видимости
	public function GetItemById($id,$mode=0){
		
		if($id===NULL) return false;
		
		
		$debug_prefix=''; if($this->debug) $debug_prefix='debug_';
		
		$connection=new MySQLi(ProgramHostName, ProgramUserName, ProgramPassword, ProgramUserName);
		$connection->query('set names cp1251');
		
		
		
		if($mode==0) $result=$connection->query('select * from '.$this->tablename.' where id='.$id.';');
		else $result=$connection->query('select * from '.$this->tablename.' where '.$this->vis_name.'=1 and id='.$id.';');
		

		 
		$rc=$result->num_rows;
		unset($item);
		if($rc!=0){
			$res=mysqli_fetch_array($result);
			$this->item= Array();
			foreach($res as $key=>$val){
				$this->item[$key]=$val;
			}
			
			return $this->item;
		} else {
			$this->item=NULL;
			return false;
		}
	}
	
}
?>
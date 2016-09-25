<?
require_once('NonSet.php');
require_once('MysqlSet.php');

//абстрактный элемент
class AbstractItem{
	protected $tablename;//='mmenu';
	protected $item;//=NULL;
	protected $pagename;//='viewsubs.php';	
	protected $vis_name;
	protected $subkeyname;
	
	const SET_NULL=NULL;
	
	
	public function __construct(){
		$this->init();
	}
	
	//установка всех имен
	protected function init(){
		$this->tablename='table';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
	//установка имени таблицы
	public function SetTableName($name){
		$this->tablename=$name;
		return true;
	}
	
	//добавить 
	public function Add($params){
		$names='';
		$vals='';
		
		
		foreach($params as $key=>$val){
			if($names=='') $names.=$key;
			else $names.=','.$key;
			
			if($vals=='') {
				if($val===self::SET_NULL) $vals.='NULL';
				else  $vals.='"'.$val.'"';
			}else{
				if($val===self::SET_NULL)  $vals.=','.' NULL';
				else  $vals.=','.'"'.$val.'"';
			}
		}
		$query='insert into '.$this->tablename.' ('.$names.') values('.$vals.');';
		$it=new nonSet($query);
		//echo $query;
		
		$code=$it->getResult();
		unset($it);
		return $code;
	}
	
	//править
	public function Edit($id,$params){
		if(count($params)==0) return;
		$qq='';
		foreach($params as $key=>$val){
			if($qq==''){
				 if($val===self::SET_NULL) $qq.=$key.'=NULL'; 
				 else $qq.=$key.'="'.$val.'"';
			}else{
				 
				if($val===self::SET_NULL) $qq.=','.$key.'=NULL';
				else $qq.=','.$key.'="'.$val.'"';
			}
		}
		$query='update '.$this->tablename.' set '.$qq.' where id="'.$id.'";';
		$it=new nonSet($query);
		
		
		
		unset($it);
	}
	
	//удалить
	public function Del($id){
		
		$query = 'delete from '.$this->tablename.' where id='.$id.';';
		$it=new nonSet($query);
		
		unset($it);				
		
		$this->item=NULL;
	}	
	
	
	
	
	//получить по айди и коду видимости
	public function GetItemById($id,$mode=0){
		if($id===NULL) return false;
		if($mode==0) $item=new mysqlSet('select * from '.$this->tablename.' where id='.$id.';');
		else $item=new mysqlSet('select * from '.$this->tablename.' where '.$this->vis_name.'=1 and id='.$id.';');
		

		$result=$item->getResult();
		$rc=$item->getResultNumRows();
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
	
	//получение первого итема по набору полей
	public function GetItemByFields($params){
		
		$qq='';
		foreach($params as $key=>$val){
			if($qq=='') $qq.=$key.'="'.$val.'" ';
			else $qq.=' and '.$key.'="'.$val.'" ';
		}
		
		$item=new mysqlSet('select * from '.$this->tablename.' where '.$qq.';');
		$result=$item->getResult();
		$rc=$item->getResultNumRows();
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
	
	
	//получение первого итема по набору полей
	public function GetItemByFieldsWithExcept($params, $except_params){
		
		$qq='';
		foreach($params as $key=>$val){
			if($qq=='') $qq.=$key.'="'.$val.'" ';
			else $qq.=' and '.$key.'="'.$val.'" ';
		}
		
		
		$qq1='';
		foreach($except_params as $key=>$val){
			 $qq1.=' and '.$key.'<>"'.$val.'" ';
		}
		
		$item=new mysqlSet('select * from '.$this->tablename.' where '.$qq.' '.$qq1.';');
		$result=$item->getResult();
		$rc=$item->getResultNumRows();
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
	
	
	public function GetPageName(){
		return $this->pagename;	
	}
	
	public function GetTableName(){
		return $this->tablename;
	}	

}
?>
<?
require_once('abstractitem.php');

//юзер итем
class UserItem extends AbstractItem{
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='user';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_active';	
		//$this->subkeyname='mid';	
	}
	
	/*
	
	//добавить 
	public function Add($params){
		
		$login=$params['login'];
		$cou=$this->CheckLogin($login,NULL);
		
		if($cou==0) return AbstractItem::Add($params);
		else return false;
	}
	
	
	
	//править
	public function Edit($id,$params){
		//если логин содержится в параметрах, то проверять его
		if(isset($params['login'])){
			$login=$params['login'];
			$cou=$this->CheckLogin($login,$id);
			if($cou==0) {
				AbstractItem::Edit($id,$params);
				return true;
			}else return false;
		}else{
			AbstractItem::Edit($id,$params);
			return true;
		}
	}
	
	
	//подсчет количества клиентов с таким логином
	public function CheckLogin($login,$except=NULL){
		$sql='select count(*) from '.$this->tablename.' where login="'.$login.'"';
		
		//echo $sql;
		if($except!==NULL) $sql.=' and id<>"'.$except.'"';
		
		$set=new MysqlSet($sql);
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);
		
		return $f['count(*)'];
	}
	
	*/
	
}
?>
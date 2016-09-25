<?
require_once('NonSet.php');
require_once('MysqlSet.php');
require_once('abstractgroup.php');

// абстрактная группа
class CacheReportsGroup extends  AbstractGroup {
 
	//установка всех имен
	protected function init(){
		$this->tablename='cache_reports';
		$this->pagename='view.php';		
		$this->subkeyname='mid';	
		$this->vis_name='is_shown';		
		
		$this->_auth_result=NULL;
		
	}
	
	//получение итемov по набору полей
	public function GetItemsByFieldsArr($params){
		$res=array();
		
		$qq='';
		foreach($params as $key=>$val){
			if($qq=='') $qq.=$key.'="'.$val.'" ';
			else $qq.=' and '.$key.'="'.$val.'" ';
		}
		
		$sql='select p.*, s.is_active as s_is_active, s.full_name, s.org_id, s.is_org, opf.name as opf_name from '.$this->tablename.' as p
		left join user as u on u.id=p.user_id
		left join supplier as s on s.id=p.supplier_id
		left join opf as opf on opf.id=s.opf_id
		
		 where '.$qq.';';
		//echo $sql;
		
		
		$item=new mysqlSet($sql);
		$result=$item->getResult();
		$rc=$item->getResultNumRows();
		//unset($item);
		for($i=0;$i<$rc; $i++){
			$f=mysqli_fetch_array($result);
			
			foreach($res as $k=>$v){
				$f[$k]=stripslashes($v);	
			}
			$res[]=$f;
		}
		
		
		return $res;
	}
	
}
?>
<?
require_once('abstractgroup.php');
require_once('user_s_item.php');
require_once('supcontract_item.php');

// группа договоров поставщика
class AnFillSupContractGroup extends AbstractGroup {
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='supplier_contract';
		$this->pagename='view.php';		
		$this->subkeyname='user_id';	
		$this->vis_name='is_shown';		
		
		
		
	}
	
	
	public function GetItemsByIdArr($id, $current_id=0){
		$arr=array();
		$_ui=new UserSItem;
		
		$_itm=new SupContractItem;
		
		$flt='';
		
		  $flt.=' and is_incoming="0" ';
		
		$sql='select * from '.$this->tablename.' where '.$this->subkeyname.'="'.$id.'" '.$flt.' order by is_incoming desc, id asc';
		
		$set=new MysqlSet($sql);
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			if($current_id==0){
				$f['is_current']=(bool)($f['is_basic']==1);
			}else $f['is_current']=(bool)($f['id']==$current_id);
			
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			//запросить, кто утв-л и когда
			if($f['has_dog']==1){
				$uu=$_ui->GetItemById($f['has_dog_confirm_user_id']);
				$f['user_has_dog']= $uu['position_s'].' '.$uu['name_s'].' '.' '.$uu['login'].' '.date("d.m.Y H:i:s",$f['has_dog_confirm_pdate']);		
				
			}
			
			
			$f['can_delete']=$_itm->DocCanAnnul($f['id'], $reason);
			$f['cannot_del_reason']=$reason;
			
			
			$arr[]=$f;
		}
		
		return $arr;
	}
	
}
?>
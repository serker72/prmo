<?
require_once('abstractitem.php');

//pol'zovateli i org
class SupplierToUser1 extends AbstractItem{
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='supplier_to_user'; //position - storage
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
	
	//добавить пользователей на орг.
	public function AddUsersToSupplierArray($book_id, array $questions){
		/*$sql='delete from '.$this->tablename.' where org_id="'.$book_id.'"';
		//echo $sql; die();
		$ns=new NonSet($sql);
		
		if(count($sects)>0){
			$sc=array();
			foreach($sects as $v){
				$sc[]='('.$book_id.', '.$v.')';	
			}
			$ss=join(', ',$sc);
			$sql='insert into '.$this->tablename.' (org_id,user_id) values '.$ss;
			$ns=new NonSet($sql);
		}*/
		
		
		//$_kpi=new QuestionUserItem;
		
		$log_entries=array();
		
		//сформируем список старых позиций
		$old_positions=array();
		//$old_positions=$this->GetQuestionsArr($user_id);
		$sql='select * from '.$this->tablename.' where org_id="'.$book_id.'" ';
		$set=new mysqlSet($sql);
		$tc=$set->GetResultNumRows();
		
		$rs=$set->GetResult();
		for($i=0;$i<$tc;$i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$old_positions[]=$f;
		}
		
		
		
		foreach($questions as $k=>$v){
			$kpi=$this->GetItemByFields(array('org_id'=>$book_id,'user_id'=>$v));
			
			if($kpi===false){
				//dobavim pozicii	
				
				
				$add_array=array();
				$add_array['user_id']=$v;
				$add_array['org_id']=$book_id;
				
				
				
				
				$this->Add($add_array);//, $add_pms);
				
				$log_entries[]=array(
					'action'=>0,
					'user_id'=>$v,
					'org_id'=>$book_id
				);
				
			}/*
			секция редактирования не нужна!
			*/
		}
		
		//найти и удалить удаляемые позиции:
		//удал. поз. - это позиция, которой нет в массиве $positions
		$_to_delete_positions=array();
		foreach($old_positions as $k=>$v){
			//$v['id']
			$_in_arr=false;
			foreach($questions as $kk=>$vv){
				if($vv==$v['user_id']){
					$_in_arr=true;
					break;	
				}
			}
			
			if(!$_in_arr){
				$_to_delete_positions[]=$v;	
			}
		}
		
		//удаляем найденные позиции
		foreach($_to_delete_positions as $k=>$v){
			
			//формируем записи для журнала
			
			
			$log_entries[]=array(
					'action'=>2,
					'user_id'=>$v['user_id'],
					'org_id'=>$book_id
			);
			
			//удаляем позицию
			$this->Del($v['id']);
		}
		
		
		//необходимо вернуть массив измененных записей для журнала
		return $log_entries;
		
		
		
		
		
	}
	
	
	
	
	
	
	
	
	
	//добавить пользователя на организации
	public function AddSuppliersToUserArray($book_id, array $sects){
		$sql='delete from '.$this->tablename.' where user_id="'.$book_id.'"';
		$ns=new NonSet($sql);
		if(count($sects)>0){
			$sc=array();
			foreach($sects as $v){
				$sc[]='('.$book_id.', '.$v.')';	
			}
			$ss=join(', ',$sc);
			$sql='insert into '.$this->tablename.' (user_id, org_id) values '.$ss;
			$ns=new NonSet($sql);
		}
		
		
	}
	
	
	
	
	//список категорий по айди книги
	public function GetBookCategsArr($id, $is_active=0){
		$arr=array();
		if($is_active==1){
			$sql='
		select distinct c.id as id, c.name_s as name, "1" as is_in, c.is_active as is_active from user as c inner join '.$this->tablename.' as bc on c.id=bc.user_id where bc.org_id="'.$id.'" and c.is_active=1 order by name asc';
		}else $sql='
		select distinct c.id as id, c.name_s as name, "1" as is_in, c.is_active as is_active from user as c inner join '.$this->tablename.' as bc on c.id=bc.user_id where bc.org_id="'.$id.'" order by name asc';
		$set=new mysqlSet($sql);
		$tc=$set->GetResultNumRows();
		
		$rs=$set->GetResult();
		for($i=0;$i<$tc;$i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$arr[]=$f;
		}
		
		return $arr;
	}
	
	
	//список пол-лей по айди орг.
	public function GetAllOrgUsersArr($id){
		$arr=array();
		$sql='
		
		(select distinct c.id as id, c.name_s as name, c.login, c.position_s, c.is_active as is_active, "0" as is_in from user as c where (c.id in(select distinct user_id from '.$this->tablename.' where org_id<>"'.$id.'") or c.id not in (select distinct user_id from '.$this->tablename.')) and c.id not in(select distinct user_id from '.$this->tablename.' where org_id="'.$id.'"))
		
		UNION
		(select distinct c.id as id, c.name_s as name, c.login, c.position_s, c.is_active as is_active, "1" as is_in from user as c inner join '.$this->tablename.' as bc on c.id=bc.user_id where bc.org_id="'.$id.'") order by name asc';
		$set=new mysqlSet($sql);
		$tc=$set->GetResultNumRows();
		
		$rs=$set->GetResult();
		for($i=0;$i<$tc;$i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$arr[]=$f;
		}
		
		return $arr;
	}
	
	
	//список организаций по айди пол-ля
	public function GetAllUserOrgsArr($id){
		$arr=array();
		$sql='
		
		(select distinct c.id as id, c.name as name, c.is_active as is_active, "0" as is_in from supplier as c where (c.id in(select distinct org_id from '.$this->tablename.' where user_id<>"'.$id.'") or c.id not in (select distinct org_id from '.$this->tablename.')) and c.id not in(select distinct org_id from '.$this->tablename.' where user_id="'.$id.'"))
		
		UNION
		(select distinct c.id as id, c.name as name, c.is_active as is_active, "1" as is_in from supplier as c inner join '.$this->tablename.' as bc on c.id=bc.org_id where bc.user_id="'.$id.'") order by name asc';
		$set=new mysqlSet($sql);
		$tc=$set->GetResultNumRows();
		
		$rs=$set->GetResult();
		for($i=0;$i<$tc;$i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$arr[]=$f;
		}
		
		return $arr;
	}
	
	//список айди организаций по айди пол-ля
	public function GetUserOrgIds($user_id){
		
		$arr=array();
		$sql='select distinct c.id as id, c.name as name, c.is_active as is_active
		 from supplier as c 
		 inner join '.$this->tablename.' as bc on c.id=bc.org_id 
		where bc.user_id="'.$user_id.'" order by name asc';
		$set=new mysqlSet($sql);
		$tc=$set->GetResultNumRows();
		
		$rs=$set->GetResult();
		for($i=0;$i<$tc;$i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$arr[]=$f['id'];
		}
		
		return $arr;
	}
	
	//список  организаций по айди пол-ля
	public function GetUserOrgs($user_id){
		
		$arr=array();
		$sql='select distinct c.id as id, c.full_name, c.is_active as is_active, opf.name as opf_name
		 from supplier as c 
		 left join opf on c.opf_id=opf.id
		 inner join '.$this->tablename.' as bc on c.id=bc.org_id 
		where bc.user_id="'.$user_id.'" order by full_name asc';
		
		//echo $sql;
		
		$set=new mysqlSet($sql);
		$tc=$set->GetResultNumRows();
		
		$rs=$set->GetResult();
		for($i=0;$i<$tc;$i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$arr[]=$f;
		}
		
		return $arr;
	}
}
?>
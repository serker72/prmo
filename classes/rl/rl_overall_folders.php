<?
require_once('rl_rightitem.php');
require_once('rl_useritem.php');
require_once('rl_rightuseritem.php');
require_once('rl_recorditem.php');
require_once('rl_grobjectitem.php');
require_once('rl_man.php');



class RLOverallFolders{
	protected $user;
	protected $right;
	protected $userright;
	protected $record;
	protected $group;
	protected $man;
	protected $storage_id;
	protected $num_users;
	 
	
	function __construct(){
		$this->user=new RLUserItem;
		$this->right=new RLRightItem;
		$this->userright=new RLRightUserItem;
		$this->record=new RLRecordItem;
		$this->group=new  RLGrObjectItem;
		$this->num_users=50; //сотрудников без загрузки данных по ajax
		
		$this->man=new RLMan;
	}
	
	
	
	//построение таблицы прав на странице
	public function BuildTable($group_id, $storage_id, $usernames, $template, $do_it=true){
		$sm=new SmartyAdm;
		$this->storage_id=$storage_id;
		$sm->assign('storage_id', $storage_id);
		
		//найдем раздел
		$sql='select * from object_group where id="'.$group_id.'"';
		$set=new mysqlset($sql);
		$rs=$set->getResult();
		$f=mysqli_fetch_array($rs);
		$sm->assign('group_name', stripslashes($f['name']));
		
		if($do_it){
			$_dsi=new  DiscrRightUserItem;
			
			
			
			
			//найдем массив заданных пользователей
			$users=array();
			$sql='select * from user ';
			if(count($usernames)>0){
				$sql.=' where ';
				$_flts=array();
				foreach($usernames as $k=>$v) $_flts[]=' name_s LIKE "%'.trim($v).'%" or login LIKE "%'.trim($v).'%" ';
				
				$sql.=implode(' or ', $_flts);
				
			}
			
			$sql.='order by name_s';
			//echo $sql;
			
			$set=new mysqlset($sql);
			$rs=$set->getResult();
			$rc=$set->getResultNumRows();
			
			for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);
				foreach($f as $k=>$v) $f[$k]=stripslashes($v);
				$users[]=$f;	
			}
			
			//print_r($users);
			
			//найдем объединенный список прав
			$rights=array();
			$sql='select 
				rlc.object_id, rlc.rl_object_id,
				rl.name, rlc.has_inherits from
			rl_object as rl 
			inner join rl_connections as rlc on rl.id=rlc.rl_object_id
			inner join object as o on o.id=rlc.object_id
			where o.group_id="'.$group_id.'"
			order by rl.ord desc, rl.id asc';
			//echo $sql;
			$set=new mysqlset($sql);
			$rs=$set->getResult();
			$rc=$set->getResultNumRows();
			
			$rl_group_id=0;
			for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);
				foreach($f as $k=>$v) $f[$k]=stripslashes($v);
				if($f['has_inherits']) $rl_group_id=$f['rl_group_id'];
				
				$rights[]=$f;	
			}
			
			
			//ищем папки...
			$folders=array();
			$this->BuildFolders(0, $folders, '', $rl_group_id);
			
			//print_r($folders);
			
			//ищем ОБЩИЕ ПРАВА
			$common_rights=array();
			foreach($users as $k1=>$user){
				$_rights=array(); $_big_rights=array();
				foreach($rights as $k2=>$right){
					$userright=$_dsi->GetItemByFields(array('user_id'=>$user['id'], 'right_id'=>2, 'object_id'=>$right['object_id']));	
					
					 
					// $result=($userright!==false);
					if($userright!==false) $result=true;
					else $result=false;
					
					$_rights[]=$result;
					$_big_rights[]=array('has'=>$result,'object_id'=>$right['object_id'], 'rl_object_id'=>$right['rl_object_id'], 'name'=>$right['name']);
				}
				
				$user['rights']=$_rights;
				$user['big_rights']=$_big_rights;
				$users[$k1]=$user;
			}
			
			
			//попробуем вызвать метод для папки, пол-ля
			//BuildRow($storage_id, $group_id, $user_id, $folder_id, $template, &$rights)
			
			foreach($folders as $k=>$folder){
				$users1=array();
				foreach($users as $k1=>$user){
					if(count($users)<=$this->num_users){
						$this->BuildRow($storage_id, $group_id, $user['id'], $folder['id'], '', $rightss, false, $user);
						$user['rights']=$rightss;
					}
					$users1[$k1]=$user;
				}
				$folder['users']=$users1;
				$folders[$k]=$folder;
			
			}
			
			$sm->assign('users', $users);
			$sm->assign('folders', $folders);
			$sm->assign('rights', $rights);
		
		}
		
		
		$sm->assign('do_it', $do_it);
		$sm->assign('num_users', $this->num_users);
		$sm->assign('group_id', $group_id);
		$sm->assign('username', implode(';',$usernames));
		
		return $sm->fetch($template);
	}
	
	
	//построение ряда по пользователю и папке
	public function BuildRow($storage_id, $group_id, $user_id, $folder_id, $template, &$rights, $is_ajax=true, $user=NULL){
		if($is_ajax) $sm=new SmartyAj;
		else  $sm=new SmartyAdm;
		
		if($user===NULL) {
			$_ui=new UserSitem();
			$user=$_ui->GetItemById($user_id);	
		}
		
		$_dsi=new  DiscrRightUserItem;
		 $this->storage_id=$storage_id;
			//найдем объединенный список прав
		$rights=array();
		$sql='select 
			rlc.object_id, rlc.rl_object_id,
			rl.name, rlc.has_inherits from
		rl_object as rl 
		inner join rl_connections as rlc on rl.id=rlc.rl_object_id
		inner join object as o on o.id=rlc.object_id
		where o.group_id="'.$group_id.'"
		order by rl.ord desc, rl.id asc';
		//echo $sql;
		$set=new mysqlset($sql);
		$rs=$set->getResult();
		$rc=$set->getResultNumRows();
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			//проверить доступ
			//варианты:
			/*
			доступ в папку (has_inherits==1):
			нет у obj - вообще неактивно, доступа нет
			есть у obj - 
				проверять попапочно от верхней папки до этой
			 
			*/
			
			$has_control=$this->man->HasControl($folder_id, 'file', $this->storage_id, $template_gr_id, $rl_record_id);
				
			
				 
			 
			if($f['has_inherits']==1){
				//доступ в папку
				$userright=$_dsi->GetItemByFields(array('user_id'=>$user_id, 'right_id'=>2, 'object_id'=>$f['object_id']));	
				
				$is_inactive=false;
				
				if($userright===false) {
					 
					$f['has']=false;
					$is_inactive=$is_inactive||true;
					 
				}else{
					 
					$ids=array();
					$this->BuildFolderChain($folder_id, $ids);
					$ids=array_reverse($ids);
					$f['ids']=$ids;
					
					$has_rights=true;
					//$f['is_inactive']=false;
					foreach($ids as $kk=>$id){
						
						 
						$trr=$this->man->CheckFullAccess( $user_id, $id, $f['rl_object_id'], 'w', 'file', $this->storage_id, $has_control);
						 
						 //var_dump($trr);
						 
						 
						$has_rights=$has_rights&&$trr;
						//if(!$trr&&($id!==$folder_id)) $f['is_inactive']=true;
						if(($id!==$folder_id)) $is_inactive=$is_inactive||!$trr;
						 
					}
					//var_dump($ids);
					
					$f['has']=$has_rights;
				} 
				
				$f['is_inactive']= !$has_control || $is_inactive;
					
			}else{
				//доступ к другим функциям
				$trr=$this->man->CheckFullAccess($user_id, $folder_id, $f['rl_object_id'], 'w', 'file', $this->storage_id, $has_control);
				if($has_control){
					$has_rights=$trr;
				}else{
					$userright=$_dsi->GetItemByFields(array('user_id'=>$user_id, 'right_id'=>2, 'object_id'=>$f['object_id']));	
				
				
					if($userright===false) $has_rights=false;
					else $has_rights=true;
				}
				
				
				$f['has']=$has_rights;
				
				$f['is_inactive']=!$has_control;		 	
			}
			
			
			$rights[]=$f;	
		}
		
		$sm->assign('rights', $rights);
		$sm->assign('user_id', $user_id);
		$sm->assign('folder_id', $folder_id);
		
		$sm->assign('user', $user);
	
		
		return $sm->fetch($template);
	}
	
	
	
	//построение цепочек id папок (рекурсивно)
	protected function BuildFolderChain($id, &$ids){
		if($id!=0) $ids[]=$id;
		$sql='select 
			*
			from  file_folder
		where id="'.$id.'" 
		';	
		$set=new mysqlset($sql);
		$rs=$set->getResult();
		$rc=$set->getResultNumRows();
		if($rc>0){
			$f=mysqli_fetch_array($rs);
			
			$this->BuildFolderChain($f['parent_id'], $ids);
			
		}
		
	}
	
	
	
	//построение списка папок (рекурсивно)
	protected function BuildFolders($parent_id, &$folders, $prefix, $rl_group_id){
		$sql='select 
			*
			from  file_folder
		where parent_id="'.$parent_id.'" and storage_id="'.$this->storage_id.'"
		order by filename asc';
		//echo $sql;
		$set=new mysqlset($sql);
		$rs=$set->getResult();
		$rc=$set->getResultNumRows();
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$f['prefix']=$prefix;
			
			$f['has_control']=$this->man->HasControl($f['id'], 'file', $this->storage_id, $rl_group_id, $rl_record_id);
			
			
			$folders[]=$f;
			
			//$prefix=$prefix.$f['filename'].' - ';
			$this->BuildFolders($f['id'], $folders, $prefix.$f['filename'].' / ', $rl_group_id);	
		}
	}
	
	
	
	 /*
	//получить список айди объектов, к которым нет доступа у текущего пол-ля
	public function GetBlockedItemsArr($user_id, $object_id, $right_letter, $tablename, $additional_id=0){
		$arr=array();	
		
		$sql='select t2.record_id from  rl_record as t2
		inner join rl_group as t4 on t4.id=t2.rl_group_id
				
		where t2.id not in(
			select distinct t1.rl_record_id from  rl_user_rights as t1
			inner join rights as r on r.id=t1.right_id
			where user_id="'.$user_id.'" and r.name="'.$right_letter.'" and rl_object_id="'.$object_id.'"
			
		)
		and t4.tablename="'.$tablename.'" and t4.additional_id="'.$additional_id.'"
		';
		
		//echo $sql;
		
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			$arr[]=$f['record_id'];	
		}
		
		//print_r($arr);
		
		return $arr;
	}
	
	
	//проверить наличие контроля доступа к записи и затем - конкретные права
	public function CheckFullAccess($user_id, $record_id, $object_id, $right_letter, $tablename, $additional_id=0, &$has_control){
		$res=true;
		
		$has_control=$this->HasControl($record_id, $tablename, $additional_id, $group_id, $rl_record_id);
		
		if($has_control){
			//проверим доступ данного пол-ля к данному праву
			$res=$this->CheckAccess($rl_record_id, $user_id, $right_letter, $object_id);	
		}
		
		
		return $res;
	}
	
	
	//проверить наличие контроля доступа к записи
	public function HasControl($record_id, $tablename, $additional_id, &$group_id, &$rl_record_id){
		$res=false; $group_id=false; $rl_record_id=false;
		//определен ли набор действий?
		$grp=$this->group->GetItemByFields(array('tablename'=>$tablename, 'additional_id'=>$additional_id));
		
		if($grp!==false) {
			$group_id=$grp['id'];
		
			//включен ли контроль для данной записи???
			$recrd=$this->record->GetItemByFields(array('record_id'=>$record_id, 'rl_group_id'=>$group_id));
			
			if($recrd!==false){
				//контроль включен!
				$rl_record_id=$recrd['id'];
				$res=true;
			}
		}
		
		
		return $res;
	}
	
	
	
	
	//проверить у пользователя право на объект
	public function CheckAccess($record_id, $user_id, $right_letter, $object_id){
		$result=false;
		
		$right_id=$this->GetRightIdByLetter($right_letter);
		
		if($right_id!==false){
			$userright=$this->userright->GetItemByFields(array('rl_record_id'=>$record_id, 'user_id'=>$user_id, 'right_id'=>$right_id, 'rl_object_id'=>$object_id));
			
			if($userright!==false) $result=true;
		}
		
		
		return $result;
	}
	
	//проверить у пользователя многие права на объект
	public function CheckAccessArr($record_id, $user_id, array $right_letters, $object_id){
		$result=true;
		
		foreach($right_letters as $k=>$v){
			$right_id=$this->GetRightIdByLetter($v);
		
			if($right_id!==false){
				$userright=$this->userright->GetItemByFields(array('rl_record_id'=>$record_id, 'user_id'=>$user_id, 'right_id'=>$right_id, 'rl_object_id'=>$object_id));
				
				if($userright!==false) $result=$result&&true;
				else $result=$result&&false;
			}else $result=$result&&false;	
		}
		
		return $result;
	}
	
	
	//дать пользователю право на объект
	public function GrantAccess($record_id, $user_id, $right_letter, $object_id){
		$result=false;
		
		$right_id=$this->GetRightIdByLetter($right_letter);
		if($right_id!==false) {
			$userright=$this->userright->GetItemByFields(array('rl_record_id'=>$record_id, 'user_id'=>$user_id, 'right_id'=>$right_id, 'rl_object_id'=>$object_id));
			
			if($userright===false) {
				$params=array();
				$params['user_id']=$user_id;
				$params['rl_object_id']=$object_id;
				$params['rl_record_id']=$record_id;
				$params['right_id']=$right_id;
				$result=$this->userright->Add($params);	
				
			}
		}
		return $result;
	}
	
	//снять у пользователя право на объект
	public function RevokeAccess($record_id, $user_id, $right_letter, $object_id){
		$right_id=$this->GetRightIdByLetter($right_letter);
		if($right_id!==false) {
			$userright=$this->userright->GetItemByFields(array('rl_record_id'=>$record_id, 'user_id'=>$user_id, 'right_id'=>$right_id, 'rl_object_id'=>$object_id));
			
			if($userright!==false) $this->userright->Del($userright['id']);
		}
	}
	
		
	
	
	//получить вид прав по букве
	protected function GetRightIdByLetter($right_letter){
		$result=false;
		
		$right=$this->right->GetItemByFields(array('name'=>$right_letter));
		if($right!==false){
			$result=$right['id'];
		}
		
		return $result;
	}*/
}

?>
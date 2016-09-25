<?
require_once('abstractfilegroup.php');
require_once('filefolderitem.php');
require_once('rl/rl_man.php');

//отладка
//require_once('spitem.php');

// универсальный список файлов файловых разделов с папками
class AbstractFileFolderGroup extends AbstractFileGroup {
	protected $storage_id;
	protected $storage_name;
	protected $storage_path;

	 
	
	public function __construct($id=1){
		$this->init($id);
	}
	
	//установка всех имен
	protected function init($id){
		$this->tablename='file';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='user_id';
			
		$this->storage_id=$id;	
		$this->storage_name='storage_id';	
		$this->storage_path='.';
		
		 
	}
	
	
	public function ShowFiles(
		$template, //1
		DBDecorator $dec, //2
		$from=0, //3
		$to_page=ITEMS_PER_PAGE, //4
		$pagename='files.php', //5
		$loadname='load.html', //6
		$uploader_name='/swfupl-js/files.php', //7
		$can_load=false, //8
		$can_delete=false, //9
		$can_edit=false, //10
		$folder_id=0, //11
		$can_create_folder=false, //12
		$can_edit_folder=false, //13
		$can_delete_folder=false, //14
		$can_move_folder=false, //15
		$id_prefix='',   //16
		$result=NULL ,  //17
		$can_edit_own=false,  //18 
		$can_admin_records=false, //администрирование на уровне записей //19
		
		/*коды прав по файловым реестрам*/
		$code_access=35, //19
		$code_load=2, //20
		$code_edit_all=3, //21
		$code_edit_own=4, //22
		$code_delete_file=5, //23
		$code_create_folder=6, //24
		$code_edit_folder=7, //25
		$code_delete_folder=8, //26
		$code_move=9, //27
		
		/*код группы прав (для построения списка администрируемых папок)*/
		$code_group_id=5
	 ){
		
		$_rl=new RLMan;
		
		$sm=new SmartyAdm;
		
		$sql='select f.*, 
		u.login as u_login,
		 u.name_s as u_name_s,   u.group_id as u_group_id,
		  u.id as user_id from '.$this->tablename.' as f left join user as u on (f.user_id=u.id)  where '.$this->storage_name.'="'.$this->storage_id.'" ';
		
		$sql_count='select count(*) from '.$this->tablename.' as f left join user as u on (f.user_id=u.id)  where '.$this->storage_name.'="'.$this->storage_id.'" ';
		
		
		$db_flt=$dec->GenFltSql(' and ');
		if(strlen($db_flt)>0){
			$sql.=' and '.$db_flt;
			$sql_count.=' and '.$db_flt;	
		}
		
		$ord_flt=$dec->GenFltOrd();
		if(strlen($ord_flt)>0){
			$sql.=' order by '.$ord_flt;
		}
		//echo $sql;
		
		$set=new mysqlSet($sql,$to_page, $from,$sql_count);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$total=$set->GetResultNumRowsUnf();
		
		
		//page
		$navig = new PageNavigator($this->pagename,$total,$to_page,$from,10,'&'.$dec->GenFltUri());
		$navig->SetFirstParamName('from');
		$navig->setDivWrapperName('alblinks');
		$navig->setPageDisplayDivName('alblinks1');			
		$pages= $navig->GetNavigator();
		
		$alls=array();
		
		//отладка
		//$_instance=new SpItem;
		
		for($i=0; $i<$rc; $i++){
			
			
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$f['pdate']=date("d.m.Y H:i:s",$f['pdate']);
			
			$f['size']=filesize($this->storage_path.$f['filename'])/1024/1024;
			
			$f['can_edit']=false;
			
			
			if($this->CompareAccessOper($can_edit, $result['id'], $folder_id,$code_edit_all)){
				$f['can_edit']=true;	
			}elseif(($f['user_id']==$result['id'])&&$this->CompareAccessOper($can_edit_own, $result['id'], $folder_id, $code_edit_own)){
				$f['can_edit']=true;
			}
			
			
			//отладка
			//echo $f['orig_name'];
			//var_dump( $_instance->CheckUserAccess($f['id'], 63));
			
			
			//print_r($f);	
			$alls[]=$f;
		}
		
		
		$fields=$dec->GetUris();
		$tab_page=1;
		foreach($fields as $k=>$v){
			$name=$v->GetName();
			if($name=='tab_page') $tab_page=$v->GetValue();
			
			$sm->assign($v->GetName(),$v->GetValue());	
			//echo $v->GetName(); echo $v->GetValue();
			
		}
		
		
		//заполнить блок папок
		$parent_id=0; $_ffi=new FileFolderItem($this->storage_id);  $navi=array();
		$_ffi->setpagename($this->pagename);
		
		if($folder_id>0){
			
			$ffi=$_ffi->getItemById($folder_id);
			$parent_id=$ffi['parent_id'];
			//$parent_id=$ffi['parent_id'];
			
		
			
			//построим навигацию папки
			$navi=$_ffi->DrawNavigCli($folder_id,'','/',false, $tab_page);
			
		}	
			//найти все папки внутри текущего хранилища и текущей папки
		$sql='select f.*, 
		u.login as u_login,
		 u.name_s as u_name_s,   u.group_id as u_group_id,
		  u.id as user_id from file_folder as f left join user as u on (f.user_id=u.id)  where f.'.$this->storage_name.'="'.$this->storage_id.'" and f.parent_id="'.$folder_id.'" order by f.filename asc, f.id desc';
		
		//echo $sql;
		
		$set=new mysqlSet($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();	
		
		
		$alls1=array();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$f['pdate']=date("d.m.Y H:i:s",$f['pdate']);
			
			//$f['size']=filesize($this->storage_path.$f['filename'])/1024/1024;
			
			//print_r($f);
			
			$has_files_or_dirs=false;
			$_c_arr=array();
			
			$_ffi->SubsListView($f['id'],$_c_arr);
			
			//
			
			if(count($_c_arr)>0) $has_files_or_dirs=$has_files_or_dirs||true;
			
			//есть ли файлы
			$_c_arr[]=$f['id'];
			
			
				
			$has_files=$_ffi->HasFiles($id, $_c_arr);
			$has_files_or_dirs=$has_files_or_dirs||$has_files;
			
			
			$f['has_files']=$has_files;
			$f['has_files_or_dirs']=$has_files_or_dirs;
			
				
			//есть ли доступ в эту папку:
			$f['has_access']=true;
			$f['has_access']=$f['has_access']&&$_rl->CheckFullAccess($result['id'], $f['id'], $code_access, 'w', $this->tablename, $this->storage_id);	
			
			
			//может ли делиться папкой?
			$f['can_share']=($can_admin_records||($f['user_id']==$result['id']));
			
			//var_dump($f['has_access']);
			
			//echo $f['filename'];
				
			$alls1[]=$f;
		
		}
		
		//var_dump($alls1);
		
		
		
		
		
		$sm->assign('from',$from);
		$sm->assign('to_page',$to_page);
		$sm->assign('pages',$pages);
		$sm->assign('items',$alls);
		
		
		
		$sm->assign('folders',$alls1);
		$sm->assign('navi',$navi);
		$sm->assign('folder_id',$folder_id);
		$sm->assign('parent_id',$parent_id);
		
		//$sm->assign('tab_page',$tab_page);
		$sm->assign('id_prefix',$id_prefix);
		
		$sm->assign('code_group_id', $code_group_id);
		
		/*
		таблица истинности:
		базовый доступ|RL-доступ|Итоговый доступ
		0|0|0
		0|1|1
		1|0|0
		1|1|1				
		*/
		
		//$sm->assign('can_load', $this->CompareAccess($can_load, $_rl->CheckFullAccess($result['id'], $folder_id, $code_load, 'w', $this->tablename, $this->storage_id)));
		
		$sm->assign('can_load', $this->CompareAccessOper($can_load, $result['id'], $folder_id, $code_load)); // $_rl->CheckFullAccess($result['id'], $folder_id, $code_load, 'w', $this->tablename, $this->storage_id)));
		
		//var_dump($this->CompareAccessOper($can_load, $result['id'], $folder_id, $code_delete_file));
		
		$sm->assign('can_delete',$this->CompareAccessOper($can_delete, $result['id'], $folder_id, $code_delete_file));
		$sm->assign('can_edit',$this->CompareAccessOper($can_edit, $result['id'], $folder_id, $code_edit_all));
		
		
		$sm->assign('can_create_folder',$this->CompareAccessOper($can_create_folder, $result['id'], $folder_id, $code_create_folder));
		$sm->assign('can_edit_folder',$this->CompareAccessOper($can_edit_folder, $result['id'], $folder_id, $code_edit_folder));
		
		//var_dump($this->CompareAccessOper($can_delete, $result['id'], $folder_id, $code_delete_folder));
		
		$sm->assign('can_delete_folder',$this->CompareAccessOper($can_delete, $result['id'], $folder_id, $code_delete_folder));
		$sm->assign('can_move_folder',$this->CompareAccessOper($can_move_folder, $result['id'], $folder_id, $code_move));
		
		
		
	 
		 
		
		
		
		$sm->assign('can_admin_records', $can_admin_records); //администрирование на уровне записей
		$sm->assign('tablename', $this->tablename);
		
		$sm->assign('storage_name', $this->storage_name);
		$sm->assign('storage_id',$this->storage_id);
		
		$sm->assign('session_id',session_id());
		
		$sm->assign('uploader_name',$uploader_name);
		$sm->assign('pagename',$pagename);
		
		$sm->assign('this_pagename',$this->pagename);
		
		$sm->assign('loadname',$loadname);		
			
		return $sm->fetch($template);
	}
	
	
	//логическая функция вычисления доступа
	/*
		таблица истинности:
		базовый доступ|RL-доступ|Итоговый доступ
		0|0|0
		0|1|1
		1|0|0
		1|1|1				
		*/
	public function CompareAccess($base_access, $rl_access){
		$full_access=false;
		
		
		if(($base_access==false)&&($rl_access==false)){
			$full_access=false;	
		}
		elseif(($base_access==false)&&($rl_access==true)){
			$full_access=true;	
		}
		elseif(($base_access==true)&&($rl_access==false)){
			$full_access=false;	
		}
		elseif(($base_access==true)&&($rl_access==true)){
			$full_access=true;	
		}
		
		
		return $full_access;
	}
	
	
	public function CompareAccessOper($base_access, $user_id,  $record_id, $object_id){
		
		$rl=new RLMan;
		
		$local_access=$rl->CheckFullAccess($user_id, $record_id, $object_id, 'w', $this->tablename, $this->storage_id, $has_control);
		
	
		$full_access=false;
		
		if(!$has_control){
			$full_access=$base_access;	
		}else{
		
			if(($base_access==false)&&($local_access==false)){
				$full_access=false;	
			}
			elseif(($base_access==false)&&($local_access==true)){
				$full_access=true;	
			}
			elseif(($base_access==true)&&($local_access==false)){
				$full_access=false;	
			}
			elseif(($base_access==true)&&($local_access==true)){
				$full_access=true;	
			}
		}
		
		return $full_access;
	}
}
?>
<?
require_once('abstractfilegroup.php');
require_once('filefolderitem.php');
require_once('authuser.php');

//require_once('supplier_file_item.php');


// универсальный список файлов конкретного документа (сотрудника, организации, ...) с папками
class AbstractFileDocFolderGroup extends AbstractFileGroup {
	protected $storage_id;
	protected $storage_name;
	protected $storage_path;
	
	protected $tablename_folder;
	protected $doc_id;
	
	protected $doc_id_name;
	
	protected $folder_instance;
	
	
	public function __construct($id=4, $doc_id, $folder_instance){
		$this->init($id, $doc_id,  $folder_instance);
	}
	
	//установка всех имен
	protected function init($id, $doc_id, $folder_instance){
		$this->tablename='komplekt_ved_file';
		$this->folder_instance=$folder_instance; //экземпл€р класса папки
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='komplekt_ved_id';
			
		$this->storage_id=$id;	
		$this->storage_name='storage_id';	
		$this->storage_path=ABSPATH.'upload/files/kv/';	
		
		
		$this->tablename_folder='komplekt_ved_file_folder';
		$this->doc_id=$doc_id;
		$this->doc_id_name='komplekt_ved_id';
		
		$this->folder_instance->tablename=$this->tablename_folder;
		$this->folder_instance->doc_id_name=$this->doc_id_name;
		
			
	}
	
	
	
	
	
	
	
	//реестр файлов
	public function ShowFiles($template, DBDecorator $dec,$from=0,$to_page=ITEMS_PER_PAGE,$pagename='files.php', $loadname='load.html', $uploader_name='/swfupl-js/files.php', 
	$can_load=false, 
	$can_delete=false, 
	$can_edit=false, 
	$folder_id=0, 
	$can_create_folder=false, 
	$can_edit_folder=false, 
	$can_delete_folder=false, 
	$can_move_folder=false, 
	$id_prefix='',
	$can_edit_own=false,
	$result=NULL ,
	
	$navi_decorator=NULL, $elem_id_prefix='', &$alls
	){
		$_au=new AuthUser;
		if($result===NULL) $result=$_au->Auth();
		
		$sm=new SmartyAdm;
		
		$sql='select f.*, 
		u.login as u_login,
		 u.name_s as u_name_s,   u.group_id as u_group_id,
		  u.id as user_id from '.$this->tablename.' as f left join user as u on (f.user_id=u.id)  where '.$this->storage_name.'="'.$this->storage_id.'" and '.$this->subkeyname.'="'.$this->doc_id.'" ';
		
		$sql_count='select count(*) from '.$this->tablename.' as f left join user as u on (f.user_id=u.id)  where '.$this->storage_name.'="'.$this->storage_id.'" and '.$this->subkeyname.'="'.$this->doc_id.'" ';
		
		
		$db_flt=$dec->GenFltSql(' and ');
		if(strlen($db_flt)>0){
			$sql.=' and '.$db_flt;
			$sql_count.=' and '.$db_flt;	
		}
		
		$ord_flt=$dec->GenFltOrd();
		if(strlen($ord_flt)>0){
			$sql.=' order by '.$ord_flt;
		}
		//echo $sql."<br>";;
		
		$set=new mysqlSet($sql,$to_page, $from,$sql_count);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$total=$set->GetResultNumRowsUnf();
		
		
		//page
		$navig = new PageNavigator($pagename,$total,$to_page,$from,10,'&'.$dec->GenFltUri());
		$navig->SetFirstParamName('from');
		$navig->setDivWrapperName('alblinks');
		$navig->setPageDisplayDivName('alblinks1');			
		$pages= $navig->GetNavigator();
		
		$alls=array();
		
		//$_instance=new SupplierFileItem($this->storage_id);
		for($i=0; $i<$rc; $i++){
			
			
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$f['pdate']=date("d.m.Y H:i:s",$f['pdate']);
			
			$f['size']=filesize($this->storage_path.$f['filename'])/1024/1024;
			
			
			$f['can_edit']=false;
			if($can_edit) $f['can_edit']=true;
			elseif(($f['user_id']==$result['id'])&&$can_edit_own) $f['can_edit']=true;
			
			//var_dump($_instance->CheckUserAccess($this->doc_id, 69, $result));
			
			//print_r($f);	
			$alls[]=$f;
		}
		
		
		$fields=$dec->GetUris();
		foreach($fields as $k=>$v){
			$name=$v->GetName();

			
			$sm->assign($v->GetName(),$v->GetValue());	
			//echo $v->GetName(); echo $v->GetValue();
			
		}
		
		
		//заполнить блок папок
		$parent_id=0;  $navi=array();
		
		
		
		//$_ffi=new FileFolderItem($this->storage_id); 
		//$this->folder_instance->setpagename($this->pagename);
		
		//$_ffi->setpagename($this->pagename);
		
		if($folder_id>0){
			
			$ffi=$this->folder_instance->getItemById($folder_id);
			$parent_id=$ffi['parent_id'];
			//$parent_id=$ffi['parent_id'];
			
			
			
			//echo 'zzzzzzzzzz';
			
			//построим навигацию папки
			$navi=$this->folder_instance->DrawNavigCli($folder_id,'','/',false, $navi_decorator);
			
		}	
			//найти все папки внутри текущего хранилища и текущей папки
		$sql='select f.*, 
		u.login as u_login,
		 u.name_s as u_name_s,   u.group_id as u_group_id,
		  u.id as user_id from '.$this->tablename_folder.' as f left join user as u on (f.user_id=u.id)  where f.'.$this->storage_name.'="'.$this->storage_id.'" and f.parent_id="'.$folder_id.'" and doc_id="'.$this->doc_id.'" order by f.filename asc, f.id desc';
		
		//echo $sql;
		
		$set=new mysqlSet($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();	
		
		
		$alls1=array();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$f['pdate']=date("d.m.Y H:i:s",$f['pdate']);
			
			
			$has_files_or_dirs=false;
			$_c_arr=array();
			
			
			$this->folder_instance->SubsListView($f['id'],$_c_arr);
			
			
			
			//
			
			if(count($_c_arr)>0) $has_files_or_dirs=$has_files_or_dirs||true;
			
			//есть ли файлы
			$_c_arr[]=$f['id'];
			
			
			//$has_files_or_dirs=$has_files_or_dirs||$this->folder_instance->HasFiles($id, $_c_arr);
			
			$has_files=$this->folder_instance->HasFiles($id, $_c_arr);
			
			$has_files_or_dirs=$has_files_or_dirs||$has_files;
			
			
			$f['has_files_or_dirs']=$has_files_or_dirs;
			$f['has_files']=$has_files;
			
			
			 
			
			
				
			$alls1[]=$f;
		
		}
		
		//var_dump($alls1);
		
		
		
		
		
		$sm->assign('from',$from);
		$sm->assign('to_page',$to_page);
		$sm->assign('pages',$pages);
		$sm->assign('items',$alls);
		$e_path='';
		if($navi_decorator!==NULL){
			$e_path=$navi_decorator->GenFltUri();
		}
		if(strlen($e_path)>0) $e_path='&'.$e_path;
		$sm->assign('e_path', $e_path);
		
		
		
		$sm->assign('folders',$alls1);
		$sm->assign('navi',$navi);
		$sm->assign('folder_id',$folder_id);
		$sm->assign('parent_id',$parent_id);
		
		$sm->assign('id_prefix',$id_prefix);
		$sm->assign('elem_id_prefix',$elem_id_prefix);
		
		
		$sm->assign('can_load',$can_load);
		$sm->assign('can_delete',$can_delete);
		$sm->assign('can_edit',$can_edit);
		
		//$can_create_folder=false, $can_edit_folder=false, $can_delete_folder=false, $can_move_folder=false
		$sm->assign('can_create_folder',$can_create_folder);
		$sm->assign('can_edit_folder',$can_edit_folder);
		$sm->assign('can_delete_folder',$can_delete_folder);
		$sm->assign('can_move_folder',$can_move_folder);
		
		
		
		$sm->assign('storage_name', $this->storage_name);
		$sm->assign('storage_id',$this->storage_id);
		
		$sm->assign('session_id',session_id());
		
		$sm->assign('uploader_name',$uploader_name);
		$sm->assign('pagename',$pagename);
		
		$sm->assign('this_pagename',$this->pagename);
		$sm->assign('doc_id_name',$this->doc_id_name);		
		$sm->assign('doc_id',$this->doc_id);		
		
		
		$sm->assign('loadname',$loadname);		
			
		return $sm->fetch($template);
	}
	
	
	
	
	public function SetTablenameFolder($tablename_folder){
		$this->tablename_folder=$tablename_folder;
		$this->folder_instance->tablename=$this->tablename_folder;	
	}
	
	
	public function SetDocIdName($doc_id_name){
		$this->doc_id_name=$doc_id_name;	
		$this->folder_instance->doc_id_name=$this->doc_id_name;
	}
	
	
	/*
	
	$this->folder_instance->tablename=$this->tablename_folder;
		$this->folder_instance->doc_id_name=$this->doc_id_name;
	*/
	
	//проверка, есть ли в реестре файл с датой, >= заданной
	public function HasFileByPdate($pdate=0){
		$sql='select count(f.id) 
	
		from '.$this->tablename.' as f  where '.$this->storage_name.'="'.$this->storage_id.'" and '.$this->subkeyname.'="'.$this->doc_id.'" and 	pdate>="'.$pdate.'"';
		
		 
		//echo $sql."<br>";;
		
		$set=new mysqlSet($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$f=mysqli_fetch_array($rs);
		
		return ((int)$f[0]>0);
	}
	
}
?>
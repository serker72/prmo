<?
require_once('abstractfiledocfoldergroup.php');

require_once('filedocfolderitem.php');

// абстрактная группа файлов
class ContractUchGroup extends AbstractFileDocFolderGroup {
	
	protected function init($id, $doc_id, $folder_instance){
		$this->tablename='contract_file';
		$this->file_instance=$file_instance; //экземпляр класса файла
		$this->folder_instance=$folder_instance; //экземпляр класса папки
		$this->pagename='uchcontracts.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='user_d_id';
			
		$this->storage_id=$id;	
		$this->storage_name='storage_id';	
		$this->storage_path=ABSPATH.'upload/files/uch/';	
		
		
		$this->tablename_folder='contract_file_folder';
		$this->doc_id=$doc_id;
		$this->doc_id_name='sup_id';
		
		$this->folder_instance->tablename=$this->tablename_folder;
		$this->folder_instance->doc_id_name=$this->doc_id_name;
			
	}
	
	
	/*protected $storage_id;
	protected $storage_name;
	protected $storage_path;
	
	public function __construct($id=4){
		$this->init($id);
	}
	
	//установка всех имен
	protected function init($id){
		$this->tablename='contract_file';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='user_d_id';
			
		$this->storage_id=$id;	
		$this->storage_name='storage_id';	
		$this->storage_path=ABSPATH.'upload/files/uch/';	
	}
	
	
	public function ShowFiles($user_id, $template, DBDecorator $dec,$from=0,$to_page=ITEMS_PER_PAGE,$pagename='files.php', $loadname='load.html', $uploader_name='/swfupl-js/files.php', $can_load=false, $can_delete=false, $can_edit=false){
		
		$sm=new SmartyAdm;
		
		$sql='select f.*, 
		u.login as u_login,
		 u.name_s as u_name_s,  u.group_id as u_group_id,
		  u.id as user_id from '.$this->tablename.' as f left join user as u on (f.user_id=u.id)  where '.$this->storage_name.'="'.$this->storage_id.'" and '.$this->subkeyname.'="'.$user_id.'" ';
		
		$sql_count='select count(*) from '.$this->tablename.' as f left join user as u on (f.user_id=u.id)  where '.$this->storage_name.'="'.$this->storage_id.'"  and '.$this->subkeyname.'="'.$user_id.'" ';
		
		
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
		$navig = new PageNavigator($pagename,$total,$to_page,$from,10,'&'.$dec->GenFltUri());
		$navig->SetFirstParamName('from');
		$navig->setDivWrapperName('alblinks');
		$navig->setPageDisplayDivName('alblinks1');			
		$pages= $navig->GetNavigator();
		
		$alls=array();
		for($i=0; $i<$rc; $i++){
			
			
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$f['pdate']=date("d.m.Y H:i:s",$f['pdate']);
			
			$f['size']=filesize($this->storage_path.$f['filename'])/1024/1024;
			
			//print_r($f);	
			$alls[]=$f;
		}
		
		$sm->assign('from',$from);
		$sm->assign('to_page',$to_page);
		$sm->assign('pages',$pages);
		$sm->assign('items',$alls);
		
		$sm->assign('can_load',$can_load);
		$sm->assign('can_delete',$can_delete);
		$sm->assign('can_edit',$can_edit);
		
		$sm->assign('sup_id',$user_id);
		
		$sm->assign('storage_name', $this->storage_name);
		$sm->assign('storage_id',$this->storage_id);
		
		$sm->assign('session_id',session_id());
		
		$sm->assign('uploader_name',$uploader_name);
		$sm->assign('pagename',$pagename);
		$sm->assign('loadname',$loadname);		
			
		return $sm->fetch($template);
	}*/
	
}
?>
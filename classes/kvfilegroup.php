<?
require_once('abstractfiledocfoldergroup.php');

require_once('filedocfolderitem.php');

// абстрактна€ группа файлов
class KvFileGroup extends AbstractFileDocFolderGroup {
	
	
	
	//установка всех имен
	//установка всех имен
	protected function init($id, $doc_id, $folder_instance){
		$this->tablename='komplekt_ved_file';
		$this->file_instance=$file_instance; //экземпл€р класса файла
		$this->folder_instance=$folder_instance; //экземпл€р класса папки
		$this->pagename='komplekt_ved_files.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='komplekt_ved_id';
			
		$this->storage_id=$id;	
		$this->storage_name='storage_id';	
		$this->storage_path=ABSPATH.'upload/files/kv/';	
		
		
		$this->tablename_folder='komplekt_ved_file_folder';
		$this->doc_id=$doc_id;
		$this->doc_id_name='komplekt_ved_id';
			
	}
	
	
	/*
	public function ShowFiles($bill_id, $template, DBDecorator $dec,$from=0,$to_page=ITEMS_PER_PAGE,$pagename='files.php', $loadname='load.html', $uploader_name='/swfupl-js/files.php', $can_load=false, $can_delete=false, $can_edit=false){
		
		$sm=new SmartyAdm;
		
		$sql='select f.*, 
		u.login as u_login,
		 u.name_s as u_name_s,   u.group_id as u_group_id,
		  u.id as user_id from '.$this->tablename.' as f left join user as u on (f.user_id=u.id)  where '.$this->storage_name.'="'.$this->storage_id.'" and '.$this->subkeyname.'="'.$bill_id.'" ';
		
		$sql_count='select count(*) from '.$this->tablename.' as f left join user as u on (f.user_id=u.id)  where '.$this->storage_name.'="'.$this->storage_id.'" and '.$this->subkeyname.'="'.$bill_id.'" ';
		
		
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
		
		$sm->assign('storage_name', $this->storage_name);
		$sm->assign('storage_id',$this->storage_id);
		$sm->assign('komplekt_ved_id',$bill_id);
		
		$sm->assign('session_id',session_id());
		
		$sm->assign('uploader_name',$uploader_name);
		$sm->assign('pagename',$pagename);
		$sm->assign('loadname',$loadname);		
			
		return $sm->fetch($template);
	}
	
	
	
	public function GetItemsByIdArr($id){
		$arr=Array();
		
		$set=new MysqlSet('select * from '.$this->tablename.' where '.$this->subkeyname.'="'.$id.'" and '.$this->storage_name.'="'.$this->storage_id.'" order by id asc');
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			
			$f=mysqli_fetch_array($rs);
			
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			$f['size']=filesize($this->storage_path.$f['filename'])/1024/1024;
			//$f['address']=nl2br($f['address']);
			$arr[]=$f;
		}
		
		return $arr;
	}
	
	
	
	
	public function ClearLostFiles($ttl=86400){
		//$files=array();
		$message_files=array();
		
		$message_files=$this->GetFilenamesArr();
		//print_r($message_files);
		$iterator = new DirectoryIterator($this->storage_path);
		foreach ($iterator as $fileinfo) {
			if ($fileinfo->isFile()) {
				//echo $fileinfo->__toString();
				//$filenames[$fileinfo->getMTime()] = $fileinfo->getFilename();
				if(!in_array($fileinfo->__toString(), $message_files)){
					
					$tm=$fileinfo->getMTime();
					if($tm<(time()-$ttl)){
					  //провер€ть дату
					  @unlink($this->storage_path.$fileinfo->__toString());
					}
				}
			}
		}
		
	}
	
	protected function GetFilenamesArr(){
		//список позиций

		$arr=Array();
		$set=new MysqlSet('select * from '.$this->tablename.' where '.$this->storage_name.'="'.$this->storage_id.'" order by id asc');
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			$arr[]=$f['filename'];
		}
		
		return $arr;
		
	}*/
	
}
?>
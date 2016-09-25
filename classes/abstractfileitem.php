<?
require_once('abstractitem.php');
require_once('filefolderitem.php');
require_once('supplier_to_user.php');
require_once('authuser.php');


//абстрактный файл
class AbstractFileItem extends AbstractItem{
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
		$this->storage_path=ABSPATH.'upload/files/po/';	
	}
	
	
	public function GetStoragePath(){
		return $this->storage_path;	
	}
	
	public function GetStorageId(){
		return $this->storage_id;	
	}
	
	public function GetPageName(){
		return $this->pagename;	
	}
	
	
	//удалить
	public function Del($id){
		$item=$this->GetItemById($id);
		if($item!==false){
		
		  @unlink($this->storage_path.$item['filename']);
		  parent::Del($id);
		}
	}	
	
	
	public function SetPageName($pagename){
		$this->pagename=$pagename;	
	}
	
	
	
	//проверка наличия доступа пользователя к файлу
	public function CheckUserAccess($file_id, $user_id){
		return true;	
	}
	
	//внутренняя проверка наличия доступа пользователя к файлу (для файловых реестров с контролем доступа по папкам)
	protected function FileRLCheckUserAccess($file_id, $user_id, $right_id, $rl_right_id){
		$ret=true;
		//проверка наличия доступа во вкладку
		
		$sql='select count(*) from user as s inner join user_rights as us on s.id=us.user_id and us.right_id=2 and us.object_id='.$right_id.' where s.is_active=1 and s.id="'.$user_id.'"';
		
		$set=new mysqlSet($sql);	
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);
		
		if((int)$f[0]==0) $ret=$ret&&false;
		
		//var_dump( (int)$f[0]);
		//echo $sql.'<br>';
		
		//проверка по доступу к папкам
		$item=$this->GetItemById($file_id);
		
		//построить массив из id папок
		$folder_ids=array(); //$folder_ids[]=$item['folder_id'];
		
		//...
		if($item['folder_id']!=0){
			$_ff=new FileFolderItem($this->storage_id);
			
			$_folder_ids=$_ff->RetrievePath($item['folder_id'], $flaglost, $vloj);
			foreach($_folder_ids as $k=>$v) foreach($v as $kk=>$vv){ 
				$folder_ids[]=$vv['path'];
			 
			}
		
			//	
		}
		
		//var_dump($folder_ids);
		
		foreach($folder_ids as $k=>$folder_id){
		
			
			if($folder_id>0){
				if(!$this->_rl_man->CheckFullAccess($user_id, $folder_id, $rl_right_id, 'w', 'file', $this->storage_id, $has_control)){
					 $ret=$ret&&false;
					
					  break;	 
				}
			}
			
			// echo " $folder_id <br>";
		}
		
		
		return $ret;
	}
	
	//внутрення проверка наличия доступа сотудника к контрагенту
	protected function SupplierCheckUserAccess($supplier_id, $user_id, $result=NULL){
		$au=new AuthUser;
		if($result===NULL) $result=$au->Auth(false,false);
		
		$_s_to_u=new SupplierToUser;
		
		$s_to_u=$_s_to_u->GetExtendedViewedUserIdsArr($user_id, $result);

		
		$limited_supplier=$s_to_u['sector_ids'];	
		
		
		return in_array($supplier_id, $limited_supplier);
		
	}
	

}
?>
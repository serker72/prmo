<?
require_once('abstractfileitem.php');

//абстрактный файл
class Supplier_Akt_Item extends AbstractFileItem{

	
	
	public function __construct($id=4){
		$this->init($id);
	}
	
	//установка всех имен
	protected function init($id){
		$this->tablename='supplier_shema_file';
		$this->item=NULL;
		$this->pagename='supplier_aktsv.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='user_d_id';
			
		$this->storage_id=$id;	
		$this->storage_name='storage_id';	
		$this->storage_path=ABSPATH.'upload/files/supplier_akt_file/';	
	}
	
	
	//добавить 
	public function Add($params){
		$params[$this->storage_name]=$this->storage_id;
		
		
		return parent::Add($params);
	}
	
	//править
	public function Edit($id,$params){
		$params[$this->storage_name]=$this->storage_id;
		
		return parent::Edit($id,$params);
	}
	
	
	//получение первого итема по набору полей
	public function GetItemByFields($params){
		$params[$this->storage_name]=$this->storage_id;
		return parent::GetItemByFields($params);
	}
	
	
	
	//проверка наличия доступа пользователя к файлу
	public function CheckUserAccess($supplier_id, $user_id, $result=NULL){
		 $au=new AuthUser;
		 if($result===NULL) $result=$au->Auth(false,false);
		 
		 return $this->SupplierCheckUserAccess($supplier_id, $user_id, $result);
		
		 
	}

}
?>
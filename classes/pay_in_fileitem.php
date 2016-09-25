<?
require_once('abstractfileitem.php');

//абстрактный файл
class PayInFileItem extends AbstractFileItem{
	
	public function __construct($id=3){
		$this->init($id);
	}
	
	//установка всех имен
	protected function init($id){
		$this->tablename='payment_file';
		$this->item=NULL;
		$this->pagename='pay_in_files.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='payment_id';
			
		$this->storage_id=$id;	
		$this->storage_name='storage_id';	
		$this->storage_path=ABSPATH.'upload/files/pays_in/';	
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
	
}
?>
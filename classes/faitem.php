<?
require_once('abstractitem.php');

//фактический адрес
class FaItem extends AbstractItem{
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='fact_address';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='user_id';	
	}
	
	
	//получить по айди и коду видимости
	/*public function GetItemById($id,$mode=0){
		
		$res=parent::GetItemById($id,$mode);
		if($res!==false){
			$item=new mysqlSet('select * from fact_address_form where id='.$res['form_id'].';');	
			$result=$item->getResult();
			$rc=$item->getResultNumRows();
			if($rc!=0){
				$f=mysqli_fetch_array($result);
				$res['name']=$f['name'];
			}
		}
		
		return $res;
	}*/
	
}


//форма факт. адреса
class FaFormItem extends AbstractItem{
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='fact_address_form';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='user_id';	
	}
}
?>
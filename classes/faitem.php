<?
require_once('abstractitem.php');

//����������� �����
class FaItem extends AbstractItem{
	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='fact_address';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='user_id';	
	}
	
	
	//�������� �� ���� � ���� ���������
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


//����� ����. ������
class FaFormItem extends AbstractItem{
	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='fact_address_form';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='user_id';	
	}
}
?>
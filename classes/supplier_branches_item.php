<?
require_once('abstractitem.php');
 


require_once('authuser.php');
require_once('actionlog.php');
 

//����������� �������
class SupplierBranchesItem extends AbstractItem{
	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='supplier_branches';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';
		
	}
	 
	
	//�������
	public function Del($id){
		
		$query = 'delete from '.$this->tablename.' where parent_id="'.$id.'"';
		$it=new nonSet($query);
		
		 	
		
		parent::Del($id); 
	}	
	
	//����� ������������
	public function CountSubs($id){
		$item=new mysqlSet('select count(*) from '.$this->tablename.' where parent_id="'.$id.'"');
		

		$result=$item->getResult();
		$rc=$item->getResultNumRows();
		 
		 
			$res=mysqli_fetch_array($result);	
			
		return (int)$res[0];	
	}
	
}
?>
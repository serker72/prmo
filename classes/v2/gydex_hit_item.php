<?
 

// 
class HitItem extends AbstractItem{
	 
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='gydex_hits';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		//$this->subkeyname='mid';	
	}
	 
	
	
}
?>
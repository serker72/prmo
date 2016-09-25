<?
require_once('abstractitem.php');


//����� �����
class FolderItem extends AbstractItem{
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='folder';
	}
	


	//�������
	public function Del($id){
		
		AbstractItem::Del($id);
	}	
	
	
	
	//�������� ������ ����...
	public function GetFullFolderName($id){
		$txt='';
		
		$arrs=array();
		$this->GetSub($id,$arrs);
		
		$arrs=array_reverse($arrs);
		
		$txt=implode('-> ', $arrs); 
		
		return $txt;
	}
	
	
	protected function GetSub($id, &$elems){
		if($id!=0){
			$itm=$this->GetItemById($id);
			if($itm!==false){
			$elems[]=$itm['filename'];
			if($itm['parent_id']!=0) $this->GetSub($itm['parent_id'],$elems);
			}
		}
	}
}
?>
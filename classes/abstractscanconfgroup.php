<?
require_once('abstractgroup.php');

// ����������� ������
class AbstractScanConfGroup extends AbstractGroup {
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='komplekt_ved_confirm';
		$this->pagename='view.php';		
		$this->subkeyname='komplekt_ved_id';
	
		$this->vis_name='is_shown';		
		
		
		
	}
	
	
	
	
	//������������ "���������-�� ���������"
	public function ScanConfirm($id){
		$res=false;
		
		$set=new MysqlSet('select count(*) from '.$this->tablename.' where '.$this->subkeyname.'="'.$key1.'" ');
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		$f=mysqli_fetch_array($rs);
		if($f[0]>=3) $res=true;
			
		
		return $res;
	}
	
	
	
	
}
?>
<?
require_once('abstractscanconfgroup.php');

// ����������� ������
class KomplScanConf extends AbstractScanConfGroup {
	
	
	
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
		
		$set=new MysqlSet('select count(*) from '.$this->tablename.' where '.$this->subkeyname.'="'.$id.'" ');
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		$f=mysqli_fetch_array($rs);
		if($f[0]>=1) $res=true;
			
		
		return $res;
	}
	
	
	
}
?>
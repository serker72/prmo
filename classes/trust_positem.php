<?
require_once('abstractitem.php');
require_once('trust_pospmitem.php');

//����������� �������
class TrustPosItem extends AbstractItem{
	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='trust_position';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='trust_id';	
	}
	
	
	
	//�������� 
	public function Add($params, $pms=NULL){
		
		$code=AbstractItem::Add($params);
		
		if($pms!==NULL){
			//������� +/- ��� �������
			$bpm=new TrustPosPMItem;
			
			if($code>0){
				$pms['trust_position_id']=$code;
				$bpm->Add($pms);	
			}
		}
		
		return $code;
	}
	
	
	//�������������
	public function Edit($id,$params,$pms=NULL){
		AbstractItem::Edit($id,$params);
		
		if($pms!==NULL){
			//���� ��� ���� ��, �� ����� ����������� ���
			//���� ��� - �� �������
			$_bpm=new TrustPosPMItem;
			$bpm=$_bpm->GetItemByFields(array('trust_position_id'=>$id));
			if($bpm===false){
				$pms['trust_position_id']=$id;
				$_bpm->Add($pms);	
			}else{
				$pms['trust_position_id']=$id;
				$_bpm->Edit($bpm['id'],$pms);	
			}
		}
	}
	
	
	
	//�������
	public function Del($id){
		
		$query = 'delete from trust_position_pm where trust_position_id='.$id.';';
		$it=new nonSet($query);
		
		
		parent::Del($id);
	}	
	
	
	//����� ��� ����� �� ������� � ������ ������������� ����� �����
	public function GetOtherPosArr($position_id, $bill_id, $trust_id){
		$arr=array();	
		
		$sql='select t.*, b.code as bill_code from 
		  '.$this->tablename.' as t 
		  inner join trust as tt on t.trust_id=tt.id 
		  left join bill as b on t.bill_id=b.id 
		  where 
		  	t.trust_id<>"'.$trust_id.'" 
			and t.bill_id="'.$bill_id.'" 
			and t.position_id="'.$position_id.'" 
		
			and tt.is_confirmed=1  
		order by t.trust_id asc';
		$set=new MysqlSet($sql);
		
		//echo $sql.'<br>';
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			
			$f['hash']=md5($f['position_id'].'_'.$f['bill_id']);
			
			$arr[]=$f;
		}
		
		
		return $arr;
	}
	
}
?>
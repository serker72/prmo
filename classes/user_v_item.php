<?
require_once('abstractitem.php');
require_once('discr_man_group.php');
require_once('discr_rightuseritem.php');
require_once('questionuseritem.php');

require_once('usercontactdatagroup.php');
require_once('user_int_group.php');
require_once('messageitem.php');
require_once('suppliercontactkindgroup.php');


//��������� V ����
class UserVItem extends AbstractItem{
	public $pagename;
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='user';
		$this->item=NULL;
		$this->pagename='user_v.php';		
		$this->vis_name='is_active';	
		//$this->subkeyname='mid';	
	}
	
	public function GetItemById($id,$mode=0){
		$res=AbstractItem::GetItemById($id, $mode);	
		
		 
		
		return $res;
	}
	
	
	
	public function Add($params,$questions=NULL){
		$params['group_id']=2;	
		
		$code=parent::Add($params);
		
		if($code!=0){
		  //������������ ���������������� �������
		  $dmg= new DiscrManGroup;
		  
		  $dmg->BuildRightsTable($params['group_id']);
		  $dmg->ApplyTableToUser($code);
		 
		  //������� �������
		  $this->SetQuestions($code,$questions);
		}
		
		//�������� - ���� ���� ������ � ���-1 ��� ������ ����� �����������, ��� ���������
		new NonSet('insert into supplier_to_user (`org_id`, `user_id`) values("1", "'.$code.'")');
		
		
		//��������� ������ ������������
		if($code!=0){
			$_mi=new MessageItem;	
			$mparams=array();
			
			$mparams['topic']='����� ���������� � ��������� �'.SITETITLE.'�';
			$mparams['txt']='
			<div><em>������ ��������� ������������� �������������.</em></div>
			<div>���������/�� '.$params['name_s'].'!</div>
<div><br /></div>
<div>������������ ��� � ��������� �'.SITETITLE.'�.</div>
<div>�� ��������� ����� �����������.</div>
<div><br /></div>
<div>��� �����: '.$params['login'].'</div>
<div>��� ������ ��� ���������� � ���� ��� �������� ����� ������� ������.</div>
<div>��� ����������� ������������ ������ � ��������� ������ ������� ��� ������. ��� ����� ������� � ������� ���� ��������.</div>
<div><br /></div>
<div>��������� ���������� �� ������ � ��������� �������� � ������� ����������� �����������.</div>
<div>�� ���� �������� ��������� �'.SITETITLE.'� �������� ���������� �������.</div>
<div><br /></div>
<div>������ ������� ������!</div>
<div>� ���������, ��������� �'.SITETITLE.'� � ������� �������������.</div>

			
			';
			
			
			$mparams['to_id']= $code;
			$mparams['from_id']=-1; //�������������� ������� �������� ���������
			$mparams['pdate']=time();
			
			$_mi->Send(0,0,$mparams,false);
			
		}
		
		
		return $code;
	}
	
	public function Edit($id,$params,$questions=NULL){
		if(isset($params['group_id'])) unset($params['group_id']); //$params['group_id']=1;	
		
		$log_entries=array();
		
		if($questions!==NULL) $log_entries=$this->SetQuestions($id,$questions);
		
		parent::Edit($id,$params);
		
		return $log_entries;
	}
	
	//�������
	public function Del($id){
		
		
		new NonSet('delete from user_rights where user_id="'.$id.'"');
		new NonSet('delete from question_user where user_id="'.$id.'"');
		
		new NonSet('delete from user_work_intervals where user_id="'.$id.'"');
		
		
		
		parent::Del($id);
	}	
	
	//������� ������� ��������� � �������� ������
	
	
	//������������ ������ ���������� �������� ������������
	public function SetQuestions($user_id, $questions){
		/*new NonSet('delete from question_user where user_id="'.$user_id.'"');
		$qui=new QuestionUserItem;
		foreach($questions as $k=>$v){
			$qui->Add(array('user_id'=>$user_id, 'question_id'=>$v));	
		}*/
		
		
		$_kpi=new QuestionUserItem;
		
		$log_entries=array();
		
		//���������� ������ ������ �������
		$old_positions=array();
		$old_positions=$this->GetQuestionsArr($user_id);
		
		foreach($questions as $k=>$v){
			$kpi=$_kpi->GetItemByFields(array('user_id'=>$user_id,'question_id'=>$v));
			
			if($kpi===false){
				//dobavim pozicii	
				
				
				$add_array=array();
				$add_array['question_id']=$v;
				$add_array['user_id']=$user_id;
				
				
				
				
				$_kpi->Add($add_array);//, $add_pms);
				
				$log_entries[]=array(
					'action'=>0,
					'user_id'=>$user_id,
					'question_id'=>$v
				);
				
			}
		}
		
		//����� � ������� ��������� �������:
		//����. ���. - ��� �������, ������� ��� � ������� $positions
		$_to_delete_positions=array();
		foreach($old_positions as $k=>$v){
			//$v['id']
			$_in_arr=false;
			foreach($questions as $kk=>$vv){
				if($vv==$v['question_id']){
					$_in_arr=true;
					break;	
				}
			}
			
			if(!$_in_arr){
				$_to_delete_positions[]=$v;	
			}
		}
		
		//������� ��������� �������
		foreach($_to_delete_positions as $k=>$v){
			
			//��������� ������ ��� �������
			
			
			$log_entries[]=array(
					'action'=>2,
					'user_id'=>$user_id,
					'question_id'=>$v['question_id']
			);
			
			//������� �������
			$_kpi->Del($v['id']);
		}
		
		
		//���������� ������� ������ ���������� ������� ��� �������
		return $log_entries;
		
		
		
		
		
			
	}
	
	
	//������� �������
	public function GetQuestionsArr($id){
		$arr=array();	
		
		$set=new mysqlSet('select * from question_user where user_id="'.$id.'" order by question_id asc');
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		for($i=0;$i<$rc;$i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			//
			$arr[]=$f;	
		}
		
		
		
		return $arr;		
		
	}
	
	
	//�������� ������� ������ �������� � ����������� �����������
	public function GetQuestionsAllArr($user_id=0){
		$arr=array();	
		
		$set=new mysqlSet('select q.*, qu.id as qu_id from question as q left join question_user as qu on q.id=qu.question_id and qu.user_id="'.$user_id.'" order by q.id asc');
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		for($i=0;$i<$rc;$i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			//
			$arr[]=$f;	
		}
		
		return $arr;
	}
	
	
	
	//������������ ������� � ������ ������������
	public function Deploy($id){
		$arr=array();
		
		$user=$this->GetItemById($id);
		if($user!==false){
			$arr['login']=$user['login'];
			$arr['name_s']=$user['name_s'];
			$arr['photo']=$user['photo'];
			
			
			$arr['position_s']=$user['position_s'];
			$arr['phone_work_s']=$user['phone_work_s'];
			$arr['phone_cell_s']=$user['phone_cell_s'];
			$arr['email_s']=$user['email_s'];
			$arr['time_from_h_s']=$user['time_from_h_s'];
			$arr['time_from_m_s']=$user['time_from_m_s'];
			$arr['time_to_h_s']=$user['time_to_h_s'];
			$arr['time_to_m_s']=$user['time_to_m_s'];
			
			$arr['pasp_ser']=$user['pasp_ser'];
			$arr['pasp_no']=$user['pasp_no'];
			$arr['pasp_kogda']=$user['pasp_kogda'];
			$arr['pasp_kem']=$user['pasp_kem'];
			$arr['pasp_reg']=$user['pasp_reg'];
			
			if($user['pasp_bithday']==0) $arr['pasp_bithday']='-';
			else $arr['pasp_bithday']=date("d.m.Y",$user['pasp_bithday']);
			
			$arr['is_in_vac']=($user['vacation_till_pdate']>=time())&&($user['is_in_vacation']==1);
			$arr['vacation_till_pdate']=date("d.m.Y",$user['vacation_till_pdate']);
			
			/*$quests=array();
			$set=new mysqlSet('select q.name from question as q inner join question_user as qu on q.id=qu.question_id where qu.user_id="'.$user['id'].'" order by q.id asc');
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			for($i=0;$i<$rc;$i++){
				$f=mysqli_fetch_array($rs);
				foreach($f as $k=>$v) $f[$k]=stripslashes($v);
				$quests[]=$f;
			}
			*/
			
				//��������
			$rg=new UserContactDataGroup;
		  	$arr['contacts']=$rg->GetItemsByIdArr($user['id']);
		
			
			//$arr['questions']=$quests;
			
			//���. ����� ������:
			$_uig=new UserIntGroup;
			$arr['ints']=$_uig->GetItemsByIdArr($user['id']);
		}
		return $arr;
	}
	
	
	
	
	
	
	//�������� ����������� ��������
	public function CanDelete($id){
		$can_delete=true;
		
		$set=new mysqlSet('select count(*) from kp where (user_confirm_price_id="'.$id.'"  ) and is_confirmed_price=1');
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);
		
		if($f[0]>0) $can_delete=$can_delete&&false;
		
		
		$set=new mysqlSet('select count(*) from bill where (user_confirm_price_id="'.$id.'" or user_confirm_shipping_id="'.$id.'" ) and is_confirmed_shipping=1');
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);
		
		if($f[0]>0) $can_delete=$can_delete&&false;
		
		
		$set=new mysqlSet('select count(*) from sh_i where user_confirm_id="'.$id.'" and is_confirmed=1');
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);
		
		if($f[0]>0) $can_delete=$can_delete&&false;
		
		
		$set=new mysqlSet('select count(*) from acceptance where user_confirm_id="'.$id.'" and is_confirmed=1');
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);
		
		if($f[0]>0) $can_delete=$can_delete&&false;
		
		
		$set=new mysqlSet('select count(*) from trust where user_confirm_id="'.$id.'" and is_confirmed=1');
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);
		
		if($f[0]>0) $can_delete=$can_delete&&false;
		
		
		$set=new mysqlSet('select count(*) from payment where user_confirm_id="'.$id.'" and is_confirmed=1');
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);
		
		
		if($f[0]>0) $can_delete=$can_delete&&false;
		
		
		
		return $can_delete;
	}
}
?>
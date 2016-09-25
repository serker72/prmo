<?
require_once('abstractfileitem.php');

require_once('messageitem.php');
require_once('usersgroup.php');

require_once('authuser.php');
require_once('actionlog.php');
require_once('rl/rl_man.php');


require_once('filefolderitem.php');


// ����
class FilePoItem extends AbstractFileItem{
	protected $storage_id;
	protected $storage_name;
	protected $storage_path;
	
	protected $_rl_man;
	
	public function __construct($id=1){
		$this->init($id);
	}
	
	//��������� ���� ����
	protected function init($id){
		$this->tablename='file';
		$this->item=NULL;
		$this->pagename='files.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='user_id';
			
		$this->storage_id=$id;	
		$this->storage_name='storage_id';	
		$this->storage_path=ABSPATH.'upload/files/po/';	
		$this->_rl_man= new RLMan;
	}
	
	
	//�������� 
	public function Add($params){
		$params[$this->storage_name]=$this->storage_id;
		
		
		$code=parent::Add($params);
		
		$sql='select s.* from user as s inner join user_rights as us on s.id=us.user_id and us.right_id=2 and us.object_id=28 where s.is_active=1 ';
		
		$set=new mysqlSet($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		
		
		$_ffi=new FileFolderItem($this->storage_id);
		
		$fld='';
		if(isset($params['folder_id'])&&($params['folder_id']>0)){
			$ffi=$_ffi->GetItemById($params['folder_id']);
			
			$fld=' � ����� '.SecStr($ffi['filename']);
		}
		
		$message_to_managers="
			  <div><em>������ ��������� ������������� �������������.</em></div>
			  <div>��������� �������!</div>
			  <div>�������, ".date("d.m.Y").", � ������� &quot;����� � ���������&quot; ".$fld." ��� �������� ���� $params[orig_name].</div>
			  <div>�������� ������ ���� �� ������ � ������� &quot;����� � ��������� &quot; ".$fld."  ��������� &quot;".SITETITLE."&quot;.</div>
			 
			  ";
		$mi=new MessageItem();
		
		for($i=0; $i<$rc; $i++){
			$v=mysqli_fetch_array($rs);
			 $params1=array();
			 
			 //��������� ������ � ����� ������� ������������.
			if(isset($params['folder_id'])&&($params['folder_id']>0)){
				if(!$this->_rl_man->CheckFullAccess($v['id'], $params['folder_id'], 37, 'w', 'file', $this->storage_id, $has_control)) continue;
			}
			  
			  $params1['topic']='����� ���� � ������� ����� � ��������� ';
			  $params1['txt']=$message_to_managers;
			  $params1['to_id']= $v['id'];
			  $params1['from_id']=-1; //�������������� ������� �������� ���������
			  $params1['pdate']=time();
			  
			  $mi->Send(0,0,$params1,false);	
				
			
		}
		
		
		
		
		return $code;
	}
	
	//�������
	public function Edit($id,$params, $item=NULL, $result=NULL){
		$params[$this->storage_name]=$this->storage_id;
		
		$au=new AuthUser;
		$log=new ActionLog;
		if($item===NULL) $item=$this->GetItemById($id);
		if($result===NULL) $result=$au->Auth();
		
		if(isset($params['folder_id'])&&($params['folder_id']!=$item['folder_id'])){
			
			$_fi=new FileFolderItem($this->storage_id);
			
			$oldf=$_fi->GetItemById($item['folder_id']);
			$newf=$_fi->GetItemById($params['folder_id']);
			
			if($item['folder_id']==0) $oldf['filename']='�������� �����';
			if($params['folder_id']==0) $newf['filename']='�������� �����';
			
			
			$log->PutEntry($result['id'], '���������� ����', NULL, 575, NULL, '���� '.SecStr($item['orig_name']).' ��������� �� ����� '.SecStr($oldf['filename']).' � ����� '.SecStr($newf['filename']).'',$id);
			
			//��������� ����, ��� ����� ������
			$sql='select s.* from user as s inner join user_rights as us on s.id=us.user_id and us.right_id=2 and us.object_id=28 where s.is_active=1 ';
		
			$set=new mysqlSet($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
		
			$_ffi=new FileFolderItem($this->storage_id);
			
			$fld='';
			if(isset($params['folder_id'])&&($params['folder_id']>0)){
				$ffi=$_ffi->GetItemById($params['folder_id']);
				
				$fld=' � ����� '.SecStr($ffi['filename']);
			}
			
			$message_to_managers="
				  <div><em>������ ��������� ������������� �������������.</em></div>
				  <div>��������� �������!</div>
				  <div>�������, ".date("d.m.Y").", � ������� &quot;����� � ��������� &quot; ���� $item[orig_name]  ��������� �� ����� ".SecStr($oldf['filename'])." � ����� ".SecStr($newf['filename'])."</div>
				  <div>�������� ������ ���� �� ������ � ������� &quot;����� � ��������� &quot;  � ����� ".SecStr($newf['filename'])." ��������� &quot;".SITETITLE."&quot;.</div>
				 
				  ";
			$mi=new MessageItem();
			
			for($i=0; $i<$rc; $i++){
				$v=mysqli_fetch_array($rs);
				 $params1=array();
				 
				 //��������� ������ � ����� ������� ������������.
			if(isset($params['folder_id'])&&($params['folder_id']>0)){
				if(!$this->_rl_man->CheckFullAccess($v['id'], $params['folder_id'], 37, 'w', 'file', $this->storage_id, $has_control)) continue;
			}
				  
				  $params1['topic']='����������� ����� � ������� ����� � ���������  ';
				  $params1['txt']=$message_to_managers;
				  $params1['to_id']= $v['id'];
				  $params1['from_id']=-1; //�������������� ������� �������� ���������
				  $params1['pdate']=time();
				  
				  $mi->Send(0,0,$params1,false);	
					
				
			}
			
			
		}
		
		
		return parent::Edit($id,$params);
	}
	
	
	//��������� ������� ����� �� ������ �����
	public function GetItemByFields($params){
		$params[$this->storage_name]=$this->storage_id;
		return parent::GetItemByFields($params);
	}
	
		
	
	
	//�������� ������� ������� ������������ � �����
	public function CheckUserAccess($file_id, $user_id){
		
		return $this->FileRLCheckUserAccess($file_id, $user_id, 28, 37);
		
	}

	
}
?>
<?
require_once('abstractfileitem.php');

require_once('messageitem.php');
require_once('usersgroup.php');

require_once('fileitem.php'); //1
require_once('spitem.php'); //2
require_once('spsitem.php'); //3
require_once('filelitem.php'); //4
require_once('filepmitem.php'); //5

require_once('authuser.php');
require_once('actionlog.php');



//����� ��������� �������
class FileFolderItem extends AbstractFileItem{
	protected $storage_id;
	protected $storage_name;
	
	
	public function __construct($id=1){
		$this->init($id);
	}
	
	//��������� ���� ����
	protected function init($id){
		$this->tablename='file_folder';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='user_id';
			
		$this->storage_id=$id;	
		$this->storage_name='storage_id';
	}
	
	
	//�������� 
	public function Add($params){
		
		if(isset($params['filename'])) $params['filename']= strtoupper(substr($params['filename'], 0, 1)).substr($params['filename'],1,strlen($params['filename']));
	
	
		
		$mid=AbstractItem::Add($params);
		
		
		
		return $mid;
	}
	
	
	
	
	
	
	//�������
	public function Edit($id,$params=NULL, $item=NULL, $result=NULL){
		
		if(isset($params['filename'])) $params['filename']= strtoupper(substr($params['filename'], 0, 1)).substr($params['filename'],1,strlen($params['filename']));
		
		$au=new AuthUser;
		$log=new ActionLog;
		if($item===NULL) $item=$this->GetItemById($id);
		if($result===NULL) $result=$au->Auth();
		
		
		/*
			require_once('fileitem.php'); //1
require_once('spitem.php'); //2
require_once('spsitem.php'); //3
require_once('filelitem.php'); //4
require_once('filepmitem.php'); //5
	*/
		
		
		$_ob=NULL; $name=NULL; $can=NULL;
		switch($this->storage_id){
			case 1:
				$_ob=575;
				$can=28;
				$name='����� � ���������';
			break;
			case 2:
				$_ob=567;
				$can=29;
				$name='���������� ����������';
			break;
			case 3:
				$_ob=571;
				$can=476;
				$name='���������� ���������� - �������������';
			break;
			case 4:
				$_ob=579;
				$can=556;
				$name='����� � ��������� - ������';
				
			break;
			case 5:
				$_ob=583;
				$can=560;
				$name='����� � ��������� - ����� +/-';
			break;	
				
			default:
				$_ob=575;
				$can=28;
				$name='����� � ���������';
			;	
		}
		
		
		if(isset($params['parent_id'])&&($params['parent_id']!=$item['parent_id'])){
		
			$_fi=new FileFolderItem($this->storage_id);
			
			$oldf=$_fi->GetItemById($item['parent_id']);
			$newf=$_fi->GetItemById($params['parent_id']);
			
			if($item['parent_id']==0) $oldf['filename']='�������� �����';
			if($params['parent_id']==0) $newf['filename']='�������� �����';
			
			
			$log->PutEntry($result['id'], '���������� �����', NULL, $_ob, NULL, '����� '.SecStr($item['filename']).' ���������� �� ����� '.SecStr($oldf['filename']).' � ����� '.SecStr($newf['filename']).'',$id);
		
		
		
			//��������� ����, ��� ����� ������
			$sql='select s.* from user as s inner join user_rights as us on s.id=us.user_id and us.right_id=2 and us.object_id="'.$can.'" where s.is_active=1 ';
		
			$set=new mysqlSet($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
		
			$_ffi=new FileFolderItem($this->storage_id);
			
			
			
			$message_to_managers="
				  <div><em>������ ��������� ������������� �������������.</em></div>
				  <div>��������� �������!</div>
				  <div>�������, ".date("d.m.Y").", � ������� &quot;$name&quot; ����� $item[filename] � ��� �� ���������� ���������� �� ����� ".SecStr($oldf['filename'])." � ����� ".SecStr($newf['filename'])."</div>
				  
				 
				  ";
			$mi=new MessageItem();
			
			for($i=0; $i<$rc; $i++){
				$v=mysqli_fetch_array($rs);
				 $params1=array();
				  
				  $params1['topic']='����������� ����� � ������� '.$name;
				  $params1['txt']=$message_to_managers;
				  $params1['to_id']= $v['id'];
				  $params1['from_id']=-1; //�������������� ������� �������� ���������
				  $params1['pdate']=time();
				  
				  $mi->Send(0,0,$params1,false);	
					
				
			}
		}
		
		
		AbstractItem::Edit($id,$params);
			
	}
	
	
	//�������
	public function Del($id,$item=NULL, $result=NULL){
		//������� ���!!!!
		
		$log=new ActionLog;
		
		$au=new AuthUser;
		if($item===NULL) $item=$this->getitembyid($id);
		
		if($result===NULL) $result=$au->Auth();
		
		
		//������ ���� ���������
		$arr=array();
		
		//������� ��� ����������
		$this->SubsListView($id, $arr);
		
		//������� ��� ������ � ������
		$arr[]=$id;
		
		
		
		//������� ��� ����� � ����� � ��������
		$sql='select * from file where folder_id in ('.implode(', ', $arr).') ';
		$set=new mysqlSet($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		
		
		/*
			require_once('fileitem.php'); //1
require_once('spitem.php'); //2
require_once('spsitem.php'); //3
require_once('filelitem.php'); //4
require_once('filepmitem.php'); //5
	*/
		
		
		$_i=NULL; $del_id=NULL;
		switch($this->storage_id){
			case 1:
				$_i=new FilePoItem;
				$del_id=32;
			break;
			case 2:
				$_i=new SpItem;
				$del_id=33;
			break;
			case 3:
				$_i=new SpSItem;
				$del_id=479;
			break;
			case 4:
				$_i=new FileLetItem;
				$del_id=559;
				
			break;
			case 5:
				$_i=new FilePmItem;
				$del_id=563;
			break;	
				
			default:
				$_i=new FilePoItem;
				$del_id=32;
			;	
		}
			
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			if($_i!==NULL){
				$_i->Del($f['id']);
				
				//������ �� �������� �����	
				$log->PutEntry($result['id'],'�������������� �������� ����� ��� �������� ������������ �����',NULL, $del_id, NULL, ''.$f['orig_name'], $f['id']);
			}
		}
		
		
		
		//������ ������ �� �������� ��������� �����������+������ �������
		foreach($arr as $k=>$v){
			$nm=$this->getitembyid($v);
			
			$log->PutEntry($result['id'],'�������������� �������� ����� ��� �������� ������������ �����',NULL, $del_id, NULL, ''.$nm['filename'], $v);	
		}
		
		
		
		$q1='delete from '.$this->tablename.' where id in('.implode(', ',$arr).');';	
		
		//������� ������+����������
		$ns=new nonSet($q1);
		
		
		
		$this->item=NULL;
	}	
	
	
	
	//��������������� ������� ��� �������� �������	
	//�������� �� ������
	//������ ������ ���� �����������
	public function SubsListView($id,&$arr){
		$l_arr=$this->GetSubsList($id, $arr);
		if(count($l_arr)>0){
			foreach($l_arr as $k=>$v){
				$this->SubsListView($v,$arr);
			}
		}
		
	}
	
	//��������������� ������� ��� �������� �������
	//������ ���� ��������� �����������
	protected function GetSubsList($id, &$arr){
		$l_arr=Array();
		$query='select * from '.$this->tablename.' where parent_id="'.$id.'"';
		//echo $query.'<br>';
		
		$set=new mysqlSet($query);
		$count=$set->GetResultNumRows();
		if($count>0){
			$rs=$set->GetResult();
			for($i=0;$i<$count;$i++){
				$f=mysqli_fetch_array($rs);
				$arr[]=$f['id'];
				$l_arr[]=$f['id'];
			}
		}
		return $l_arr;
	}
	
	
	//�������� ������ �����
	public function DelOne($id){
		
		AbstractItem::Del($id);
		
		
	}
	
	
	//���� �� ����� � ����� � �����������
	public function HasFiles($id, $folders=NULL){
	
		if($folders===NULL){
			$folders=array();
			
			$this->SubsListView($id, $folders);
			
			$folders[]=$id;
			
			
		}
		
		$sql='select count(*) from file where folder_id in ('.implode(', ', $folders).') ';
		
		
		$set=new mysqlSet($sql);
		$rs=$set->GetResult();
		
		$f=mysqli_fetch_array($rs);
		if((int)$f[0]>0) return true;
		else return false;
		
	}
	
	
	//������ ��������� ����������
	//���������� ���������-�������!!!
	public function DrawNavigCli($id, $fintext=' ������ ������� ',$separator='/', $last_is_link=true, $tab_page=1){
		$txt='';
		
		$arr=Array();
		$arr=$this->RetrievePath($id, $flaglost, $vloj);
		
		
		
		$sm=new SmartyAdm;
		$sm->debug=DEBUG_INFO;
		$alls=Array();
		//������ ������ �� �������� ��������
		$alls[]=Array(
			'itemname'=>'�������� �����',
			'filepath'=>$this->pagename.'?tab_page='.$tab_page,
			'has_symb'=>true,
			'symb'=>$separator
		);
		
		foreach($arr as $k=>$v){
			foreach($v as $kk=>$vv){
				
				
				if($kk!=$id) $has_symb=true; else $has_symb=false;
				
				$path=$this->ConstructUrl($vv['path'], $tab_page);
				
				$alls[]=array(
					'itemname'=>stripslashes($vv['name']),
					'filepath'=>$path,
					'has_symb'=>$has_symb,
					'symb'=>$separator
				);
			}
		}
		
		$sm->assign('items',$alls);
		$sm->assign('aftertext',$fintext);
		
		$sm->assign('last_is_link',$last_is_link);
		$txt=$sm->fetch('files/navi.html');
		
		return $txt;
	}
	
	
	//������������ ������ ���� /path1/path2/...
	//����������� ���� � �������� ������� � �����
	public function ConstructPath($id,$is_shown=0,$separator='/'){
		$path='';
		$t_arr=Array();
		$t_arr=$this->RetrievePath($id, $flaglost, $vloj,  $is_shown);
		
		if($flaglost) {
			//echo ' LOST ';
			return $this->error404;//'/404.php';
		}
		
		//echo 'beg <p>';
		foreach($t_arr as $tk=>$tv){
			//echo "odna strukt <p>";
			foreach ($tv as $key=>$value){
				//echo "podstr <p>";
				//echo "$key $value<p>";
				if($key!=0){
					/*$tm=new MmenuItem();
					$ttm=$tm->GetItemById($key,$lang_code);
					$path.=stripslashes($ttm['path']).$separator;*/
					//unset($tm);
					$path.=stripslashes($value['path']).$separator;
					//echo "$key = $value<p>";
				}else $path=$separator;
			}
		}
		//echo " end: $path<p>";
		return $path;
	}
	
	
	
	//�������� ���� � �������
	//� ����� ������� �����������
	public function RetrievePath($id, &$flaglost, &$vloj){
		unset($path);
		$path=array(); 
		//���� ���, �� ���� ������ ����!
		$flaglost=false;
		$vloj=0;
		
		
		$x=$this->GetItemById($id);
		
		if($x!=false){
			$temp_arr=array();
			//$temp_arr[$x['id']]=$x['name'];
			$temp_arr[$x['id']]=array(
						'name'=>$x['filename'],
						'path'=>$x['id']
					);
			
			$path[]=$temp_arr;
			
			
			
		}else $flaglost=true;
		
		
		if($x['parent_id']!=0){
			
			
			
			$parent_id=$x['parent_id'];
			$count=999;
			while(($count!=0)&&($parent_id!=0)){
				//echo $is_shown;
				//echo $x['parent_id'];
				
				$x=$this->GetItemById($parent_id);
				if($x!=false){
					$count=999;
					$parent_id=$x['parent_id'];
										//echo "$f[id]<br>";
					
					$temp_arr=Array();
					//$temp_arr[$x['id']]=$x['name'];
					$temp_arr[$x['id']]=Array(
						'name'=>$x['filename'],
						'path'=>$x['id']
					);
					$path[]=$temp_arr;
					$vloj++;
				}else{
					$count=0;
					$flaglost=true;
					//echo 'qqqqqqqqqqqqqqqqqqqqqqq';
				}
			}
		
		}
		
		$path=array_reverse($path);
		//array_reverse($path);
		return $path;
	}
	
	
	
	//���������� ����������� ������
	public function ConstructUrl($id, $tab_page=1){
		$result=$this->pagename.'?tab_page='.$tab_page.'&folder_id='.$id;
		
		return $result;
	}
	
	public function SetPageName($pagename){
		$this->pagename=$pagename;
	}
		
}
?>
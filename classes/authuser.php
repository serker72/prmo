<?
require_once('global.php');
require_once('useritem.php');
require_once('usersession.php');
require_once('discr_man_cache.php');

require_once('user_to_user.php');
require_once('supplier_to_user.php');

require_once('actionlog.php');
require_once('opfitem.php');

//require_once('sectoritem.php');
//require_once('sectortouser.php');


//����� ��� ���������� ������ � ������
class AuthUser{
	//����������� ��������� �������
	static protected $profile;
	
	
	//��������� ������ ������ �������������
	public $users_sessions;
	
	//��������� ������ ������� ���� ������������
	public $user_rights;
	
	
	protected $sess_name; //��� ������, ��� ����
	protected $show_errors=true;
	protected $err_mess=Array();
	protected $err_code=1;
	
	protected $max_cookie_time=3900; //3900; //����� ����� �������
	
	public function __construct($sess_name='sya_user'){
		$this->init($sess_name);
	}
	
	//��������� ���� ����
	protected function init($sess_name){
		$this->sess_name=$sess_name;
		
		$this->users_sessions=new UserSession();
		$this->user_rights=new DiscrManCache; //=NULL;
		
		if(!isset($_SESSION[$this->sess_name])) $_SESSION[$this->sess_name]=Array();
		
		$this->err_mess[1]='�������� �����/������!';
		$this->err_mess[2]='�������� �����/������!';
		$this->err_mess[3]='�������� �����/������!';
		$this->err_mess[4]='�������� �����/������!';
		$this->err_mess[5]='���������� �������� ������������!';
		$this->err_mess[6]='���������� ��������������� �������!';
		$this->err_mess[7]='������ ����������� ��� �������������� �������.';
		
		$this->err_mess[8]='��������� ���� �����!';
		$this->err_mess[9]='����� ����� ��� ����������!';
		
		$this->err_mess[10]='������� �������� ������!';
		$this->err_mess[11]='��������� ���� e-mail!';
		 
		$this->err_mess[12]='������ ������ � ��������'; //������ ����, �� ��� ��������
		
		$this->err_mess[13]='����� ������ � ��� ������������� �� ���������!'; //
		
		$this->err_mess[14]='������ �������� ������� ������!'; //�������� ������� ������ ��� ������ �������
		$this->err_mess[15]='������ �������� ������� ������!';
		
	}
	
	//������� ����������� (���������� �� ������, ������)
	public function Authorize($login,$password,$rem_me=false){
		if($this->AuthUser($login, $password)){
			$_SESSION[$this->sess_name]['username']=$login;
			$_SESSION[$this->sess_name]['password']=$password;
			//if($rem_me){
				@setcookie("user[0]", $login,time()+$this->max_cookie_time,'/');				
				@setcookie("user[1]", $password,time()+$this->max_cookie_time,'/');	
				@setcookie("user[2]", '',time()-84000,'/');
				
			//}
			unset($_SESSION[$this->sess_name]['org_id']);
			
		}
	}
	
	//������� ������
	public function DeAuthorize(){
		if(isset(self::$profile['id'])) {
			$this->users_sessions->DelSession(self::$profile['id']);
			$this->user_rights->ClearRightsTable(); //=NULL;
			self::$profile=NULL;
			//self::$user_rights_table=NULL;
		}
		unset($_SESSION[$this->sess_name]['username']);
		unset($_SESSION[$this->sess_name]['password']);
		@setcookie("user[0]", '',time()-84000,'/');
		@setcookie("user[1]", '',time()-84000,'/');
		
		unset($_SESSION[$this->sess_name]['org_id']);
		@setcookie("user[2]", '',time()-84000,'/');
		
		
	}
	
	//������� ����������� ������������ ������
	public function AuthorizeOrgId($org_id){
		if($this->AuthOrgId($org_id)){
			
			
			$_SESSION[$this->sess_name]['org_id']=$org_id;
			/*if($rem_me){
				@setcookie("user[2]", $org_id,time()+$this->max_cookie_time,'/');				
				
			}*/
			
		}
	}
	
	
	//������� �������� ����������� (���������� ������, ������ � ��.)
	public function Auth($make_cookie=true,$try_session=false, $fix_activity=true, $build_rights=true){
	
		$err_code=1;//����������� �� ���������	
		//print_r( debug_backtrace());
		//echo '<h1>��� </h1>';
		
		
		if(isset($_COOKIE['user'])){
			
			$value['username'] = $_COOKIE['user']['0'];
			$value['password'] = $_COOKIE['user']['1'];			
			if(@$this->AuthUser(($value['username']), $value['password'], $fix_activity, $build_rights))	{
				//�������������� �����!
				$_SESSION[$this->sess_name]['username']=$value['username'];
				$_SESSION[$this->sess_name]['password']=$value['password'];	
				
				//��� ��� ��������� � ������� AuthUser
				
				if($make_cookie){
					@setcookie("user[0]", $value['username'],time()+$this->max_cookie_time,'/');				
					@setcookie("user[1]", $value['password'],time()+$this->max_cookie_time,'/');	
				}
				
				$err_code=0;//��� ������
			}else{
				$err_code=2;//����� �� ������� ��� � ��
			}			
		}elseif(($try_session)&&(isset($_SESSION[$this->sess_name]['username'])&&isset($_SESSION[$this->sess_name]['password']))){
			//��������� ���-��
			//��������� �����-������
			//���� �������, ������ ����������
			if(@$this->AuthUser(($_SESSION[$this->sess_name]['username']), $_SESSION[$this->sess_name]['password'], $fix_activity, $build_rights))	{
			
				//�������������� �����!
				//�������� ��� 
				$value['username'] = $_SESSION[$this->sess_name]['username'];
				$value['password'] = $_SESSION[$this->sess_name]['password'];
				
				$err_code=0;//��� ������			
			}else{
				$err_code=4;//����� �� ������ ��� � ��			
			}	
		
		}else{	
			$err_code=3;//����� �� ���������, ������� ���		
			
		}
		
		
		if($err_code==0){
			
			
		}
		$this->FA_Auth();
		$this->err_code=$err_code;
		return self::$profile;
	}
	
	//������� �������� ������-������
	//���� ��� �������� ��������, �� ���������� ���, � ��������� �������
	//���� ��� �������� �� ��������, �����-� ����, ������ ������� ����� NULL
	public function AuthUser($login,$password, $fix_activity=true, $build_rights=true){
	
		$us=new mysqlSet("select * from user where login=\"".SecStr($login,10)."\" and password=\"".SecStr($password,10)."\" and is_active=1;");
		
		$nr=$us->getResult();
		$rc=$us->GetResultNumRows();
		if($rc>=1){
			$f =  mysqli_fetch_array($nr);
			
			if($fix_activity) $this->users_sessions->UpdateSession($f['id']);
			//$this->user_rights=new DiscrManCache();
			//self::$user_rights_table=$this->user_rights->GetRightsTable();
			if($build_rights) $this->user_rights->BuildRightsTable($f['id']);
			self::$profile=$f;
			
			//�������� ���� setted_password - ���������� ����������� � ����� ������:
			if($f['setted_password']!=""){
				$_ui=new UserItem;
				$_ui->Edit($f['id'], array('setted_password'=>''));	
			}

			
			return true;
		}else{
			
			self::$profile=NULL;
			//self::$user_rights_table=NULL;
			//$this->user_rights=NULL;
			$this->user_rights->ClearRightsTable();
		    return false;
		}
	}
	
	
	
	
	
	//������� ������ � �������� �� ������, ������� ������ � ���� ������ ������
	protected function FA_Auth(){
		//echo 'ZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZ';
		//var_dump($_SESSION);
		if(isset($_SESSION[$this->sess_name]['org_id'])){
			//
			//��������� ���-��
			//��������� �����-������
			//���� �������, ������ ����������
			if(@$this->AuthOrgId($_SESSION[$this->sess_name]['org_id']))	{
				//�������������� �����!
				//�������� ��� 
				$value['org_id'] = $_SESSION[$this->sess_name]['org_id'];
				
				//$err_code=0;//��� ������			
			}else{
				//$err_code=4;//����� �� ������ ��� � ��	
				$_SESSION[$this->sess_name]['org_id']=false;
					
			}	
		}else{
			if(isset($_COOKIE['user'])){
				//� ������ ��� ���-��, �������� ������	
				$value['org_id'] = $_COOKIE['user']['2'];
						
				if(@$this->AuthOrgId($value['org_id']))	{
					//�������������� �����!
					$_SESSION[$this->sess_name]['org_id']=$value['org_id'];
					
					//��� ��� ��������� � ������� AuthUser
					
					@setcookie("user[2]", $value['org_id'],time()+$this->max_cookie_time,'/');				
					//$err_code=0;//��� ������
				}else{
					//$err_code=2;//����� �� ������� ��� � ��
					@setcookie("user[2]", false,time()+$this->max_cookie_time,'/');				
					
					$_SESSION[$this->sess_name]['org_id']=false;
					
				}			
			}
		}
	  if(!(isset($_SESSION[$this->sess_name]['org_id']))&&!isset($_COOKIE['user'])){		
		  //$err_code=3;//����� �� ���������, ������� ���
		  $_SESSION[$this->sess_name]['org_id']=false;
		
		  $this->AuthOrgId(false);  
	  }
	  
		
		
	}
	
	
	
	//������� �������� ������������ ������
	public function AuthOrgId($org_id){
		if(self::$profile!==NULL){
			
			
			//���������, ������� ������� � ������������
				$us=new mysqlSet("select count(*) from  supplier where is_org=1 and is_active=1 and id in(select distinct org_id from supplier_to_user where user_id=\"".self::$profile['id']."\")");
				$nr=$us->getResult();
				$g=mysqli_fetch_array($nr);
				
				//��� ������
				//var_dump($_SESSION);
				if($g[0]==0){
					self::$profile['org_id']=false;
					
					return false;
				}elseif($g[0]==1){
				//1 �����
					
					$us=new mysqlSet("select * from supplier where is_org=1 and is_active=1 and id in(select distinct org_id from supplier_to_user where user_id=\"".self::$profile['id']."\")");
		
					$nr=$us->getResult();
					$rc=$us->GetResultNumRows();
					if($rc>=1){
						$f =  mysqli_fetch_array($nr);
						self::$profile['org_id']=$f['id'];
						/*
						$_opf=new OpfItem; $opf=$_opf->GetItemById($f['opf_id']);
						$log= new ActionLog;
						
						$log->PutEntry(self::$profile['id'],'�������������� ����� �����������',NULL,NULL,NULL,SecStr($f['full_name'].', '.$opf['name']));*/
						
						return true;
					}
					
				}else{
				//����� ������� - ��������� �������
			
						
				
					$us=new mysqlSet("select * from supplier where is_org=1 and is_active=1 and id=\"".$org_id."\" and id in(select distinct org_id from supplier_to_user where user_id=\"".self::$profile['id']."\")");
					
					
				
					
					$nr=$us->getResult();
					$rc=$us->GetResultNumRows();
					if($rc>=1){
						/*echo "select * from fact_address where user_id=\"".self::$profile['id']."\" and id=\"".abs((int)$fact_address_id)."\" and pin=\"".SecStr($pin,10)."\";<br>";
						echo 'eeee<br>';
						*/
						$f =  mysqli_fetch_array($nr);
						self::$profile['org_id']=$f['id'];
						
						return true;
					}else{
						self::$profile['org_id']=false;
						
						return false;	
					}
				
				
				}
			/*}else{
				self::$profile['fact_address_id']='0';
				self::$profile['pin']='';
				return true;
				
			}*/
			
		}else return false;
		
	}
	
	
	
	
	
	
	
	//��������, �������� ��� ��� ���� �����, � ���� �� ��� ���������
	public function CheckOrgId(){
		//var_dump(self::$profile);
		
		if(self::$profile!==NULL){
					
		  if((self::$profile['org_id']===false)) return false;
		  else return true;	
	
		}else return false;
	}
	
	
	
	
	
	
	
	
	
	//���������� ������� �������
	public function GetProfile(){
		return self::$profile;
	}
	
	//���������� ������� ������� ����
	public function GetRightsTable(){
		/*if($this->user_rights==NULL) return NULL;
		else{*/
			return $this->user_rights->GetRightsTable();	
		//}
		
	}
	
	//����� ��������� �� ������ �� ���� ������
	public function ShowError($code){
		if(($this->show_errors)&&isset($this->err_mess[$code])) return $this->err_mess[$code];
		else return '';
	}
	
	public function GetErrorCode(){
		return $this->err_code;	
	}
	
	//��������� ����� ������ ������
	public function SetShowErrors($flag){
		$this->show_errors=$flag;
	}
	
	
	//���������, ����� �� ����������� ������ �� �������
	public function FltSector($result, $s_s_id=NULL){
		$res=true;
		
		if($this->user_rights->CheckAccess('w',136)){
			
			$res=false;
			//echo '<h1>zzzu</h1>';
			
		}else{
			//echo '<h1>zzz2</h1>';
		  $_ssg=new SectorToUser;
		  $sectors=$_ssg->GetSectorArr($result['id']);
		  if(count($sectors)==0){
			  $res=false;
			 
		  }else foreach($sectors as $k=>$v){
			
			
			$_si=new SectorItem;
			$si=$_si->GetItemById($v);
			
			if($si['is_central']==1){
				$res=false;
				//
				
				break;	
			}
		  }
		  
		  if(($s_s_id!==NULL)&&!$res){
		  		//�������������� ��������
				$res=true;  
				//echo '<h1>zzz</h1>';
		  }
		  
		  
		}
		
		
		
		
		return $res;
	}
	
	
	
	//���������, ����� �� ����������� ������ �� ���-���
	public function FltUser($result ){
		$res=false;
		
		if($result['has_restricted_users']==1){
			
			$res=true;
			
			
		}else{
			//echo '<h1>zzz2</h1>';
		 	$res=false;
		}
		 
		
		return $res;
	}
	
	//���������, ����� �� ����������� ������ �� �-���
	public function FltSupplier($result ){
		$res=false;
		
		if($result['has_restricted_suppliers']==1){
			
			$res=true;
			
		//���� ��� ���� ������� �� ���� ������ ������������ - ����������� ������!	
		}elseif(!$this->user_rights->CheckAccess('w',909)){
			//echo '<h1>zzz2</h1>';
			$res=true;
		 
		}else{
			$res=false;
		}
		 
		//var_dump($res);
		return $res;
	}
	
	public function GetMaxCookieTime(){
		return $this->max_cookie_time;
	}


}
?>
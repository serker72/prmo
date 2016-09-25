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


//класс для клиентской работы с юзером
class AuthUser{
	//статическая структура профиля
	static protected $profile;
	
	
	//экземпляр класса сессий пользователей
	public $users_sessions;
	
	//экземпляр класса таблицы прав пользователя
	public $user_rights;
	
	
	protected $sess_name; //имя сессии, где юзер
	protected $show_errors=true;
	protected $err_mess=Array();
	protected $err_code=1;
	
	protected $max_cookie_time=3900; //3900; //время жизни кукисов
	
	public function __construct($sess_name='sya_user'){
		$this->init($sess_name);
	}
	
	//установка всех имен
	protected function init($sess_name){
		$this->sess_name=$sess_name;
		
		$this->users_sessions=new UserSession();
		$this->user_rights=new DiscrManCache; //=NULL;
		
		if(!isset($_SESSION[$this->sess_name])) $_SESSION[$this->sess_name]=Array();
		
		$this->err_mess[1]='Неверный логин/пароль!';
		$this->err_mess[2]='Неверный логин/пароль!';
		$this->err_mess[3]='Неверный логин/пароль!';
		$this->err_mess[4]='Неверный логин/пароль!';
		$this->err_mess[5]='Невозможно добавить пользователя!';
		$this->err_mess[6]='Невозможно отредактировать профиль!';
		$this->err_mess[7]='Ошибка авторизации при редактировании профиля.';
		
		$this->err_mess[8]='Заполните поле Логин!';
		$this->err_mess[9]='Такой логин уже существует!';
		
		$this->err_mess[10]='Слишком короткий пароль!';
		$this->err_mess[11]='Заполните поле e-mail!';
		 
		$this->err_mess[12]='Ошибка работы с профилем'; //ошибка есть, но код потеряли
		
		$this->err_mess[13]='Новый пароль и его подтверждение не совпадают!'; //
		
		$this->err_mess[14]='Введен неверный текущий пароль!'; //неверный текущий пароль при правке профиля
		$this->err_mess[15]='Введен неверный текущий пароль!';
		
	}
	
	//функция авторизации (авторизуем по логину, паролю)
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
	
	//функция выхода
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
	
	//функция авторизации фактического адреса
	public function AuthorizeOrgId($org_id){
		if($this->AuthOrgId($org_id)){
			
			
			$_SESSION[$this->sess_name]['org_id']=$org_id;
			/*if($rem_me){
				@setcookie("user[2]", $org_id,time()+$this->max_cookie_time,'/');				
				
			}*/
			
		}
	}
	
	
	//функция проверки авторизации (опрашивает сессии, кукисы и пр.)
	public function Auth($make_cookie=true,$try_session=false, $fix_activity=true, $build_rights=true){
	
		$err_code=1;//авторизация не выполнена	
		//print_r( debug_backtrace());
		//echo '<h1>АВТ </h1>';
		
		
		if(isset($_COOKIE['user'])){
			
			$value['username'] = $_COOKIE['user']['0'];
			$value['password'] = $_COOKIE['user']['1'];			
			if(@$this->AuthUser(($value['username']), $value['password'], $fix_activity, $build_rights))	{
				//приветствовать юзера!
				$_SESSION[$this->sess_name]['username']=$value['username'];
				$_SESSION[$this->sess_name]['password']=$value['password'];	
				
				//это все переехало в функцию AuthUser
				
				if($make_cookie){
					@setcookie("user[0]", $value['username'],time()+$this->max_cookie_time,'/');				
					@setcookie("user[1]", $value['password'],time()+$this->max_cookie_time,'/');	
				}
				
				$err_code=0;//нет ошибок
			}else{
				$err_code=2;//юзера из кукисов нет в бд
			}			
		}elseif(($try_session)&&(isset($_SESSION[$this->sess_name]['username'])&&isset($_SESSION[$this->sess_name]['password']))){
			//проверить пол-ля
			//проверить логин-пароль
			//если неверно, значит разлогинен
			if(@$this->AuthUser(($_SESSION[$this->sess_name]['username']), $_SESSION[$this->sess_name]['password'], $fix_activity, $build_rights))	{
			
				//приветствовать юзера!
				//получаем имя 
				$value['username'] = $_SESSION[$this->sess_name]['username'];
				$value['password'] = $_SESSION[$this->sess_name]['password'];
				
				$err_code=0;//нет ошибок			
			}else{
				$err_code=4;//юзера из сессии нет в бд			
			}	
		
		}else{	
			$err_code=3;//никто не залогинен, кукисов нет		
			
		}
		
		
		if($err_code==0){
			
			
		}
		$this->FA_Auth();
		$this->err_code=$err_code;
		return self::$profile;
	}
	
	//функция проверки логина-пароля
	//если все проверки пройдены, то возвращает тру, и обновляет профиль
	//если все проверки НЕ пройдены, возвр-т фолс, вместо профиля будет NULL
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
			
			//сбросить поле setted_password - прекратить уведомления о смене пароля:
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
	
	
	
	
	
	//функция чтения и проверки из сессии, кукисов данных о факт адресе дилера
	protected function FA_Auth(){
		//echo 'ZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZ';
		//var_dump($_SESSION);
		if(isset($_SESSION[$this->sess_name]['org_id'])){
			//
			//проверить пол-ля
			//проверить логин-пароль
			//если неверно, значит разлогинен
			if(@$this->AuthOrgId($_SESSION[$this->sess_name]['org_id']))	{
				//приветствовать юзера!
				//получаем имя 
				$value['org_id'] = $_SESSION[$this->sess_name]['org_id'];
				
				//$err_code=0;//нет ошибок			
			}else{
				//$err_code=4;//юзера из сессии нет в бд	
				$_SESSION[$this->sess_name]['org_id']=false;
					
			}	
		}else{
			if(isset($_COOKIE['user'])){
				//в сессии нет пол-ля, проверим кукисы	
				$value['org_id'] = $_COOKIE['user']['2'];
						
				if(@$this->AuthOrgId($value['org_id']))	{
					//приветствовать юзера!
					$_SESSION[$this->sess_name]['org_id']=$value['org_id'];
					
					//это все переехало в функцию AuthUser
					
					@setcookie("user[2]", $value['org_id'],time()+$this->max_cookie_time,'/');				
					//$err_code=0;//нет ошибок
				}else{
					//$err_code=2;//юзера из кукисов нет в бд
					@setcookie("user[2]", false,time()+$this->max_cookie_time,'/');				
					
					$_SESSION[$this->sess_name]['org_id']=false;
					
				}			
			}
		}
	  if(!(isset($_SESSION[$this->sess_name]['org_id']))&&!isset($_COOKIE['user'])){		
		  //$err_code=3;//никто не залогинен, кукисов нет
		  $_SESSION[$this->sess_name]['org_id']=false;
		
		  $this->AuthOrgId(false);  
	  }
	  
		
		
	}
	
	
	
	//фукнция проверки фактического адреса
	public function AuthOrgId($org_id){
		if(self::$profile!==NULL){
			
			
			//проверить, сколько адресов у пользователя
				$us=new mysqlSet("select count(*) from  supplier where is_org=1 and is_active=1 and id in(select distinct org_id from supplier_to_user where user_id=\"".self::$profile['id']."\")");
				$nr=$us->getResult();
				$g=mysqli_fetch_array($nr);
				
				//нет адреса
				//var_dump($_SESSION);
				if($g[0]==0){
					self::$profile['org_id']=false;
					
					return false;
				}elseif($g[0]==1){
				//1 адрес
					
					$us=new mysqlSet("select * from supplier where is_org=1 and is_active=1 and id in(select distinct org_id from supplier_to_user where user_id=\"".self::$profile['id']."\")");
		
					$nr=$us->getResult();
					$rc=$us->GetResultNumRows();
					if($rc>=1){
						$f =  mysqli_fetch_array($nr);
						self::$profile['org_id']=$f['id'];
						/*
						$_opf=new OpfItem; $opf=$_opf->GetItemById($f['opf_id']);
						$log= new ActionLog;
						
						$log->PutEntry(self::$profile['id'],'автоматический выбор организации',NULL,NULL,NULL,SecStr($f['full_name'].', '.$opf['name']));*/
						
						return true;
					}
					
				}else{
				//много адресов - проверяем текущий
			
						
				
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
	
	
	
	
	
	
	
	//проверка, заполнен или нет факт адрес, и надо ли его заполнять
	public function CheckOrgId(){
		//var_dump(self::$profile);
		
		if(self::$profile!==NULL){
					
		  if((self::$profile['org_id']===false)) return false;
		  else return true;	
	
		}else return false;
	}
	
	
	
	
	
	
	
	
	
	//возвращает текущий профиль
	public function GetProfile(){
		return self::$profile;
	}
	
	//возвращает текущую таблицу прав
	public function GetRightsTable(){
		/*if($this->user_rights==NULL) return NULL;
		else{*/
			return $this->user_rights->GetRightsTable();	
		//}
		
	}
	
	//вывод сообщения об ошибке по коду ошибки
	public function ShowError($code){
		if(($this->show_errors)&&isset($this->err_mess[$code])) return $this->err_mess[$code];
		else return '';
	}
	
	public function GetErrorCode(){
		return $this->err_code;	
	}
	
	//установка флага показа ошибок
	public function SetShowErrors($flag){
		$this->show_errors=$flag;
	}
	
	
	//проверить, нужно ли накладывать фильтр по участку
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
		  		//дополнительная проверка
				$res=true;  
				//echo '<h1>zzz</h1>';
		  }
		  
		  
		}
		
		
		
		
		return $res;
	}
	
	
	
	//проверить, нужно ли накладывать фильтр по пол-лям
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
	
	//проверить, нужно ли накладывать фильтр по к-там
	public function FltSupplier($result ){
		$res=false;
		
		if($result['has_restricted_suppliers']==1){
			
			$res=true;
			
		//если нет прав доступа ко всем картам контрагентов - накладывать фильтр!	
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
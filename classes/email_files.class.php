<?
require_once('abstractitem.php');
require_once('actionlog.php');

require_once('fileitem.php');
require_once('spitem.php');
require_once('spsitem.php');
require_once('filelitem.php');
require_once('filepmitem.php');
require_once('contractuchitem.php');
require_once('contractitem.php');
require_once('supplier_akt_item.php');
require_once('supplier_sh_item.php');
require_once('supplier_file_item.php');



require_once('suppliercontactdatagroup.php');
require_once('suppliercontactdataitem.php');

require_once('usercontactdatagroup.php');
require_once('usercontactdataitem.php');

require_once('supplieritem.php');
require_once('suppliercontactitem.php');

require_once('user_s_item.php');

require_once('authuser.php');


require_once('phpmailer/class.phpmailer.php');


//библиотека классов отправки файлов на электронную почту


//работа с почтой: получить список и отправить
class EmailFiles_Former{
	
	protected $classes;
	
	//отправить файл
	public function SendFile($file_id, $load_name, $addresses, $result=NULL){
		$au=new AuthUser;
		$log=new actionlog;
		if($result===NULL) $result=$au->Auth();
		
		$hash=md5(time()+$file_id+$load_name);
		
		$_ef=new EmailFilesItem;
		$params=array();
		$params['hash']=$hash;
		$params['pdate']=time();
		$params['load_name']=$load_name;
		$params['file_id']=$file_id;
		
		$_ef->Add($params);
		
		 
		$_orgitem=new OrgItem;
		$org=$_orgitem->GetItemById($result['org_id']);
		$_opf=new OpfItem;
		$opf=$_opf->GetItemById($org['opf_id']);
		
		//находим нужный класс для фильтрации. 
		$instance=new EmailFiles_AbstractFileItem;
		foreach($this->classes as $k=>$v){
			if($v->load_name==$load_name){
				$instance=$v;
				break;	
			}
		}
 
		$item=$instance->instance->GetItemById($file_id);
		
		$_uci=new UserContactDataItem;
		$_ui=new UserSItem;
		
		$_sdi=new SupplierContactDataItem;
		$_sci=new SupplierContactItem;
		$_si=new supplieritem;
		
		foreach($addresses as $k=>$v){
			$valarr=explode(';',$v);
			
			/* user_name 
			
			
			$sdi=$_sdi->GetItemByFields(array('value'=>$email));
			if($sdi!==false){
				
				$sci=$_sci->GetItemById($sdi['contact_id']);
				if($sci!==false){
					$user_name=$sci['name'];
					$has_cont=true;
				}
			} 
			
			//2) в карте сотр-ка
			if(!$has_cont){
				
				$uci=$_uci->GetItemByFields(array('value'=>$email));
				$ui=$_ui->GetItemById($uci['user_id']);
				if($ui!==false) $user_name=$ui['name_s'];
				
			}
			
			*/
			//hashed=kind+";"+email_id+";"+contact_id+";"+$(element).val();
			//        0          1               2                 3
			
			if($valarr[0]==0){
				//контрагент
				$sci=$_sci->GetItemById($valarr[2]);
				$user_name=$sci['name'];
				$si=$_si->getitembyid($sci['supplier_id']);
				$desc='Отправлен контакту контрагента '.$si['full_name'].' '.$user_name;
			}else{
				//cотрудник
				$ui=$_ui->GetItemById($valarr[2]);
				$user_name=$ui['name_s'];
				$desc='Отправлен сотруднику '.' '.$user_name;
			}
			
			$mail = new PHPMailer();
			$body = "<div>Уважаемый(ая) %contact_name%!</div> <div>&nbsp;</div> <div><i>Это сообщение сформировано автоматически, просьба не отвечать на него.</i></div> <div>&nbsp;</div> <div>Отправляем Вам следующие документы: %docs%.</div> <div>&nbsp;</div> <div>Благодарим Вас за то, что Вы обратились к нам!</div> <div>С уважением, компания %opf_name% %company_name% .</div>
	"; 
			
			$body=str_replace('%contact_name%',  $user_name,$body);
			
			$body=str_replace('%docs%', '<a href="'.SITEURL.$this->GetFileName($hash).'" download>'.$item['orig_name'].'</a>',  $body);
			
			$body=str_replace('%company_name%', $org['full_name'],  $body);
			$body=str_replace('%opf_name%', $opf['name'],  $body);
			
			
		
			$mail->SetFrom(FEEDBACK_EMAIL, $opf['name'].' '.$org['full_name']);
		
			  
		
			$mail->AddAddress(trim($valarr[3]),   $valarr[3]);
		
			$mail->Subject = "документы для Вас!"; 
			$mail->Body=$body;
			
			//echo $body;
			
			/*$f=fopen(ABSPATH.'/tmp/lolo.html','w');
			fputs($f, $body);
			fclose($f);*/
			
			//foreach($filenames_to_send as $k=>$v) $mail->AddAttachment($v['fullname'], $v['name']);  
			 
			$mail->CharSet = "windows-1251";
			$mail->IsHTML(true);  
			
			if(!$mail->Send())
			{
				//echo "Ошибка отправки письма: " . $mail->ErrorInfo;
			}
			else 
			{
				// echo "Письмо отправленно!";
			} 
			
		 
			 
			
			if($instance->doc_id_name!==NULL){
				//контрагент
				$log->PutEntry($result['id'],'отправил на электронную почту файл контрагента',NULL,87, NULL,  $desc.' файл '.$item['orig_name'].',  адрес эл. почты '.$valarr[3], $item[$instance->doc_id_name]); 
			}else{
				//файл	
				
				$log->PutEntry($result['id'],'отправил на электронную почту файл из файлового хранилища',NULL,NULL, NULL, $desc.' файл '.$item['orig_name'].',  адрес эл. почты '.$valarr[3]);
			}
		}
		
	}
	
	
	
	//получить список адресатов
	public function GetAbonents($file_id, $load_name, $template, $result=NULL){
		$au=new AuthUser;
		if($result===NULL) $result=$au->Auth();
		
		//находим нужный класс для фильтрации. 
		$instance=new EmailFiles_AbstractFileItem;
		foreach($this->classes as $k=>$v){
			if($v->load_name==$load_name){
				$instance=$v;
				break;	
			}
		}
		
		 
		
//		$instance->instance->CheckUserAccess($file_id, $user_id, $result);
		$alls=array(
			'contacts'=>array(),
			'users'=>array()
		);
		
		$_sdg=new SupplierContactDataGroup;
		$_udg=new UserContactDataGroup;
		
		
		$item=$instance->instance->GetItemById($file_id);
		
		if($instance->doc_id_name!==NULL){
			//контрагент, подтянуть ветку по контрагенту	
			//$item[$instance->doc_id_name];
			$sql='
			select "0" as kind, name as name_s, "" as login, position as position_s, id, "" as email_s
			from supplier_contact
			where supplier_id="'.$item[$instance->doc_id_name].'"
			and id in(select distinct contact_id from supplier_contact_data where kind_id=5)
			order by 2 asc
			';
			
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultnumrows();
				
			for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);
				foreach($f as $k=>$v) $f[$k]=stripslashes($v);
				
				 $data=$_sdg->GetItemsByIdArr($f['id']);
			 
				
				$data1=array();
				foreach($data as $k=>$v){
					if($v['kind_id']==5) $data1[]=$v;	
				}
				
				
				 
				
				$f['data']=$data1;
				$alls['contacts'][]=$f;	
			}
		}
		
		//контакты сотрудников
		//ограничения по сотруднику
		//echo 'zzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzz';
		$limited='';
		$limited_user=NULL;
		if($au->FltUser($result)){
			
			$_u_to_u=new UserToUser();
			$u_to_u=$_u_to_u->GetExtendedViewedUserIdsArr($result['id']);
			$limited_user=$u_to_u['sector_ids'];
			$limited=' and id in('.implode(', ', $limited_user).') ';
		}
		
		$sql='select "1" as kind, u.name_s as name_s, u.login as login, u.position_s, u.id, u.email_s as email_s		
			from user as u
			 
			where u.is_active=1 
			 '.$limited.'
			 
			 order by 2 asc';
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultnumrows();	 
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			if($instance->doc_id_name!==NULL){
				//контрагент
				if(!$instance->instance->CheckUserAccess($item[$instance->doc_id_name], $f['id'], $result)) continue;
			}else{
				//файл
				if(!$instance->instance->CheckUserAccess($file_id, $f['id'], $result)) continue;
			}
			
			 
		   $data=$_udg->GetItemsByIdArr($f['id']);
		   
		   $was_in=false; foreach($data as $k=>$v) if(($v['kind_id']==5)&&($v['value']==$f['email_s'])) $was_in=$was_in||true;
		   //добавить адрес из карты
		   if(!$was_in) $data[]=array('id'=>0, 'kind_id'=>5, 'value'=>$f['email_s']);
			 
			
			$data1=array();
			foreach($data as $k=>$v){
				if($v['kind_id']==5) $data1[]=$v;	
			}
			
			
			 
			
			$f['data']=$data1;
			
			$alls['users'][]=$f;	
		
		 
		}	 
			 
		
		$sm=new SmartyAj;
		
		$sm->assign('items', $alls);
		return $sm->fetch($template);
		
		
	}
	
	//получение экземпляра класса файла и его записи по хешу
	public function GetFileByHash($hash, &$item){
		$_fi=new EmailFilesItem;
		$fi=$_fi->GetItemByFields(array('hash'=>$hash));	
		
		$instance=NULL;
		$item=false;
		//var_dump($fi);		
		if($fi!==false){
			
			
			//$instance=new EmailFiles_AbstractFileItem;
			foreach($this->classes as $k=>$v){
				if($v->load_name==$fi['load_name']){
					$instance=$v;
					
					break;	
				}
			}
			
	 
			$item=$instance->instance->GetItemById($fi['file_id']);
			
		}
		return $instance;
		
	}
	
	
	
	
	protected function GetFileName($hash){
		return '/email_file.html?file='.$hash;	
	}
	
	
	function __construct(){
		$this->classes=array();
		$this->classes[]=new EmailFiles_ContractUchItem;
		$this->classes[]=new EmailFiles_ContractItem;
		$this->classes[]=new EmailFiles_Supplier_Akt_Item;
		$this->classes[]=new EmailFiles_Supplier_Sh_Item;
		$this->classes[]=new EmailFiles_SupplierFileItem;
		$this->classes[]=new EmailFiles_FileSpsItem;
		$this->classes[]=new EmailFiles_FilePmItem;
		$this->classes[]=new EmailFiles_FileLetItem;
		$this->classes[]=new EmailFiles_FileSpItem;
		$this->classes[]=new EmailFiles_FilePoItem;
			
	}
}


//классы разновидностей файлов контрагента
class EmailFiles_ContractUchItem extends EmailFiles_AbstractFileItem{
	function __construct(){
		$this->load_name='uchcontract.html';
		$this->instance=new ContractUchItem;
		$this->doc_id_name='user_d_id';	
	}
}
class EmailFiles_ContractItem extends EmailFiles_AbstractFileItem{
	function __construct(){
		$this->load_name='contract.html';
		$this->instance=new ContractItem;
		$this->doc_id_name='user_d_id';	
	}
}
class EmailFiles_Supplier_Akt_Item extends EmailFiles_AbstractFileItem{
	function __construct(){
		$this->load_name='akt.html';
		$this->instance=new Supplier_Akt_Item;
		$this->doc_id_name='user_d_id';	
	}
}
class EmailFiles_Supplier_Sh_Item extends EmailFiles_AbstractFileItem{
	function __construct(){
		$this->load_name='shema.html';
		$this->instance=new Supplier_Sh_Item;
		$this->doc_id_name='user_d_id';	
	}
}


class EmailFiles_SupplierFileItem extends EmailFiles_AbstractFileItem{
	function __construct(){
		$this->load_name='supplier_file.html';
		$this->instance=new SupplierFileItem;
		$this->doc_id_name='user_d_id';	
	}
}






//классы разновидностей файлов
class EmailFiles_FileSpsItem extends EmailFiles_AbstractFileItem{
	function __construct(){
		$this->load_name='load_spl.html';
		$this->instance=new SpSItem;
		$this->doc_id_name=NULL;	
	}
}


class EmailFiles_FilePmItem extends EmailFiles_AbstractFileItem{
	function __construct(){
		$this->load_name='load_pm.html';
		$this->instance=new FilePmItem;
		$this->doc_id_name=NULL;	
	}
}

class EmailFiles_FileLetItem extends EmailFiles_AbstractFileItem{
	function __construct(){
		$this->load_name='load_l.html';
		$this->instance=new FileLetItem;
		$this->doc_id_name=NULL;	
	}
}


class EmailFiles_FileSpItem extends EmailFiles_AbstractFileItem{
	function __construct(){
		$this->load_name='load_pl.html';
		$this->instance=new SpItem;
		$this->doc_id_name=NULL;	
	}
}

class EmailFiles_FilePoItem extends EmailFiles_AbstractFileItem{
	function __construct(){
		$this->load_name='load.html';
		$this->instance=new FilePoItem;
		$this->doc_id_name=NULL;	
	}
}




//абстрактный класс для отправляемых файлов
class EmailFiles_AbstractFileItem{
	public $load_name, $instance, $doc_id_name;
	
	function __construct(){
		$this->load_name='';
		$this->instance=new FilePoItem;
		$this->doc_id_name=NULL;	
	}
		
}


//элемент таблицы отправленных файлов
class EmailFilesItem extends AbstractItem{
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='email_files';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='supplier_id';	
	}
	
	

	
}

?>
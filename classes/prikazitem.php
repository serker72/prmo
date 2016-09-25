<?
require_once('abstractitem.php');
require_once('user_s_group.php');
require_once('actionlog.php');
require_once('messageitem.php');

//абстрактный элемент
class PrikazItem extends AbstractItem{
	protected $subkeyvalue;
	
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='prikaz';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';
		
		
		$this->subkeyvalue='';	
	}
	
	
	
	public function Add($params){
		$code=parent::Add($params);	
		
		$_usg=new UsersSGroup;
		$log=new ActionLog;
		
		$usg=$_usg->GetItemsArr(0,1);
		$_log=new ActionLog;
		
		$message_to_managers="
			  <div><em>Данное сообщение сгенерировано автоматически.</em></div>
			  <div>Уважаемые коллеги!</div>
			  <div>Сегодня, ".date("d.m.Y")." был размещен приказ № $params[vhod_no], $params[name].</div>
			  <div>Ознакомиться с приказом вы можете в разделе Каталог - Сотрудники - Приказы.</div>
			 
			  ";
		$mi=new MessageItem();
		
		foreach($usg as $k=>$v){
			 $params1=array();
			  
			  $params1['topic']='Новый приказ';
			  $params1['txt']=$message_to_managers;
			  $params1['to_id']= $v['id'];
			  $params1['from_id']=-1; //Автоматическая система рассылки сообщений
			  $params1['pdate']=time();
			  
			  $mi->Send(0,0,$params1,false);	
				
			 //  $_log->PutEntry(0, "Автоматическая система рассылки сообщений",$params1['to_id'],NULL,NULL,"тема сообщения: ".$params1['topic']." текст сообщения: ".$params1['txt']);
		}
		
		
		
		
		return $code;
	}
	
	
	
	//удалить
	public function Del($id){
		//удалить все файлы
		$fset=new MysqlSet('select * from prikaz_file where prikaz_id="'.$id.'"');
		$fc=$fset->GetResultNumRows();
		$rfs=$fset->GetResult();
		
		$fi=new PrikazFileItem;
		for($i=0; $i<$fc; $i++){
			$f=mysqli_fetch_array($rfs);
			//GetStoragePath()
			@unlink($fi->GetStoragePath().$f['filename']);
		}
		
		
		
		//удалить файлы из бд
		$query = 'delete from prikaz_file where prikaz_id="'.$id.'"';
		$it=new nonSet($query);
		
		
		parent::Del($id);
	}	
	
}
?>
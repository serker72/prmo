<?
require_once('abstractfileitem.php');

require_once('messageitem.php');
require_once('usersgroup.php');



require_once('authuser.php');
require_once('actionlog.php');



//папка реестра файлов документа
class FileDocFolderItem extends AbstractFileItem{
	protected $storage_id;
	protected $storage_name;
	
	
	protected $tablename_file;
	protected $doc_id;
	
	public $doc_id_name;
	
	protected $file_instance;
	
	public $tablename;
	
	public function __construct($id=1, $doc_id, $file_instance){
		$this->init($id, $doc_id, $file_instance);
	}
	
	//установка всех имен
	protected function init($id, $doc_id, $file_instance){
		
		
		$this->tablename='komplekt_ved_file_folder'; //?????
		
		$this->storage_id=$id;	
		
		$this->doc_id=$doc_id;
		$this->doc_id_name='komplekt_ved_id'; //?????
		
		
		$this->file_instance=$file_instance;
		$this->tablename_file=$this->file_instance->tablename; 
		$this->pagename=$this->file_instance->pagename;
		$this->vis_name=$this->file_instance->vis_name;
		$this->subkeyname=$this->file_instance->subkeyname;
		$this->storage_name=$this->file_instance->storage_name;
			
		
		
		
		
	}
	
	
	//добавить 
	public function Add($params){
		
		if(isset($params['filename'])) $params['filename']= strtoupper(substr($params['filename'], 0, 1)).substr($params['filename'],1,strlen($params['filename']));
	
	
		
		$mid=AbstractItem::Add($params);
		
		
		
		return $mid;
	}
	
	
	
	
	
	
	//править
	public function Edit($id,$params=NULL, $item=NULL, $result=NULL, $move_security_object=575){
		
		if(isset($params['filename'])) $params['filename']= strtoupper(substr($params['filename'], 0, 1)).substr($params['filename'],1,strlen($params['filename']));
		
		$au=new AuthUser;
		$log=new ActionLog;
		if($item===NULL) $item=$this->GetItemById($id);
		if($result===NULL) $result=$au->Auth();
		
		
		
		
		
		
		
		
		
		
		
		
		if(isset($params['parent_id'])&&($params['parent_id']!=$item['parent_id'])){
		
			//$_fi=new FileFolderItem($this->storage_id);
			
			
			$oldf=$this->GetItemById($item['parent_id']);
			$newf=$this->GetItemById($params['parent_id']);
			
			if($item['parent_id']==0) $oldf['filename']='Основная папка';
			if($params['parent_id']==0) $newf['filename']='Основная папка';
			
			
			$log->PutEntry($result['id'], 'переместил папку', NULL, $move_security_object, NULL, 'папка '.SecStr($item['filename']).' перемещена из папки '.SecStr($oldf['filename']).' в папку '.SecStr($newf['filename']).'',$id);
		
		
		
		}
		
		
		AbstractItem::Edit($id,$params);
			
	}
	
	
	//удалить
	public function Del($id,$item=NULL, $result=NULL, $del_security_object=32){
		//удалять ВСЕ!!!!
		
		$log=new ActionLog;
		
		$au=new AuthUser;
		if($item===NULL) $item=$this->getitembyid($id);
		
		if($result===NULL) $result=$au->Auth();
		
		
		//список всех вложенных
		$arr=array();
		
		//находим все подразделы
		$this->SubsListView($id, $arr);
		
		//добавим сам раздел в список
		$arr[]=$id;
		
		
		
		//удалить все файлы в папке и подпапке
		$sql='select * from '.$this->tablename_file.' where folder_id in ('.implode(', ', $arr).') and '.$this->subkeyname.'="'.$this->doc_id.'" ';
		$set=new mysqlSet($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		//echo $sql;
		
		
		
		$_i=$this->file_instance;
		
		
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			if($_i!==NULL){
				$_i->Del($f['id']);
				
				//запись об удалении файла	
				$log->PutEntry($result['id'],'автоматическое удаление файла при удалении родительской папки',NULL, $del_security_object, NULL, ''.$f['orig_name'], $f['id']);
			}
		}
		
		
		
		//строим запрос на удаление найденных подразделов+самого раздела
		foreach($arr as $k=>$v){
			$nm=$this->getitembyid($v);
			
			$log->PutEntry($result['id'],'автоматическое удаление папки при удалении родительской папки',NULL, $del_security_object, NULL, ''.$nm['filename'], $v);	
		}
		
		
		
		$q1='delete from '.$this->tablename.' where id in('.implode(', ',$arr).');';	
		
		//echo $q1;
		
		//удаляем раздел+подразделы
		$ns=new nonSet($q1);
		
		
		
		$this->item=NULL;
	}	
	
	
	
	//Вспомогательная функция при удалении раздела	
	//РЕКУРСИЯ по списку
	//строим список всех подразделов
	public function SubsListView($id,&$arr){
		$l_arr=$this->GetSubsList($id, $arr);
		if(count($l_arr)>0){
			foreach($l_arr as $k=>$v){
				$this->SubsListView($v,$arr);
			}
		}
		
	}
	
	//Вспомогательная функция при удалении раздела
	//список всех вложенных подразделов
	protected function GetSubsList($id, &$arr){
		$l_arr=Array();
		$query='select * from '.$this->tablename.' where parent_id="'.$id.'" and doc_id="'.$this->doc_id.'"';
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
	
	
	//удаление одного итема
	public function DelOne($id){
		
		AbstractItem::Del($id);
		
		
	}
	
	
	//есть ли файлы в папке и подразделах
	public function HasFiles($id, $folders=NULL){
	
		if($folders===NULL){
			$folders=array();
			
			$this->SubsListView($id, $folders);
			
			$folders[]=$id;
			
			
		}
		
		$sql='select count(*) from '.$this->tablename_file.' where folder_id in ('.implode(', ', $folders).') ';
		
		
		$set=new mysqlSet($sql);
		$rs=$set->GetResult();
		
		$f=mysqli_fetch_array($rs);
		if((int)$f[0]>0) return true;
		else return false;
		
	}
	
	
	//строим навигацию Клиентскую
	//игнорируем аргументы-шаблоны!!! - вставить action_id=1 для приказов
	public function DrawNavigCli($id, $fintext=' правка раздела ',$separator='/', $last_is_link=true, $navi_decorator=NULL){
		$txt='';
		
		$arr=Array();
		$arr=$this->RetrievePath($id, $flaglost, $vloj);
		
		
		
		$sm=new SmartyAdm;
		$sm->debug=DEBUG_INFO;
		$alls=array();
		//впишем ссылку на корневую страницу
		
		$e_path='';
		if($navi_decorator!==NULL){
			$e_path='&'.$navi_decorator->GenFltUri();	
		}
		
		$alls[]=Array(
			'itemname'=>'Основная папка',
			'filepath'=>$this->pagename.'?'.$this->doc_id_name.'='.$this->doc_id.$e_path,
			'has_symb'=>true,
			'symb'=>$separator
		);
		
		
		foreach($arr as $k=>$v){
			foreach($v as $kk=>$vv){
				
				
				if($kk!=$id) $has_symb=true; else $has_symb=false;
				
				$path=$this->ConstructUrl($vv['path'], $tab_page).$e_path;
				
				$alls[]=array(
					'itemname'=>stripslashes($vv['name']),
					'filepath'=>$path,
					'has_symb'=>$has_symb,
					'symb'=>$separator
				);
			}
		}
		
		//var_dump($alls);
		
		
		$sm->assign('items',$alls);
		$sm->assign('aftertext',$fintext);
		
		$sm->assign('last_is_link',$last_is_link);
		$txt=$sm->fetch('files/navi.html');
		
		return $txt;
	}
	
	
	//конструируем строку вида /path1/path2/...
	//описывающую путь к текущему разделу в урлах
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
	
	
	
	//получаем путь к разделу
	//а также глубину вложенности
	public function RetrievePath($id, &$flaglost, &$vloj){
		unset($path);
		$path=array(); 
		//если тру, то есть потеря пути!
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
	
	
	
	//построение клиентского адреса
	public function ConstructUrl($id, $tab_page=1){
		$result=$this->pagename.'?'.$this->doc_id_name.'='.$this->doc_id.'&folder_id='.$id;
		
		return $result;
	}
	
	public function SetPageName($pagename){
		$this->pagename=$pagename;
	}
	
	
	public function SetTablenameFile($tablename_file){
		$this->tablename_file=$tablename_file;	
	}
	public function SetTablename($tablename){
		$this->tablename=$tablename;	
	}
	
	public function SetDocIdName($doc_id_name){
		$this->doc_id_name=$doc_id_name;	
	}
}
?>
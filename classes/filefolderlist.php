<?

require_once('filefolderitem.php');
require_once('abstractfilegroup.php');
require_once('authuser.php');
require_once('rl/rl_man.php');


// папки файлового реестра
class FileFolderList extends AbstractGroup{
	
	protected $storage_id;
	protected $storage_name;
	protected $_rl;
	
	public function __construct($id=1){
		$this->init($id);
	}
	
	//установка всех имен
	protected function init($id){
		$this->tablename='file_folder';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='user_id';
			
		$this->storage_id=$id;	
		$this->storage_name='storage_id';
		$this->_rl=new RLMan;
	}
	

	
	//иерарх список
	public function GetItemsArr($selected_ids, $code_access=35, $result=NULL ){
		if($result===NULL){
			$_au=new AuthUser;
			$result=$_au->Auth();	
		}
		
		
		//echo $query;
		
		$alls=array();
		
		$alls[]=array(
		'id'=>0,
		'filename'=>'Основная папка',
		'disabled'=>in_array(0, $selected_ids),
		'depth'=>0);
		
		$this->GetBound(0,1,$selected_ids,$alls, $code_access, $result);
		
		return $alls;
	}
	
	
	protected function GetBound($id, $depth, $selected_ids, &$arr, $code_access=35, $result=NULL){
		$query='select * from '.$this->tablename.' where '.$this->storage_name.'="'.$this->storage_id.'" and parent_id="'.$id.'" order by filename asc, id desc';
		
		
		
		$items=new mysqlSet($query);
		$rs=$items->GetResult();
		$rc=$items->GetResultNumRows();
		for($i=0;$i<$rc;$i++){
			$f=mysqli_fetch_array($rs);
			$disabled=in_array($f['id'], $selected_ids);
			$f['disabled']=$disabled;
			
			$f['depth']=$depth;
			
			
			
			$has_access=$this->_rl->CheckFullAccess($result['id'], $f['id'], $code_access, 'w', 'file', $this->storage_id);	
			
			
			
			if($has_access){
				$arr[]=$f;
			
				$this->GetBound($f['id'],$depth+1,$selected_ids, $arr, $code_access, $result);
			}
		}
		
	}
	
	
	
	
	//выдает массивы для смарти, шаблоны - пустые
	public function GetItemsArrCli($parent_id, $current_id=0,  $as_thumbs=true,$add_params=NULL){
		
		$params=Array();
		$params['parent_id']=$parent_id;
		
		$paramsord=array();
		$paramsord[]=' ord desc ';
		if($add_params!==NULL){
			foreach($add_params as $k=>$v){
				$params[$k]=$v;
			}
		}
		
		$query=$this->GenerateSQL($params,NULL,$paramsord,$sql_count);
		$items=new mysqlSet($query);
		$rs=$items->GetResult();
		$rc=$items->GetResultNumRows();
		//echo $query;
		
		$alls=array();
		
		for($i=0;$i<$rc;$i++){
			$f=mysqli_fetch_array($rs);
			$f['is_active']=($current_id==$f['id']);
			$alls[]=$f;
		}
		return $alls;
	}
	
	public function GetItemsMainCli($parent_id, $current_id=0,  $add_params=NULL){
		$txt='';
		
		$params=array();
		$params['parent_id']=$parent_id;
		
		$paramsord=array();
		$paramsord[]=' ord desc ';
		$paramsord[]=' id asc ';
		if($add_params!==NULL){
			foreach($add_params as $k=>$v){
				$params[$k]=$v;
			}
		}
		
		$query=$this->GenerateSQL($params,NULL,$paramsord,$sql_count);
		$items=new mysqlSet($query);
		$rs=$items->GetResult();
		$rc=$items->GetResultNumRows();
		//echo $query;
		
		$alls=array();
		
		
		
		
		for($i=0;$i<$rc;$i++){
			$f=mysqli_fetch_array($rs);
			
			if($f['is_new_window']=='1') $is_new=true; else $is_new=false;
			
			
			$tm=new FileFolderItemItem($this->storage_id);
			$path=$tm->ConstructPath($f['id'],1,'/');
				//echo "the path: $path<p>";
			
			
			$f['path']='/'.$path;
			
			$alls[]=$f;
		}
		
		
		
		return $alls;
	}
	
	
	
	
	
	protected function GenerateSQL($params=NULL, $notparams=NULL, $orderbyparams=NULL, &$sql_count=''){
		$sql='';
		
		$sql='select * from '.$this->tablename.' as t ';
		
		//запрос для посчета общего числа итемов
		$sql_count='select count(*) from '.$this->tablename.' as t ';
		
		$qq=' where true ';
		if($params!==NULL){
		  foreach($params as $k=>$v){
			  $qq.=' and '.$k.'="'.$v.'" ';
		  }
		}
		if($notparams!==NULL){
			foreach($notparams as $k=>$v){
				$qq.=' and '.$k.'<>"'.$v.'" ';
			}
		}
		
		$qq2='';
		if($orderbyparams!==NULL){
			$cter=0;
			foreach($orderbyparams as $k=>$v){
				if($cter==0) $qq2.=' order by ';
				
				$qq2.=$v.'';
				$cter++;
				
				if($cter!=count($orderbyparams)) $qq2.=', ';
			}
		}
		
		$sql=$sql.$qq.$qq2;
		$sql_count=$sql_count.$qq;
				
		return $sql;
	}
	
	
}
?>
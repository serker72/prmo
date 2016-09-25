<?

require_once('filedocfolderitem.php');
require_once('abstractfilegroup.php');


// список папок реестра файлов документа
class FileDocFolderList extends AbstractGroup{
	
	protected $storage_id;
	protected $storage_name;
	
	
	protected $tablename_file;
	protected $doc_id;
	
	protected $doc_id_name;
	
	protected $file_instance;
	protected $folder_instance;
	
	
	
	public function __construct($id=1, $doc_id, $file_instance, $folder_instance){
		$this->init($id, $doc_id, $file_instance, $folder_instance);
	}
	
	//установка всех имен
	protected function init($id, $doc_id, $file_instance, $folder_instance){
		
		
		$this->file_instance=file_instance;
		$this->folder_instance=folder_instance;
		
		$this->tablename='komplekt_ved_file_folder'; //?????????
		$this->tablename_file='komplekt_ved_file'; //??
		
		
		
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='komplekt_ved_id';
			
		$this->storage_id=$id;	
		$this->storage_name='storage_id';
		
		$this->doc_id=$doc_id;
		$this->doc_id_name='komplekt_ved_id';
	}
	

	
	//иерарх список
	public function GetItemsArr($selected_ids){
		
		
		
		//echo $query;
		
		$alls=array();
		
		$alls[]=array(
		'id'=>0,
		'filename'=>'Основная папка',
		'disabled'=>in_array(0, $selected_ids),
		'depth'=>0);
		
		$this->GetBound(0,1,$selected_ids,$alls);
		
		return $alls;
	}
	
	
	protected function GetBound($id, $depth, $selected_ids, &$arr){
		$query='select * from '.$this->tablename.' where '.$this->storage_name.'="'.$this->storage_id.'" and parent_id="'.$id.'" and doc_id="'.$this->doc_id.'" order by filename asc, id desc';
		
		//echo $query;
		$items=new mysqlSet($query);
		$rs=$items->GetResult();
		$rc=$items->GetResultNumRows();
		for($i=0;$i<$rc;$i++){
			$f=mysqli_fetch_array($rs);
			$disabled=in_array($f['id'], $selected_ids);
			$f['disabled']=$disabled;
			
			$f['depth']=$depth;
			
			$arr[]=$f;
			
			$this->GetBound($f['id'],$depth+1,$selected_ids, $arr);
		}
		
	}
	
	
	
	
	//выдает массивы для смарти, шаблоны - пустые
	/*public function GetItemsArrCli($parent_id, $current_id=0,  $as_thumbs=true,$add_params=NULL){
		
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
	*/
	
}
?>
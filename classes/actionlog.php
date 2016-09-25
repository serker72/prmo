<?
require_once('actionitem.php');
require_once('db_decorator.php');
require_once('PageNavigator.php');

class ActionLog{
	protected $item=NULL;
	
	function __construct(){
		$this->item=new ActionItem();
	}
	
	public function PutEntry($user_subject_id, $description, $user_object_id=NULL, $object_id=NULL, $user_group_id=NULL, $value=NULL, $affected_object_id=NULL){
		
		$params=array();
		$params['user_subj_id']=$user_subject_id;
		$params['ip']=getenv('HTTP_X_REAL_IP');//getenv('REMOTE_ADDR'); //
		$params['pdate']=time();
		$params['description']=$description;
		
		if($user_object_id!==NULL) $params['user_obj_id']=$user_object_id;
		if($object_id!==NULL) $params['object_id']=$object_id;
		if($user_group_id!==NULL) $params['user_group_id']=$user_group_id;
		if($value!==NULL) $params['value']=$value;
		
		if($affected_object_id!==NULL) $params['affected_object_id']=$affected_object_id;
		
		$this->item->Add($params);
		
	}
	
	public function ShowLog($template, DBDecorator $dec,$from=0,$to_page=ITEMS_PER_PAGE,$pagename='syslog.php',$has_action=false,$has_id=false, $do_show_log=false){
		/*echo $dec->GenFltSql(' and ');
		echo $dec->GenFltUri();
		echo $dec->GenFltOrd();*/
		
		
		
		$sm=new SmartyAdm;
		
		$sql='select l.*, 
					 s.login as s_login,  s.name_s as s_name_s, s.group_id as s_group_id,
					 o.login as o_login, o.name_s as o_name_s, o.group_id as o_group_id,
					 ob.name as ob_name,
					 gr.name as gr_name
				from
					 action_log as l
					 left join user as s on l.user_subj_id=s.id
					 left join user as o on l.user_obj_id=o.id
					 left join object as ob on l.object_id=ob.id
					 left join groups as gr on l.user_group_id=gr.id ';
		
		$sql_count='select count(*) from
					 action_log as l
					 left join user as s on l.user_subj_id=s.id
					 left join user as o on l.user_obj_id=o.id
					 left join object as ob on l.object_id=ob.id
					 left join groups as gr on l.user_group_id=gr.id ';
					 
		$db_flt=$dec->GenFltSql(' and ');
		if(strlen($db_flt)>0){
			$sql.=' where '.$db_flt;
			$sql_count.=' where '.$db_flt;	
		}
		
		$ord_flt=$dec->GenFltOrd();
		if(strlen($ord_flt)>0){
			$sql.=' order by '.$ord_flt;
		}
		//echo $sql;
		
		$set=new mysqlSet($sql,$to_page, $from,$sql_count);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$total=$set->GetResultNumRowsUnf();
		
		
		//page
		$navig = new PageNavigator($pagename,$total,$to_page,$from,10,'&'.$dec->GenFltUri());
		$navig->SetFirstParamName('from');
		$navig->setDivWrapperName('alblinks');
		$navig->setPageDisplayDivName('alblinks1');			
		$pages= $navig->GetNavigator();
		
		$alls=array();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$f['pdate']=date("d.m.Y H:i:s",$f['pdate']);
			
			//print_r($f);	
			$alls[]=$f;
		}
		
		//заполним шаблон полями
		$current_action='';
		$current_object='';
		$current_group='';
		$fields=$dec->GetUris();
		foreach($fields as $k=>$v){
			if($v->GetName()=='description') $current_action=$v->GetValue();
			if($v->GetName()=='object_id') {
				$current_object=$v->GetValue();
				
			}
			if($v->GetName()=='user_group_id') $current_group=$v->GetValue();
			$sm->assign($v->GetName(),$v->GetValue());	
		}
		
		//действия
		/*$as=new mysqlSet('select distinct description from action_log order by description asc');
		$rs=$as->GetResult();
		$rc=$as->GetResultNumRows();
		$acts=array();
		$acts[]=array('description'=>'');
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$f['is_current']=($current_action==$f[0]); 
			$acts[]=$f;
		}
		$sm->assign('ac',$acts);*/
		
		//объекты
		/*$as=new mysqlSet('select * from object order by name asc');
		$rs=$as->GetResult();
		$rc=$as->GetResultNumRows();
		$acts=array();
		$acts[]=array('id'=>'', 'name'=>'');
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$f['is_current']=($current_object==$f['id']); 
			$acts[]=$f;
		}
		$sm->assign('ob',$acts);*/
		
		//группы
		//объекты
		$as=new mysqlSet('select * from groups order by name asc');
		$rs=$as->GetResult();
		$rc=$as->GetResultNumRows();
		$acts=array();
		$acts[]=array('id'=>'', 'name'=>'');
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$f['is_current']=($current_group==$f['id']); 
			$acts[]=$f;
		}
		$sm->assign('ug',$acts);
		
		
		$sm->assign('from',$from);
		$sm->assign('to_page',$to_page);
		$sm->assign('pages',$pages);
		$sm->assign('items',$alls);
		$sm->assign('pagename',$pagename);
		
		$sm->assign('has_action',$has_action);
		$sm->assign('has_id',$has_id);
		$sm->assign('do_show_log',$do_show_log);
		
		//ссылка для кнопок сортировки
		$link=$dec->GenFltUri();
		$link=$pagename.'?'.eregi_replace('&sortmode=[[:digit:]]+','',$link);
		$sm->assign('link',$link);
		
		return $sm->fetch($template);
	}
}
?>
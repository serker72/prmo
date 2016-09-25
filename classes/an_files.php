<?
/*require_once('billpospmformer.php');
require_once('supplieritem.php');
require_once('orgitem.php');
require_once('opfitem.php');
require_once('bdetailsgroup.php');*/

require_once('authuser.php');


require_once('an_files_item.php');

require_once('discr_man.php');
require_once('folderitem.php');

require_once('rl/rl_man.php');

require_once('db_decorator.php');

class AnFiles{
	
	public $prefix='_3';
	

	public function ShowData($input_params,  $auth_result, $template, DBDecorator $dec,$pagename='an_fill.php',  $do_it=false, $can_print=false, &$alls){
		
		$sm=new SmartyAdm;
		$_discr=new DiscrMan;
		$_rl=new RLMan;
		
		$_fi=new FolderItem;
	 
		
		$alls=array();
		
		//построить основной запрос...
		$data_fields=array();
		$fields=$dec->GetUris();
		foreach($fields as $k=>$v){
			

			if($v->GetName( )=='fields') $data_fields=explode(',',$v->GetValue());
			
			$sm->assign($v->GetName( ),$v->GetValue());	
		}
		
		
		//сформировать массив видимых полей выбора вида файлов
		$_sqls=array();
		foreach($input_params as $k=>$v){
			
			if($v instanceof AnFilesItem){ 
				if($_discr->CheckAccess($auth_result['id'], 'w', $v->object_id)) {
					$is_active=((count($data_fields)==0)||in_array($v->_id, $data_fields));
					if($is_active){
						
						
						$_sql='( select ';
						
						$_sql.='"'.$v->_id.'" as kind, "'.$v->description.'" as description, d.id, d.storage_id, d.user_id, d.filename, d.orig_name, d.pdate, d.txt, d.folder_id ';
						
						//родит. док-т
						if($v->document_id_fieldname!==NULL) $_sql.=', d.'.$v->document_id_fieldname.' as document_id ';
						else $_sql.=', "" as document_id ';
						
						//получить организацию
						if($v->how_to_get_org==AnFilesItem::BY_PARENT_DOC){
							 $_sql.=', opf.name as opf_name, org.id as org_id, org.full_name as org_name ';	
						}else $_sql.=', "" as opf_name, "" as org_id, "" as org_name ';	
						
						
						
						$_sql.=', "'.$v->document_filename.'" as document_filename ';
						$_sql.=', "'.$v->file_item_instance->GetPageName().'" as pagename ';
						$_sql.=', "'.$v->filter_id_fieldname.'" as filter_id_fieldname ';
						
						$_sql.=', "'.$v->download_filename.'" as download_filename ';
						
						
						$_sql.=', u.login as user_login, u.name_s as user_name_s ';
						
						
						$_sql.=' from  '.$v->file_item_instance->GetTableName().' as d ';
						
						$_sql.=' left join user as u on u.id=d.user_id ';
						
						
						//родит. док-т
						if($v->document_id_fieldname!==NULL)  $_sql.=' left join '.$v->document_tablename.' as doc on d.'.$v->document_id_fieldname.'=doc.id ';
						
						//получить организацию
						if($v->how_to_get_org==AnFilesItem::BY_PARENT_DOC){
							$_sql.=' left join supplier as org on org.id=doc.org_id 
									 left join opf on opf.id=org.opf_id
							';
							
							 
						}
						
						
						
						$_sql.='where d.storage_id="'.$v->file_item_instance->GetStorageId().'"';
						//также при наличии папок и ограничений на них - ограничить по папкам
						$restricted_ids=array();
						
						if($v->has_rl_restrictions){
							 //$folder_ids[]=0;
							 $restricted_ids=$_rl->GetBlockedItemsArr($auth_result['id'],  $v->rl_object_id, 'w',  $v->file_item_instance->GetTableName(), $v->additional_params['additional_id']);
						}
						
						if(count($restricted_ids)>0) $_sql.=' and d.folder_id not in('.implode(', ',$restricted_ids).') ';
						
						//отличаем организацию от к-та
						if(($v->how_to_get_org==AnFilesItem::BY_PARENT_DOC)&&(isset($v->additional_params['is_org']))){
							$_sql.=' and doc.is_org="'.$v->additional_params['is_org'].'"' ;
						}
						
						//отличаем вх и исх док-ты
						if(($v->how_to_get_org==AnFilesItem::BY_PARENT_DOC)&&(isset($v->additional_params['is_incoming']))){
							$_sql.=' and doc.is_incoming="'.$v->additional_params['is_incoming'].'"' ;
						}
						
						
						//разберем декоратор
						
						if($v->filter_decorator instanceof DBDecorator){
							if(strlen($v->filter_decorator->GenFltSql())>0) $_sql.=' and '.$v->filter_decorator->GenFltSql();	
						}
						
						$_sql.=' and CHAR_LENGTH(TRIM(d.txt))<=5)';
						
					//	echo $_sql;
						
						$_sqls[]=$_sql;	
					}
					
					
				}
			} 
		}
		
		//print_r($_sqls);
		
		if($do_it&&(count($_sqls)>0)){
			
			$sql=implode(' UNION ALL ',$_sqls);
			
			$sql.=' order by 1 asc, 8 desc ';
			
			//echo $sql;
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			
			for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);
				
				foreach($f as $k=>$v) $f[$k]=stripslashes($v);
				
				$f['pdate_unf']=$f['pdate'];
				$f['pdate']=date('d.m.Y H:i:s', $f['pdate']);
				
				//$f[kind'] = Это v->id,
				//т.е.
				
				$item=NULL;
				foreach($input_params as $k=>$v){
					if(($v instanceof AnFilesItem)&&($v->_id	==$f['kind'])) $item=$v;
				}
				//var_dump($item);
				
				if(($item!==NULL)&&($item->document_instance instanceof AbstractItem)){
					 
					//получить род. документ
					
					$document=$item->document_instance->GetItemById($f['document_id']);
					$doc_name=''.$item->document_text;
					
					foreach($item->document_affected_fields as $k=>$v) $doc_name.=' '.$document[$v];
					
					$f['doc_name']=$doc_name;
					
				}
				
				if(($item!==NULL)){
					
					//получим папку
					$_fi->SetTableName($item->file_folder_tablename);
					$f['folder_name']=$_fi->GetFullFolderName($f['folder_id']);
					
					/*echo $item->file_folder_tablename;
					var_dump($f['folder_name']);*/
					
					//доп. параметр (tab_page)
					if(isset($item->additional_params['tab_page'])) $f['tab_page']=$item->additional_params['tab_page'];
				}
				
				
				
				$alls[]=$f;	
			}
			
			
			
		}
		
		
		
		
		 
	   //заполним шаблон полями
		$current_storage='';
		$current_bank_id='';
		$current_user_confirm_price='';
		$current_sector='';
		
		$data_fields=array();
		$fields=$dec->GetUris();
		foreach($fields as $k=>$v){
			

			if($v->GetName( )=='fields') $data_fields=explode(',',$v->GetValue());
			
			$sm->assign($v->GetName( ),$v->GetValue());	
		}
		
		
		//var_dump($data_fields);
		
		//сформировать массив видимых полей выбора вида файлов
		$fields=array();
		foreach($input_params as $k=>$v){
			
			if($v instanceof AnFilesItem){ 
				if($_discr->CheckAccess($auth_result['id'], 'w', $v->object_id)) {
					$is_active=((count($data_fields)==0)||in_array($v->_id, $data_fields));
					$fields[]=array('kind'=>-1, 'id'=>$v->_id, 'name'=>$v->description, 'is_active'=>$is_active);
					
					
				}
			}elseif($v['kind']==0)  $fields[]=array('kind'=>0, 'name'=>$v['label']);
			elseif($v['kind']==1)  $fields[]=array('kind'=>1, 'name'=>$v['label']);
		}
		$sm->assign('fields', $fields);
		
		
		
		
		
		 $link=$dec->GenFltUri('&', $this->prefix);
		//echo $link;
		$link=$this->pagename.'?'.eregi_replace('&sortmode'.$this->prefix.'=[[:digit:]]+','',$link).'&doSub'.$this->prefix.'=1';
		$link=eregi_replace('tab_page'.$this->prefix, 'tab_page', $link);
		$sm->assign('link',$link);
		$sm->assign('sortmode',$sortmode);
		
		
		$sm->assign('prefix',$this->prefix);
	   
		$sm->assign('items', $alls);
		
		$sm->assign('can_print',$can_print);
		$sm->assign('do_it',$do_it);	
	
		$sm->assign('pagename',$pagename);
		
		return $sm->fetch($template);
	}
	
	
	
}
?>
<?
require_once('abstractgroup.php');


require_once('user_s_item.php');
require_once('user_s_group.php');
require_once('opfitem.php');



class RepUsersQuests {
	
	
	public function ShowData(array $qst, $template='',$do_it=false, $print=0, $limited_user=NULL){
		$txt='';
		$alls=array();
		
		$sm=new SmartyAdm;
		
		//сформируем общий список вопросов
		$arr=array();	
		
		$set=new mysqlSet('select q.* from question as q order by q.name asc, q.id asc');
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		for($i=0;$i<$rc;$i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			//
			$f['is_checked']=(in_array($f['id'],$qst)) ;
			$arr[]=$f;	
		}
		
		
		$sm->assign('per_page',ceil($rc/2));
		$sm->assign('qsts',$arr);
		$sm->assign('do_it',$do_it);
		
		if(count($qst)>0){
			//сформировать таблицу...	
			//заголовок - выбранные вопросы
			//строки - попадающие пользователи
			//клетки: + -
			
			$flt='';
			if($limited_user!==NULL) $flt=' and u.id in('.implode(', ',$limited_user).')';
			
			//найти всех сотрудников, которые курируют хотя бы один вопрос из списка...
			$sql='select distinct u.id, u.login, u.name_s from user as u 
			inner join question_user as qu on qu.user_id=u.id
			where  u.is_active=1 and qu.question_id in('.implode(', ',$qst).') '.$flt.' order by u.name_s asc, u.login asc';
			
			//echo $sql;
			
			$set=new mysqlSet($sql); //,$to_page, $from,$sql_count);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			
			for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);
				
				$subs=array();
				
				//проверить, есть ли доступ у пользователя к вопросам
				//получим массив айди вопросов пользователя
				//найдем в них наши, есть - плюс, нет - минус
				$sql1='select distinct question_id from question_user where user_id="'.$f['id'].'"';
				$set1=new mysqlSet($sql1); //,$to_page, $from,$sql_count);
				$rs1=$set1->GetResult();
				$rc1=$set1->GetResultNumRows();
				
				$all_ids=array();
				for($j=0; $j<$rc1; $j++){
					$g=mysqli_fetch_array($rs1);
					$all_ids[]=$g[0];
				}
				
				/*echo '<pre>';
				var_dump($all_ids);
				echo '</pre>';*/
				
				foreach($qst as $k=>$v){
					if(in_array($v,$all_ids)) $subs[]=1;
					else $subs[]=0;	
				}
				
				
				$f['subs']=$subs;
				
				$alls[]=$f;
			}
		}
		
		
		
		
		
		$sm->assign('print',$print);
		
		$sm->assign('items',$alls);
		
		$txt=$sm->fetch($template);
		
		return $txt;
	}
	
	
}
?>
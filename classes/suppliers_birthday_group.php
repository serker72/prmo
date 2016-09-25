<?
require_once('abstractgroup.php');
require_once('opfitem.php');

require_once('supplieritem.php');

require_once('suppliersgroup.php');

require_once('supplier_responsible_user_group.php');

require_once('authuser.php');
require_once('user_to_user.php');
require_once('array_sorter.php');


// служебный класс для поздравления с ДР контактов контрагентов
class SuppliersBirthGroup extends SuppliersGroup {
	 
	
	
	//рассылки о днях рождения  
	 
	public function AutoBirthdayMessages($days,$debug=false){
		$_log=new ActionLog;
		$mi=new MessageItem;
		
		 
		
		$d2=time();
		$y2=mktime(0,0,0,1,1,date("Y"));
		$now_midnight=mktime(0,0,0,date('m'),date('d'),date('Y'));
		
		
		
		
		
		
		$sql='select c.name, c.position, c.birthdate, c.supplier_id, 
			s.full_name, s.code, s.org_id,
			opf.name as opf_name,
			
			org.full_name as org_full_name, org_opf.name as org_opf_name
		
		 from  supplier_contact as c
			inner join supplier as s on s.id=c.supplier_id
			left join opf as opf on opf.id=s.opf_id
			left join supplier as org on org.id=s.org_id
			left join opf as org_opf on org_opf.id=org.opf_id		
		
		  where s.is_active=1 and c.birthdate is not NULL and s.is_org=0 ';
		//echo $sql.'<br>';
		
		$set1=new mysqlSet($sql);
		$rs1=$set1->GetResult();
		$rc1=$set1->GetResultNumRows();
		
		
		if($debug) echo 'число дней: '.$days.'<br>';
		
		for($i=0; $i<$rc1; $i++){
			$f=mysqli_fetch_array($rs1);
			
			$bd=$f['birthdate'];
			$y1=mktime(0,0,0,1,1,date("Y",$bd));
			$birth_midnight=mktime(0,0,0,date('m',$bd),date('d',$bd), date('Y'));
			
			//был ли еще др в этом году
			if($birth_midnight>=$now_midnight){
				//еще не было или есть сегодня	
				if($debug) echo "еще не был, ".$f['name'].' '." bd: ".date("d.m.Y",$bd)." check: ".date("d.m.Y",($birth_midnight))." now: ".date("d.m.Y",$d2)." <br>";
				
				//проверить попадание по дням
				$compare_midnight=$birth_midnight-$days*24*60*60;
				
				if($compare_midnight==$now_midnight){
					
					if($debug) echo "срок НАСТУПИЛ, дней: ".$days." ".$f['name'].' '." bd: ".date("d.m.Y",$bd)." check: ".date("d.m.Y",($compare_midnight))." now: ".date("d.m.Y",$d2)." <br>";
					
					 
									
					$flt='';
					/*$_u_to_u=new UserToUser();
					$u_to_u=$_u_to_u->GetExtendedViewedUserIdsArr($f['id']);
					$limited_user=$u_to_u['sector_ids'];
					
					$flt.=' and id in('.implode(', ', $limited_user).') '; 
					*/	
						
					//выполним рассылку. нас интересуют пользователи:
					/*
					1) активные
					2) состоят в организации, к которой приписан контрагент supplier_to_user
					3) не имеют ограничений по доступу к контрагенту (limited supplier:
						user.has_restricted_suppliers==0 || has_restricted_suppliers==1 and in(supplier_to_user_r...)
						)
					4) имеют следующие права: 87 - обязательно,
						И: 909 ИЛИ
						supplier_responsible_user
					*/
					$sql3='select * from user where is_active=1 
						and id in(select distinct user_id from supplier_to_user where org_id="'.$f['org_id'].'")
						and(
							has_restricted_suppliers=0
							or
							(
								has_restricted_suppliers=1
								and id in(select distinct user_id from supplier_to_user_r where org_id="'.$f['org_id'].'" and supplier_id="'.$f['supplier_id'].'" )
							)
						)
						
						and(
							id in(select distinct user_id from user_rights where object_id=87)
							and(
								id in(select distinct user_id from user_rights where object_id=909)
								or id in(select distinct user_id from supplier_responsible_user where  supplier_id="'.$f['supplier_id'].'" )
							)
						)
						
					
					';
					
					//if($debug) echo $sql3.'<br>';
					
					$set3=new mysqlSet($sql3);
					$rs3=$set3->GetResult();
					$rc3=$set3->GetResultNumRows();
					
					for($k=0; $k<$rc3; $k++){
						$h=mysqli_fetch_array($rs3);
						$_dl='';
						
						if($debug) echo "Отправим пользователю $h[name_s] $h[login]...<br>";
						
						if($days==0) $_dl="Сегодня";
						elseif($days==1) $_dl="Через один день";
						elseif($days==3) $_dl="Через три дня";
						elseif($days==7) $_dl="Через семь дней";
						
						$message_to_managers="
					  <div><em>Данное сообщение сгенерировано автоматически.</em></div>
					  <div>&nbsp;</div>
					  <div>Уважаемые коллеги!</div>
					  <div>&nbsp;</div>
					  <div>".$_dl.", а именно, ".date("d.m.",$f['birthdate']).date("Y",$d2).", день рождения следующего контактного лица из справочника контрагентов:</div>
					  <div>&nbsp;</div>
					  <ul><li><strong>".($f['name'])."</strong> (должность: ".($f['position'])."), контрагент <a target=\"_blank\" href=\"supplier.php?action=1&id=$f[supplier_id]\">$f[code] $f[opf_name] $f[full_name]</a>, организация $f[org_opf_name] $f[org_full_name].</li></ul>
					 
					  ";
					  
					 
					   if($debug) echo $message_to_managers;
					  
						$params1=array();
						
						$params1['topic']=SecStr($_dl." день рождения контактного лица из справочника контрагентов: ".($f['name'])." (должность: ".($f['position']).") контрагент $f[code] $f[opf_name] $f[full_name]");
						$params1['txt']=SecStr($message_to_managers);
						$params1['to_id']= $h['id'];
						$params1['from_id']=-1; //Автоматическая система рассылки сообщений
						$params1['pdate']=time();
						
						$mi->Send(0,0,$params1,false);
						
						
					}
						
						
						 	
						
						 
						
				}else{
					if($debug) echo "срок не наступил, дней: ".$days." ".$f['name'].' '." bd: ".date("d.m.Y",$bd)." check: ".date("d.m.Y",($compare_midnight))." now: ".date("d.m.Y",$d2)." <br>";
				}
				
			}else{
				if($debug) echo 'уже был, '.$f['name'].' '." bd: ".date("d.m.Y",$bd)." check: ".date("d.m.Y",($birth_midnight))." now: ".date("d.m.Y",$d2)." <br>";	
			}
			
		}
		
	}
	
	
	
	
	      
	
}
?>
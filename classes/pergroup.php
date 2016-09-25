<?
require_once('abstractgroup.php');
require_once('peritem.php');
require_once('user_s_item.php');

// группа отч периодов
class PerGroup extends AbstractGroup {
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='period';
		$this->pagename='view.php';		
		$this->subkeyname='org_id';	
		$this->vis_name='is_shown';		
		
		
		
	}
	
	
	public function GetItemsByIdArr($id, $current_id=0, $is_shown=0){
		$arr=array();
		if($is_shown==0) 
		$set=new MysqlSet('select * from '.$this->tablename.' 		
		 where '.$this->subkeyname.'="'.$id.'" order by id asc');
		else $set=new MysqlSet('select * from '.$this->tablename.' 		
		 where '.$this->subkeyname.'="'.$id.'" and is_confirmed=1 order by id asc');
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			$f['is_current']=(bool)($f['id']==$current_id);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			$f['pdate_beg_unf']=$f['pdate_beg'];
			$f['pdate_end_unf']=$f['pdate_end'];
			
			$f['pdate_beg']=date('d.m.Y',$f['pdate_beg']);
			$f['pdate_end']=date('d.m.Y',$f['pdate_end']);
			
			
			
			//$f['address']=nl2br($f['address']);
			$arr[]=$f;
		}
		
		return $arr;
	}
	
	
	public function DrawPeriods($org_id, $year, $template='', $can_confirm=false, $can_unconfirm=false, $org_id, $is_ajax=false, $has_header=true, $the_only_period=NULL, $can_bind_payments=false){
		if($is_ajax) $sm1=new SmartyAj;
		else $sm1=new SmartyAdm;
		$arr=array();
		
		//годы
		$_years=array();
		for($_year=2012; $_year<=date('Y')+3; $_year++){
			$_years[]=$_year;		
		}
		$sm1->assign('years', $_years);
		$sm1->assign('now_year', $year);
		
		//кварталы!
		$month=array(
			array('number'=>'1', 'year'=>$year, 'pdate_beg'=>date('d.m.Y', mktime(0,0,0,1,1,$year)), 'pdate_end'=>date('d.m.Y', mktime(23,59,59,3,31,$year)), 'pdate_beg_unf'=>mktime(0,0,0,1,1,$year), 'pdate_end_unf'=>mktime(23,59,59,3,31,$year)),
			array('number'=>'2', 'year'=>$year, 'pdate_beg'=>date('d.m.Y', mktime(0,0,0,4,1,$year)), 'pdate_end'=>date('d.m.Y', mktime(23,59,59,6,30,$year)), 'pdate_beg_unf'=>mktime(0,0,0,4,1,$year), 'pdate_end_unf'=>mktime(23,59,59,6,30,$year)),
			
			array('number'=>'3', 'year'=>$year, 'pdate_beg'=>date('d.m.Y', mktime(0,0,0,7,1,$year)), 'pdate_end'=>date('d.m.Y', mktime(23,59,59,9,30,$year)), 'pdate_beg_unf'=>mktime(0,0,0,7,1,$year), 'pdate_end_unf'=>mktime(23,59,59,9,30,$year)),
			
			array('number'=>'4', 'year'=>$year, 'pdate_beg'=>date('d.m.Y', mktime(0,0,0,10,1,$year)), 'pdate_end'=>date('d.m.Y', mktime(23,59,59,12,31,$year)), 'pdate_beg_unf'=>mktime(0,0,0,10,1,$year), 'pdate_end_unf'=>mktime(23,59,59,12,31,$year)),
			
		
		);
		
		
		
		//$ti=new TuItem;
		$_pi=new PerItem;
		$_ui=new UserSItem;
		foreach($month as $k=>$v){
			$struc=array();
			
			if($the_only_period!==NULL){
				if($v['number']!=$the_only_period) continue;	
			}
			
			$struc=$v;
			if($year==date("Y")){
				if(date('m',$v['pdate_end_unf'])>=(int)date("m")) $struc['enabled']=false;
				else $struc['enabled']=true;
			}elseif($year<date("Y")){
				$struc['enabled']=true;
			}elseif($year>date("Y")){
				$struc['enabled']=false;
			}
			
			$struc['year']=$year;
			
			
			
			//подгрузим данные об открытии-закрытии периода
			$test_pi=$_pi->GetItemByFields(array('org_id'=>$org_id, 'pdate_beg'=>$v['pdate_beg_unf'], 'pdate_end'=>$v['pdate_end_unf']));
			
			if($test_pi!==false){
				$struc['is_confirmed']=$test_pi['is_confirmed'];	
				if($test_pi['confirm_pdate']==0) $struc['confirm_pdate']='-';
				else $struc['confirm_pdate']=date('d.m.Y H:i:s', $test_pi['confirm_pdate']);
				
				$user=$_ui->getitembyid($test_pi['user_confirm_id']);
				if($user!==false){
					$struc['user_confirm']=$user['position_s'].' '.$user['name_s'];	
				}
			}
			
			$arr[]=$struc;
		}
		
		$sm1->assign('has_header',$has_header);
		$sm1->assign('month',$arr);
		
		$sm1->assign('can_confirm',$can_confirm);	
		$sm1->assign('can_unconfirm',$can_unconfirm);
		
		$sm1->assign('can_bind_payments',$can_bind_payments); 
		
		return $sm1->fetch($template);	
	}
	
	
	
}
?>
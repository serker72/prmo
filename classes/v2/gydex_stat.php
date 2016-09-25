<?
require_once('gydex_hit_item.php');
require_once('gydex_stat_item.php');


class GydexStat{
	
	public function Put($ip, $uri=NULL){
		
		$params=array();
		$_hi=new HitItem;
		
		//'YYYY-MM-DD HH:MM:SS'
		$params['pdate']=date('Y-m-d H:i:s');
		$params['ip']=$ip;
		if($uri!==NULL) $params['uri']=$uri;
		
		
		$_hi->Add($params);
		
		//нарастим статиститку
		$_test_st=new StatsItem;
		
		$test_st=$_test_st->GetItemByFields(array('pdate'=>date('Y-m-d'), 'ip'=>$ip));
				
		if($test_st!==false){
			$_test_st->Edit(	$test_st['id'], array('total'=>((int)$test_st['total']+1)));
		}else{
			$_test_st->Add(array(
				'pdate'=>date('Y-m-d'),
				'ip'=>$ip,
				'total'=>1
			));	
		}
			
		
	}
	
	
	
	
	
	
	
	
	
	//статистика по суткам
	public function GetTotal($pdate1, $pdate2){
		$alls=array();
		
		
		$curr_pdate=$this->DatefromYmd($pdate1); $end_pdate=$this->DatefromYmd($pdate2);
		
		//echo $curr_pdate;
		
		while($curr_pdate<=$end_pdate){
			$data=array();
			
			$data['pdate']=date('d.m.Y', $curr_pdate);
			
			//получить сумму посещаемости за сутки
			$sql='select sum(total) from gydex_stats where pdate between "'.date('Y-m-d H:i:s', ($curr_pdate)).'" and "'.date('Y-m-d H:i:s', ($curr_pdate+24*60*60-1)).'"   ';
			//echo $sql.'<br>';
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			$subs=array();
			 
				$f=mysqli_fetch_array($rs);
			 
					
			 
			$data['total']=(int)$f[0];
			
			
			
			//получить сумму посещаемости за сутки
			$sql='select count(*) from gydex_stats where pdate between "'.date('Y-m-d H:i:s', ($curr_pdate)).'" and "'.date('Y-m-d H:i:s', ($curr_pdate+24*60*60-1)).'"   ';
			//echo $sql.'<br>';
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			$subs=array();
			 
				$f=mysqli_fetch_array($rs);
			
			$data['total_uniq']=(int)$f[0];
			
			
			$curr_pdate+=24*60*60;
			$alls[]=$data;
		}
		return $alls;	
	}
	
	//распределение посещаемости разделов по суткам
	public function GetSub($pdate1, $pdate2){
		$alls=array();
		
		
		$curr_pdate=$this->DatefromYmd($pdate1); $end_pdate=$this->DatefromYmd($pdate2);
		
		//echo $curr_pdate;
		
		while($curr_pdate<=$end_pdate){
			$data=array();
			
			$data['pdate']=date('d.m.Y', $curr_pdate);
			
			//получить разделы, какие были посещены за эти сутки
			$sql='select uri, count(id) as c_id from gydex_hits where pdate between "'.date('Y-m-d H:i:s', ($curr_pdate)).'" and "'.date('Y-m-d H:i:s', ($curr_pdate+24*60*60-1)).'" group by uri  order by uri asc';
			//echo $sql.'<br>';
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			$subs=array();
			for($i=0;$i<$rc; $i++){
				$f=mysqli_fetch_array($rs);
				
				$subs[]=$f;
					
			}
			$data['subs']=$subs;
			
			
			$curr_pdate+=24*60*60;
			$alls[]=$data;
		}
		return $alls;	
	}
	
	
	//распределение посещаемости разделов за сутки
	public function GetSubPerDay($pdate1){
		$alls=array();
		
		
		$curr_pdate=$this->DatefromYmd($pdate1);  
		
		//echo $curr_pdate;
		
	 
			$data=array();
			
			$data['pdate']=date('d.m.Y', $curr_pdate);
			
			//получить разделы, какие были посещены за эти сутки
			$sql='select uri, count(id) as c_id from gydex_hits where pdate between "'.date('Y-m-d H:i:s', ($curr_pdate)).'" and "'.date('Y-m-d H:i:s', ($curr_pdate+24*60*60-1)).'" group by uri  order by uri asc';
			//echo $sql.'<br>';
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			$subs=array();
			for($i=0;$i<$rc; $i++){
				$f=mysqli_fetch_array($rs);
				
				$subs[]=$f;
					
			}
			$data['subs']=$subs;
			
			
		 
		 
		 
		return $data;	
	}
	
	
	//кол-во заказов в инет-маг за сутки
	public function GetOrders($pdate1, $pdate2){
		$alls=array();
		
		$curr_pdate=$this->DatefromYmd($pdate1); $end_pdate=$this->DatefromYmd($pdate2);
		
		//echo $curr_pdate;
		
		
	
		 while($curr_pdate<=$end_pdate){
			$data=array();
			
			$data['pdate']=date('d.m.Y', $curr_pdate);
			
			$sql='select  count(id) as c_id from orders where pdate ="'.date('Y-m-d', ($curr_pdate)).'" group by pdate order by pdate asc';
		
			//echo $sql.'<br>';
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			$subs=array();
			 
				$f=mysqli_fetch_array($rs);
				
			$data['orders']=(int)$f;
					
			 
			
			
			$curr_pdate+=24*60*60;
			$alls[]=$data;
		}
		 
		return $alls;	
		
	}
	
	//среднее время работы на сайте по суткам
	public function GetAverageTime($pdate1, $pdate2, $interval=3600){
		/*
		цикл по датам, в каждой дате:
		какие айпи работали
		по каждому айпи: получить сессии работы
		сессия - это отделенный от предыдущего запроса на час или более последующий запрос
		или группа запросов, с интервалами между  ними не боле часа. 
		*/	
		
		$alls=array();
		
		$curr_pdate=$this->DatefromYmd($pdate1); $end_pdate=$this->DatefromYmd($pdate2);
		
		//echo $curr_pdate;
		while($curr_pdate<=$end_pdate){
			$data=array();
			
			$data['pdate']=date('d.m.Y', $curr_pdate);
			
			$sql='select  distinct ip from gydex_hits where  pdate between "'.date('Y-m-d H:i:s', ($curr_pdate)).'" and "'.date('Y-m-d H:i:s', ($curr_pdate+24*60*60-1)).'"  ';
		
			//echo $sql.'<br>';
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			$subs=array();
			for($i=0;$i<$rc; $i++){
				$f=mysqli_fetch_array($rs);
				
				//найдем все записи по активности
				$sql1='select  * from gydex_hits where ip="'.$f['ip'].'" order by pdate asc';
				//echo $sql1.'<br>';;
				$entries=array();
				$set1=new mysqlset($sql1);
				$rs1=$set1->GetResult();
				$rc1=$set1->GetResultNumRows();
				for($i1=0;$i1<$rc1; $i1++){
					$f1=mysqli_fetch_array($rs1);
					$entries[]=$f1;  
				}
				
			 
				
				$f['sessions']=$this->GetSessions($curr_pdate,  $entries, $interval);
				
				$subs[]=$f;
					
			}
			$flat_times=array();
			foreach($subs as $k=>$v){
				foreach($v['sessions'] as $kk=>$vv) $flat_times[]=$vv;	
			}
			//print_r($flat_times);
			
			$sum=0; 
			foreach($flat_times as $k=>$v) $sum+=$v;
			if(count($flat_times)>0){
				$ave=round($sum/count($flat_times)/60);
			}else $ave=0;
			
			
			$data['ave']=$ave;
			 
			$curr_pdate+=24*60*60;
			$alls[]=$data;
		}
		 
		return $alls;		 
	}
	
	protected function GetSessions($curr_pdate,  $entries, $interval=3600){
		$session=array();
		
		$beg=$curr_pdate;
		$per_beg=$curr_pdate;
		
		$was_closed=false;
		foreach($entries as $k=>$v){
			$current_time=self::DateFromYmdHis($v['pdate']);
			//echo date('d.m.Y H:i:s', $current_time);
			
			
			if(($current_time-$beg)>$interval) {
				
				//$per=array();	
				if($k!=0){
					// echo date('d.m.Y H:i:s', $current_time).' vs '. date('d.m.Y H:i:s', $beg).'<br>';	
					
					 $session[]=$beg-$per_beg;
					 $was_closed=true;
				}
				
				
				$per_beg=$current_time;

			}else $was_closed=false;
			
			
				
			
			$beg=self::DateFromYmdHis($v['pdate']);
		}
		if(!$was_closed){
			// echo date('d.m.Y H:i:s', $current_time).' vs '. date('d.m.Y H:i:s', $per_beg).'<br>';	
					
			if($current_time-$per_beg>0) $session[]=$current_time-$per_beg; 
		}
		
		/*echo '<pre>';
		print_r($session);
		echo '</pre>';*/
		
		return $session;
	}
	
	static public function DateFromdmY($string='01.01.2008'){
		return mktime(0,0,0,substr($string,3,2),substr($string,0,2),substr($string,6,4) );
	}
		
	
	static public function DateFromYmd($string='2008-01-01'){
		return (mktime(0,0,0,substr($string,5,2),substr($string,8,2),substr($string,0,4) ));
	}
	
	static public function DateFromYmdHis($string='2008-01-01 00:00:00'){
		//mktime(
		return (mktime( substr($string,11,2),substr($string,14,2),substr($string,17,2),substr($string,5,2),substr($string,8,2),substr($string,0,4) ));
	}
	
}

?>
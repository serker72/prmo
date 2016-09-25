<?
//require_once('db_decorator.php');
require_once('tabgeo/tabgeo_country_v4.php');
require_once('tabgeo/countries_descrs.php');

require_once('delivery.class.php');
 
//отчеты по рассылке
class Delivery_Reports{
 	protected $instance;
	public function __construct($kind){
		switch($kind){
			
			case 1:
				$this->instance=new Delivery_Reports_1;
			break;	
			case 2:
				$this->instance=new Delivery_Reports_2;
			break;	
			case 3:
				$this->instance=new Delivery_Reports_3;
			break;	
			case 4:
				$this->instance=new Delivery_Reports_4;
			break;	
			case 5:
				$this->instance=new Delivery_Reports_5;
			break;	
			case 6:
				$this->instance=new Delivery_Reports_6;
			break;	
			
			default:
				$this->instance=new Delivery_Reports_1;
			break;	
		}
	}
	
	public function GetDataArr($id, $data){
		return $this->instance->GetDataArr($id, $data);		
	}
}


//обзорный отчет
class Delivery_Reports_1 extends Delivery_ReportsAbstract{
	
	public function GetDataArr($id, $data){
		$arr=array();
		
		$_dli=new delivery_listitem; $dli=$_dli->Getitembyid($data['list_id']); $arr['list_name']=$dli['name'];
		$_dsi=new delivery_segmentitem; 
		if($data['segment_id']!=0) {
			$dsi=$_dsi->getitembyid($data['segment_id']);
			$arr['segment_name']=$dsi['name'];
		}
		
		//ќткрыто писем	%
		$sql='select count(distinct user_id) from delivery_subscriber where is_sent=1 and delivery_id="'.$data['id'].'"';
		$set=new mysqlSet($sql);
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);
		$total=(int)$f[0];
		
		
		$arr['users_count']=$total;
		
		$sql='select count(distinct user_id) from delivery_subscriber where is_sent=1 and	is_viewed=1 and delivery_id="'.$data['id'].'"';
		$set=new mysqlSet($sql);
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);
		$opened=(int)$f[0];
		
		 
		
		if($total!=0) $arr['open_percent']=round(100*$opened/$total);
		else  $arr['open_percent']=0;
		
		// ликов по ссылкам click_percent
		//перебрать всех подписчиков
		//по каждому найти его процент кликов
		//усреднить эти проценты и вывести как итог
		$num_of_links=0;
		$sql='select count(*) from delivery_link where delivery_id="'.$data['id'].'"';
		$set=new mysqlSet($sql);
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);
		$num_of_links=(int)$f[0];
		
		
		$sql='select distinct user_id from delivery_subscriber where is_sent=1 and delivery_id="'.$data['id'].'" ';
		 
		$summ=0;
		$set=new mysqlSet($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			//отношение числа кликнутых ссылок к общему числу ссылок
			$sql1='select count(distinct link_id) from delivery_link_hits where delivery_id="'.$data['id'].'" and user_id="'.$f[0].'"';
			//echo $sql1;
			
			$set1=new mysqlSet($sql1);
			$rs1=$set1->GetResult();
			$f1=mysqli_fetch_array($rs1);
			$hit_user=(int)$f1[0];
			
			if($num_of_links!=0) $summ+=100*$hit_user/$num_of_links;
		}
		
		
		if($rc>0) $arr['click_percent']=round($summ/$rc);
		else $arr['click_percent']=0;
		
		 
		
		//открыто писем в среднем по списку подписчиков open_average
		$sql1='select * from delivery where id<>"'.$data['id'].'" and list_id="'.$data['list_id'].'" and segment_id="'.$data['segment_id'].'" and status_id=3 and pdate_status_change<"'.$data['pdate_status_change'].'"';
		$set1=new mysqlSet($sql1);
		$rs1=$set1->GetResult();
		$rc1=$set1->Getresultnumrows();
		$summ=0;
		for($i=0; $i<$rc1; $i++){
			$g=mysqli_fetch_array($rs1);
			$sql='select count(distinct user_id) from delivery_subscriber where is_sent=1 and delivery_id="'.$g['id'].'"';
			$set=new mysqlSet($sql);
			$rs=$set->GetResult();
			$f=mysqli_fetch_array($rs);
			$_total=(int)$f[0];
			
			$sql='select count(distinct user_id) from delivery_subscriber where is_sent=1 and	is_viewed=1 and delivery_id="'.$g['id'].'"';
			$set=new mysqlSet($sql);
			$rs=$set->GetResult();
			$f=mysqli_fetch_array($rs);
			$_opened=(int)$f[0];
			
			if($_total!=0) $summ+=round(100*$_opened/$_total);
		 
		}
		$arr['open_average']=round($summ/$rc1);
		
		//клики в среднем по списку click_average
		$sql1='select * from delivery where id<>"'.$data['id'].'" and list_id="'.$data['list_id'].'" and segment_id="'.$data['segment_id'].'" and status_id=3 and pdate_status_change<"'.$data['pdate_status_change'].'"';
		$set1=new mysqlSet($sql1);
		$rs1=$set1->GetResult();
		$rc1=$set1->Getresultnumrows();
		$summ=0;
		for($i=0; $i<$rc1; $i++){
			$g=mysqli_fetch_array($rs1);
			
			
		 
			
			 
			
			$_num_of_links=0;
			$sql='select count(*) from delivery_link where delivery_id="'.$g['id'].'"';
			$set=new mysqlSet($sql);
			$rs=$set->GetResult();
			$f=mysqli_fetch_array($rs);
			$_num_of_links=(int)$f[0];
			
			
			
			$sql='select distinct user_id from delivery_subscriber where is_sent=1 and delivery_id="'.$g['id'].'" ';
		 
			$_summ=0;
			$set=new mysqlSet($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);
				
				//отношение числа кликнутых ссылок к общему числу ссылок
				$sql1='select count(distinct link_id) from delivery_link_hits where delivery_id="'.$g['id'].'" and user_id="'.$f[0].'"';
				//echo $sql1;
				
				$set1=new mysqlSet($sql1);
				$rs1=$set1->GetResult();
				$f1=mysqli_fetch_array($rs1);
				$_hit_user=(int)$f1[0];
				
				if($_num_of_links!=0) $_summ+=100*$_hit_user/$_num_of_links;
			}
			
			
			if($rc>0) $summ+=round($_summ/$rc);
			
			
		 
		}
		$arr['click_average']=round($summ/$rc1);
		
		//всего открытий open_count
		$sql='select sum(hits) from delivery_subscriber_hits where delivery_id="'.$data['id'].'"';
		$set=new mysqlSet($sql);
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);
		$arr['open_count']=(int)$f[0];
		
		//всего кликов clicks_count
		$sql='select sum(hits) from delivery_link_hits where delivery_id="'.$data['id'].'"';
		$set=new mysqlSet($sql);
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);
		$arr['clicks_count']=(int)$f[0];
		
		//всего отписок unsub_count
		$sql='select count(*) from delivery_user where is_subscribed=0 and unsubscribed_delivery_id="'.$data['id'].'"';
		$set=new mysqlSet($sql);
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);
		$arr['unsub_count']=(int)$f[0];
		
		
		// ƒата последнего открыти€: %{$rep.open_last_pdate}%
		$sql='select pdate from delivery_subscriber_hits where delivery_id="'.$data['id'].'" order by pdate desc limit 1';
		$set=new mysqlSet($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		if($rc>0){
			$f=mysqli_fetch_array($rs);
			$arr['open_last_pdate']=date('d.m.Y H:i:s', $f['pdate']);
		}else $arr['open_last_pdate']='-';
		
		//дата последнего клика clicks_last_pdate
		$sql='select pdate from delivery_link_hits where delivery_id="'.$data['id'].'" order by pdate desc limit 1';
		$set=new mysqlSet($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		if($rc>0){
			$f=mysqli_fetch_array($rs);
			$arr['clicks_last_pdate']=date('d.m.Y H:i:s', $f['pdate']);
		}else $arr['clicks_last_pdate']='-';
		
		//кликов на открытие, %
		//100% - это если при каждом открытии открытли все ссылки!
		// 100* клики/открыти€*число ссылок
		//найдем число
		if(($num_of_links*$arr['open_count'])!=0){
			$arr['clicks_per_opens']=round(100*$arr['clicks_count']/($num_of_links*$arr['open_count']));
		}else $arr['clicks_per_opens']=0;
		
		
		//Ќаиболее часто кликаемые ссылки click_links
		$click_links=array();
		$sql='select u.url, sum(hu.hits) as s_q
		from delivery_link as u
		inner join delivery_link_hits as hu on u.id=hu.link_id
		where
			u.delivery_id="'.$data['id'].'"
		
		group by u.id
		having s_q>0	
		order by s_q desc limit 10';
		//echo $sql;
		
		$set=new mysqlSet($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			$click_links[]=$f;
		}
		$arr['click_links']=$click_links;
		
		//ѕодписчики, чаще всего открывшие письмо рассылки< users_opened
		$users_opened=array();
		$sql='select u.email, sum(hu.hits) as s_q
		from delivery_user as u
		inner join delivery_subscriber as ds on ds.user_id=u.id and ds.delivery_id="'.$data['id'].'"
		inner join delivery_subscriber_hits as hu on ds.id=hu.subscriber_id
		where
			hu.delivery_id="'.$data['id'].'"
		
		group by u.id
		having s_q>0	
		order by s_q desc limit 10';
		//echo $sql;
		
		$set=new mysqlSet($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			$users_opened[]=$f;
		}
		$arr['users_opened']=$users_opened;
		
		
		
		//собрать данные по странам
		$county_ips=array();
		$ips_data=array();
		
		
		$sql='select distinct hu.ip, sum(hu.hits) as s_q from
		delivery_subscriber_hits as hu
		
		where
			hu.delivery_id="'.$data['id'].'"
		group by hu.ip
		having s_q>0	
		order by s_q desc ';
		$set=new mysqlSet($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			$ips_data[]=array('ip'=>$f['ip'], 's_q'=>(int)$f['s_q']);
		}
		//$arr['users_opened']=$users_opened;
		//var_dump($ips_data);	
		
		foreach($ips_data as $k=>$ip){
			$country_code = tabgeo_country_v4($ip['ip']);
			//echo $country_code;	
			//$adding=array('country_code'=>$country_code, 
			if(!isset($county_ips[$country_code]))  $county_ips[$country_code]=$ip['s_q'];
			else  $county_ips[$country_code]+=$ip['s_q'];
		}
		
		$countries_opened=array();		 
		foreach($county_ips as $code=>$value){
			if(isset(CountriesDescrs::$Data[$code])) $countries_opened[]= array(
					'name'=>CountriesDescrs::$Data[$code]['name'],
					'flag'=>CountriesDescrs::$Data[$code]['flag'],
					'code'=>$code,
					's_q'=>$value
					);
					
		}
		
		
		$arr['countries_opened']=$countries_opened;
		//var_dump($county_ips);
		
		
		
		//построим график 24час. активности
		//2 показател€: сумма кликов на час, сумма просмотров на час
		
		$x_set=array(); $y_set1=array(); $y_set2=array();
		//найдем врем€ отправки рассылки
		$now=time();
		$yest=time()-24*60*60;
		$begin=$data['pdate_status_change'];
		
		//кто из них позже, тот и начало
		if($yest>$begin) $begin=$yest;
		//$begin;
		
		$begin_o=date('d.m.Y H',$begin);
		
		$begin_o=mktime(substr($begin_o,11,2), 0,0,substr($begin_o,3,2),substr($begin_o,0,2),substr($begin_o,6,4));
		
		for($i=$begin_o; $i<=$now; $i=$i+60*60){
			$x_set[]=  array(
				'val'=>date('H:i', $i),
				'stamp'=>$i);
		}
		
		//print_r($x_set);
		
		//находим данные
		foreach($x_set as $k=>$v){
			if($k==0){
				//просмотров
				$sql='select sum(hits) from delivery_subscriber_hits where delivery_id="'.$data['id'].'" and pdate<="'.$v['stamp'].'"';
//				echo $sql;
				$set=new mysqlSet($sql);
				$rs=$set->GetResult();
				$f=mysqli_fetch_array($rs);
				
				$views=(int)$f[0];
				
				//кликов
				$sql='select sum(hits) from delivery_link_hits where delivery_id="'.$data['id'].'" and pdate<="'.$v['stamp'].'"';
				$set=new mysqlSet($sql);
				$rs=$set->GetResult();
				$f=mysqli_fetch_array($rs);
				
				$clicks=(int)$f[0];
				
				$y_set1[]=$views; $y_set2[]=$clicks;	
			}else{
				//просмотров
				$sql='select sum(hits) from delivery_subscriber_hits where delivery_id="'.$data['id'].'" and (pdate between  "'.$x_set[$k-1]['stamp'].'" and "'.$v['stamp'].'" )';
				$set=new mysqlSet($sql);
				$rs=$set->GetResult();
				$f=mysqli_fetch_array($rs);
				
				$views=(int)$f[0];
				
				//кликов
				$sql='select sum(hits) from delivery_link_hits where delivery_id="'.$data['id'].'" and (pdate between  "'.$x_set[$k-1]['stamp'].'" and "'.$v['stamp'].'" )';
				$set=new mysqlSet($sql);
				$rs=$set->GetResult();
				$f=mysqli_fetch_array($rs);
				
				$clicks=(int)$f[0];
				
				$y_set1[]=$views; $y_set2[]=$clicks;	
			}
		}
		
		
		//print_r($y_set2);
		
		$arr['activity_x']=$x_set; $arr['activity_views']=$y_set1; $arr['activity_clicks']=$y_set2;
		
		
		return $arr;	
	}
	
}


//кому отправлено
class Delivery_Reports_2 extends Delivery_ReportsAbstract{
	public function GetDataArr($id, $data){
		$arr=array();
		$sql='SELECT u . *
FROM delivery_user AS u
INNER JOIN delivery_subscriber AS ds ON ds.user_id = u.id
AND ds.delivery_id = "'.$data['id'].'"
WHERE ds.delivery_id = "'.$data['id'].'"
ORDER BY u.email ASC';
		//echo $sql;
		
		$set=new mysqlSet($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			$users_opened[]=$f;
		}
		$arr['users']=$users_opened;
		
		return $arr;	
	}
}

//кем открыта рассылка и сколько раз
class Delivery_Reports_3 extends Delivery_ReportsAbstract{
	public function GetDataArr($id, $data){
		$arr=array();
		$sql='SELECT u . *, sum(hu.hits) as s_q
FROM delivery_user AS u
INNER JOIN delivery_subscriber AS ds ON ds.user_id = u.id
left join delivery_subscriber_hits as hu on ds.id=hu.subscriber_id
AND ds.delivery_id = "'.$data['id'].'"
WHERE ds.delivery_id = "'.$data['id'].'"
group by ds.id


ORDER BY s_q desc, u.email asc';
		//echo $sql;
		
		$set=new mysqlSet($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			$f['s_q']=(int)$f['s_q'];
			
			$users_opened[]=$f;
		}
		$arr['users']=$users_opened;
		
		return $arr;	
	}
}

//клики по ссылкам
class Delivery_Reports_4 extends Delivery_ReportsAbstract{
	public function GetDataArr($id, $data){
		$arr=array();
		$sql='SELECT u . *, sum(hu.hits) as s_q
FROM delivery_user AS u

left join delivery_link_hits as hu on hu.user_id=u.id
left JOIN delivery_link AS ds ON ds.id = hu.link_id

AND ds.delivery_id = "'.$data['id'].'"


WHERE ds.delivery_id = "'.$data['id'].'"
group by u.id


ORDER BY s_q desc, u.email asc';
		//echo $sql;
		
		$set=new mysqlSet($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			$f['s_q']=(int)$f['s_q'];
			
			$users_opened[]=$f;
		}
		$arr['users']=$users_opened;
		
		return $arr;	
	}
}


//кем не открыта
class Delivery_Reports_5 extends Delivery_ReportsAbstract{
	public function GetDataArr($id, $data){
		$arr=array();
		$sql='SELECT u . *, sum(hu.hits) as s_q
FROM delivery_user AS u
INNER JOIN delivery_subscriber AS ds ON ds.user_id = u.id
left join delivery_subscriber_hits as hu on ds.id=hu.subscriber_id
AND ds.delivery_id = "'.$data['id'].'"
WHERE ds.delivery_id = "'.$data['id'].'"
group by ds.id
having s_q=0 or s_q is null

ORDER BY s_q desc, u.email asc';
		//echo $sql;
		
		$set=new mysqlSet($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			$f['s_q']=(int)$f['s_q'];
			
			$users_opened[]=$f;
		}
		$arr['users']=$users_opened;
		
		return $arr;	
	}
}

//кто отписалс€
class Delivery_Reports_6 extends Delivery_ReportsAbstract{
	public function GetDataArr($id, $data){
		$arr=array();
		$sql='SELECT u . * 
FROM delivery_user AS u
INNER JOIN delivery_subscriber AS ds ON ds.user_id = u.id

WHERE ds.delivery_id = "'.$data['id'].'"
 
and u.is_subscribed=0 and u.unsubscribed_delivery_id="'.$data['id'].'"

ORDER BY   u.email asc';
		//echo $sql;
		
		$set=new mysqlSet($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			 
			$users_opened[]=$f;
		}
		$arr['users']=$users_opened;
		
		
		/*сделать разбивку по способам отписки */
		
		return $arr;	
	}
}

/*еще нужны отчеты по ссылкам!*/


class Delivery_ReportsAbstract{
 	
	
	public function GetDataArr($id, $data){
		$arr=array();
		
		return $arr;	
	}
}


?>
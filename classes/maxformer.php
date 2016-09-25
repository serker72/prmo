<?
require_once('MysqlSet.php');
require_once('NonSet.php');


class MaxFormer{
	
	//число позиций в заявке - кв
	
	public function MaxInKomplekt($komplekt_id, $position_id){
		$res=0;
		
		//найти айди позиции в к.в. 
		$set=new mysqlSet('select * from komplekt_ved_pos where komplekt_ved_id="'.$komplekt_id.'" and position_id="'.$position_id.'" limit 1');
		
		$rc=$set->getResultNumRows();
		$rs=$set->getResult();
		if($rc>0){
			$f=mysqli_fetch_array($rs);
			$kvpid=$f['id'];
				
			$res=$f['quantity_confirmed'];
		}
		
		return round($res,3);
		
	}
	
	
	public function MaxInKomplektInit($komplekt_id, $position_id){
		$res=0;
		
		//найти айди позиции в к.в. 
		$set=new mysqlSet('select * from komplekt_ved_pos where komplekt_ved_id="'.$komplekt_id.'" and position_id="'.$position_id.'" limit 1');
		
		$rc=$set->getResultNumRows();
		$rs=$set->getResult();
		if($rc>0){
			$f=mysqli_fetch_array($rs);
			$kvpid=$f['id'];
				
			$res=$f['quantity_initial'];
		}
		
		return round($res,3);
		
	}
	
	
	
	//число позиций в счете
	public function MaxInBill($bill_id, $position_id,$storage_id=NULL, $sector_id=NULL,$komplekt_ved_id=NULL){
		$res=0;
		
		$flt='';
		
		if($storage_id!==NULL) $flt.=' and storage_id="'.$storage_id.'"';
		if($storage_id!==NULL) $flt.=' and sector_id="'.$sector_id.'"';
		if($komplekt_ved_id!==NULL) $flt.=' and komplekt_ved_id="'.$komplekt_ved_id.'"';
		
		//найти число позиций по счету 
		$sql='select sum(quantity) from bill_position where bill_id="'.$bill_id.'" and position_id="'.$position_id.'" '.$flt;
		
		//echo $sql;
		$set=new mysqlSet($sql);
		
		
		$rc=$set->getResultNumRows();
		$rs=$set->getResult();
		if($rc>0){
			$f=mysqli_fetch_array($rs);
				
			$res=(float)$f[0];
		}
		
		return round($res,3);
	}
	
	
	
	
	//число позиций в распоряжениях на отгрузку
	public function MaxInShI($bill_id, $position_id, $sh_i_id=NULL, $storage_id=NULL, $sector_id=NULL,$komplekt_ved_id=NULL){
		$res=0;
		
		//найти число позиций по счету 
		$flt='';
		$flt2='';
		if($sh_i_id!==NULL) $flt.=' and id="'.$sh_i_id.'" ';
		//else $flt='';
		if($storage_id!==NULL) $flt.=' and storage_id="'.$storage_id.'"';
		if($storage_id!==NULL) $flt.=' and sector_id="'.$sector_id.'"';
		if($komplekt_ved_id!==NULL) $flt2.=' and komplekt_ved_id="'.$komplekt_ved_id.'"';
		
		
		$set=new mysqlSet('select sum(quantity) from sh_i_position where sh_i_id in(select id from sh_i where bill_id="'.$bill_id.'" '.$flt.' and is_confirmed=1) and position_id="'.$position_id.'" '.$flt2);
		
		
		$rc=$set->getResultNumRows();
		$rs=$set->getResult();
		if($rc>0){
			$f=mysqli_fetch_array($rs);
				
			$res=(float)$f[0];
		}
		
		return round($res,3);
	}
	
	
	
	//максимальное число позиций для реализации согласно связанным поступлениям за вычетом других реализаций
	public function MaxForAccByAccIn($bill_id, $position_id,$except_id=0, $sh_i_id=NULL,$storage_id=NULL, $sector_id=NULL, $komplekt_ved_id=NULL, $acceptance_in_id=NULL){
		$res=0;
		
		//найти число позиций по счету 
		$flt=''; $flt2=''; $flt3='';
		 
		//лазейка для старых документов: дополнительное условие &&($acceptance_in_id!=0)
		 
		if($storage_id!==NULL) $flt.=' and sector_id="'.$sector_id.'"'; 
		if($komplekt_ved_id!==NULL) $flt2.=' and komplekt_ved_id="'.$komplekt_ved_id.'"'; 
		if(($acceptance_in_id!==NULL)&&($acceptance_in_id!=0)) $flt3.=' and id="'.$acceptance_in_id.'"';
		//echo $acceptance_in_id;
		
		$sql='select sum(quantity) from acceptance_position where acceptance_id in(select id from acceptance where id<>"'.$except_id.'" '.$flt.'  '.$flt3.'  and is_confirmed=1 and is_incoming=1 ) and out_bill_id="'.$bill_id.'" and position_id="'.$position_id.'" '.$flt2;
		//echo $sql;
		
		$set=new mysqlSet($sql);
		
		
		$rc=$set->getResultNumRows();
		$rs=$set->getResult();
		if($rc>0){
			$f=mysqli_fetch_array($rs);
				
			$res=(float)$f[0];
		}
		
		if(($acceptance_in_id!==NULL)&&($acceptance_in_id!=0)) $flt2.=' and acceptance_in_id="'.$acceptance_in_id.'"';
		
		//вычесть кол-во по другим реализациям
		$sql='select sum(quantity) from acceptance_position where acceptance_id in(select id from acceptance where id<>"'.$except_id.'" '.$flt.'  and  bill_id="'.$bill_id.'"  and is_confirmed=1 and is_incoming=0 ) and position_id="'.$position_id.'" '.$flt2;
		//echo $sql.'<br>';
		
		$set=new mysqlSet($sql);
		
		
		$rc=$set->getResultNumRows();
		$rs=$set->getResult();
		if($rc>0){
			$f=mysqli_fetch_array($rs);
				
			$res-=(float)$f[0];
		}
		
		return round($res,3);
	}
	
	
	
	//число позиций в реализации
	public function MaxInAcc($bill_id, $position_id,$except_id=0, $sh_i_id=NULL,$storage_id=NULL, $sector_id=NULL, $komplekt_ved_id=NULL, $acceptance_in_id=NULL){
		$res=0;
		
		//найти число позиций по счету 
		$flt=''; $flt2='';
		 
		
		 
		if($storage_id!==NULL) $flt.=' and sector_id="'.$sector_id.'"'; 
		if($komplekt_ved_id!==NULL) $flt2.=' and komplekt_ved_id="'.$komplekt_ved_id.'"'; 
	//	if($acceptance_in_id!==NULL) $flt2.=' and acceptance_in_id="'.$acceptance_in_id.'"';
		
		
		$sql='select sum(quantity) from acceptance_position where acceptance_id in(select id from acceptance where bill_id="'.$bill_id.'" and id<>"'.$except_id.'" '.$flt.'  and is_confirmed=1 and is_incoming=0 ) and position_id="'.$position_id.'" '.$flt2;
		//echo $sql;
		
		$set=new mysqlSet($sql);
		
		
		$rc=$set->getResultNumRows();
		$rs=$set->getResult();
		if($rc>0){
			$f=mysqli_fetch_array($rs);
				
			$res=(float)$f[0];
		}
		
		return round($res,3);
	}
	
	//число позиций в поступлении
	public function MaxInInAcc($bill_id, $position_id,$except_id=0, $sh_i_id=NULL,$storage_id=NULL, $sector_id=NULL, $komplekt_ved_id=NULL){
		$res=0;
		
		//найти число позиций по счету 
		$flt=''; $flt2='';
		 
		
		 
		if($storage_id!==NULL) $flt.=' and sector_id="'.$sector_id.'"'; 
		if($komplekt_ved_id!==NULL) $flt2.=' and komplekt_ved_id="'.$komplekt_ved_id.'"'; 
		
		
		$sql='select sum(quantity) from acceptance_position where acceptance_id in(select id from acceptance where bill_id="'.$bill_id.'" and id<>"'.$except_id.'" '.$flt.'  and is_confirmed=1 and is_incoming=1 ) and position_id="'.$position_id.'" '.$flt2;
		//echo $sql;
		
		$set=new mysqlSet($sql);
		
		
		$rc=$set->getResultNumRows();
		$rs=$set->getResult();
		if($rc>0){
			$f=mysqli_fetch_array($rs);
				
			$res=(float)$f[0];
		}
		
		return round($res,3);
	}
	
	
	//число позиций в поступлении, связанном со входящим счетом по данному исходящему счету
	public function MaxInAccIn($bill_id, $position_id,$except_id=0, $sh_i_id=NULL,$storage_id=NULL, $sector_id=NULL, $komplekt_ved_id=NULL){
		$res=0;
		
		//найти число позиций по счету 
		$flt=''; $flt2='';
		 
		
		 
		if($storage_id!==NULL) $flt.=' and sector_id="'.$sector_id.'"'; 
		if($komplekt_ved_id!==NULL) $flt2.=' and komplekt_ved_id="'.$komplekt_ved_id.'"'; 
		
		
		
		$sql='select sum(quantity) from acceptance_position where acceptance_id in(select id from acceptance where  id<>"'.$except_id.'" '.$flt.'  and is_confirmed=1 and is_incoming=1 ) and out_bill_id="'.$bill_id.'" and position_id="'.$position_id.'" '.$flt2;
		 //if($position_id==1470) echo $sql;
		
		$set=new mysqlSet($sql);
		
		
		$rc=$set->getResultNumRows();
		$rs=$set->getResult();
		if($rc>0){
			$f=mysqli_fetch_array($rs);
				
			$res=(float)$f[0];
		}
		
		return round($res,3);
	}
	
	
	//число позиций в поступлениях, связанных со входящими счетами по данному исходящему счету
	public function MaxInAccInComplex($bill_id, $position_id,$except_id=0, $sh_i_id=NULL,$storage_id=NULL, $sector_id=NULL, $komplekt_ved_id=NULL){
		$res=array();
		
		//найти число позиций по счету 
		$flt=''; $flt2='';
		 
		
		 
		if($storage_id!==NULL) $flt.=' and sector_id="'.$sector_id.'"'; 
		if($komplekt_ved_id!==NULL) $flt2.=' and komplekt_ved_id="'.$komplekt_ved_id.'"'; 
		
		
		$sql1='select id from acceptance where  id<>"'.$except_id.'" '.$flt.' and out_bill_id="'.$bill_id.'"  and is_confirmed=1 and is_incoming=1 ';
		$set1=new mysqlSet($sql1);
		$rc1=$set1->getResultNumRows();
		$rs1=$set1->getResult();
		for($i=0; $i<$rc1; $i++){
			$g=mysqli_fetch_array($rs1);
		
			$sql='select sum(quantity) from acceptance_position where acceptance_id="'.$g['id'].'" and out_bill_id="'.$bill_id.'" and position_id="'.$position_id.'" '.$flt2;
			 
			
			$set=new mysqlSet($sql);
			
			
			$rc=$set->getResultNumRows();
			$rs=$set->getResult();
			 
			$f=mysqli_fetch_array($rs);
						
				//$res=(float)$f[0];
			 
			$res[]=array((int)$g['id'], round((float)$f[0],3));
		}
		
		return $res;
	}
	
	//ДОСТУПНОЕ число согласно числу позиций в поступлениях, связанных со входящими счетами по данному исходящему счету
	public function FreeInAccInComplex($bill_id, $position_id,$except_id=0, $sh_i_id=NULL,$storage_id=NULL, $sector_id=NULL, $komplekt_ved_id=NULL){
		$res=array();
		
		//найти число позиций по счету 
		$flt=''; $flt2='';
		 
		
		 
		if($storage_id!==NULL) $flt.=' and sector_id="'.$sector_id.'"'; 
		if($komplekt_ved_id!==NULL) $flt2.=' and komplekt_ved_id="'.$komplekt_ved_id.'"'; 
		
		
		
		//лазейка для старых документов:
		//проверить число в реализациях, связанных только по айди позиции и нулевому айди поступления. если оно не ноль, то
		//это старый документ, и контроль вести по старому алгоритму...
		
		//найти число в связ утвержд. реализациях...
		
		$sql2='select sum(quantity) from acceptance_position where acceptance_id in(select id from acceptance where is_confirmed=1 and is_incoming=0  '.$flt.' and bill_id="'.$bill_id.'" )  and position_id="'.$position_id.'" and acceptance_in_id="0"  '.$flt2;
		
		$set2=new mysqlSet($sql2);
		$rs2=$set2->getResult();			
		
		$f2=mysqli_fetch_array($rs2);
		if((int)$f2[0]!=0) {
			//это старый документ, вести контроль по старому алгоритму
			$in_rasp=round($this->MaxInAcc($bill_id ,   $position_id, $except_id,  $sh_i_id,   $storage_id,$sector_id,  $komplekt_ved_id),3);   
			  
			  
			$in_rasp_in=round($this->MaxInAccIn( $bill_id, $position_id,  $except_id,  $sh_i_id, $storage_id,$sector_id, $komplekt_ved_id),3);   
			 
			$in_free=$in_rasp_in - $in_rasp;
			
			$res=array(array(0,$in_free));
			//echo 'old';
		}else{
			//echo 'new';
			//это новый документ, контроль вести по новому алгоритму
		
			$sql1='select id from acceptance where  id<>"'.$except_id.'" '.$flt.' and out_bill_id="'.$bill_id.'"  and is_confirmed=1 and is_incoming=1 ';
			$set1=new mysqlSet($sql1);
			$rc1=$set1->getResultNumRows();
			$rs1=$set1->getResult();
			for($i=0; $i<$rc1; $i++){
				$g=mysqli_fetch_array($rs1);
				
				
				
				//число в этом поступлении
				$sql='select sum(quantity) from acceptance_position where acceptance_id="'.$g['id'].'" and out_bill_id="'.$bill_id.'" and position_id="'.$position_id.'" '.$flt2;
				 
				
				$set=new mysqlSet($sql);
				
				
				$rc=$set->getResultNumRows();
				$rs=$set->getResult();
				 
				$f=mysqli_fetch_array($rs);
				
				
				
				
				//найти число в связ утвержд. реализациях...
				
				$sql2='select sum(quantity) from acceptance_position where acceptance_id in(select id from acceptance where is_confirmed=1 and is_incoming=0  '.$flt.' and bill_id="'.$bill_id.'" )  and position_id="'.$position_id.'" and acceptance_in_id="'.$g['id'].'" '.$flt2;
				
				$set2=new mysqlSet($sql2);
				$rs2=$set2->getResult();			
				
				$f2=mysqli_fetch_array($rs2);
				 
				$res[]=array((int)$g['id'], round( (float)$f[0]-(float)$f2[0],3));
			}
			
			
			if($rc1==0) $res=array(array(0,0));
		}
		
		return $res;
	}
	
	
	//число позиций в доверенности
	public function MaxInTrust($bill_id, $position_id){
		$res=0;
		
		//найти число позиций по счету 
		$set=new mysqlSet('select sum(quantity) from trust_position where trust_id in(select id from trust where bill_id="'.$bill_id.'"  and is_confirmed=1) and position_id="'.$position_id.'"');
		
		
		$rc=$set->getResultNumRows();
		$rs=$set->getResult();
		if($rc>0){
			$f=mysqli_fetch_array($rs);
				
			$res=(float)$f[0];
		}
		
		return round($res,3);
	}
	
	//макс доступное количество позиции для формирования доверенности
	public function MaxForTrust($bill_id, $position_id, $except_trust_id=NULL){
		$res=0;
		
		//найти 
		$sql='select sum(quantity) from bill_position where bill_id="'.$bill_id.'" and position_id="'.$position_id.'" ';
		//echo $sql.'<br>';
		$set=new mysqlSet($sql);
		
		$rc=$set->getResultNumRows();
		$rs=$set->getResult();
		if($rc>0){
			$f=mysqli_fetch_array($rs);
			
			$res=(float)$f[0];
			
			//найдем сумму по дов-тям счета
			$flt='';
			if($except_trust_id!==NULL) $flt.=' and trust_id<>"'.$except_trust_id.'"';
			
			$sql='select sum(quantity) from trust_position where position_id="'.$position_id.'" and bill_id="'.$bill_id.'" and trust_id in(select t.id from trust as t inner join trust_position as tp on t.id=tp.trust_id where tp.bill_id="'.$bill_id.'" and tp.position_id="'.$position_id.'" and t.is_confirmed=1) '.$flt;
			
			//echo $sql.'<br>';
			$set=new mysqlSet($sql);
			
			
			//echo 'select sum(quantity) from trust_position where position_id="'.$position_id.'" and bill_id="'.$bill_id.'"'.$flt;
			$rs=$set->getResult();
			
			$f=mysqli_fetch_array($rs);
			
			$res-=(float)$f[0];
			
			
			
			if($res<0) $res=0;
		}
		
		
		return round($res,3);
	}
	
	
	
	//макс доступное количество позиции для формирования Исход. счета
	public function MaxForBill($komplekt_id, $position_id, $except_bill_id=NULL, $except_is_id=NULL){
		$res=0;
		
		//найти айди позиции в к.в. 
		$set=new mysqlSet('select * from komplekt_ved_pos where komplekt_ved_id="'.$komplekt_id.'" and position_id="'.$position_id.'" limit 1');
		
		$rc=$set->getResultNumRows();
		$rs=$set->getResult();
		if($rc>0){
			$f=mysqli_fetch_array($rs);
			$kvpid=$f['id'];
				
			$res=(float)$f['quantity_confirmed'];
			
			//echo $res;
			
			//найдем сумму по счетам к.в.
			$flt='';
			if($except_bill_id!==NULL) $flt.=' and b.id<>"'.$except_bill_id.'"';
			
			$sql='select sum(bp.quantity) from bill_position as bp
			inner join bill as b on b.id=bp.bill_id
			 where bp.position_id="'.$position_id.'" and bp.komplekt_ved_id="'.$komplekt_id.'" '.$flt.'  and b.is_confirmed_shipping=1 and b.is_incoming=0
			 group by bp.position_id
			 ';
			 
			//echo $sql.'<br>';
			$set=new mysqlSet($sql);
			 
			
			$rs=$set->getResult();
			
			$f=mysqli_fetch_array($rs);
			
			//echo $sql.(float)$f[0]; echo '<p>';
			
			$res-=(float)$f[0];
			
			
			$flt='';
			if($except_is_id!==NULL) $flt.=' and id<>"'.$except_is_id.'"';
			
			//отнять сумму по связанному не вывез. межскладу
			/*$sql='select sum(quantity) from interstore_to_komplekt where komplekt_ved_id="'.$komplekt_id.'" and position_id="'.$position_id.'" and interstore_id in(select id from interstore where status_id<>3 and is_or_writeoff=0 and is_confirmed=1 and is_confirmed_wf=0 '.$flt.')';
		
			//echo $sql;
			
			$set=new mysqlSet($sql);
			
			$rs=$set->getResult();
			$f=mysqli_fetch_array($rs);
			
			$res-=(float)$f[0];*/
			
			
			if($res<0) $res=0;
			
		}
		
		//echo $res; echo '<p>';
		return round($res,3);
	}
	
	
	
	//макс доступное количество позиции для формирования Вход. счета
	public function MaxForBillIn($komplekt_id, $position_id, $except_bill_id=NULL, $except_is_id=NULL){
		$res=0;
		
		//найти айди позиции в к.в. 
		$set=new mysqlSet('select * from komplekt_ved_pos where komplekt_ved_id="'.$komplekt_id.'" and position_id="'.$position_id.'" limit 1');
		
		$rc=$set->getResultNumRows();
		$rs=$set->getResult();
		if($rc>0){
			$f=mysqli_fetch_array($rs);
			$kvpid=$f['id'];
				
			$res=(float)$f['quantity_confirmed'];
			
			//echo $res;
			
			//найдем сумму по счетам к.в.
			$flt='';
			if($except_bill_id!==NULL) $flt.=' and b.id<>"'.$except_bill_id.'"';
			
			$sql='select sum(bp.quantity) from bill_position as bp
			inner join bill as b on b.id=bp.bill_id
			 where bp.position_id="'.$position_id.'" 
			 and bp.komplekt_ved_id="'.$komplekt_id.'" '.$flt.'  
			 and b.is_confirmed_shipping=1 
			 and b.is_incoming=1
			 group by bp.position_id
			 ';
			 
			//echo $sql.'<br>';
			$set=new mysqlSet($sql);
			 
			
			$rs=$set->getResult();
			
			$f=mysqli_fetch_array($rs);
			
			//echo $sql.(float)$f[0]; echo '<p>';
			
			$res-=(float)$f[0];
			
		 
			if($res<0) $res=0;
			
		}
		
		//echo $res; echo '<p>';
		return round($res,3);
	}
	
	
	
	
	
	//макс доступное количество позиции для формирования входящего счета из исходящего
	public function MaxForIncomingBill($out_bill_id, $position_id, $komplekt_id, $except_bill_id=NULL){
		$res=0;
		
		//найти айди позиции в исх. счете
		$sql='select * from bill_position where bill_id="'.$out_bill_id.'" and komplekt_ved_id="'.$komplekt_id.'" and position_id="'.$position_id.'" limit 1';
		//echo $sql.'<br>';
		$set=new mysqlSet($sql);
		
		$rc=$set->getResultNumRows();
		$rs=$set->getResult();
		if($rc>0){
			$f=mysqli_fetch_array($rs);
			 
				
			$res=(float)$f['quantity'];
			
			//echo $res;
			
			//найдем сумму по другим вх. счетам
			$flt='';
			if($except_bill_id!==NULL) $flt.=' and b.id<>"'.$except_bill_id.'"';
			
			$sql='select sum(bp.quantity) from bill_position as bp
			inner join bill as b on b.id=bp.bill_id
			 where is_incoming=1 and 
			 bp.position_id="'.$position_id.'" and 
			 b.out_bill_id="'.$out_bill_id.'" and 
			 bp.komplekt_ved_id="'.$komplekt_id.'" '.$flt.'  
			 
			 and b.is_confirmed_price=1 
			 group by bp.position_id
			 ';
			 
		//	echo $sql.'<br>';
			$set=new mysqlSet($sql);
			 
			
			$rs=$set->getResult();
			
			$f=mysqli_fetch_array($rs);
			
			//echo $sql.(float)$f[0]; echo '<p>';
			
			$res-=(float)$f[0];
			
		 
		
			
			if($res<0) $res=0;
			
		}
		
		//echo $res; echo '<p>';
		return round($res,3);
	}
	
	
	
	
	
	//макс доступное количество позиции для распоряжения
	public function MaxForShI($bill_id, $position_id, $except_sh_i_id=NULL,$storage_id=NULL,$sector_id=NULL,$komplekt_ved_id=NULL){
		$res=0;
		
		$flt='';
		if($storage_id!==NULL) $flt.=' and storage_id="'.$storage_id.'"';
		if($sector_id!==NULL) $flt.=' and sector_id="'.$sector_id.'"';
		if($komplekt_ved_id!==NULL) $flt.=' and komplekt_ved_id="'.$komplekt_ved_id.'"';
		
		
		//найти число позиций по счету 
		$set=new mysqlSet('select sum(quantity) from bill_position where bill_id="'.$bill_id.'" and position_id="'.$position_id.'" '.$flt);
		
		
		$rc=$set->getResultNumRows();
		$rs=$set->getResult();
		if($rc>0){
			$f=mysqli_fetch_array($rs);
				
			$res=(float)$f[0];
			
			//найдем сумму по распоряжениям счета
			$flt=''; $flt2='';
			if($except_sh_i_id!==NULL) $flt.=' and id<>"'.$except_sh_i_id.'"';
			if($storage_id!==NULL) $flt.=' and storage_id="'.$storage_id.'"';
			if($sector_id!==NULL) $flt.=' and sector_id="'.$sector_id.'"';
			if($komplekt_ved_id!==NULL) $flt2.=' and komplekt_ved_id="'.$komplekt_ved_id.'"';
			
			$sql='select sum(quantity) from sh_i_position where position_id="'.$position_id.'" and sh_i_id in(select id from sh_i where bill_id="'.$bill_id.'" '.$flt.' and is_confirmed=1) '.$flt2;
			$set=new mysqlSet($sql);
			
						
			$rs=$set->getResult();
			
			$f=mysqli_fetch_array($rs);
			
			$res-=(float)$f[0];
			
			
			if($res<0) $res=0;
			
		}
		
		
		return round($res,3);
	}
	
	
	
	//макс доступное количество позиции для поступления
	public function MaxForAcc($bill_id, $position_id, $except_acc_id=NULL,$komplekt_ved_id=NULL){
		$res=0;
		$flt_k='';
		if($komplekt_ved_id!==NULL) $flt_k.=' and komplekt_ved_id="'.$komplekt_ved_id.'"';
		
		//найти число позиций по распоряжению 
		$sql='select sum(quantity) from bill_position where bill_id="'.$bill_id.'" and position_id="'.$position_id.'" '.$flt_k;
		$set=new mysqlSet($sql);
		
		
		$rc=$set->getResultNumRows();
		$rs=$set->getResult();
		if($rc>0){
			$f=mysqli_fetch_array($rs);
				
			$res=$f[0];
			
			//найдем сумму по поступлениям распоряжения
			$flt='';
			if($except_acc_id!==NULL) $flt.=' and id<>"'.$except_acc_id.'"';
			
			$sql='select sum(quantity) from acceptance_position where position_id="'.$position_id.'" '.$flt_k.' and acceptance_id in(select id from acceptance where bill_id="'.$bill_id.'" '.$flt.' and is_confirmed=1)';
			
			
			$set=new mysqlSet($sql);
			
						
			$rs=$set->getResult();
			
			$f=mysqli_fetch_array($rs);
			
			$res-=$f[0];
			
			
			if($res<0) $res=0;
			
		}
		
		
		return round($res,3);
	}
	
	//подсчет максимального кол-ва по позициям распоряжения на межсклад
	public function MaxForAccIs($is_id, $position_id, $except_p_id=NULL,$except_komplekt_ved_id=NULL){
		$res=0;
		
		$flt_k='';
		$flt_p='';
		
		if($except_komplekt_ved_id!==NULL) $flt_k.=' and komplekt_ved_id<>"'.$except_komplekt_ved_id.'"';
		if($except_p_id!==NULL) $flt_p.=' and id<>"'.$except_p_id.'"';
		
		
		//сколько всего к списанию
		$sql='select sum(quantity) from interstore_wf_position 
			where position_id="'.$position_id.'" and iwf_id in(select id from interstore_wf where interstore_id="'.$is_id.'")';	
		
		$set1=new mysqlSet($sql);
		$rs=$set1->GetResult();
		
		$f=mysqli_fetch_array($rs);
		$res=(float)$f[0];
		
		//echo $res;
		
		//вычтем сумму по тем же позициям этого же поступления, но по другим заявкам.
		$sql='select sum(quantity) from  acceptance_position where position_id="'.$position_id.'" '.$flt_k.' '.$flt_p.' and acceptance_id in(select id from acceptance where interstore_id="'.$is_id.'")';		
		
		//echo " $sql<br />";
		
		$set1=new mysqlSet($sql);
		$rs=$set1->GetResult();
		
		$f=mysqli_fetch_array($rs);
		$res-=(float)$f[0];
		
		//echo " $res <br />";
		
		$res=round($res,3);
		
		return round($res,3);
	}
	
	
	
	
	//число в исход. счетах
	public function InBills($komplekt_id, $position_id){
		$res=0;
		$sql='select sum(bp.quantity) from bill_position as bp
			inner join bill as b on b.id=bp.bill_id
		 where bp.position_id="'.$position_id.'"
		     and b.is_confirmed_shipping=1
			 and b.is_incoming=0
			 and bp.komplekt_ved_id="'.$komplekt_id.'"';
			 
		//echo $sql;
		$set=new mysqlSet($sql);
		
		$rs=$set->getResult();
		$f=mysqli_fetch_array($rs);
				
		$res=(float)$f[0];
		
		
		
		
		return round($res,3);	
	}
	
	//число во вход. счетах
	public function InBillsIn($komplekt_id, $position_id){
		$res=0;
		$sql='select sum(bp.quantity) from bill_position as bp
			inner join bill as b on b.id=bp.bill_id
		 where bp.position_id="'.$position_id.'"
		     and b.is_confirmed_shipping=1
			 and b.is_incoming=1
			 and bp.komplekt_ved_id="'.$komplekt_id.'"';
			 
		//echo $sql;
		$set=new mysqlSet($sql);
		
		$rs=$set->getResult();
		$f=mysqli_fetch_array($rs);
				
		$res=(float)$f[0];
		
		
		
		
		return round($res,3);	
	}
	
	//число в распоряжениях
	public function InSh($komplekt_id, $position_id){
		$res=0;
		
		//?? проверить потом!
		$set=new mysqlSet('select sum(sp.quantity) from sh_i_position as sp
		inner join sh_i as s on sp.sh_i_id=s.id
		inner join bill as b on b.id=s.bill_id
		where sp.position_id="'.$position_id.'" and s.is_confirmed=1 and sp.komplekt_ved_id="'.$komplekt_id.'" 
		and b.is_confirmed_shipping=1');
	
		
		
		$rs=$set->getResult();
		$f=mysqli_fetch_array($rs);
				
		$res=(float)$f[0];
		
		return round($res,3);	
	}
	
	//число в реализациях
	public function InAcc($komplekt_id, $position_id){
		$res=0;
		
		 

							 
		$sql='select sum(sp.quantity) from acceptance_position as sp
		inner join acceptance as s on sp.acceptance_id=s.id
		inner join bill as b on b.id=s.bill_id
		where sp.position_id="'.$position_id.'" and s.is_confirmed=1 and sp.komplekt_ved_id="'.$komplekt_id.'" 
		and b.is_confirmed_shipping=1
		and b.is_incoming=0
		and s.is_incoming=0
		'	;			 

		//echo $sql;
								 
		$set=new mysqlSet($sql);				 
		
		
		$rs=$set->getResult();
		$f=mysqli_fetch_array($rs);
				
		$res=(float)$f[0];
		
		return round($res,3);	
	}
	
	
	//число в поступлениях
	public function InAccIn($komplekt_id, $position_id){
		$res=0;
		
		 

							 
		$sql='select sum(sp.quantity) from acceptance_position as sp
		inner join acceptance as s on sp.acceptance_id=s.id
		inner join bill as b on b.id=s.bill_id
		where sp.position_id="'.$position_id.'" and s.is_confirmed=1 and sp.komplekt_ved_id="'.$komplekt_id.'" 
		and b.is_confirmed_shipping=1
		and b.is_incoming=1
		and s.is_incoming=1
		'	;			 

		//echo $sql;
								 
		$set=new mysqlSet($sql);				 
		
		
		$rs=$set->getResult();
		$f=mysqli_fetch_array($rs);
				
		$res=(float)$f[0];
		
		return round($res,3);	
	}
	
	
	//число свободно
	public function InFree($komplekt_id, $position_id, $except_bill_id=NULL){
		$res=0;
		
		
		$res=$this->MaxForBill($komplekt_id, $position_id,$except_bill_id);
		
		
		return round($res,3);	
	}
	
	//число свободно
	public function InFreeIn($komplekt_id, $position_id, $except_bill_id=NULL){
		$res=0;
		
		
		$res=$this->MaxForBillIn($komplekt_id, $position_id,$except_bill_id);
		
		
		return round($res,3);	
	}
	
}
?>
<?
//фильтр по контрагенту
	
	if(isset($_GET['supplier'.$prefix])&&(strlen($_GET['supplier'.$prefix])>0)){
		$_suppliers=explode(';', $_GET['supplier'.$prefix]);
		
		
		$decorator->AddEntry(new UriEntry('supplier',  $_GET['supplier'.$prefix]));
		
		
		//поиск по субхолдингам
		if(isset($_GET['has_holdings'.$prefix])){
	 		$decorator->AddEntry(new UriEntry('has_holdings', 1));
			
			
			$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_L));
			
			//0. исходный вариант
			$decorator->AddEntry(new SqlEntry('p.id', NULL, SqlEntry::IN_VALUES, NULL,$_suppliers));
			
			$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
			
			//найти 4 варианта:
			//1. записи по тем контрагентам, у кого холдинг=заданному к-ту
			
			$decorator->AddEntry(new SqlEntry('p.id','select distinct ss.id from supplier as ss where ss.is_active=1 and ss.holding_id in( '.implode(', ',$_suppliers).')', SqlEntry::IN_SQL));
			
			$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
			
			//2. найти все субхолдинги заданного к-та (у кого он холдинг, св€зь через контрагентов)
			$decorator->AddEntry(new SqlEntry('p.id','select distinct ss.id from supplier as ss  where ss.is_active=1 and ss.id in(select distinct subholding_id from supplier where is_active=1 and holding_id in(  '.implode(', ',$_suppliers).'))', SqlEntry::IN_SQL));
			
			$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
			
			//3. найти все дочерние предпри€ти€ субхолдингов заданного предпри€ти€
			$decorator->AddEntry(new SqlEntry('p.id','select distinct ss.id from supplier as ss    /*запись контрагента, у кого холдинг и субхолдинг определены */
			inner join supplier as sub on sub.id=ss.subholding_id and sub.is_active=1  /*запись субхолдинга*/
			inner join supplier as doch on sub.id=doch.subholding_id and doch.is_active=1  /*дочерн€€ компани€ субхолдинга */
			where  ss.is_active=1  and ss.holding_id in(  '.implode(', ',$_suppliers).')  ', SqlEntry::IN_SQL));
			
			//4. найти всех контрагентов, у кого субхолдинг = заданному
			$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
			
			$decorator->AddEntry(new SqlEntry('p.id','select distinct ss.id from supplier as ss where ss.is_active=1 and ss.subholding_id in( '.implode(', ',$_suppliers).')', SqlEntry::IN_SQL));
			
			
			$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_R));
			 
		}else {
			$decorator->AddEntry(new UriEntry('has_holdings', 0));
			$decorator->AddEntry(new SqlEntry('p.id', NULL, SqlEntry::IN_VALUES, NULL,$_suppliers));
		}
	}else $_suppliers=NULL;

?>
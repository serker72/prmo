<?

	//блок поиска по содержимому (включая файлы)
	if(isset($_GET['contents'.$prefix])&&(strlen($_GET['contents'.$prefix])>0)){
			
			$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_L));
			$decorator->AddEntry(new SqlEntry('p.topic',SecStr($_GET['contents'.$prefix]), SqlEntry::LIKE));
			$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
			$decorator->AddEntry(new SqlEntry('p.description',SecStr($_GET['contents'.$prefix]), SqlEntry::LIKE));
			
			$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
			$decorator->AddEntry(new SqlEntry('p.report',SecStr($_GET['contents'.$prefix]), SqlEntry::LIKE));
			
			$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
			
			
			//поиск по цели, рез-там
		
			$decorator->AddEntry(new SqlEntry('p.id','select distinct ss.sched_id from sched_suppliers as ss    WHERE MATCH (ss.note) AGAINST ("'.SecStr(($_GET['contents'.$prefix])).'" IN BOOLEAN MODE) ', SqlEntry::IN_SQL));
			 
			$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
			
			$decorator->AddEntry(new SqlEntry('p.id','select distinct ss.sched_id from sched_suppliers as ss    WHERE MATCH (ss.result) AGAINST ("'.SecStr(($_GET['contents'.$prefix])).'" IN BOOLEAN MODE) ', SqlEntry::IN_SQL));
			 
			$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
			
			
			
			//поиск по ИМЕНАМ ФАЙЛОВ
			
			//$decorator->AddEntry(new SqlEntry('p.id','select distinct bill_id from sched_file    WHERE MATCH (orig_name) AGAINST ("'.SecStr(($_GET['contents'.$prefix])).'" IN BOOLEAN MODE) ', SqlEntry::IN_SQL));
			$decorator->AddEntry(new SqlEntry('p.id','select distinct bill_id from sched_file    WHERE  orig_name LIKE "%'.SecStr(($_GET['contents'.$prefix])).'%" ', SqlEntry::IN_SQL));
			
			
			//поиск по СОДЕРЖИМОМУ ФАЙЛОВ
			$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
			$decorator->AddEntry(new SqlEntry('p.id','select distinct bill_id from sched_file    WHERE MATCH (text_contents) AGAINST ("'.SecStr(($_GET['contents'.$prefix])).'" IN BOOLEAN MODE) ', SqlEntry::IN_SQL));
			
			
			
			//поиск по ленте задачи
			$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
			$decorator->AddEntry(new SqlEntry('p.id','select distinct h.sched_id from sched_history as h  WHERE MATCH (h.txt) AGAINST ("'.SecStr(($_GET['contents'.$prefix])).'" IN BOOLEAN MODE) ', SqlEntry::IN_SQL));
			
			
			
			
			//поиск по ИМЕНАМ ФАЙЛОВ задачи
			$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
			//$decorator->AddEntry(new SqlEntry('p.id','select distinct h.sched_id from sched_history as h inner join sched_history_file as f on f.history_id=h.id    WHERE MATCH (f.orig_name) AGAINST ("'.SecStr(($_GET['contents'.$prefix])).'" IN BOOLEAN MODE) ', SqlEntry::IN_SQL));
			$decorator->AddEntry(new SqlEntry('p.id','select distinct h.sched_id from sched_history as h inner join sched_history_file as f on f.history_id=h.id    WHERE f.orig_name LIKE "%'.SecStr(($_GET['contents'.$prefix])).'%" ', SqlEntry::IN_SQL));
			
			
			
			//поиск по СОДЕРЖАНИЮ файла задачи
			$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
			$decorator->AddEntry(new SqlEntry('p.id','select distinct h.sched_id from sched_history as h inner join sched_history_file as f on f.history_id=h.id    WHERE MATCH (f.text_contents) AGAINST ("'.SecStr(($_GET['contents'.$prefix])).'" IN BOOLEAN MODE) ', SqlEntry::IN_SQL));
			
			
			 
			$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_R));
			
			$decorator->AddEntry(new UriEntry('contents',$_GET['contents'.$prefix]));
		}

?>
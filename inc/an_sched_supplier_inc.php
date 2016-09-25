<?
//������ �� �����������
	
	if(isset($_GET['supplier'.$prefix])&&(strlen($_GET['supplier'.$prefix])>0)){
		$_users1=explode(';', $_GET['supplier'.$prefix]);
		
		$decorator->AddEntry(new UriEntry('supplier',  $_GET['supplier'.$prefix]));
		
		
		//����� �� ������������
		if(isset($_GET['has_holdings'.$prefix])){
	 		$decorator->AddEntry(new UriEntry('has_holdings', 1));
			
			
			$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_L));
			
			//0. �������� �������
			$decorator->AddEntry(new SqlEntry('p.id','select distinct sched_id from  sched_suppliers where supplier_id in ('.implode(', ',$_users1).')', SqlEntry::IN_SQL));
			
			$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
			
			//����� 4 ��������:
			//1. ������ �� ��� ������������, � ���� �������=��������� �-��
			
			$decorator->AddEntry(new SqlEntry('p.id','select distinct ps.sched_id from  sched_suppliers as ps inner join supplier as ss on ss.id=ps.supplier_id and ss.is_active=1 and ss.holding_id in( '.implode(', ',$_users1).')', SqlEntry::IN_SQL));
			
			$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
			
			//2. ����� ��� ����������� ��������� �-�� (� ���� �� �������, ����� ����� ������������)
			$decorator->AddEntry(new SqlEntry('p.id','select distinct ps.sched_id from  sched_suppliers as ps inner join supplier as ss on ss.id=ps.supplier_id where ss.is_active=1 and ss.id in(select distinct subholding_id from supplier where is_active=1 and holding_id in(  '.implode(', ',$_users1).'))', SqlEntry::IN_SQL));
			
			$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
			
			//3. ����� ��� �������� ����������� ������������ ��������� �����������
			$decorator->AddEntry(new SqlEntry('p.id','select distinct ps.sched_id from  sched_suppliers as ps 
			inner join supplier as ss on ss.id=ps.supplier_id and ss.is_active=1  /*������ �����������, � ���� ������� � ���������� ���������� */
			inner join supplier as sub on sub.id=ss.subholding_id and sub.is_active=1  /*������ �����������*/
			inner join supplier as doch on sub.id=doch.subholding_id and doch.is_active=1  /*�������� �������� ����������� */
			where  ss.holding_id in(  '.implode(', ',$_users1).')  ', SqlEntry::IN_SQL));
			
			//4. ����� ���� ������������, � ���� ���������� = ���������
			$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
			
			$decorator->AddEntry(new SqlEntry('p.id','select distinct ps.sched_id from  sched_suppliers as ps inner join supplier as ss on ss.id=ps.supplier_id and ss.is_active=1 and ss.subholding_id in( '.implode(', ',$_users1).')', SqlEntry::IN_SQL));
			
			
			$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_R));
			 
		}else {
			$decorator->AddEntry(new UriEntry('has_holdings', 0));
			$decorator->AddEntry(new SqlEntry('p.id','select distinct sched_id from  sched_suppliers where supplier_id in ('.implode(', ',$_users1).')', SqlEntry::IN_SQL));	
		}
	}

?>
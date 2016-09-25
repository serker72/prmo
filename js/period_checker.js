/* функция проверки периода */

function PeriodChecker(field_name, control_value){
	if(($("#"+field_name).prop("disabled")==true)||($("#"+field_name).val()=="-")||($("#"+field_name).val()=="")){
		return true;	
	}else{
		current_date=new Date($("#"+field_name).val().substring(6,10), $("#"+field_name).val().substring(3,5)-1, $("#"+field_name).val().substring(0,2), 0,0,0,0 );
		check_date=new Date(control_value.substring(6,10), control_value.substring(3,5)-1, control_value.substring(0,2), 0,0,0,0 );
		
		if(current_date<check_date){
			 $("#"+field_name).focus();
			 return false;
		}else return true;			
		
		
	}
	
	
}

/*функция проверки периода по закрытым периодам*/
function PeriodCheckerByPeriod(field_name, closed_date){
	interval_string="";
	if(($("#"+field_name).prop("disabled")==true)||($("#"+field_name).val()=="-")||($("#"+field_name).val()=="")){
		return true;	
	}else{
		current_date=new Date($("#"+field_name).val().substring(6,10), $("#"+field_name).val().substring(3,5)-1, $("#"+field_name).val().substring(0,2), 0,0,0,0 );
		
		//перебрать весь период
		var not_in_closed=true;
		
		$.each(closed_date, function(k,v){
			check_date1=new Date(v[0].substring(6,10), v[0].substring(3,5)-1, v[0].substring(0,2), 0,0,0,0 );
			check_date2=new Date(v[1].substring(6,10), v[1].substring(3,5)-1, v[1].substring(0,2), 0,0,0,0 );
			
			if((current_date>=check_date1)&&(current_date<=check_date2)){
				not_in_closed=not_in_closed&&false;
				
				interval_string=' с '+v[0]+' по '+v[1];
			}
		});
		if(!not_in_closed){
			$("#"+field_name).focus();
		};
		
		return not_in_closed;
	}
}
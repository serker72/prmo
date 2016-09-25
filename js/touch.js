function isTouchDevice(){
				try{
					document.createEvent("TouchEvent");
					return true;
				}catch(e){
					return false;
				}
			}
			function touchScroll(id){
				if(isTouchDevice()){ //if touch events exist...
					
					try{
					var el=document.getElementById(id);
					var scrollStartPos=0;

					document.getElementById(id).addEventListener("touchstart", function(event) {
						scrollStartPos=this.scrollTop+event.touches[0].pageY;
						//event.preventDefault();
					},false);

					document.getElementById(id).addEventListener("touchmove", function(event) {
						this.scrollTop=scrollStartPos-event.touches[0].pageY;
						event.preventDefault();
					},false);
					}catch(e){}
				}
			}
			
			function touchScrollXY(id){
				if(isTouchDevice()){ //if touch events exist...
					try{
					var el=document.getElementById(id);
					var scrollStartPos=0;
					var scrollStartPosX=0;

					document.getElementById(id).addEventListener("touchstart", function(event) {
						scrollStartPos=this.scrollTop+event.touches[0].pageY;
						scrollStartPosX=this.scrollTop+event.touches[0].pageX;
						//event.preventDefault();
					},false);

					document.getElementById(id).addEventListener("touchmove", function(event) {
						this.scrollTop=scrollStartPos-event.touches[0].pageY;
						this.scrollLeft=scrollStartPosX-event.touches[0].pageX;
						event.preventDefault();
					},false);
					}catch(e){}
				}
			}
			
			
			function touchScrolljq(selector){
				
				if(isTouchDevice()){ //if touch events exist...
					
					
					
					var el=$(selector); //document.getElementById(id);
					var scrollStartPos=0;
					
					//alert($(selector));
					$(selector).addEventListener("touchstart", function(event) {
						scrollStartPos=this.scrollTop+event.touches[0].pageY;
						//event.preventDefault();
					},false);

					$(selector).addEventListener("touchmove", function(event) {
						this.scrollTop=scrollStartPos-event.touches[0].pageY;
						event.preventDefault();
					},false);
					
				}
			}
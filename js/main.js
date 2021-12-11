jQuery(document).ready(function(){

if(jQuery('body').find('.mob_successfull_payment').length) {

    // code
			// Continously check payment status
			var interval = setInterval(function () {    
				var url = jQuery('.request_url').val();
				var apikey = jQuery('.apikey').val();


				var xhr = new XMLHttpRequest();
				xhr.open("GET", url);
				xhr.setRequestHeader("Authorization", apikey);
				xhr.onreadystatechange = function () {
				
   					if (xhr.readyState === 4) {
   					
						var data=xhr.responseText;
						var jsonResponse = JSON.parse(data);
					 //console.log(jsonResponse["status"]);
					 //console.log(jsonResponse["total_received"]);

        				    
                        var order_id = jQuery(".order_id").val();
                                var data = {
                                    action: 'mob_payment_check',
                                    mob_ajax_nonce: mob_ajax_params.mob_ajax_nonce,
                                    order_id: order_id,
                                    status: jsonResponse["status"],
                                }
                    		jQuery.ajax({
                    			type: 'post',
                    			url: mob_ajax_params.mob_ajax_url,
                    			data: data,
                    			beforeSend: function(data){
                    				
                    			},
                    			complete: function(response){
                    			 //  console.log(response); 
                    			    
     
                    			
                    			},
                    			success: function(data){
                    			    
                				var successurl = jQuery('.success_url').val();
                				var cancelurl = jQuery('.cancel_url').val(); 
                    			    
               			          if(data == "succeeded"){
                    			     console.log(cancelurl);
                    			     jQuery(".success_notice").show();  
                    			    window.location = successurl;
                    			     }else if(data == "canceled"){
                    			       jQuery(".error_notice").show();    
                    			        window.location = errorurl; 
                    			     }	
                    			},
                    			error: function(data){
                    				console.log(data);
                    			},
                
                    		});


   					}
				};
				xhr.send();    
			}, 2000);

			// Clear interval function
			setTimeout(function( ) { clearInterval(interval); }, 600000);

}





});


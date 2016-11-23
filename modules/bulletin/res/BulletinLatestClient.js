(function($) {

	SpringBulletinLatestClient = {
		    network: "",
		    tags: "",
		    limit: "",
		    activeAnchor: undefined,

		    request: function(network, tags, limit) {
		    	SpringBulletinLatestClient.network = network;

		    	$("#sdvs-bulletin-list-filter").text(tags);
		        $('#spring-bulletin-loader').show();

		    	$.post('/springnet/bulletin/latest/', {
		             action: "gateway_bulletin_request",
		             network: network,
		             tags: tags,
		             limit: limit
		    	}, function(response) {
		    		data = $.parseJSON(response);
		    	    if(data.service == "error"){ console.log("Service Error"); return; }
		    	    if(data.status != "ok"){ console.log("Service Error"); console.log(data.uri); return; }
		    	    SpringBulletinLatestClient.apply(data.content);
		    	});

		    },
		    
		    rerequest: function(tags) {
		        self.tags = tags;
		        tags = tags == "" ? "none" : tags;
		       
		        
		        $("#sdvs-bulletin-list-filter").text(tags);

		        SpringBulletinLatestClient.request(
		            SpringBulletinLatestClient.network,
		            self.tags,
		            self.limit
		        );
		        
		    },
		    
		    requestProfile: function(node) {

		       
		        $('#spring-bulletin-loader').show();

		    	$.post('/springnet/bulletin/latest/', {
		             action: "gateway_bulletin_request",
		             node: node,
		    	}, function(response) {
		    		data = $.parseJSON(response);
		    	    if(data.service == "error"){ console.log("Service Error"); return; }
		    	    if(data.status != "ok"){ console.log("Service Error"); console.log(data.uri); return; }
		    	    SpringBulletinLatestClient.applyProfile(data.content);
		    	});
		    },
		    
		    requestContent: function(node, uid) {

		    	
		        $('#spring-bulletin-loader').show();
		        
		       	$.post('/springnet/bulletin/latest/', {
		             action: "gateway_bulletin_request",
		             node: node,
		             uid: uid
		    	}, function(response) {
		    		var data = $.parseJSON(response);
		    		if(data.service == "error"){ console.log("Service Error"); return; }
		    		if(data.status != "ok"){ console.log("Service Error"); console.log(data.uri); return; }
		    		SpringBulletinLatestClient.applyContent(data.content);
		    	});

		    },
		    
		    safeEid: function(node) {
		   	 return "snetbl303-" + node.replace(/\./g, "-");
		    },
		    
		    apply: function(bulletins) {
		        
		       
		        var html = "";
		        for(index in bulletins) {
		            for(node in  bulletins[index]) {
		                var list = bulletins[index][node];
		                
		                var list_html = "";
		                var l = list.length-1;
		                for(i in list) {
		                    item = list[i];
		                    
		                    for(ti in item.tags) {
		                        tag = item.tags[ti];
		                        item.tags[ti] = "<a href='javascript:void(0)' onclick='SpringBulletinLatestClient.rerequest(`"+tag+"`)'>" + tag +"</a>"
		                    }
		                    
		                    list_html += ([
		                        "<tr><td><a class='title' href='javascript:void(0)' onclick='SpringBulletinLatestClient.requestContent(`"+node+"`,`"+item.uid+"`)'>" + item.title +"</a> &rsaquo;&rsaquo;</td></tr>",
		                        "<tr><td style='display: none;' id='content-"+item.uid+"'>Content</td></tr>"
		                    ].join('\n'));
		                    
		                    if(i == l) {
		                    	list_html += "<tr><td class='details'>tags: "+item.tags.join(', ')+"</td></tr>";
		                    } else {
		                    	list_html += "<tr><td class='details separator'>tags: "+item.tags.join(', ')+"</td></tr>";
		                    }
		                }
		                
		                var eid = node.replace(/\./g, "-");
		                
		               html += [
		                    "<tr><td class='node-uri'>",
		                    "<a id='"+this.safeEid(node)+"-profile' href='javascript:void(0);'>"+node+"</a> &rsaquo;&rsaquo;",
		                    "</td></tr>",

		                    "<tr><td><table class='inner'><tbody>"+list_html+"</tbody></table></td></tr>"
		                ].join('\n');
		                
		                
		            }
		        }
		        

			
				
				
		        $("#sdvs-bulletin-list-body").empty();
		        $("#sdvs-bulletin-list-body").html(html);
		        $('#spring-bulletin-loader').hide();
		        
		        for(index in bulletins) {
		            for(node in  bulletins[index]) {
		            	
		            	var v = function(node) {
		            		prefix = SpringBulletinLatestClient.safeEid(node)
		            		$('#'+prefix+'-profile').click(function(){            		
			            		SnetPopup.clearPopups();
			            		var idPopup = prefix+"-popup";
			            		SpringBulletinLatestClient.activeAnchor = new PopupAnchor(idPopup, this.id)
			            		SpringBulletinLatestClient.requestProfile(node);
		            		});
		            	};
		            	
		            	v(node);
		            }
		        }
		    },

		    applyProfile: function(profile) {
		    	
		       	
		    	var pid = SpringBulletinLatestClient.activeAnchor.id;
		    	var anchor = SpringBulletinLatestClient.activeAnchor.anchor;
				var pos = $('#'+anchor).position();
				var arrowLeft = 20;
				var left = pos.left;
				var top = pos.top + 20;
				
				
				
		    	
		    	
		    	var m = "";
		        for(index in profile) {
		            for(node in profile[index]) {
		                                
		                var item = profile[index][node];
		                m = [
		        		         '<h3 class="snetbx-popup">' + item.name + '</h3>',
		        		         '<a target="_BLANK" class="snetbx-popup" href="' + item.website + '">' + item.website + '</a>'
		        		         ].join('\n');
		            }
		        }
		        
		        SnetPopup.popupProfile(m, pid, left, 0, top, arrowLeft, 'snetbl-list-container');
		        $('#spring-bulletin-loader').hide();
		    },
		    
		    hideProfile: function(node) {
		       
		        var eid = node.replace(/\./g, "-");
		        var element = $("#"+eid+"-profile");
		        element.hide();
		    },
		    
		    applyContent: function(bulletins) {
		    	
		    	 for(index in bulletins) {
		    		 for(node in  bulletins[index]) {
		    			 var item = bulletins[index][node];
		    			 
		    			 var info = [
		    			         item.content,
		    			         "<br><a href='javascript:void(0);' onclick='SpringBulletinLatestClient.hideContent(`"+item.uid+"`)'>hide</div>"
		    			       ].join('\n');
		    			 $('#content-'+item.uid).html(info);
		    			 $('#content-'+item.uid).show();
		    		 }
		    	 }
		    	 
		         $('#spring-bulletin-loader').hide();
		    },
		    
		    hideContent: function(uid) {
		       
		        var element = $("#content-"+uid);
		        element.hide();
		    }
		}
	
		$(document).ready(function(){
			var network = $('#spring-bulletin-network').val();
			var tags = $('#spring-bulletin-tags').val();
			var limit = $('#spring-bulletin-limit').val();
			if(limit == ""){ limit=2; }
			SpringBulletinLatestClient.request(network, tags,limit);
		});

})(jQuery);

(function($) {
	SpringBulletinExplorerClient = {
	    network: "",
	    category: "",
	    activeAnchor: undefined,

	    request: function(network, category) {

	    	
	    	
	    	var oldcat = SpringBulletinExplorerClient.category;

	    	if(oldcat != "") {
	    		$('#snetb-explorer-'+oldcat).removeClass("tab-button-active");
	    	}
	    	SpringBulletinExplorerClient.category = category;
	    	$('#snetb-explorer-'+category).addClass("tab-button-active");
	    	SpringBulletinExplorerClient.applyMessage(" ");
	    	
	    	
	    	SpringBulletinExplorerClient.network = network;
	    	SpringBulletinExplorerClient.filter = category;
	        
	        $('#spring-explorer-loader').show();

	    	$.post('/springnet/bulletin/explorer/', {
	            
	             action: "gateway_bulletin_explore",
	             network: network,
	             category: category
	    	}, function(response) {
	    		var data = $.parseJSON(response);
	    		$('#spring-explorer-loader').hide();
	    		if(data.status != 'ok') {
	    			SpringBulletinExplorerClient.applyMessage("Error occured");
	    			return;
	    		}
	    		SpringBulletinExplorerClient.apply(data.content);
	    	});
	    },
	    
	    filterCat: function(category) {
	    	SpringBulletinExplorerClient.request(SpringBulletinExplorerClient.network, category);
	    	return false;
	    },
	    
	    requestUid: function(node, uid) {
	    	
	    	$('#spring-explorer-loader').show();
	    	$.post('/springnet/bulletin/explorer/', {
	            
	             action: "gateway_bulletin_explore",
	             network: node,
	             uid: uid
	    	}, function(response) {
	    		var data = $.parseJSON(response);
	    		$('#spring-explorer-loader').hide();
	    		if(data.status != 'ok') {
	    			SpringBulletinExplorerClient.applyMessage("Error occured");
	    			return;
	    		}
	    		SpringBulletinExplorerClient.applyDetails(data.content);
	    	});
	    	
	    	this.swapClickEvent(node,uid, true);
	    	return false;
	    },
	    
	    requestProfile: function(node) {
	    	
	    	$('#spring-explorer-loader').show();
	    	$.post('/springnet/bulletin/explorer/', {
	            
	             action: "gateway_bulletin_explore",
	             profile: node,
	    	}, function(response) {
	    		var data = $.parseJSON(response);
	    		$('#spring-explorer-loader').hide();
	    		if(data.status != 'ok') {
	    			console.log("Error occurred");
	    			return;
	    		}
	    		SpringBulletinExplorerClient.applyProfile(data.content);
	    	});
	    	return false;
	    },
	    
	    safeEid: function(node) {
	    	 return "snetbx303-" + node.replace(/\./g, "-");
	    },
	    
	    swapClickEvent: function (node,uid, displayed) {
	    	
	    	var prefix = this.safeEid(node);
	    	var idAnchor = prefix+"-open-"+uid;
	    	
	    	var e = $('#'+idAnchor);
	    	e.unbind();

	    	if(displayed == true) {
	    		e.click(function() {
	    			SpringBulletinExplorerClient.hideDetails(node,uid);
	    		});
	    		
	    	} else {
	    		e.click(function() {
	    			SpringBulletinExplorerClient.requestUid(node,uid);
	    		});
	    	}
	    	
	    },

	    apply: function(listing) {
	    	var html = [];
	    	for(var i = 0; i < listing.length; i++) {
	    		var prefix = this.safeEid(listing[i].node);
	    		
	    		var idDetail = prefix+"-detail-" + listing[i].uid;
	    		var idAnchor = prefix+"-open-"+listing[i].uid;
	    		
	    		var html = html.concat([
	    		 '<tr>',
	    		 '<td>',
	    		 '<a class="title-link" href="javascript:void(0);" id="'+idAnchor+'">',
	    		 	listing[i].title,
	    		 '</a>',
	    		 '</td>',
	    		 '</tr>',
	    		 '<tr class="detail" id="'+idDetail+'">',
	    		 '<td ></td>',
	    		 '</tr>'
	    		]);		
	    	}
	    	
	    	
	    	
	    	
	    	$('#snetb-explorer-listing').html(html.join('\n'));

	    	for(i = 0; i < listing.length; i++) {
	    		var uid = listing[i].uid;
	    		var node = listing[i].node;

	    		(function(uid, node, ref) {

	    			prefix = ref.safeEid(node);
	    			idAnchor = prefix+"-open-"+uid;
	    			var e = $('#'+idAnchor);
    				e.click(function() {
    					SpringBulletinExplorerClient.requestUid(node,uid);
    				});

	    		})(uid, node,this);
	    	}

	    },
	    
	    applyDetails: function(details) {

	    	var prefix = this.safeEid(details.node);
	    	var idDetail = prefix+"-detail-" + details.uid;
	    	var idNodeName = prefix+"-name-" + details.uid;
	    	
	    	
	    	var html = [
	    	    '<td>',
	    	    details.content,
	    	    '<div>',
	    	    '<a class="node-name" id="'+ idNodeName +'">',
	    	    	details.node,
	    	    '</a>',
	    	    '</div>',
	    	    '</td>'
	    	].join("\n");
	    	
	    	var e = $('#'+idDetail); 
	    	e.html(html);
	    	
	    	$('#'+idNodeName).click(function(){
	    		
	    		SnetPopup.clearPopups();
	    		var idPopup = prefix+"-popup";
	    		SpringBulletinExplorerClient.activeAnchor = new PopupAnchor(idPopup, this.id)
	    		SpringBulletinExplorerClient.requestProfile(details.node);
	    	});
	    	
	    	e.show();
	    },
	    
	    applyProfile: function(details) {
	    	
	    	
	    	var pid = SpringBulletinExplorerClient.activeAnchor.id;
	    	var anchor = SpringBulletinExplorerClient.activeAnchor.anchor;
    		var pos = $('#'+anchor).position();
    		var arrowLeft = 10;
    		var left = pos.left;
    		var top = pos.top + 25;
    		
    		var m = [
    		         '<h3 class="snetbx-popup">' + details.name + '</h3>',
    		         '<a target="_BLANK" class="snetbx-popup" href="' + details.website + '">' + details.website + '</a>'
    		         ].join('\n');
    		
    		
	    	SnetPopup.popupProfile(m, pid, left, -200, top, arrowLeft, 'snetb-explorer-container');
	    	
	    },
	    
	    applyMessage: function(error) {
	    	var html = [
    		 '<tr>',
    		 '<td>'+error+'</td>',
    		 '</tr>'
    		];			    	
	    	
	    	$('#snetb-explorer-listing').html(html.join('\n'));
	    },
	    
	    hideDetails: function (node,uid) {
	    	
	    	var prefix = this.safeEid(node);
	    	var idDetail = prefix+"-detail-" + uid;
	    	$('#'+idDetail).hide();
	    	this.swapClickEvent(node, uid, false);
	    }
 }
	
	$(document).ready(function(){
		var network = $('#spring-explorer-network').val();
		var category = $('#spring-explorer-category').val();

		SpringBulletinExplorerClient.request(network, category);
	});
})(jQuery);
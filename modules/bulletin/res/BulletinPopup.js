(function($) {
	

	PopupAnchor = function(id, anchor) {
		this.id = id;
		this.anchor = anchor;
	};
	
	SnetPopup = {
		activePopupId: undefined,
			
		popupProfile: function(message, id, left, boxOffset, top, arrowLeft, anchor) {
			
	    	var popup = [
	    	            '<div id="'+id+'-arrow" class="arrow-up" style="top: '+top+'px; left: '+(left+arrowLeft)+'px;"></div>',
	 	    		    '<div id="'+id+'" class="popup-profile-container" style="position: absolute; left:'+(left + boxOffset)+'px; top:'+(top+5)+'px;">',
	 	    		      	'<div class="snet-popup popup-profile-window">'+message+'</div>',
	 	    		    '</div>'
	 	    		].join('\n');
	    	this.activePopupId = id;
	    	$('#'+anchor).append(popup);
	    	$('body').click(SnetPopup.closePopup);
		},
	
		clearPopups: function() {
			
			if(this.activePopupId != undefined) {
				$('#'+this.activePopupId).remove();
			}
		},
		
		closePopup: function(e) {
			
			if(e.target.className == 'snet-popup') {
				return;
			};
			$('#'+SnetPopup.activePopupId).remove();
			$('#'+SnetPopup.activePopupId+'-arrow').remove();
			$('body').unbind('click', SnetPopup.closePopup);		
		}	
		
	}

})(jQuery);
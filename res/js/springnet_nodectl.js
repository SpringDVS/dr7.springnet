(function($) {
	
	function loading_show() {
		$('#springnet-loading-icon').show();
	}
	function loading_hide() {
		$('#springnet-loading-icon').hide();
	}
	
	
	$(document).ready(function(){
		
		if($('#sn-nodectl-disable').length) {
			$('#sn-nodectl-disable').click(function(){
				loading_show();
				$.get("/admin/config/services/springnet/nodectl/disable", function(res) {
					loading_hide();
					window.location.reload(true);
				});
			});
		} else if($('#sn-nodectl-enable').length) {
			$('#sn-nodectl-enable').click(function(){
				loading_show();
				$.get("/admin/config/services/springnet/nodectl/enable", function(res) {
					loading_hide();
					window.location.reload(true);
				});
			});
		} else if($('#sn-nodectl-reg').length) {
			
			$('#sn-nodectl-reg').click(function(){
				loading_show();
				$.get("/admin/config/services/springnet/nodectl/register", function(res) {
					loading_hide();
					window.location.reload(true);
				});
			});
		}

	});
})(jQuery);
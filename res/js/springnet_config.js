(function($) {
	function hide_error() {
		$('#springnet-error-console').hide();
	}
	function display_error(msg) {
		$('#springnet-error-console').text(msg);
		$('#springnet-error-console').show();
	}
	
	function loading_show() {
		$('#springnet-loading-icon').show();
	}
	function loading_hide() {
		$('#springnet-loading-icon').hide();
	}
	$(document).ready(function(){
		
		$('#edit-springnet-geonet-lookup').click(function(){
			hide_error();
			loading_show();
			geonet = $('#edit-springnet-geonet-name').val();
			$.getJSON("/admin/config/services/springnet/georesolve/"+geonet, function(res) {
				loading_hide();
				if(res.hostname == "invalid") {
					display_error("GeoNetwork cannot be resolved; does not exist.")
					$('input[name=springnet_geonet_hostname]').val('');
					$('#edit-springnet-geonet-hostname-vis').val('');
					
					$('input[name=springnet_geonet_address]').val('');
					$('#edit-springnet-geonet-address-vis').val('');

					$('input[name=springnet_geonet_resource]').val('');
					$('#edit-springnet-geonet-resource-vis').val('');
				} else {
					$('input[name=springnet_geonet_hostname]').val(res.hostname);				
					$('#edit-springnet-geonet-hostname-vis').val(res.hostname);
					
					$('input[name=springnet_geonet_address]').val(res.address);
					$('#edit-springnet-geonet-address-vis').val(res.address);
					
					$('input[name=springnet_geonet_resource]').val(res.resource);
					$('#edit-springnet-geonet-resource-vis').val(res.resource);
					
					var uri = $('#edit-springnet-node-springname').val() + '.'
							  + $('#edit-springnet-geonet-name').val() + '.uk';

					$('input[name=springnet_node_uri]').val(uri);
					$('#edit-springnet-node-uri-vis').text(uri);
				}
			});
			return false;
		});
	});
})(jQuery);
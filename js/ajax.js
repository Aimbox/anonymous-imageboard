jQuery(function($) {
	$('.delete-aib-post').click(function(e) {
		if (confirm(AibAjax.confirmation))
		{
			$.getJSON(
				AibAjax.ajaxUrl,
				{
					action: 'delete_aib_post',
					nonce: AibAjax.nonce,
					post_id: $(this).data('id')
				},
				function(response) {
					if (response.success) {
						var $post = $('#aib' + response.id);
						$post.fadeOut(800, function() { $post.remove(); });
					} else {
						alert(response.message);
					}
				}
			);
		}
		e.preventDefault();
	});
	$('#aib-comment').focusin(function(){
	  $(this).attr("rows", "4");
	});
})
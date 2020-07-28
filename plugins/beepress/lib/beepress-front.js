(function($) {
	var bpIframe = $('.bp-iframe');
	bpIframe.each(function() {
		$(this).height($(this).width() / 1.7);
	});
})(jQuery);

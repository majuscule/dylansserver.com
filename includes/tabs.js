$(document).ready(function() {
	$(".content").hide();
	$("ul.tabs li:first").addClass("active").show();
	$(".content:first").show();

	$("ul.tabs li").click(function() {
		$("ul.tabs li").removeClass("active");
			$(this).addClass("active");
			$(".content").hide();

			var activeTab = $(this).find("a").attr("href");
			$(activeTab).fadeIn();
			return false;
	});

});

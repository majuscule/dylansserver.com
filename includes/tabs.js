$(document).ready(function() {

	$("ul.portfolio li").click(function() {
		//$("ul.tabs li").removeClass("active");
			$(this).addClass("active");
			$(".exhibit").hide();

			var activeTab = $(this).find("a").attr("href");
			$(activeTab).fadeIn();
			return false;
	});

});

$(document).ready(function() {
	$(".tabs").hide();
	var i = 0;
	$("ul.portfolio li.project a.exhibit").click(function() {
		i++;
		$(".tabs").hide();
		$("ul.portfolio li").removeClass("active");
		$(this).addClass("active_project");

		var activeTab = $(this).attr("href");
		if (i==1)
			$(activeTab).show("slide", 400);
		else
			$(activeTab).show("puff", 600);
		$(activeTab).addClass("active_tab");
		return false;
	});
	$('#showdivs').click(function() {
		var divs = document.getElementsByTagName('div');
		for(i=0;i<divs.length;i++){
			divs[i].className += (" shownDiv");
		}
	});

});

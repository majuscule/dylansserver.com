$(document).ready(function() {
	$(".tabs").hide();
	var divs = document.getElementsByTagName('div');
	if(document.location.hash){
		$(document.location.hash + '_').show();
	}
	var i = 0;
	$("ul.portfolio li.project a.exhibit").click(function() {
		i++;
		$(".tabs").hide();
		$("ul.portfolio li").removeClass("active");
		$(this).addClass("active_project");

		var activeTab = $(this).attr("href") + '_';
		if (i==1)
			$(activeTab).show("slide", 600);
		else
			$(activeTab).show("puff", 600);
		$(activeTab).addClass("active_tab");
	});
	$('#showdivs').click(function() {
		for(i=0;i<divs.length;i++){
			divs[i].className += (" shownDiv");
		}
	});

});

$(document).ready(function() {
	$(".exhibit").hide();
	var divs = document.getElementsByTagName('div');
	if(document.location.hash){
		$(document.location.hash + '_').show();
	}
	var i = 0;
	$("ul#portfolio li a.tab").click(function() {
		i++;
		$(".exhibit").hide();
		var activeTab = $(this).attr("href") + '_';
		if (i==1)
			$(activeTab).show("slide", 600);
		else
			$(activeTab).show("puff", 600);
		$(activeTab).addClass("active_exhibit");
	});
	$('#showdivs').click(function() {
		for(i=0;i<divs.length;i++){
			divs[i].className += (" shownDiv");
		}
	});

});

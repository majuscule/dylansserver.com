$(document).ready(function() {
	if(document.location.hash){
		$(document.location.hash + '_').show();
	}
	$("ul#portfolio li a.tab").each(function(){
		if (document.location.href.indexOf($(this).attr("href")) == 0) {
			$("#" + this.attr("href")).show("slide", 600);
		}
		$(this).attr("href", "#" + $(this).attr("href"));
	});
	var divs = document.getElementsByTagName('div');
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

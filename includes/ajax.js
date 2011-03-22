$(document).ready(function() {
  $('#recaptcha_widget').show();
  $('.submit').click(function() {
    $('#not_human').hide();
    $('#blank_comment').hide();
    $('#comment_text').css('border', '1px solid grey')
    $('#recaptcha_response_field').css('border', '1px solid grey')
    if ($('#comment_text').val() != '') {
	 var challenge = Recaptcha.get_challenge();
	 var response = Recaptcha.get_response();
     var captcha_data = { "challenge" : challenge,
	 				     "response" : response};
     $.ajax({  
       type: "GET",  
       url: "/captcha",  
       data: captcha_data,  
       success: function(data) {  
	     if (data.split('\n')[0] == 'true') {
	       var name = $("#comment_name").val();
	       var text = $("#comment_text").val();
		   if (name == '') { name = "anon" }
           var comment_data = { "captcha" : "passed",
		                        "name" : name,
                                "text" : text};
		   $.ajax({
		     type: "POST",
			 // the url may need to be adjusted for
			 // trailing slashes
			 url: "verify",
			 data: comment_data,
			 success: function() {
			   var new_post = "<h3>" + name + "</h3>"
							     + text + "<br><br>";
			   $('#comments').prepend(new_post);
			   $('#comment').hide();
			 }
		   });
		 } else {
		   $('#not_human').show();
           $('#recaptcha_response_field').css('border', '2px solid red')
		 }
       },
	   error: function() {
	     console.log('error');
	   },  
	   complete: function() {
                   Recaptcha.reload();
	   }
     }); 
	} else {
	  $('#blank_comment').show();
      $('#comment_text').css('border', '2px solid red')
	}
	return false;
  });
});

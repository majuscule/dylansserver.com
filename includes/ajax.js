$(document).ready(function() {
  $('#recaptcha_widget').show();
  $('.submit').click(function() {
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
	       var email = $("#comment_email").val();
	       var text = $("#comment_text").val();
           var comment_data = { "captcha" : "passed",
		                        "name" : name,
                                "email" : email,
                                "text" : text};
		   $.ajax({
		     type: "POST",
			 // the url may need to be adjusted for
			 // trailing slashes
			 url: "verify",
			 data: comment_data,
			 success: function() {
			   var new_post = "<h3><a href='mailto:" + email
			                   + "'>" + name + "</a></h3>"
							   + text + "<br><br>";
			   $('#comments').prepend(new_post);
			   $('#comment').hide();
			   console.log('posted new comment');
			 }
		   });
		 } else {
           var error = "<span style='font-weight:bold;font-family:sans-serif;color:red;margin-top:15px;'>reCAPTCHA said you're not human</span>";
		   $('#comment').append(error);
		 }
       },
	   error: function() {
	     console.log('error');
	   },  
	   complete: function() {
	     Recaptcha.destroy();
	   }
     }); 
	} else {
      var error = "<span style='font-weight:bold;font-family:sans-serif;color:red;margin-top:15px;'>but you didn't write anything!<br></span>";
	  $('#submit').before(error);
	}
	return false;
  });
});

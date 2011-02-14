<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">

<html>
<head>
  <meta name="generator" content=
  "HTML Tidy for Linux (vers 25 March 2009), see www.w3.org">
  <meta http-equiv="Content-type" content="text/html;charset=UTF-8">

  <title>dylanstestserver</title>
  <link href="../includes/style.css" rel="stylesheet" type="text/css">
  <link rel="icon" href="../favicon.ico" type="image/png">
</head>

<body>
  <div id="structure">
    <div id="banner">
      <a href=
      "http://dylanstestserver.com/">
      <img src="../images/dylanstestserver.png" alt="dylanstestserver"
      border="0"></a>
    </div>

    <div id="content">
	  <div id="notes">
	    <div class="note">
	      <h2>Amazon EC2 PTR/reverse DNS record/2/15/10</h2>
		  <p><a href="https://aws-portal.amazon.com/gp/aws/html-forms-controller/contactus/ec2-email-limit-rdns-request">Here</a> is the form to request a custom PTR record for an Amazon EC2 Elastic IP. It's listed, of course, under <u>Request to Remove Email Sending Limitations</u>, and took me an unfortunate amount of time to locate.</p>
		  <p>This is good when setting up a mail server in Amazon's cloud, since email providers (like gmail) flag mail whose reverse DNS does not match the MX record. Without this request, the reverse DNS lookup will return the default PTR record that will look something like <a href="ec2-50-16-219-8.compute-1.amazonaws.com">ec2-50-16-219-8.compute-1.amazonaws.com</a></p>
	    </div>
	    <div class="note">
	      <h2>init/2/15/10</h2>
		  <p>Every other day I manage to get something working with help from a blog I find with google that just-so-happens to include a detail the manual spares. These notes are in the hope of helping in the same way. I don't plan on writing often, and there will be no order to the notes; in this way these notes are meant more for spiders than humans.</p>
	    </div>
          <h1 id="contact_me" style="margin-top:60px;"><a href=
          "mailto:dylan@psu.edu">dylan</a></h1><a href=
          "mailto:dylan@psu.edu">@psu.edu</a>
	  </div>
	</div>
  </div>
</body>
</html>

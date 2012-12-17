<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
    <title>Thank you!</title>
</head>
<body>
<div align="center">
<p>&nbsp;</p>
<h3>Thanks for your comments!</h3>
<?php
    if(!empty($_GET['OrigRef'])) {
	$orig_referer = urldecode($_GET['OrigRef']);
	echo <<<EndOfWeRemembered
	    <p>
	    We remembered where you were.<br>
	    Click the link below to be taken back.
	    </p>
	    <p><a href="$orig_referer">Take Me Back</a></p>
EndOfWeRemembered;
    }
?>
</div>
</body>
</html>

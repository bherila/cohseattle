<?php
    session_start();
    require 'scfconfig.php';	// Get our config
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<!--
   scform.php - Web page form for Simple Contact Form package
   Copyright (C) 2002-2012 by James S. Seymour (jseymour [at] LinxNet [dot] com)
   (See "License", below.)  Release 1.2.7.
  
   License:
      This program is free software; you can redistribute it and/or
      modify it under the terms of the GNU General Public License
      as published by the Free Software Foundation; either version 2
      of the License, or (at your option) any later version.
      
      This program is distributed in the hope that it will be useful,
      but WITHOUT ANY WARRANTY; without even the implied warranty of
      MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
      GNU General Public License for more details.
      
      You may have received a copy of the GNU General Public License
      along with this program; if not, write to the Free Software
      Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307,
      USA.
      
      An on-line copy of the GNU General Public License can be found
      http://www.fsf.org/copyleft/gpl.html.
  
   The SCForm Home Page is at:
  
      http://jimsun.LinxNet.com/SCForm.html
-->
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<meta http-equiv="Description" content="Contact Page">
<title>Contact Page</TITLE>
</head>
<body>
<div align="center"><h2>Contact Page</h2></div>
<?php
    // Web form spammers frequently either leave HTTP_REFERER empty, set it
    // equal to the form's own URL, or make something up out of thin air.
    // We don't do this "is it our own server" if it's blank, as the "is it
    // blank" check will get that one
    $selfChkStr = $_SERVER['PHP_SELF'];
    $serverChkStr = "http://" . $_SERVER['SERVER_NAME'];
    if(($chkFormRefNotBlank && !$_SERVER['HTTP_REFERER']) ||
       ($chkFormRefNotSelf && preg_match("#$selfChkStr$#i", $_SERVER['HTTP_REFERER'])) ||
       ($chkFormRefOwnServer && $_SERVER['HTTP_REFERER'] &&
        !preg_match("#^$serverChkStr#i", $_SERVER['HTTP_REFERER'])))
    {
	// Almost certainly web form spammers - let 'em wait for it ;)
	sleep(10);
	// Crude, very crude (gracefully "terminate" the page early)
	print("<div align=\"center\"><font color=\"red\">Disallowed HTTP Referer! (&quot;" .
	    $_SERVER['HTTP_REFERER'] . 
	    "&quot;)</font></div>");
	print("</body></html>");
	exit;
    }

    if($requireVerify)
	print("<div align=\"center\"><font color=\"red\">Cookies must be enabled to use this form.</font></div><p />");
?>
<div align="center"><table><tr><td>
<form action="scformproc.php" method="post">
    <table>
	<tr>
	    <td align="right">
		Send To:
	    </td>
	    <td align="left">
		    <?php
			// Get a pseudo-random alpha-numeric string (no zeros and O's)
			function pseudo_random_string($length) {
			    global $noSimilarChars;
			    $string = "";
			    while($length--) {
				for($indx = rand(49, 90);
				    ($indx > 57 && $indx < 65) || $indx == 79 || $indx == 48 ||
				        ($noSimilarChars && array_search($indx,array(0,49,50,53,56,66,76,83,90)));
				    $indx = rand(49, 90))
				    ;
				$string .= chr($indx);
			    }

			    return($string);
			}

			// Read a line from a config file, stripping comments
			// and blank lines
			function read_file_line($fp) {
			    while(($inString = fgets($fp, 2048)) != false) {
				$inString = rtrim(preg_replace('/\s*#.*/', '',
				    $inString));
				if(!empty($inString))
				    break;
			    }

			    return $inString;
			}

			if(!isset($_SESSION['majik_string']))
			    $_SESSION['majik_string'] = pseudo_random_string(5);

			// Read the contact list keys and descriptions into hash
			if(($fp = fopen($recipientFile, "r")) == false) {
			     die("Can't open data file '$recipientFile'.\n");
			}
			while($inString = read_file_line($fp)) {
			    list($key, $description, $value) =
				explode(':', $inString);
			    $options[$key] = $description;
			}
			fclose($fp);

			// If we've more than one choice: present a menu
			if(count($options) > 1) {
			    // If we were given a single arg, that'll be the
			    // selected menu item.
			    if(count($_GET) == 1)
				$selected = strtolower(key($_GET));
			    print("<select name=\"whoto\">\n");
			    foreach($options as $key => $description) {
				print("<option ");
				if(strtolower($key) == $selected)
				    print("selected ");
				print("value=\"" . trim($key) .  "\">" .
				       trim($description) . "\n");
			    }
			    print("</select>\n");
			} else {
			    // There'll be only one...
			    foreach($options as $key => $description) {
				print("<input type=\"hidden\" name=\"whoto\" value=\"" .
				       trim($key) . "\">" . trim($description) . "\n");
			    }
			}

			// Used by the form processor acknowledgment to create a
			// "take me back" link.
			if(!empty($_SERVER['HTTP_REFERER'])) {
			    print("<input type=\"hidden\" name=\"orig_referer\" value=\"" .
				   $_SERVER['HTTP_REFERER'] . "\">\n");
			}
		    ?>
	    </td>
	</tr>
	<tr>
	    <td align="right">
		Your name:
	    </td>
	    <td align="left">
		<input type="text" name="name" size=30>
	    </td>
	</tr>
	<tr>
	    <td align="right">
		Email address:
	    </td>
	    <td align="left">
		<input type="text" name="email" size=30>
	    </td>
	</tr>
	<tr>
	    <td align="right">
		Subject:
	    </td>
	    <td align="left">
		<input type="text" name="subject" size=30>
	    </td>
	</tr>
	<?php
	    // Are we requiring CAPTCHA-style "is a human" verification?
	    if($requireVerify) {
		print <<<End_Of_Data
		    <tr><td>&nbsp;</td></tr>
		    <tr>
			<td colspan="2">
			    Please enter the verification string on the right into the box on the left.
			</td>
		    </tr>
		    <tr>
			<td align="right">
			    Verification:
			</td>
			<td align="left">
			    <input type="text" name="verify" size=10>
			    <img src="scfgenimg.php" width="60" height="20" align="top" alt="Verification string image"/>
			</td>
		    </tr>
End_Of_Data;
	    }
	?>
    </table>
    <p>
    Please enter your comments below.  Click &quot;Submit&quot; when done.
    <br>
    <textarea name="comments" rows=10 cols=50></textarea>
    </p>
    <p>
    <input type="submit" name="s" value="Submit" />
    &nbsp;
    <input type="reset">
    </p>
</form>
</td></tr></table></div>
<p />
<div align="left">
<font size="-1">Powered by <i><a href="http://jimsun.LinxNet.com/SCForm.html">SCForm</a></i></font>
</div>
</body>
</html>

<?php
/*
 * scformproc.php - Process results of scform.php web form
 * Copyright (C) 2002-2012 by James S. Seymour (jseymour [at] LinxNet [dot] com)
 * (See "License", below.)  Release 1.2.7.
 *
 * Features
 *
 *  . Single simple, secure, straight-forward script
 *  . Flexible configuration
 *  . Contact list email addresses are completely hidden, thus
 *    foiling web-page-scraping, scum-sucking, dirtbag spammers
 *      . Easy to add new recipients to the list with just a
 *        text editor
 *      . Uses format similar to common "aliases" style recipient
 *        email address lists
 *      . Contact list shared between web form and form processing
 *  . Optional check for banned email addresses and remote hosts
 *      . Ban list easily edited with common text editor
 *      . Option for mis-direction that allows banned senders
 *        to *think* they're getting through (heh heh heh)
 *
 * TBD
 *    . Add DTD and content/char encoding to error message and
 *      response pages?
 *
 * License:
 *    This program is free software; you can redistribute it and/or
 *    modify it under the terms of the GNU General Public License
 *    as published by the Free Software Foundation; either version 2
 *    of the License, or (at your option) any later version.
 *    
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU General Public License for more details.
 *    
 *    You may have received a copy of the GNU General Public License
 *    along with this program; if not, write to the Free Software
 *    Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307,
 *    USA.
 *    
 *    An on-line copy of the GNU General Public License can be found
 *    http://www.fsf.org/copyleft/gpl.html.
 *
 * The SCForm Home Page is at:
 *
 *    http://jimsun.LinxNet.com/SCForm.html
 *
 */
session_start();
require 'scfconfig.php';	// Get our config

/*
 * Shouldn't have to touch anything beyond here
 */
$scriptName = "SCForm";
$version = "1.2.7";

$errors = array();

// Function, shared by the show_errors() and show_fatal() functions,
// to emit the list of problems.  (Note: Does so as an HTML unnumbered
// list.  Assumes caller has already done the HTML "opening" stuff.)
function show_error_list($errors) {
    $nerrors = count($errors);

    print("<ul>\n");
    for($i = 0; $i < $nerrors; $i++)
	print("<li>" . $errors[$i] . "</li>\n");
    print("</ul>\n");
}

// Show list of non-fatal errors
function show_errors($errors) {
    print("<html>\n<head><title>Error</title></head>\n
	   <body>\n<p>There were problems with your submission. Please go
	   back to the previous page and correct the following errors:
	   </p>\n");
    show_error_list($errors);
    print("</body>\n</html>");
}


// Show fatal error
function show_fatal($errors) {
    print("<html>\n<head><title>Error</title></head>\n
	   <body>\n<p>There were problems with your submission.</p>\n");
    show_error_list($errors);
    print("This may be through no fault of your own and is probably not
	   immediately correctable.  Please come back and try again later.
	   </p>\n</body>\n</html>\n");
}

// Read a line from a config file, stripping comments and blank lines
function read_file_line($fp) {
    while(($inString = fgets($fp, 2048)) != false) {
	$inString = rtrim(preg_replace('/\s*#.*/', '', $inString));
	if(!empty($inString))
	    break;
    }

    return $inString;
}

// Function to check the referer against the list of acceptable
// refererers.
//
// If the $referers array is empty, returns true/pass by default
//
function check_referer($referers, $logOnReferer) {

    global $errors, $scriptName, $formProcBlankRefOkay;

    $found = true;	// Default to "pass"

    // Check only if the array of allowed referers is non-empty or if the HTTP
    // REFERER is empty and we're allowing that
    if(!(empty($_SERVER['HTTP_REFERER']) && $formProcBlankRefOkay)) {
	if(count($referers)) {
	    $found = false;

	    if(!empty($_SERVER['HTTP_REFERER'])) {
		list($referer) =
		    array_slice(explode("/", $_SERVER['HTTP_REFERER']), 2, 1);
		for($x = 0; $x < count($referers); ++$x) {
		    if(eregi($referers[$x], $referer)) {
			$found = true;
			break;
		    }
		}
	    }

	    if(!$found) {
		$errors[] = "This form was used from an unauthorized server! (" .
			    $_SERVER['HTTP_REFERER'] . ")";
		if($logOnReferer) {
		    error_log("[$scriptName] Illegal Referer. (" .
			       $_SERVER['HTTP_REFERER'] . ") ", 0);
		}
	    }
	}
    }

    return $found;
}

// Function to check an IP address match
// Handles CIDR notation in the thing to check against
// Note: Address to check against is expected to be in regexp format
// (i.e.: "."s escaped with "\"s)
function check_ip($chkAgainst, $chkAddr) {

    $addrMatch = false;	// Assume no match

    // If the "check against" value contains a "/", it'll be an IP
    // address (range) in CIDR notation.
    if(ereg('/[0-9]', $chkAgainst)) {

	# Break down dot.ted.qu.ad/bits of address to check against
	list($addrBase, $hostBits) = explode('/', $chkAgainst);
	list($w, $x, $y, $z) = explode('.', $addrBase);

	# Convert to high and low address ints
	$chkAgainst = ($w << 24) + ($x << 16) + ($y << 8) + $z;
	$mask = $hostBits == 0? 0 : (~0 << (32 - $hostBits));
	$lowLimit = $chkAgainst & $mask;
	$highLimit = $chkAgainst | (~$mask & 0xffffffff);

	# Convert addr to check to int
	list($w, $x, $y, $z) = explode('.', $chkAddr);
	$chkAddr = ($w << 24) + ($x << 16) + ($y << 8) + $z;

	$addrMatch = (($chkAddr >= $lowLimit) && ($chkAddr <= $highLimit));

    } else {
	$addrMatch = ereg("^$chkAgainst", $chkAddr);
    }

    return $addrMatch;
}

// Function to check the given email address, the remote host (if
// available) and the remote IP against the ban list.
function check_banlist($logOnBan, $email) {

    global $errors, $scriptName, $banListFile;

    $notAllowed = false;	// Default to allowed

    // "whoto" to email address hash
    $banList = array();

    // Get the banList
    if($fp = @fopen($banListFile, "r")) {
	while($inString = read_file_line($fp))
	    $banList[] = $inString;
	fclose($fp);
    }

    if(count($banList)) {
	$emailFix = trim(strtolower($email));
	$remoteHostFix = trim(strtolower($_SERVER['REMOTE_HOST']));

	foreach($banList as $banned) {
	    $banFix = trim(strtolower(ereg_replace('\.', '\\.', $banned)));
	    if(strstr($banFix, "@")) {			// email address?
		if(ereg('^@', $banFix)) {		// Any user @host?
		    // Expand the match expression to catch hosts and
		    // sub-domains
		    $banFix = ereg_replace('^@', '[@\\.]', $banFix);
		    if(($notAllowed = ereg("$banFix$", $emailFix))) {
			$bannedOn = $emailFix;
			break;
		    }
		} elseif(ereg('@$', $banFix)) {	// User at any host?
		    if(($notAllowed = ereg("^$banFix", $emailFix))) {
			$bannedOn = $emailFix;
			break;
		    }
		} else {				// User@host
		     if(($notAllowed = (strtolower($banned) == $emailFix))) {
			$bannedOn = $emailFix;
			break;
		    }
		}
	    } elseif(preg_match('/^\d{1,3}(\\\.\d{1,3}){0,3}(\/\d{1,2})?$/',
	                        $banFix)) {
		// IP address
		if($notAllowed = check_ip($banFix, $_SERVER['REMOTE_ADDR'])) {
		    $bannedOn = $_SERVER['REMOTE_ADDR'];
		    break;
		}

		// If the client is working through a proxy...
		if(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		    if($notAllowed =
			    check_ip($banFix, $_SERVER['HTTP_X_FORWARDED_FOR']))
		    {
			$bannedOn = $_SERVER['HTTP_X_FORWARDED_FOR'];
			break;
		    }
		}

	    } else {				// Must be a host/domain name
		if(($notAllowed = ereg("$banFix$", $remoteHostFix))) {
		    $bannedOn = $remoteHostFix;
		    break;
		}
	    }
	}
    }

    if($notAllowed) {
	$errors[] = "Attempt from a banned email address, host or domain! ($bannedOn)";
	if($logOnBan) {
	    error_log("[$scriptName] Banned on \"$bannedOn\"", 0);
	}
    }

    return $notAllowed;
}

// Generate informational headers
function generate_additional_headers() {

    global $scriptName, $version, $reportRemoteHost, $reportRemoteAddr,
	   $reportRemoteUser, $reportRemoteIdent, $reportOrigReferer;

    // Who we are and our version
    $addlHeaders = "X-Mailer: $scriptName v${version}\n";

    // Remote host/address/user reporting?
    if($reportRemoteHost && !empty($_SERVER['REMOTE_HOST']))
	$addlHeaders .= "X-Remote-Host: " . $_SERVER['REMOTE_HOST'] . "\n";
    if($reportRemoteAddr) {
	if(!empty($_SERVER['REMOTE_ADDR']))
	    $addlHeaders .= "X-Remote-Addr: " . $_SERVER['REMOTE_ADDR'] . "\n";

	// If the client is working through a proxy...
	if(!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
	    $addlHeaders .= "X-Http-X-Forwarded-For: " .
			     $_SERVER['HTTP_X_FORWARDED_FOR'] . "\n";
    }
    if($reportRemoteUser && !empty($_SERVER['REMOTE_USER']))
	$addlHeaders .= "X-Remote-User: " . $_SERVER['REMOTE_USER'] . "\n";
    if($reportRemoteIdent && !empty($_SERVER['REMOTE_IDENT']))
	$addlHeaders .= "X-Remote-Ident: " . $_SERVER['REMOTE_IDENT'] . "\n";
    if($reportOrigReferer && !empty($_POST['orig_referer']))
	$addlHeaders .= "X-SCForm-Referer: " . $_POST['orig_referer'] . "\n";

    return $addlHeaders;
}

// Mail advisory to $errorsTo list
function mail_advisory($errors) {

    global $errorsTo, $addSubjSig;

    if(!empty($errorsTo)) {
	if($addSubjSig)
	    $finalSubject = "[$dfltSubj] ";
	$finalSubject .= "Problem with form processing";

	$content = "The following problem(s) occurred with contact form processing:\n\n";
	$nerrors = count($errors);

	for($i = 0; $i < $nerrors; $i++)
	    $content .= "    . " . $errors[$i] . "\n";

	$addlHeaders = generate_additional_headers();

	// MS-Win mail servers want crlf and *don't* want a trailing pair
	// Note: This code in two places!  (The alternatives would've been
	// just as ugly, IMO.)
	if(PHP_OS == "WIN32" || PHP_OS == "WINNT") {
	    // It seems we only need do this with the "additional headers,"
	    // but we're set up here to easily add other mail() variables.
	    foreach (array('addlHeaders') as $foo) {
		$$foo = preg_replace("/\n$/", "", $$foo);
		$$foo = preg_replace("/\n/", "\r\n", $$foo);
	    }
	}

	mail($errorsTo, $finalSubject, $content, $addlHeaders);
    }
}

// Okay, here we go...

// Check the referer
if(!check_referer($formProcAllowedReferers, $logOnReferer)) {
    show_fatal($errors);
    if($adviseOnReferer == true)
	mail_advisory($errors);
    exit;
}

// "whoto" to email address hash
$whotos = array();

// Get the list of recipient keys and values
if(($fp = fopen($recipientFile, "r")) == false) {
     die("Can't open data file '$recipientFile'.\n");
}
while($inString = read_file_line($fp)) {
    list($key, $description, $value) = explode(':', $inString);
    $whotos[trim($key)] = trim($value);
}
fclose($fp);

// Convert "whoto" to recipient.
foreach($whotos as $key => $value) {
    if($_POST['whoto'] == $key) {
	$recipient = $value;
	break;
    }
}

// No valid recipient?  That's an error.
if(empty($recipient))
    $errors[] = "\"" . $_POST['whoto'] . "\" is an invalid destination.";

// Gave an email address?  It's an error if not--unless we allow no
// email address.
if(empty($_POST['email'])) {
    // For compatibility with pre-1.2.3 version config files, if
    // $requireEmail is not set, we default to "true".
    if(!isset($requireEmail) || $requireEmail) {
	$errors[] = "You didn't enter your email address.";
    }
// Valid-looking email address?
} elseif(!preg_match('/^[^\s@]+@[a-z0-9\.-]+?\.[a-z]{2,4}$/i', $_POST['email'])) {
    $errors[] = "\"" . $_POST['email'] .
		"\" doesn't look like a valid email address";
}

// Check for banned email addresses and remote hosts
if($banned = check_banlist($logOnBan, $_POST['email'])) {
    if($adviseOnBan)
	mail_advisory($errors);
    if($warnBanned) {
	show_fatal($errors);
	exit;
    } else {
	unset($errors);		// Make it look as if all's well
    }
}

// Check for name?
if($requireName && empty($_POST['name']))
     $errors[] = "You didn't enter your name.";

// Check for subject?
if($requireSubj && empty($_POST['subject']))
     $errors[] = "You didn't enter a subject.";

// Comments?  What's the point, if not?
if(empty($_POST['comments']))
     $errors[] = "You didn't enter any comments.";

// Check the verification string
if($requireVerify) {
    if($_SESSION['majik_string'] != strtoupper($_POST['verify'])) {
	$errors[] = "Verification string check failed.";
	if($logOnVerify) {
	    error_log("[$scriptName] Verification failed for host " . $_SERVER['REMOTE_ADDR'], 0);
	}
	if($adviseOnVerify)
	    mail_advisory($errors);
    }
}

// if there were errors, print out an error page and bail out
if(count($errors)) {
    show_errors($errors);
    exit;
}

//
// We're all done with the session stuff
//

// Unset all session variables
session_unset();

// Remove the session cookie
// This is more trouble than it should be, IMO!
$cookieInfo = session_get_cookie_params();
if((empty($cookieInfo['domain'])) && (empty($cookieInfo['secure']))) {
    setcookie(session_name(), '', time()-3600, $cookieInfo['path']);
} elseif(empty($cookieInfo['secure'])) {
    setcookie(session_name(), '', time()-3600, $cookieInfo['path'],
	$cookieInfo['domain']);
} else {
    setcookie(session_name(), '', time()-3600, $cookieInfo['path'],
	$cookieInfo['domain'], $cookieInfo['secure']);
}
unset($_COOKIE[session_name()]);

// Delete all registered data for the session
session_destroy();

if(!empty($_POST['name']))
    $content = "Somebody claiming to be " . $_POST['name'] . " wrote:\n\n";
$content .= preg_replace('/\r/', '', stripslashes($_POST['comments']));

$addlHeaders = empty($_POST['email'])? "" : "From: " . $_POST['email'] . "\n" .
                                            "Reply-To: " . $_POST['email'] . "\n";

$addlHeaders .= generate_additional_headers();

// Additional recipients?
if(!empty($mailAlso))
    $addlHeaders .= "Cc: ${mailAlso}\n";

// Subject line
if(empty($_POST['subject']))
    $finalSubject = $dfltSubj;
else {
    if($addSubjSig)
	$finalSubject = "[$dfltSubj] ";
    $finalSubject .= addcslashes(stripslashes($_POST['subject']), "\x00..\x1f");
}

// MS-Win mail servers want crlf and *don't* want a trailing pair
// Note: This code in two places!  (The alternatives would've been
// just as ugly, IMO.)
if(PHP_OS == "WIN32" || PHP_OS == "WINNT") {
    // It seems we only need do this with the "additional headers,"
    // but we're set up here to easily add other mail() variables.
    foreach (array('addlHeaders') as $foo) {
	$$foo = preg_replace("/\n$/", "", $$foo);
	$$foo = preg_replace("/\n/", "\r\n", $$foo);
    }
}

// Mail it
if(!$banned) {
    if(!mail($recipient, $finalSubject, $content, $addlHeaders)) {
	$errors[] = "Unable to process form at this time";
	show_fatal($errors);
	exit;
    }
}

// Redirect them to a response page
$responseURL = "Location: http://" . $_SERVER['HTTP_HOST'] .
	       dirname($_SERVER['PHP_SELF']);
if(!ereg("/$", $responseURL))
    $responseURL .= "/";
$responseURL .= "scfresp.php";

if(!empty($_POST['orig_referer']))
    $responseURL .= "?OrigRef=" . urlencode($_POST['orig_referer']);
header($responseURL);
?>

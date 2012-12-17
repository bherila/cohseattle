<?php

/*
 * Simple Contact Form configuration
 */

// "Who to," recipient "description" and destination email addresses
// It's best to place this outside DOCUMENT_ROOT if you can
$recipientFile = "contacts.cfg";

// Ban list file
// It's best to place this outside DOCUMENT_ROOT if you can
$banListFile = "banlist.cfg";

// Whom to email regarding errors and such-like?  A space-comma-delimited
// list of email addresses.
$errorsTo = "";

// A space-comma-delimited list of email addresses that'll be "Cc"d on
// anything to to any of the regular recipients.
$mailAlso = "";

// Which form fields are optional.  It is assumed that nobody would
// want comments to be optional.
$requireName  = true;	// Require user put something in for a name?
$requireSubj  = false;	// Require user put in a subject?
$requireEmail = true;	// Require user put in a reply (from) email address?

// This is set to false, by default, for maximum portability
$requireVerify = false;	// Require CAPTCHA-style human being verification?

// Eliminate "1L2Z5S8B" from the generated CAPTCHA numbers/letters, as
// letters and numbers easily-mistaken for one another.  ("0" and "O"
// left out regardless of this setting.)
$noSimilarChars = true;

// Info to optionally add to emailed form.  Change to true for each
// you want.  Will be stashed in X-Headers.
$reportRemoteHost  = true;
$reportRemoteUser  = true;
$reportRemoteAddr  = true;
$reportRemoteIdent = true;	// Note that identd/auth is unreliable
$reportOrigReferer = true;

// Add distinctive prefix to "Subject:" in "[]" field?
// The "distinctive prefix" will be the sole contents of the "Subject:"
// line, less the "[]", if $requireSubj is false and there's no
// Subject.
$addSubjSig = false;

// Default "Subject:" line or "distinctive prefix" if $addSubjSig
// is true and there's a subject provided.
//$dfltSubj = "contact form";
$dfltSubj = $_SERVER['SERVER_NAME'] . " contact";

// This is used by the form processor, not the form
//
// Some firewalls and HTTP proxy servers delete the HTTP REFERER.
// By setting the following to "true," victims behind such misguided
// systems will still be able to use your form.
$formProcBlankRefOkay = true;

// This is used by the form processor, not the form
//
// Allowed referers - domains/IPs you will allow this processor to
// be "called" from.  You probably want this to be, for example, the
// hostname of the host your form page is on.
//
// If this is empty, *any* host can post to this script.  Then again:
// The HTTP REFERER is easily faked, and some firewalls and HTTP
// proxies delete it, anyway.  (See $formProcBlankRefOkay, above.)
//
$formProcAllowedReferers = array(
    'example.com',	// Anything ending in "example.com"
    'info.wtccorp.com',
    '.example.com',	// Anything ending in ".example.com"
    '127.0.0.1',	// Exact IP address
    '192.168',		// Anything starting with "192.168"
	'christourhopeseattle.org',
	'.temp.anaxanet.com'
);

// Tell $errorsTo recipient about invalid referer hits?
$adviseOnReferer = true;

// Log invalid referer hits?
$logOnReferer = true;

// Tell 'em they're banned, or silently discard?
$warnBanned = true;

// Tell $errorsTo recipient about banned attempts?
$adviseOnBan = true;

// Log banned attempts?
$logOnBan = true;

// Tell $errorsTo recipient about failed verification (CAPTCHA)?
$adviseOnVerify = true;

// Log failed verifications?
$logOnVerify = true;

// These are used by the form, not the form processor
//
// Check HTTP_REFERER for sanity before even offering the form?
//
// Web form spammers often have HTTP_REFERERS that are blank, the
// URL of the form itself, or something entirely made up.
//
// Use $chkFormRefNotBlank with care, as some firewalls and HTTP
// proxies delete the REFERER
//
// Make sure the referer isn't the form itself?
$chkFormRefNotSelf = true;
// Insist that, if there's a referer, it's our own server?
$chkFormRefOwnServer = true;
// Make sure not blank?
$chkFormRefNotBlank = false;

?>

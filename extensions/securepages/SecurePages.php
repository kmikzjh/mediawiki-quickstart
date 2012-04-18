<?php
$wgExtensionCredits['other'][] = array(
	'name' => 'SecurePages',
	'description' => 'Allows you to configure your Wiki to use https for confidential pages.',
	'version' => '1.0.1-1.18.0',
	'author' => 'Mathias Ertl',
	'url' => 'http://fs.fsinf.at/wiki/SecurePages',
);

$wgHooks['BeforeInitialize'][] = 'fnBeforeInitialize';

function fnBeforeInitialize( &$title, $article, &$output, &$user, $request, $wiki ) {
	global $wgCookieSecure, $wgTitle, $wgSecurePages;

	# Secure cookies hurt us because they are set on the https page
	# but inaccessible from the http page, so we lose our previous session.
	$wgCookieSecure = false;

	# Don't process JavaScript and CSS files.
	# Otherwise, a secure page will be tagged as "partially secure" because these
	# files are being hit via http.
#	if (checkQS('gen', 'js')) {return true;}
#	if (checkQS('gen', 'css') || checkQS('ctype', 'text/css')) {return true;}

	# Get page title from query string.
	$pageTitle = array_key_exists('title', $_GET)
		 ? $_GET['title']
		 : "";

	# Are we on the sign-in page or not?
	# Logic works for everything except Special pages which apparently don't
	# even run LocalSettings.php.
	$onSecurePage = false;
	$https = false;
	$namespace = $title->getNamespace();

	$securePagesInNamespace = null;
	if (array_key_exists($namespace, $wgSecurePages)) {
		$securePagesInNamespace = $wgSecurePages[$title->getNamespace()];
	}

	if ( ! is_null( $securePagesInNamespace ) && in_array ( $title->getText(), $securePagesInNamespace ) )
		$onSecurePage = true;
	if ( array_key_exists ('HTTPS', $_SERVER) && $_SERVER['HTTPS'] == 'on')
		$https = true;

	if( ! $https && $onSecurePage) {
		header('Location: https://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']);
	} elseif( $https && ! $onSecurePage ) {
		header('Location: http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']);
	}

	return true;

}

function checkQS($key, $value) {
	return array_key_exists($key, $_GET) && $_GET[$key] == $value;
}

?>

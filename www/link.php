<?php
include '../inc/urlshorten.php';
$urlShorten = new urlShorten(1);
$slug = strtolower(substr($_SERVER['REQUEST_URI'], 1));
$domain = strtolower($_SERVER['HTTP_HOST']);
$url = $urlShorten->getUrl($slug, $domain);
$urlShorten->hitUrl($url['intLinkID']);
if ($url) {
	header('Location: ' . $url['txtUrl']);
}

include '../inc/footer.php';
?>

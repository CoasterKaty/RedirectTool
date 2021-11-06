<?php
include '../inc/urlshorten.php';
$urlShorten = new urlShorten();
$slug = strtolower(substr($_SERVER['REQUEST_URI'], 1));
$url = $urlShorten->getUrl($slug);
$urlShorten->hitUrl($slug);
if ($url) {
	header('Location: ' . $url['txtUrl']);
} else {
	header('Location: https://katystech.blog/');
}

include '../inc/footer.php';
?>

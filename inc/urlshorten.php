<?php
require_once dirname(__FILE__) . '/mysql.php';

class urlShorten {
	var $modDB;

	function __construct() {
		$this->modDB = new modDB();
	}

	function listUrls($page = 1, $pageSize = 20) {
		return $this->modDB->QueryArray('SELECT * FROM tblUrls LIMIT ' . $pageSize . ' OFFSET ' . (($page - 1) * $pageSize));
	}
	function getPageCount($pageSize = 20) {
		$count = $this->modDB->Count('SELECT * FROM tblUrls');
		return ceil($count / $pageSize);
	}
	function getUrl($slug) {
		if ($url = $this->modDB->QuerySingle('SELECT * FROM tblUrls WHERE txtSlug = \'' . $this->modDB->Escape($slug) . '\'')) {
			return $url;
		} else {
			return;
		}
	}
	function hitUrl($slug) {
		$this->modDB->QuerySingle('UPDATE tblUrls SET intHits = intHits + 1 WHERE txtSlug = \'' .  $this->modDB->Escape(strtolower($slug)) . '\'');
	}
	function createUrl($slug, $url, $creator) {
		if ($this->getUrl($slug)) {
			die('Error, already exists');
		}
		$this->modDB->Insert('tblUrls', array('txtUrl' => $url, 'txtSlug' => $slug, 'intHits' => 0, 'txtCreator' => $creator));
	}
	function deleteUrl($slug, $currentUser, $currentRoles) {
		$url = $this->getUrl($slug);
		if ( (in_array('Role.Admin', $currentRoles)) || (strtolower($url['txtCreator']) == strtolower($currentUser)) ) {
			$this->modDB->Delete('tblUrls', array('intLinkID' => $url['intLinkID']));
		}

	}
	function getPageNav($curPage = 1, $pageSize = 20) {
		$pageCount = $this->getPageCount($pageSize);
		$pageString = ($curPage == 1 ? '' :'<a href="create.php?page=' . ($curPage - 1) . '">') . 'Prev' . ($curPage == 1? '' : '</a>');
		for ($page = 1; $page <= $pageCount; $page++) {
			$pageString .= ' ' . ($page == $curPage ? '' : '<a href="create.php?page=' . $page . '">') . $page . ($page == $curPage ? '' : '</a>');
		}
		$pageString .= ($curPage == $pageCount ? ' ' :' <a href="create.php?page=' . ($curPage + 1) . '">') . 'Next' . ($curPage == $pageCount ? '' : '</a>');

		return $pageString;
	}
}
?>

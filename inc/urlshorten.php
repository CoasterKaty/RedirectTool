<?php
require_once dirname(__FILE__) . '/mysql.php';
require_once dirname(__FILE__) . '/base.php';

class urlShorten extends baseClass {
	var $modDB;
	var $settings;

	function __construct() {
		$this->modDB = new modDB();
		$this->settings = $this->getSettings();
	}

	function listUrls($page = 1, $pageSize = 20, $domainID = 0) {
		if ($domainID) {
			return $this->modDB->QueryArray('SELECT intLinkID, txtUrl, txtSlug, intHits, dtCreated, txtCreator, tblDomains.txtDomain FROM tblUrls INNER JOIN tblDomains USING (intDomainID) WHERE intDomainID=\'' . $this->modDB->Escape($domainID) . '\' LIMIT ' . $pageSize . ' OFFSET ' . (($page - 1) * $pageSize));
		} else {
			return $this->modDB->QueryArray('SELECT intLinkID, txtUrl, txtSlug, intHits, dtCreated, txtCreator, tblDomains.txtDomain FROM tblUrls INNER JOIN tblDomains USING (intDomainID) LIMIT ' . $pageSize . ' OFFSET ' . (($page - 1) * $pageSize));
		}
	}
	function getPageCount($pageSize = 20, $domainID = '') {
		$count = $this->modDB->Count('SELECT * FROM tblUrls' . ($domainID ? ' WHERE intDomainID=\'' . $this->modDB->Escape($domainID) . '\'' : ''));
		return ceil($count / $pageSize);
	}
	function getUrl($slug, $domain = '', $domainID = '') {
		if ($url = $this->modDB->QuerySingle('SELECT * FROM tblUrls WHERE txtSlug = \'' . $this->modDB->Escape($slug) . '\' AND intDomainID=(SELECT intDomainID FROM tblDomains WHERE ' . ($domain ? 'txtDomain=\'' . $this->modDB->Escape($domain) . '\'' : '') . ($domainID ? 'intDomainID=\'' . $this->modDB->Escape($domainID) . '\'' : '') . ')')) {
			return $url;
		} else {
			if ($domain) {
				$defaultURL = $this->modDB->QuerySingle('SELECT txtDefaultURL from tblDomains WHERE txtDomain = \'' . $this->modDB->Escape($domain) . '\'');
				return array('txtUrl' => $defaultURL['txtDefaultURL']);
			} else {
				return;
			}
		}
	}
	function hitUrl($linkID) {
		$this->modDB->QuerySingle('UPDATE tblUrls SET intHits = intHits + 1 WHERE intLinkID = \'' .  $this->modDB->Escape($linkID) . '\'');
	}
	function createUrl($args, $creator) {
		if ($this->getUrl($args['slug'], '', $args['domainID'])) {
			die('Error, already exists');
		}
		$this->modDB->Insert('tblUrls', array('txtUrl' => $args['url'], 'txtSlug' => $args['slug'], 'intDomainID' => $args['domainID'], 'intHits' => 0, 'txtCreator' => $creator));
	}
	function deleteUrl($linkID, $currentUser, $currentRoles) {
		if ($url = $this->modDB->QuerySingle('SELECT * FROM tblUrls WHERE intLinkID=\'' . $this->modDB->Escape($linkID) . '\'')) {
			if ( (in_array('Role.Admin', $currentRoles)) || (strtolower($url['txtCreator']) == strtolower($currentUser)) ) {
				$this->modDB->Delete('tblUrls', array('intLinkID' => $url['intLinkID']));
			}
		}

	}
	function deleteDomain($domainID, $currentUser, $currentRoles) {
		if ($url = $this->modDB->QuerySingle('SELECT * FROM tblDomains WHERE intDomainID=\'' . $this->modDB->Escape($domainID) . '\'')) {
			if ( (in_array('Role.Admin', $currentRoles)) || (strtolower($url['txtOwner']) == strtolower($currentUser)) ) {
				$this->modDB->Delete('tblDomains', array('intDomainID' => $url['intDomainID']));
			}
		}

	}
	function getDomains($page = 1, $pageSize = 20) {
		return $this->modDB->QueryArray('SELECT * FROM tblDomains ORDER BY txtDomain LIMIT ' . $pageSize . ' OFFSET ' . (($page - 1) * $pageSize));

	}
	function getDomainPageCount($pageSize = 20) {
		$count = $this->modDB->Count('SELECT * FROM tblDomains');
		return ceil($count / $pageSize);
	}

	function addDomain($args, $creator) {
		$this->modDB->Insert('tblDomains', array('txtDomain' => $args['domain'], 'txtOwner' => $creator, 'txtDefaultURL' => $args['defaultURL']));

	}

	function getSettings() {
		$settings = $this->modDB->Query('SELECT * FROM tblSettings');
		foreach ($settings as $setting) {
			$toRet[$setting['txtName']] = $setting['txtValue'];
		}
		return $toRet;
	}

	function saveSettings($args) {
		foreach ($args as $name => $value) {
			$this->modDB->Update('tblSettings', array('txtValue' => $value), array('txtName' => $name));
		}
	}

}
?>

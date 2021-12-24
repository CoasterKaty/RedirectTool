<?php
require_once dirname(__FILE__) . '/mysql.php';
require_once dirname(__FILE__) . '/base.php';
require_once dirname(__FILE__) . '/auth.php';

class urlShorten extends baseClass {
	var $modDB;
	var $modAuth;
	var $settings;

	function __construct($dontRequireLogin=0) {
		$this->modDB = new modDB();
		$this->modAuth = new modAuth($dontRequireLogin);
		$this->settings = $this->getSettings();
	}

	function listUrls($page = 1, $pageSize = 20, $domainID = 0) {
		if ($domainID) {
			if ($this->modAuth->checkUserRole('Role.Admin')) {
				$query = 'SELECT intLinkID, txtUrl, txtSlug, intHits, dtCreated, txtCreator, tblDomains.txtDomain FROM tblUrls INNER JOIN tblDomains USING (intDomainID) WHERE intDomainID=\'' . $this->modDB->Escape($domainID) . '\'';
			} else {
				$query = 'SELECT intLinkID, txtUrl, txtSlug, intHits, dtCreated, txtCreator, tblDomains.txtDomain FROM tblUrls INNER JOIN tblDomains USING (intDomainID) WHERE tblDomains.intRestricted=0 OR (tblDomains.intRestricted=1 AND tblDomains.txtOwner=\'' . $this->modDB->Escape($this->modAuth->userName) . '\') AND intDomainID=\'' . $this->modDB->Escape($domainID) . '\'';
			}
		} else {
			if ($this->modAuth->checkUserRole('Role.Admin')) {
				$query = 'SELECT intLinkID, txtUrl, txtSlug, intHits, dtCreated, txtCreator, tblDomains.txtDomain FROM tblUrls INNER JOIN tblDomains USING (intDomainID)';
			} else {
				$query = 'SELECT intLinkID, txtUrl, txtSlug, intHits, dtCreated, txtCreator, tblDomains.txtDomain FROM tblUrls INNER JOIN tblDomains USING (intDomainID) WHERE tblDomains.intRestricted=0 OR (tblDomains.intRestricted=1 AND tblDomains.txtOwner=\'' . $this->modDB->Escape($this->modAuth->userName) . '\')';
			}
		}
		return $this->modDB->QueryArray($query . ' LIMIT ' . $pageSize . ' OFFSET ' . (($page - 1) * $pageSize));

	}
	function getPageCount($pageSize = 20, $domainID = '') {
		if ($this->modAuth->checkUserRole('Role.Admin')) {
			$count = $this->modDB->Count('SELECT * FROM tblUrls' . ($domainID ? ' WHERE intDomainID=\'' . $this->modDB->Escape($domainID) . '\'' : ''));
		} else {
// TO DO this query
			$count = $this->modDB->Count('SELECT * FROM tblUrls' . ($domainID ? ' WHERE intDomainID=\'' . $this->modDB->Escape($domainID) . '\'' : ''));
		}
		return ceil($count / $pageSize);
	}
	function getUrl($slug, $domain = '', $domainID = '') {
		if ($url = $this->modDB->QuerySingle('SELECT * FROM tblUrls WHERE txtSlug = \'' . $this->modDB->Escape($slug) . '\' AND intDomainID=(SELECT intDomainID FROM tblDomains WHERE ' . ($domain ? 'txtDomain=\'' . $this->modDB->Escape($domain) . '\'' : '') . ($domainID ? 'intDomainID=\'' . $this->modDB->Escape($domainID) . '\'' : '') . ')')) {
			return $url;
		} else {
			if ($domain) {
				$defaultURL = $this->modDB->QuerySingle('SELECT txtDefaultURL from tblDomains WHERE txtDomain = \'' . $this->modDB->Escape($domain) . '\'');
				die('Return default URL');
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
	function deleteUrl($linkID) {
		if ($url = $this->modDB->QuerySingle('SELECT * FROM tblUrls WHERE intLinkID=\'' . $this->modDB->Escape($linkID) . '\'')) {
			if ( $this->modAuth->checkUserRole('Role.Admin') || (strtolower($url['txtOwner']) == $this->modAuth->userName) ) {
				$this->modDB->Delete('tblUrls', array('intLinkID' => $url['intLinkID']));
			}
		}

	}
	function deleteDomain($domainID) {
		if ($url = $this->modDB->QuerySingle('SELECT * FROM tblDomains WHERE intDomainID=\'' . $this->modDB->Escape($domainID) . '\'')) {
			if ( $this->modAuth->checkUserRole('Role.Admin') || (strtolower($url['txtOwner']) == $this->modAuth->userName) ) {
				$this->modDB->Delete('tblDomains', array('intDomainID' => $url['intDomainID']));
			}
		}

	}
	function getDomains($page = 1, $pageSize = 20) {
		if (in_array('Role.Admin', $this->modAuth->userRoles)) {
			$query = 'SELECT * FROM tblDomains';
		} else {
			$query = 'SELECT * FROM tblDomains WHERE intRestricted=0 or (intRestricted=1 AND txtOwner=\'' . $this->modDB->Escape($this->modAuth->userName) . '\')';
		}

		return $this->modDB->QueryArray($query . ' ORDER BY txtDomain LIMIT ' . $pageSize . ' OFFSET ' . (($page - 1) * $pageSize));

	}
	function getDomainPageCount($pageSize = 20) {
		if (in_array('Role.Admin', $this->modAuth->userRoles)) {
			$count = $this->modDB->Count('SELECT * FROM tblDomains');
		} else {
			$count = $this->modDB->Count('SELECT * FROM tblDomains WHERE intRestricted=0 or (intRestricted=1 AND txtOwner=\'' . $this->modDB->Escape($this->modAuth->userName) . '\')');
		}
		return ceil($count / $pageSize);
	}

	function addDomain($args) {
		$this->modDB->Insert('tblDomains', array('txtDomain' => $args['domain'], 'txtOwner' => $this->modAuth->userName, 'txtDefaultURL' => $args['defaultURL'], 'intRestricted' => $args['restricted']));

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

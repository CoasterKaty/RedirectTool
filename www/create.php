<?php

require_once '../inc/page.php';
require_once '../inc/graph.php';



/*

Alter DB to cope with multiple domains

*/
include '../inc/urlshorten.php';
$urlShorten = new urlShorten();


$modGraph = new modGraph();
$profile = $modGraph->getProfile();
$modAuth = new modAuth();
$page = ($_GET['page'] ? $_GET['page'] : 1);

if ($_GET['flyout'] == '1') $isFlyout = 1;
$thisPage = new sitePage('Redirect Tool', ($isFlyout ? '' : '<script type="text/javascript">function deleteItem(itemID) {
        if (confirm(\'Confirm deletion of \' + itemID + \'?\')) {
                location.href = \'create.php?action=delete&page=' . $page . '&id=\' + itemID;
        }
        }</script>'));
if (!$isFlyout) {
	$thisPage->logo = '/images/redirecttool_logo.png';
	$thisPage->initFlyout();
}
if ($modAuth->checkUserRole('Role.Admin') || $modAuth->checkUserRole('Role.User')) {

	if ($_GET['action'] == 'addDomain') {
		echo 'Form for domain here';
		exit;
	}
	if ($_GET['action'] == 'addLink') {
		if ($_GET['submitted']) {
			if ($_POST['url'] && $_POST['slug']) {
				$urlShorten->createUrl($_POST['slug'], $_POST['url'], $profile->userPrincipalName);
				header('Location: /create.php?page=' . $urlShorten->getPageCount(20));
				exit;
			}
		}
		$createForm = new pageForm('addLink', 'create.php?action=addLink&submitted=1');
		$domainField = $createForm->addField(new pageFormField('domain', 'dropdown'));
		$domainField->placeholder = 'Select domain';
		$domainField->label = 'Domain';
		$domainField->options = array('1' => 'https://k80.cat/', '2' => 'https://aka.ms/', '3' => 'https://bit.ly', '4' => 'https://goo.gl/');
		$slugField = $createForm->addField(new pageFormField('slug', 'text'));
		$slugField->label = 'Short Link';
		$slugField->placeholder = 'link';
		$linkField = $createForm->addField(new pageFormField('url', 'text'));
		$linkField->label = 'Redirect to';
		$linkField->placeholder = 'https://long/url/here.txt';
		$submitButton = $createForm->addField(new pageFormField('save', 'submit'));
		$submitButton->value = 'Create';
		$thisPage->addContent($createForm);
		echo $thisPage->printFlyoutPage();
//		echo '<div style="width: 300px;"><form method="POST" action="create.php?action=addLink&submitted=1" target="_parent">Create Redirect:<br /><select name="domain"><option value="1">https://k80.cat/</option></select><input type="text" name="slug" placeholder="shortlink" style="width: 100px;" /><br />Full URL:<input type-"text" name="url" placeholder="https://example.com/long/page/link.html" style="width: 200px;" /><br /><br /><input type="submit" value="Create" /></form></div>';
		exit;
	}
	if ($_GET['action'] == 'delete') {
		$urlShorten->deleteUrl($_GET['id'], $profile->userPrincipalName, $modAuth->userRoles);
		header('Location: /create.php?page=' . $page);
		exit;
	}





	$createButton = new navigationItem('Create Link', 'sub');
	$createButton->flyoutAction = 'create.php?action=addLink&flyout=1';
	$createButton->flyoutTitle = 'Create New Short Link';
	$thisPage->addNavigation($createButton);

	if ($modAuth->checkUserRole('Role.Admin')) {
		$adminButton = $thisPage->addNavigation(new navigationItem('Manage', 'sub'));
		$addDomainButton = $adminButton->addItem(new navigationItem('Add Domain', 'sub'));
		$addDomainButton->flyoutAction = 'create.php?action=addDomain';
		$addDomainButton->flyoutTitle = 'Add Domain';
		$adminButton->addItem(new navigationItem('Remove Domain', 'sub'));

	}


	$urls = $urlShorten->listUrls($page);
	$urlTable = new pageTable();
	$urlTable->addColumn(new pageTableColumn('Hits'));
	$urlTable->addColumn(new pageTableColumn('Link'));
	$urlTable->addColumn(new pageTableColumn('Redirect to', '700'));
	$urlTable->addColumn(new pageTableColumn('Created On'));
	$urlTable->addColumn(new pageTableColumn('Created By'));
	$deleteCol = $urlTable->addColumn(new pageTableColumn('Delete'));
	$deleteCol->text = '';

	foreach ($urls as $urlID => $url) {
		$tableRow = $urlTable->addRow();
		$tableRow->column['Hits']->text = $url['intHits'];
		$tableRow->column['Link']->text = $url['txtSlug'];
		$tableRow->column['Redirect to']->text = $url['txtUrl'];
		$tableRow->column['Redirect to']->tooltip = $url['txtUrl'];
		$tableRow->column['Created On']->text = $thisPage->prettyDate($url['dtCreated']);
		$tableRow->column['Created By']->text = $url['txtCreator'];
		if ($modAuth->checkUserRole('Role.Admin') || strtolower($url['txtCreator']) == strtolower($modAuth->userName)) {
			$tableRow->column['Delete']->text = '<img src="/images/delete.png" style="cursor: pointer;" onclick="JavaScript:deleteItem(\'' . $url['txtSlug'] . '\');" alt="Delete" title="Delete"/>';
		}
	}
	$thisPage->addContent($urlTable);

	$thisPage->addContent($urlShorten->getPageNav($page));
} else {
	$thisPage->addContent('Look up URL form goes here');
}

echo $thisPage->printPage();

?>

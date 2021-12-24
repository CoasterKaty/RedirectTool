<?php

require_once '../inc/page.php';
require_once '../inc/graph.php';

$pageAction = ($_GET['action'] ? $_GET['action'] : 'list');
$pageSubmitted = $_GET['submitted'];
$pageValidate = $_GET['validate'];
$pageNumber = ($_GET['page'] ? $_GET['page'] : 1);

include '../inc/urlshorten.php';
$urlShorten = new urlShorten();


$modGraph = new modGraph();
$profile = $modGraph->getProfile();
$modAuth = new modAuth();

if ($_GET['flyout'] == '1') $isFlyout = 1;
$thisPage = new sitePage('Redirect Tool');
if (!$isFlyout) {
	$thisPage->logo = '/images/redirecttool_logo.png';
	$thisPage->initFlyout();
}
if ($modAuth->checkUserRole('Role.Admin') || $modAuth->checkUserRole('Role.User')) {

	switch ($pageAction) {
		case 'addDomain':
			if ($modAuth->checkUserRole('Role.Admin')) {
				if ($pageSubmitted) {
					if($_POST['domain']) {
						$urlShorten->addDomain(array('domain' => $_POST['domain'], 'defaultURL' => $_POST['defaultURL'], 'restricted' => ($_POST['restricted'] ? '1' : '0')));
					}
					header('Location: ' . urldecode($_POST['httpReferer']));
					exit;
				}
				$createForm = new pageForm('addDomain', 'create.php?action=addDomain&submitted=1');
				$createForm->method = 'post';
				$domainField = $createForm->addField(new pageFormField('domain', 'text'));
				$domainField->label = 'Domain name';
				$domainField->help = 'Adds a domain to the system, you must already have registered the domain, configured DNS to point at this server and configured the web server (Apache, IIS etc) to respond to this domain';
				$domainField->placeholder = 'contoso.com';
				$domainField->required = 1;

				$domainURLField = $createForm->addField(new pageFormField('defaultURL', 'text'));
				$domainURLField->label = 'Default Redirect';
				$domainURLField->help = 'Default location to redirect the user if the requested short URL does not exist';
				$domainURLField->placeholder = 'https://www.contoso.com/';
				$domainURLField->required = 1;

				$restrictedField = $createForm->addField(new pageFormField('restricted', 'toggle'));
				$restrictedField->label = 'Restricted Domain';
				$restrictedField->help = 'Hides this domain and prevents standard users from creating short links on this domain';

				$submitButton = $createForm->addField(new pageFormField('save', 'submit'));
				$submitButton->value = 'Create';
				$thisPage->addContent($createForm);
				echo $thisPage->printFlyoutPage();
			}
			exit;
		case 'settings':
			if ($modAuth->checkUserRole('Role.Admin')) {
				if ($pageSubmitted) {
					$urlShorten->saveSettings(array(	'listItems'	 => $_POST['listItems']
								));
					header('Location: ' . urldecode($_POST['httpReferer']));
					exit;
				}
				$settingsForm = new pageForm('settings', 'create.php?action=settings&submitted=1');
	                        $listCountField = $settingsForm->addField(new pageFormField('listItems', 'number'));
	                        $listCountField->label = 'Number of rows to show in list';
	                        $listCountField->help = 'Results in lists are split into pages. About 25 is about right for 1080p resolution.';
	                        $listCountField->required = 1;
	                        $listCountField->min = 10;
	                        $listCountField->max = 100;
	                        $listCountField->value = $urlShorten->settings['listItems'];

        	                $saveButton = $settingsForm->addField(new pageFormField('save', 'submit'));
	                        $saveButton->value = 'Update';

	                        $thisPage->addContent($settingsForm);
	                        echo $thisPage->printFlyoutPage();
			}
                        exit;
		case 'addLink':
			if ($pageSubmitted) {
				if ($_POST['url'] && $_POST['slug']) {
					$urlShorten->createUrl(array('slug' => $_POST['slug'], 'url' => $_POST['url'], 'domainID' => $_POST['domain']));
					header('Location: /create.php?page=' . $urlShorten->getPageCount($urlShorten->settings['listItems']));
					exit;
				}
			}
			if ($pageValidate) {
				//check if URL exists

		                if ($urlShorten->getUrl($_POST['slug'], '', $_POST['domain'])) {
		                        echo 'This short link already exists';
					exit;
        		        }

				echo '1';
				exit;
			}
			$createForm = new pageForm('addLink', 'create.php?action=addLink&submitted=1');
			$createForm->validate = 'create.php?action=addLink&validate=1';
			$domainField = $createForm->addField(new pageFormField('domain', 'dropdown'));
			$domainField->placeholder = 'Select domain';
			$domainField->label = 'Domain';
			$domains = $urlShorten->getDomains();
			foreach ($domains as $domain) {
				$domainOption[$domain['intDomainID']] = $domain['txtDomain'];
			}
			$domainField->options = $domainOption;
			if ($_GET['domain']) $domainField->value = $_GET['domain'];
			if (count($domainOption) == 1) {
				$domainField->value = key($domainOption);
			}
			$domainField->required = 1;
			$slugField = $createForm->addField(new pageFormField('slug', 'text'));
			$slugField->label = 'Short Link';
			$slugField->placeholder = 'link';
			$slugField->required = 1;
			$linkField = $createForm->addField(new pageFormField('url', 'text'));
			$linkField->label = 'Redirect to';
			$linkField->placeholder = 'https://long/url/here.txt';
			$linkField->required = 1;
			$submitButton = $createForm->addField(new pageFormField('save', 'submit'));
			$submitButton->value = 'Create';
			$thisPage->addContent($createForm);
			echo $thisPage->printFlyoutPage();
			exit;
		case 'delete':
			$urlShorten->deleteUrl($_GET['id']);
			header('Location: /create.php?page=' . $page);
			exit;
		case 'deleteDomain':
			if ($modAuth->checkUserRole('Role.Admin')) {
				$urlShorten->deleteDomain($_GET['id']);
			}
			header('Location: /create.php?action=domains&page=' . $page);
			exit;
		case 'domains':
			if ($modAuth->checkUserRole('Role.Admin')) {
				$sideNav = new navigationItem('', 'side');
				$addDomain = $sideNav->addItem(new navigationItem('Add Domain', 'side'));
				$addDomain->flyoutAction = 'create.php?action=addDomain';
				$addDomain->flyoutTitle = 'Add Domain';
				$addDomain->icon = 'NewDomain.png';
				$thisPage->addNavigation($sideNav);

				$sideNav2 = new navigationItem('Configuration', 'side');
				$linksNav = $sideNav2->addItem(new navigationItem('Short URLs', 'side'));
				$linksNav->link = 'create.php';
				$linksNav->icon = 'ShortURL.png';
				$settingsNav = $sideNav2->addItem(new navigationItem('Settings', 'side'));
				$settingsNav->flyoutAction = 'create.php?action=settings';
				$settingsNav->flyoutTitle = 'Settings';
				$settingsNav->icon = 'Settings.png';
				$thisPage->addNavigation($sideNav2);

				$domains = $urlShorten->getDomains($pageNumber, $urlShorten->settings['listItems']);
				$domainTable = new pageTable();
				$domainTable->addColumn(new pageTableColumn('Domain'));
				$domainTable->addColumn(new pageTableColumn('Default URL', '700'));
				$domainTable->addColumn(new pageTableColumn('Created By'));
				$domainTable->pages = 1;
				$domainTable->page = $pageNumber;
				$domainTable->pageSize = $urlShorten->settings['listItems'];
				$domainTable->pageCount = $urlShorten->getDomainPageCount($urlShorten->settings['listItems']);
				$domainTable->pageURL = 'create.php';

				$editableMenu = new pageTableMenu();
				$deleteBtn = $editableMenu->addItem(new pageTableMenuItem('Delete', 'create.php?action=deleteDomain&id=$ID'));
				$deleteBtn->icon = 'Delete.png';
				$deleteBtn->confirm = 'Are you sure you want to delete $NAME?';

				foreach ($domains as $domainID => $domain) {
					$tableRow = $domainTable->addRow();
					$tableRow->column['Domain']->text = $domain['txtDomain'];
					$tableRow->column['Default URL']->text = $domain['txtDefaultURL'];
					$tableRow->column['Created By']->text = $domain['txtOwner'];
					$tableRow->linkID = $domain['intDomainID'];
					$tableRow->name = $domain['txtDomain'];
					if ($modAuth->checkUserRole('Role.Admin') || strtolower($domain['txtOwner']) == strtolower($modAuth->userName)) {
						$tableRow->menu = $editableMenu;
					}
				}
				$thisPage->addContent($domainTable);
			}
			break;
		default:
			$sideNav = new navigationItem('', 'side');
			$pageDomain = ($_GET['domain'] == 'ALL' ? '' : $_GET['domain']);
			$pageCount =  $urlShorten->getPageCount($urlShorten->settings['listItems'], $pageDomain);
			if ($pageCount == 0) {
				header('Location: create.php?action=domains');
				exit;
			}
			if ($pageCount < $pageNumber) $pageNumber = $pageCount;

			$domains = $urlShorten->getDomains(1, 100);
			$domainOptions['ALL'] = 'All Domains';
			foreach ($domains as $domainIndex => $domain) {
				$domainOptions[$domain['intDomainID']] = $domain['txtDomain'];
			}

			$domainDropdown = $sideNav->addItem(new navigationItem('Select Domain', 'dropdown'));
			$domainDropdown->options = $domainOptions;
			$domainDropdown->action = 'create.php?domain=$VALUE&page=' . $pageNumber;
			$domainDropdown->value = ($pageDomain ? $pageDomain : 'ALL');

			$createButton = $sideNav->addItem(new navigationItem('New Link', 'side'));
			$createButton->icon = 'new.png';
			$createButton->flyoutAction = 'create.php?action=addLink&flyout=1&domain=' . ($pageDomain != 'ALL' ? $pageDomain : '');
			$createButton->flyoutTitle = 'Create New Short Link';
			$thisPage->addNavigation($sideNav);



			if ($modAuth->checkUserRole('Role.Admin')) {
				$sideNav2 = new navigationItem('Configuration', 'side');

				$addDomain = $sideNav2->addItem(new navigationItem('Domains', 'side'));
				$addDomain->link = 'create.php?action=domains';
				$addDomain->icon = 'Domains.png';

				$settingsNav = $sideNav2->addItem(new navigationItem('Settings', 'side'));
				$settingsNav->flyoutAction = 'create.php?action=settings';
				$settingsNav->flyoutTitle = 'Settings';
				$settingsNav->icon = 'Settings.png';
				$thisPage->addNavigation($sideNav2);

			}


			if ($pageCount > 0) {
				$urls = $urlShorten->listUrls($pageNumber, $urlShorten->settings['listItems'], $pageDomain);
			}
			$urlTable = new pageTable();
			$urlTable->addColumn(new pageTableColumn('Link'));
			$urlTable->addColumn(new pageTableColumn('Redirect to', '700'));
			$urlTable->addColumn(new pageTableColumn('Hits'));
			$urlTable->addColumn(new pageTableColumn('Created On'));
			$urlTable->addColumn(new pageTableColumn('Created By'));
			$urlTable->pages = 1;
			$urlTable->page = $pageNumber;
			$urlTable->pageSize = $urlShorten->settings['listItems'];
			$urlTable->pageCount = $pageCount;
			$urlTable->pageURL = 'create.php';

			$editableMenu = new pageTableMenu();
			$deleteBtn = $editableMenu->addItem(new pageTableMenuItem('Delete', 'create.php?action=delete&id=$ID'));
			$deleteBtn->icon = 'Delete.png';
			$deleteBtn->confirm = 'Are you sure you want to delete $NAME?';

			if ($urls) {
				foreach ($urls as $urlID => $url) {
					$tableRow = $urlTable->addRow();
					$tableRow->column['Hits']->text = $url['intHits'];
					$tableRow->column['Link']->text = $url['txtDomain'] . '/' . $url['txtSlug'];
					$tableRow->column['Redirect to']->text = $url['txtUrl'];
					$tableRow->column['Redirect to']->tooltip = $url['txtUrl'];
					$tableRow->column['Created On']->text = $urlShorten->prettyDate($url['dtCreated']);
					$tableRow->column['Created By']->text = $url['txtCreator'];
					$tableRow->linkID = $url['intLinkID'];
					$tableRow->name = $url['txtDomain'] . '/' . $url['txtSlug'];
					if ($modAuth->checkUserRole('Role.Admin') || strtolower($url['txtCreator']) == strtolower($modAuth->userName)) {
						$tableRow->menu = $editableMenu;
					}
				}
			}
			$thisPage->addContent($urlTable);
			break;

	}
}

echo $thisPage->printPage();

?>

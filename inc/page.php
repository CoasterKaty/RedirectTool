<?php
/*
Site Template page builder class
Katy Nicholson
https://katystech.blog/

TO DO list:

- Forms: 	Dropdown: If you select the already selected item, the combo dropdown doesn't go away
		Dropdown and lists: If you set required="required" it requires every element to be selected at once
		Dropdown and lists: Scroll bar for large lists
		List: Allow unselect of item in single select list
		Flyout panel: Scrollbar
		Form validation - highlight missed/incorrect fields in red
		Form validation - don't enable button until validation passes (different style for disabled buttons)
- Page Reload	Prompt for data loss when flyout window is closed
		Prompt for data loss if browser page is left/reloaded




*/
require_once dirname(__FILE__) . '/auth.php';
require_once dirname(__FILE__) . '/graph.php';

class sitePage {
	var $page;
	var $title;
	var $script;
	var $mainNavigation = array();
	var $modAuth;
	var $modGraph;
	var $flyout;

	public $logo;

	function __construct($title = '', $script = '', $allowAnonymous = '0') {
		$this->title = $title;
		$this->script = $script;
		$this->modAuth = new modAuth($allowAnonymous);

		if (!$allowAnonymous || $this->modAuth->isLoggedIn) $this->modGraph = new modGraph();
	}

	function addNavigation($navItem) {
		$this->mainNavigation[] = $navItem;
		return $navItem;
	}

	function printLoginItem() {
		if ($this->modAuth->isLoggedIn) {
			$profile = $this->modGraph->getProfile();
			$photo = $this->modGraph->getPhoto();

			return '<div class="login" tabindex="-1" id="m_login"><div><span>' . $profile->displayName . '</span><span class="light">' . $profile->userPrincipalName . '</span></div>' . $photo . '<ul><li>Role: ' . ($this->modAuth->checkUserRole('Role.User') ? 'User' : '') . ($this->modAuth->checkUserRole('Role.Admin') ? 'Admin' : '') . ($this->modAuth->checkUserRole('Default Access') ? 'Read Only' : '') . '</li><li><a href="https://login.microsoftonline.com/common/wsfederation?wa=wsignout1.0">Sign Out</a></li></ul></div>';
		}
		return '<div class="login" tabindex="-1" id="m_login"><div><span class="loggedout">Not signed in</span></div><span class="userPhoto notLoggedIn"><img src="/images/notLoggedIn.png" /></span><ul><li><a href="' . $_SERVER['REQUEST_URI'] . (strstr($_SERVER['REQUEST_URI'], '?') ? '&' : '?') . 'login=1">Sign in</a></li></ul></div>';

	}

	function initFlyout() {
		$this->flyout = '<div id="flyout"><div id="flyoutTitle">Loading...</div><div id="flyoutClose" title="Close" onclick="JavaScript:closeFlyout();"> </div><iframe id="flyoutFrame" title="Flyout"></iframe></div>';
	}

	function printNavigation() {
		$h1Bar = '<div id="navMain" class="nav"><div ' . ($this->logo ? ' style="background-image: url(\'' . $this->logo . '\'); padding-left: 50px; margin-left: 3px;"' : '') . ' class="title"><span>' . $this->title . '</span></div>';
		$h1Bar .= $this->printLoginItem();
		$h2Bar = '<div id="navSub" class="nav">';
		foreach ($this->mainNavigation as $navItem) {
			if ($navItem->type == 'main') {
				$h1Bar .= '<div tabindex="-1" id="' . $navItem->id . '" style="float: ' . $navItem->position . '"' . ($navItem->flyoutAction ? ' onclick="JavaScript:openFlyout(\'' . $navItem->flyoutAction . '\', \'' . $navItem->flyoutTitle . '\');"' : '') . '><span>' . $navItem->name . '</span>';
				if ($navItem->subMenu) {
					$h1Bar .= $this->printNavigationItem($navItem);
				}
				$h1Bar .= '</div>';
			} else {
				$h2Bar .= '<div tabindex="-1" id="' . $navItem->id . '" style="float: ' . $navItem->position . '"' . ($navItem->flyoutAction ? ' onclick="JavaScript:openFlyout(\'' . $navItem->flyoutAction . '\', \'' . $navItem->flyoutTitle . '\');"' : '') . '><span>' . $navItem->name . '</span>';
				if ($navItem->subMenu) {
					$h2Bar .= $this->printNavigationItem($navItem);
				}
				$h2Bar .= '</div>';
			}
		}
		$h1Bar .= '</div>';
		$h2Bar .= '</div>';
		return $h1Bar . $h2Bar;
	}

	function printNavigationItem($navItem, $level = 1) {
		$output .= '<ul class="' . ($level == 1 ? 'odd' : 'even') . '">';
		foreach ($navItem->subMenu as $subItem) {
			$output .= '<li ' . ($subItem->subMenu ? ' tabindex="-2" class="hasSubMenu"' : '') . ' id="' . $subItem->id . '"' . ($subItem->flyoutAction ? ' onclick="JavaScript:openFlyout(\'' . $subItem->flyoutAction . '\', \'' . $subItem->flyoutTitle . '\');"' : '')  . '>' . $subItem->name;
			if ($subItem->subMenu) {
				$output .= $this->printNavigationItem($subItem, ($level == 1 ? 2 : 1));
			}
			$output .= '</li>';
		}
		$output .= '</ul>';
		return $output;
	}

	function printPage() {
		return $this->printHead() . "\n" .  $this->flyout . "\n" . $this->printNavigation() . "\n" . '<div id="mainBody">' . "\n" . $this->page . "\n" . '</div>' . "\n" . $this->printFoot();
	}

	function printFlyoutPage() {
		return $this->printHead(1) . $this->page . $this->printFoot();
	}

	function printHead($flyout = '0') {
		return '<!DOCTYPE html>
			<html lang="en">
			<head>
				<meta name="viewport" content="width=device-width, initial-scale=1">
				<title>' . $this->title . '</title>
				<link rel="stylesheet" type="text/css" href="style.css?' . mt_rand(5, 15). mt_rand(5, 15). mt_rand(5, 15). mt_rand(5, 15) . '" />
				<script type="text/javascript" src="sitetemplate.js?' . mt_rand(5, 15). mt_rand(5, 15). mt_rand(5, 15). mt_rand(5, 15) . '"></script>
				' . $this->script . '
			</head>
			<body id="' . ($flyout ? 'rdFlyout' : 'rdBody') . '" tabindex="-5">';
	}

	function printfoot() {
		return '</body></html>';
	}

	function addContent($content) {
		switch (gettype($content)) {
			case 'object':
				switch (get_class($content)) {
					case 'pageTable': case 'pageForm':
						$this->page .= $content->output();
						break;

					default:
						$this->page .= '!!!Unable to handle content, type ' . get_class($content);
						break;
				}
				break;
			default:
				$this->page .= $content;
				break;
		}
	}


	function prettyDate($date) {
		//$date should be in timestamp form, Y-m-d H:i:s
		$pastDate = strtotime($date);
		$curDate = time();
		$timeElapsed = $curDate - $pastDate;
		$hours = round($timeElapsed / 3600);
		$days = round($timeElapsed / 86400);
		$weeks = round($timeElapsed / 604800);
		$months = round($timeElapsed / 2600640);
		$years = round($timeElapsed / 31207680);
		if ($years > 0) return $years . ' year' . ($years > 1 ? 's' : '') . ' ago';
		if ($months > 0) return $months . ' month' . ($months > 1 ? 's' : '') . ' ago';
		if ($weeks > 0) return $weeks . ' week' . ($weeks > 1 ? 's' : '') . ' ago';
		if ($days > 0) return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
		if ($hours > 0) return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
		return 'Just now';
	}
}


class navigationItem {
	public $id;
	public $name;
	public $subMenu;
	public $position = 'left';	//left, centre, right
	public $type = 'main';		//main, sub
	public $flyoutAction = '';	//URL to open in flyout on click
	public $flyoutTitle = '';	//Title to show in flyout

	function __construct($name, $type='main', $position='left') {
		$this->name = $name;
		$this->position = $position;
		$this->type = $type;
		$this->id = 'm' . uniqid();
	}

	function addItem($subItem) {
		$this->subMenu[] = $subItem;
		return $subItem;
	}

}

class pageTable {
	private $columns;
	private $rows;

	function __construct() {
	}

	function addColumn($column) {
		$this->columns[] = $column;
		return $column;
	}

	function addRow() {
		$newRow = new pageTableRow($this->columns);
		$this->rows[] = $newRow;
		return $newRow;
	}

	function getColumn($colName) {
		foreach ($this->columns as $column) {
			if ($column->name == $colName) return $column;
		}
		return new pageTableColumn();
	}

	function output() {
		$output = '<div class="table"><div class="head"><div class="row">';
		foreach ($this->columns as $column) {
			$output .= '<div class="cell" ' . ($column->width ? ' style="max-width: ' . $column->width . 'px;"' : '') . '>' . $column->text . '</div>';
		}
		$output .= '</div></div><div class="body">';
		foreach ($this->rows as $row) {
			$output .= '<div class="row">';
			foreach ($row->column as $column => $data) {
				$thisColumn = $this->getColumn($column);
				$output .= '<div title="' . $data->tooltip . '" class="cell" '. ($thisColumn->width ? ' style="max-width: ' . $thisColumn->width . 'px;"' : '') . '>' . $data->text . '</div>';
			}
			$output .= '</div>';
		}
		$output .= '</div></div>';
		return $output;

	}
}
class pageTableRow {
	public $column;
	function __construct($columns) {
		foreach ($columns as $column) {
			$this->column[$column->name] = new pageTableCell();
		}
	}
	function output() {
	}
}

class pageTableCell {
	public $text;
	public $value;
	public $tooltip;
}

class pageTableColumn {
	public $name;
	public $text;
	public $width;
	function __construct($name, $width = '') {
		$this->name = $name;
		$this->text = $name;
		$this->width = $width;
	}
}

class pageForm {
	public $action;
	public $name;
	public $fields;
	function __construct($name, $action) {
		$this->name = $name;
		$this->action = $action;
	}

	function addField($field) {
		$this->fields[] = $field;
		return $field;
	}

	function output() {
		$output = '<form action="' . $this->action . '" name="' . $this->name . '" method="POST" target="_parent">';
		foreach ($this->fields as $field) {
			$output .= $field->output();
		}
		$output .= '</form>';
		return $output;
	}
}

class pageFormField {
	public $id;
	public $type;
	public $name;
	public $value;
	public $placeholder;
	public $label;
	public $options;
	public $multiselect;	//list only, 1 = yes
	public $required;

	function __construct($name, $type) {
		$this->name = $name;
		$this->type = $type;
		$this->id = 'f' . uniqid();
	}

	function output() {
		$output = '';
		// for dropdown and list, $this->required needs some JS maybe, as just putting "required" in the input means all of them have to be checked/selected.
		switch ($this->type) {
			case 'text':
				$output .= ($this->label ? '<label for="' . $this->id . '">' . $this->label . '</label>' : '') . '<input ' . ($this->required ? 'required ' : '') . 'id="' . $this->id . '" type="text" name="' . $this->name . '" value="' . $this->value . '" placeholder="' . $this->placeholder . '" onchange="JavaScript:setUnsaved();"/>';
				break;
			case 'button':
				$output .= '<input type="button" name="' . $this->name . '" value="' . $this->value . '" />';
				break;
			case 'submit':
				$output .= '<input type="submit" name="' . $this->name . '" value="' . $this->value . '" />';
				break;
			case 'list':
				// $this->options should be an array of value=>text, e.g. value="item1" text="This is a thing that clicks stuff"
				if (!$this->label) $this->label = 'List';
				$outputTable = new pageTable();
				$headerRow = $outputTable->addColumn(new pageTableColumn($this->label));
				$headerRow->text = $this->label;
				foreach ($this->options as $listValue => $listText) {
					$tableRow = $outputTable->addRow();
					$thisRowID = 'r' . uniqid();
					if ($this->multiselect) {
						$tableRow->column[$this->label]->text = '<input onchange="JavaScript:setUnsaved();" type="checkbox" class="hidden" name="' . $this->name . '" id="' . $thisRowID . '" value="' . $listValue . '" /><label class="list" for="' . $thisRowID . '">' . $listText . '</label>';
					} else {
						$tableRow->column[$this->label]->text = '<input onchange="JavaScript:setUnsaved();" type="radio" class="hidden" name="' . $this->name . '" id="' . $thisRowID . '" value="' . $listValue . '" /><label class="list" for="' . $thisRowID . '">' . $listText . '</label>';
					}
					$tableRow->column[$this->label]->value = $listValue;
				}
				$output .= $outputTable->output();
				break;
			case 'dropdown':
				if (!$this->label) $this->label = 'Dropdown';
				$output .= ($this->label ? '<label for="' . $this->id . '">' . $this->label . '</label>' : '') . '<div class="dropdown" tabindex="-2"><span id="' . $this->id . '" class="placeholder">' . $this->placeholder . '</span><ul>';
				foreach ($this->options as $listValue => $listText) {
					$thisRowID = 'r' . uniqid();
					$thisLabelID = 'l' . uniqid();
					$output .= '<li><input type="radio" class="hidden" name="' . $this->name . '" id="' . $thisRowID . '" value="' . $listValue . '" onchange="JavaScript:dropdownSelected(\'' . $this->id . '\', \'' . $thisLabelID . '\');"><label id="' . $thisLabelID . '" for="' . $thisRowID . '">' . $listText . '</label></li>';
				}
				$output .= '</ul></div>';
				break;
			default:
				$output .= 'Unknown Field type: ' . $this->type;
				break;
		}
		return $output;

	}

}

?>

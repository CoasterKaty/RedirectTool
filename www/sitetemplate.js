var unsaved = false;
function openFlyout(url, title) {
	document.getElementById('flyoutFrame').src = url;
	document.getElementById('flyoutTitle').innerText = title;
	document.getElementById('flyout').style.display = 'block';
	document.getElementById('flyoutFrame').focus();
}
function setUnsaved() {
	unsaved = true;
}
function closeFlyout() {
//	var ret = warnUnsavedFlyout();
//	console.log('closeFlyout ret=' + ret);
//	if (ret) {
		document.getElementById('flyoutFrame').src = 'about:blank';
		document.getElementById('flyout').style.display = 'none';
		unsaved = false;
//	}
}
function warnUnsavedFlyout() {
	if (unsaved) {
		var c = confirm('Flyout Closing - unsaved data alert here');
		return c;
	}
	return true;
}

window.addEventListener("beforeunload", function(e) {
	if (unsaved) {
		e.preventDefault();
		(e || window.event).returnValue = 'Unsaved Warning';
		return 'Unsaved Warning';
	}
});

/*
Maybe replace this with a nice div or similar with buttons instead of using the browser thing
cause you can't read whether they clicked leave or stay on the browser one... so the flyout
just closes anyway :(d


*/

function dropdownSelected(dropdownID, itemID) {
	document.getElementById(dropdownID).classList.remove('placeholder');
	document.getElementById(dropdownID).innerText = document.getElementById(itemID).innerText;
	document.getElementById('rdFlyout').focus();
}

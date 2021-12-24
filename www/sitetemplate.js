function getPos(el) {
	return el.offsetLeft + (el.offsetParent && getPos(el.offsetParent));
}
function positionTableMenu(menuID) {
	var elMenu = document.getElementById(menuID);

	elMenu.style.display = 'none';
	var posRight = getPos(elMenu.parentNode) + 200;
	console.log(posRight);
	if (posRight > window.innerWidth) {
		elMenu.style.left = 'unset';
		elMenu.style.right = '100%';
	} else {
		elMenu.style.right = 'unset';
		elMenu.style.left = '100%';
	}
	elMenu.style.display = 'block';

}
function openFlyout(url, title) {
	if (warnUnsavedFlyout()) {
		clearUnsavedFlyout();
	}
	if (document.getElementById('flyout').getAttribute('unsaved') != '1') {
		document.getElementById('flyoutFrame').innerHTML = '';
		sendRequestDisplayResult(url, 'flyoutFrame');
		document.getElementById('flyoutFrame').classList.add('loading');
		document.getElementById('flyout').setAttribute('unsaved', '0');
		document.getElementById('flyoutTitle').innerHTML = title;
		document.getElementById('flyout').style.display = 'block';
		document.getElementById('flyout').focus();
	}
}
function setUnsavedFlyout() {
	document.getElementById('flyout').setAttribute('unsaved', '1');
}
function clearUnsavedFlyout() {
	document.getElementById('flyout').setAttribute('unsaved', '0');
}
function closeFlyout() {
	if (warnUnsavedFlyout()) {
		document.getElementById('flyoutFrame').innerHTML = '';
		document.getElementById('flyout').style.display = 'none';
		document.getElementById('flyout').setAttribute('unsaved', '0');
	}
}
function warnUnsavedFlyout() {
	var unsaved = document.getElementById('flyout').getAttribute('unsaved');
	if (unsaved == '1') {
		var c = confirm('Any unsaved changes will be lost');
		return c;
	}
	return true;
}

function sendRequestDisplayResult(sURL, elID) {
	// send to sURL, put result in elID
        var xhttp2 = (window.XMLHttpRequest ? new window.XMLHttpRequest() : new ActiveXObject("MSXML2.XMLHTTP.3.0"));
        xhttp2.onreadystatechange = function() {
                if (xhttp2.readyState == 4 && xhttp2.status == 0) {
			document.getElementById('ajaxError').style.display = 'block';
		}
                if (xhttp2.readyState == 4 && xhttp2.status == 200) {
			var sResp = xhttp2.response;
			document.getElementById('flyoutFrame').classList.remove('loading');
			document.getElementById(elID).innerHTML = sResp;
                }
        }

        xhttp2.open("GET", sURL, true);
        xhttp2.send();

}

//   *********************************** split to new file
//   forms.js


function submitForm(formID) {

	var elForm = document.getElementById(formID);
	var formMethod = elForm.getAttribute('data-method');
	var formURL = elForm.getAttribute('action');

	var elInputs = elForm.getElementsByTagName('input');
	var elSubmit;
	for (i = 0; i < elInputs.length; i++) {
		if (elInputs[i].getAttribute('type') == 'submit') {
			elSubmit = elInputs[i];
			break;
		}
	}


	if (!validateForm(formID, elSubmit)) {
		return false;
	}

	if (elSubmit) elSubmit.disabled = true;

	if (formMethod == 'post') return true;

	if (formMethod == 'ajax') {

		// Post all the data back via XHR, then close the flyout.
		var formData = new FormData(elForm);
		var xhr = new XMLHttpRequest();
		xhr.open('POST', formURL, true);
		xhr.onreadystatechange = function() {
			if (xhr.readyState == 4 && xhr.status == 200) {
				closeFlyout();
			}
			if (xhr.readyState == 4 && xhr.status != 200) {
				 document.getElementById('ajaxError').style.display = 'block';
			}
		};
		xhr.send(formData);
		return false;
	}
}

function validateForm(formID, elSubmit) {
	var elForm = document.getElementById(formID);
	var formValidate = elForm.getAttribute('data-validate');
	var validateCheck = elForm.getAttribute('data-validate-process');
	if (!formValidate) {
		clearUnsavedFlyout();
		return true;
	}
	switch (validateCheck) {
		case '0':
			//Not started yet.
			if (elSubmit) elSubmit.disabled = true;
			var formData = new FormData(elForm);
			var xhr = new XMLHttpRequest();
			xhr.open('POST', formValidate, true);
			xhr.onreadystatechange = function() {
				if (xhr.readyState == 4 && xhr.status == 200) {
					clearUnsavedFlyout();
					document.getElementById(formID).setAttribute('data-validate-process', '2');
					document.getElementById(formID).setAttribute('data-validate-result', xhr.response.replace(/^\s*/,'').replace(/\s*$/,''));
					if (submitForm(formID)) document.getElementById(formID).submit();
				}
			};
			xhr.send(formData);

			return false;
		case '1':

			//Still running
			return false;
		case '2':
			// Validation completed
			if (elSubmit) elSubmit.disabled = false;

			var validateResult = document.getElementById(formID).getAttribute('data-validate-result');
			if (validateResult == '1') {
				return true;
			} else {
				document.getElementById('error' + formID).style.display = 'block';
				document.getElementById('error' + formID).innerHTML = '<span>' + validateResult + '</span>';
				document.getElementById(formID).setAttribute('data-validate-process', '0');
				return false;
			}
		default:
			//Completed with error
			return false;
	}
	return false;
}
// When an element is selected from the dropdown field
function dropdownSelected(dropdownID, itemID) {
	document.getElementById(dropdownID).classList.remove('placeholder');
	document.getElementById(dropdownID).innerText = document.getElementById(itemID).innerText;
	document.getElementById('flyout').focus();
}

//When the top section of the dropdown field is clicked
function dropdownClose(dropdownID) {
	//don't know how to do this.
	// :focus-within CSS runs before this does, so it always shows as "currently open".
	// what we want is for the <ul> to be hidden if it's already showing, but do nothing if it isn't already showing.
	console.log(dropdownID);
	console.log(window.getComputedStyle(document.getElementById('u' + dropdownID), null).display);
	if (document.getElementById('u' + dropdownID).style.display == 'block') {
		document.getElementById('flyout').focus();
	}
}

window.addEventListener('beforeunload', function(e) {
	if (document.getElementById('flyout').getAttribute('unsaved') == '1') {
		e.preventDefault();
		(e || window.event).returnValue = 'Unsaved Warning';
		return 'Unsaved Warning';
	}
});


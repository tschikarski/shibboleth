/* shibform.js - Toggle local login form */

document.getElementById('shib_toggle_localform').addEventListener('click', function() {
	if (document.getElementById('shib_localform').style.display == 'block') {
		document.getElementById('shib_localform').style.display = 'none';
		document.getElementById('shib_toggle_localform').className = '';
	} else {
		document.getElementById('shib_localform').style.display = 'block';
		document.getElementById('shib_toggle_localform').className = 'active';
	}
});




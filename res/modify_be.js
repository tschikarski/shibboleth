/* modify_be.js - Modify BE logout button (EXT:shibboleth) */

Ext.onReady(function() {
	if (document.getElementById('logout-button') && document.getElementById('logout-button').getElementsByTagName('form')[0]) {
		document.getElementById('logout-button').getElementsByTagName('form')[0].appendChild(document.getElementById('tx_shibboleth-HiddenInputParam-redirect'));
		document.getElementById('logout-button-shib').style.display = 'none';
	}
});

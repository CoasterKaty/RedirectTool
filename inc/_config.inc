<?php
/* config.inc Configuration 
 *
 * Katy Nicholson
 *
 * https://github.com/CoasterKaty
 * https://katytech.blog/
 * https://twitter.com/coaster_katy
 
 ***RENAME TO config.inc ****
 
 *
 */

error_reporting(E_ERROR);

// MySQL connection details
define('_MYSQL_HOST', 'localhost');
define('_MYSQL_USER', 'oauthdemo');
define('_MYSQL_DB', 'oauthdemo');
define('_MYSQL_PASS', 'DATABASE_PASSWORD');




define('_OAUTH_TENANTID', 'TENANT_ID');
define('_OAUTH_CLIENTID', 'CLIENT_ID');
define('_OAUTH_LOGOUT', 'https://login.microsoftonline.com/common/wsfederation?wa=wsignout1.0');
define('_OAUTH_SCOPE', 'openid%20offline_access%20profile%20user.read');

// Define either the client secret, or the client certificate details
// method = 'certificate' or 'secret'
define('_OAUTH_METHOD', 'certificate');

define('_OAUTH_SECRET', 'CLIENT_SECRET');
define('_OAUTH_AUTH_CERTFILE', '/path/to/certificate.crt');
define('_OAUTH_AUTH_KEYFILE', '/path/to/privatekey.pem');
// on Windows, the certificate paths should be in the form c:/path/to/cert.crt


// URL to this website, no trailing slash.
define('_URL', 'https://redirecttool.domain.tld');
?>

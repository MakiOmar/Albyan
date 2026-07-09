<?php
// Before removing this file, please verify the PHP ini setting `auto_prepend_file` does not point to this.

// This file was the current value of auto_prepend_file during the Wordfence WAF installation (Thu, 14 May 2026 11:10:55 +0000)
if (file_exists('/var/www/Albyan/public/articles/wordfence-waf.php')) {
	include_once '/var/www/Albyan/public/articles/wordfence-waf.php';
}
if (file_exists(__DIR__.'/wp-content/plugins/wordfence/waf/bootstrap.php')) {
	define("WFWAF_LOG_PATH", __DIR__.'/wp-content/wflogs/');
	include_once __DIR__.'/wp-content/plugins/wordfence/waf/bootstrap.php';
}
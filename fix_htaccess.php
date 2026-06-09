<?php
$content = "RewriteEngine On\n\n# Prevent double-rewriting: don't rewrite /hotel/ requests again\nRewriteCond %{REQUEST_URI} !^/hotel/\nRewriteRule ^(.*)$ /hotel/\$1 [L]\n";
file_put_contents('/home/u102361870/domains/theicon.id/public_html/.htaccess', $content);
echo "Done\n";
echo file_get_contents('/home/u102361870/domains/theicon.id/public_html/.htaccess');

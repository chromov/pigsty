<?php

require_once('./lib/router.php');
require_once('./lib/inflect.php');
require_once('./config/routes.php');
print_r(Router::load()->parse_URI('news/articles/45/dflg', 'get'));
?>

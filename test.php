<?php

require_once('./lib/router.php');
require_once('./lib/controller.php');
require_once('./lib/inflect.php');
require_once('./config/routes.php');
$params = Router::load()->parse_URI('news/articles/45/dflg', 'get');
$controller = new Controller($params);
?>

<?php

require_once('./lib/router.php');
require_once('./lib/controller.php');
require_once('./lib/inflect.php');
require_once('./config/routes.php');
$params = Router::load()->parse_URI('news/articles/45/dflg', 'get');
$controller = new Controller($params);
//print_r(Router::load()->get_result_routes());
print(Router::load()->path_to('show_article', array('id'=>5, 'title' => 'blablabla'), array('xxx'=>'uuu'))."\n");
?>

<?php

require_once('./lib/router.php');
require_once('./lib/inflect.php');
require_once('./config/routes.php');
//Router::load();
//print_r(Router::get_routes());
print_r(Router::load()->get_result_routes());
?>

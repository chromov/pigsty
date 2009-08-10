<?php

Router::add_routes(array(
  "admin" => array( //facet
    "modules" => array(
      "users" => array(
        "users" => array(
          "default" => true,
          "type" => "resource"
        ) //default resource
      )
    )
  ),

  "app" => array(
    "default" => true,
    "modules" => array(

      "forum" => array(
        "forums" => array( // this is default resorce
          "default" => true,
          "type" => "resource",
          "formatted" => "html", // can be an array of actions
          "nested" => array(
            "threads" => array(
              "type" => "resource",
              "except" => array("edit", "update")
            )
          )
        )     
      ),

      "news" => array(
        "show_article" => array(
          "route" => "articles/{id:int}/{title:str}",
          "controller" => "articles",
          "action" => "index",
          "method" => 'get',
          "formatted" => "html"
        )
      )

    )
  )

  /*
  "root" => array(
    "route" => "/",
    "controller" => "",
    "action" => ""
  )
  */

));

?>

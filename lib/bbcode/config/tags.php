<?php
/******************************************************************************
 *                                                                            *
 *   tags.php, v 0.00 2007/07/18 - This is part of xBB library                *
 *   Copyright (C) 2006-2007  Dmitriy Skorobogatov  dima@pc.uz                *
 *                                                                            *
 *   This program is free software; you can redistribute it and/or modify     *
 *   it under the terms of the GNU General Public License as published by     *
 *   the Free Software Foundation; either version 2 of the License, or        *
 *   (at your option) any later version.                                      *
 *                                                                            *
 *   This program is distributed in the hope that it will be useful,          *
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of           *
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            *
 *   GNU General Public License for more details.                             *
 *                                                                            *
 *   You should have received a copy of the GNU General Public License        *
 *   along with this program; if not, write to the Free Software              *
 *   Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA *
 *                                                                            *
 ******************************************************************************/

/*
Файл содержит умолчальные настройки тегов bbcode.

Список поддерживаемых тегов с указанием соответствующих им классов-обработчиков:
*/
$tags = array(
    // Основные теги
    '*'            => 'Xbb_Tags_Li'     ,
    'a'            => 'Xbb_Tags_A'      ,
    'abbr'         => 'Xbb_Tags_Abbr'   ,
    'acronym'      => 'Xbb_Tags_Acronym',
    'address'      => 'Xbb_Tags_Address',
    'align'        => 'Xbb_Tags_Align'  ,
    'anchor'       => 'Xbb_Tags_A'      ,
    'b'            => 'Xbb_Tags_Simple' ,
    'bbcode'       => 'Xbb_Tags_Bbcode' ,
    'bdo'          => 'Xbb_Tags_Bdo'    ,
    'big'          => 'Xbb_Tags_Simple' ,
    'blockquote'   => 'Xbb_Tags_Quote'  ,
    'br'           => 'Xbb_Tags_Br'     ,
    'caption'      => 'Xbb_Tags_Caption',
    'center'       => 'Xbb_Tags_Align'  ,
    'cite'         => 'Xbb_Tags_Simple' ,
    'color'        => 'Xbb_Tags_Color'  ,
    'del'          => 'Xbb_Tags_Simple' ,
    'em'           => 'Xbb_Tags_Simple' ,
    'email'        => 'Xbb_Tags_Email'  ,
    'font'         => 'Xbb_Tags_Font'   ,
    'google'       => 'Xbb_Tags_Google' ,
    'h1'           => 'Xbb_Tags_P'      ,
    'h2'           => 'Xbb_Tags_P'      ,
    'h3'           => 'Xbb_Tags_P'      ,
    'h4'           => 'Xbb_Tags_P'      ,
    'h5'           => 'Xbb_Tags_P'      ,
    'h6'           => 'Xbb_Tags_P'      ,
    'hr'           => 'Xbb_Tags_Hr'     ,
    'i'            => 'Xbb_Tags_Simple' ,
    'img'          => 'Xbb_Tags_Img'    ,
    'ins'          => 'Xbb_Tags_Simple' ,
    'justify'      => 'Xbb_Tags_Align'  ,
    'left'         => 'Xbb_Tags_Align'  ,
    'list'         => 'Xbb_Tags_List'   ,
    'nobb'         => 'Xbb_Tags_Nobb'   ,
    'ol'           => 'Xbb_Tags_List'   ,
    'p'            => 'Xbb_Tags_P'      ,
    'quote'        => 'Xbb_Tags_Quote'  ,
    'right'        => 'Xbb_Tags_Align'  ,
    's'            => 'Xbb_Tags_Simple' ,
    'size'         => 'Xbb_Tags_Size'   ,
    'small'        => 'Xbb_Tags_Simple' ,
    'strike'       => 'Xbb_Tags_Simple' ,
    'strong'       => 'Xbb_Tags_Simple' ,
    'sub'          => 'Xbb_Tags_Simple' ,
    'sup'          => 'Xbb_Tags_Simple' ,
    'table'        => 'Xbb_Tags_Table'  ,
    'td'           => 'Xbb_Tags_Td'     ,
    'th'           => 'Xbb_Tags_Th'     ,
    'tr'           => 'Xbb_Tags_Tr'     ,
    'tt'           => 'Xbb_Tags_Simple' ,
    'u'            => 'Xbb_Tags_Simple' ,
    'ul'           => 'Xbb_Tags_List'   ,
    'url'          => 'Xbb_Tags_A'      ,
    'var'          => 'Xbb_Tags_Simple' ,

    // Теги для вывода кода и подсветки синтаксисов (с помощью GeSHi)
    'actionscript' => 'Xbb_Tags_Code'   ,
    'ada'          => 'Xbb_Tags_Code'   ,
    'apache'       => 'Xbb_Tags_Code'   ,
    'applescript'  => 'Xbb_Tags_Code'   ,
    'asm'          => 'Xbb_Tags_Code'   ,
    'asp'          => 'Xbb_Tags_Code'   ,
    'autoit'       => 'Xbb_Tags_Code'   ,
    'bash'         => 'Xbb_Tags_Code'   ,
    'blitzbasic'   => 'Xbb_Tags_Code'   ,
    'bnf'          => 'Xbb_Tags_Code'   ,
    'c'            => 'Xbb_Tags_Code'   ,
    'c++'          => 'Xbb_Tags_Code'   ,
    'c#'           => 'Xbb_Tags_Code'   ,
    'c_mac'        => 'Xbb_Tags_Code'   ,
    'caddcl'       => 'Xbb_Tags_Code'   ,
    'cadlisp'      => 'Xbb_Tags_Code'   ,
    'cfdg'         => 'Xbb_Tags_Code'   ,
    'cfm'          => 'Xbb_Tags_Code'   ,
    'code'         => 'Xbb_Tags_Code'   ,
    'cpp-qt'       => 'Xbb_Tags_Code'   ,
    'css'          => 'Xbb_Tags_Code'   ,
    'd'            => 'Xbb_Tags_Code'   ,
    'delphi'       => 'Xbb_Tags_Code'   ,
    'diff'         => 'Xbb_Tags_Code'   ,
    'div'          => 'Xbb_Tags_Code'   ,
    'dos'          => 'Xbb_Tags_Code'   ,
    'eiffel'       => 'Xbb_Tags_Code'   ,
    'fortran'      => 'Xbb_Tags_Code'   ,
    'freebasic'    => 'Xbb_Tags_Code'   ,
    'gml'          => 'Xbb_Tags_Code'   ,
    'groovy'       => 'Xbb_Tags_Code'   ,
    'html4'        => 'Xbb_Tags_Code'   ,
    'idl'          => 'Xbb_Tags_Code'   ,
    'ini'          => 'Xbb_Tags_Code'   ,
    'inno'         => 'Xbb_Tags_Code'   ,
    'io'           => 'Xbb_Tags_Code'   ,
    'java'         => 'Xbb_Tags_Code'   ,
    'java5'        => 'Xbb_Tags_Code'   ,
    'js'           => 'Xbb_Tags_Code'   ,
    'latex'        => 'Xbb_Tags_Code'   ,
    'lisp'         => 'Xbb_Tags_Code'   ,
    'lua'          => 'Xbb_Tags_Code'   ,
    'matlab'       => 'Xbb_Tags_Code'   ,
    'mirc'         => 'Xbb_Tags_Code'   ,
    'mpasm'        => 'Xbb_Tags_Code'   ,
    'mysql'        => 'Xbb_Tags_Code'   ,
    'nsis'         => 'Xbb_Tags_Code'   ,
    'objc'         => 'Xbb_Tags_Code'   ,
    'ocaml'        => 'Xbb_Tags_Code'   ,
    'oobas'        => 'Xbb_Tags_Code'   ,
    'oracle'       => 'Xbb_Tags_Code'   ,
    'pascal'       => 'Xbb_Tags_Code'   ,
    'perl'         => 'Xbb_Tags_Code'   ,
    'php'          => 'Xbb_Tags_Code'   ,
    'plsql'        => 'Xbb_Tags_Code'   ,
    'pre'          => 'Xbb_Tags_Code'   ,
    'python'       => 'Xbb_Tags_Code'   ,
    'qbasic'       => 'Xbb_Tags_Code'   ,
    'reg'          => 'Xbb_Tags_Code'   ,
    'robots'       => 'Xbb_Tags_Code'   ,
    'ruby'         => 'Xbb_Tags_Code'   ,
    'sas'          => 'Xbb_Tags_Code'   ,
    'scheme'       => 'Xbb_Tags_Code'   ,
    'sdlbasic'     => 'Xbb_Tags_Code'   ,
    'smalltalk'    => 'Xbb_Tags_Code'   ,
    'smarty'       => 'Xbb_Tags_Code'   ,
    'sql'          => 'Xbb_Tags_Code'   ,
    't-sql'        => 'Xbb_Tags_Code'   ,
    'tcl'          => 'Xbb_Tags_Code'   ,
    'text'         => 'Xbb_Tags_Code'   ,
    'thinbasic'    => 'Xbb_Tags_Code'   ,
    'vb'           => 'Xbb_Tags_Code'   ,
    'vb.net'       => 'Xbb_Tags_Code'   ,
    'vhdl'         => 'Xbb_Tags_Code'   ,
    'visualfoxpro' => 'Xbb_Tags_Code'   ,
    'winbatch'     => 'Xbb_Tags_Code'   ,
    'xml'          => 'Xbb_Tags_Code'   ,
    'z80'          => 'Xbb_Tags_Code'   ,
);

/*
Массив пар: 'модель_поведения_тегов' => массив_моделей_поведений_тегов.
Накладывает ограничения на вложенность тегов. Теги с моделями поведения, не
указанными в массиве справа, вложенные в тег с моделью поведения, указанной
слева, будут игнорироваться как неправильно вложенные.
*/
$children = array(
    'a'       => array('code','img','span'),
    'caption' => array('a','code','img','span'),
    'code'    => array(),
    'div'     => array('a','code','div','hr','img','p','pre','span','table','ul'),
    'hr'      => array(),
    'img'     => array(),
    'li'      => array('a','code','div','hr','img','p','pre','span','table','ul'),
    'p'       => array('a','code','img','span'),
    'pre'     => array(),
    'span'    => array('a','code','img','span'),
    'table'   => array('caption','tr'),
    'td'      => array('a','code','div','hr','img','p','pre','span','table','ul'),
    'tr'      => array('td'),
    'ul'      => array('li'),
);

/*
Массив пар: 'модель_поведения_тегов' => массив_моделей_поведений_тегов.
Накладывает ограничения на вложенность тегов.
Тег, принадлежещий указанной слева модели поведения тегов должен закрыться, как
только начинается тег, принадлежещий какой то из моделей поведения, указанных
справа.
*/
$ends = array(
    'a'       => array(
        'a','caption','div','hr','li','p','pre','table','td','tr', 'ul'
    ),
    'caption' => array('tr'),
    'code'    => array(),
    'div'     => array('li','tr','td'),
    'hr'      => array(
        'a','caption','code','div','hr','img','li','p','pre','span','table',
        'td','tr','ul'
    ),
    'img'     => array(
        'a','caption','code','div','hr','img','li','p','pre','span','table',
        'td','tr','ul'
    ),
    'li'      => array('li','tr','td'),
    'p'       => array('div','hr','li','p','pre','table','td','tr','ul'),
    'pre'     => array(),
    'span'    => array('div','hr','li','p','pre','table','td','tr','ul'),
    'table'   => array('table'),
    'td'      => array('td','tr'),
    'tr'      => array('tr'),
    'ul'      => array(),
);
<?php

/******************************************************************************
 *                                                                            *
 *   Quote.php, v 0.01 2007/04/29 - This is part of xBB library               *
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

// Класс для тегов [quote] и [blockquote]
class Xbb_Tags_Quote extends bbcode {
    public $rbr = 1;
    function get_html($tree = null) {
        if ('blockquote' == $this->tag) {
            $author = htmlspecialchars($this->attrib['blockquote']);
        } else {
            $author = htmlspecialchars($this->attrib['quote']);
        }
        if ($author) {
            $author = '<div class="bb_quote_author">' . $author . '</div>';
        }
        return '<blockquote class="bb_quote">' . $author
            . parent::get_html($this -> tree) . '</blockquote>';
    }
}
?>

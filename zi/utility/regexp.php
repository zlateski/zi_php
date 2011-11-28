<?php

//
// Copyright (C) 2011  Aleksandar Zlateski <zlateski@mit.edu>
// ----------------------------------------------------------
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>.
//


class regexp
{
    const EMAIL  = "([0-9a-zA-Z]([-.\w]*[0-9a-zA-Z])*@([0-9a-zA-Z][-\w]*[0-9a-zA-Z]\.)+[a-zA-Z]{2,9})";
    const SCALAR = "[0-9]{1,16}";
    const HEX    = "[0-9a-fA-F]{1,64}";
    const URL    = "(((ht|f)tp(s?):\/\/)|(www\.[^ \[\]\(\)\n\r\t]+)|(([012]?[0-9]{1,2}\.){3}[012]?[0-9]{1,2})\/)([^ \[\]\(\),;&quot;'&lt;&gt;\n\r\t]+)([^\. \[\]\(\),;&quot;'&lt;&gt;\n\r\t])|(([012]?[0-9]{1,2}\.){3}[012]?[0-9]{1,2})";

    const PRINTF = "%[\-\+0\s\#]{0,1}(\d+){0,1}(\.\d+){0,1}[hlI]{0,1}([cCdiouxXeEfgGnpsS]){1}";


    public static function is_scalar( $val )
    {
        return preg_match( "~^" . self::SCALAR . "$~", $val ) === 1;
    }

    public static function is_email( $val )
    {
        return preg_match( "~^" . self::EMAIL . "$~", $val ) === 1;
    }

    public static function is_hex( $val )
    {
        return preg_match( "~^" . self::HEX . "$~", $val ) === 1;
    }

    public static function is_url( $val )
    {
        return preg_match( "~^" . self::URL . "$~", $val ) === 1;
    }
}
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

class log
{
    private static $data       = array();
    private static $registered = false;

    public static function dump_logs()
    {
        var_dump(self::$data);
    }

    private static function register_on_exit()
    {
        $registered = true;
        register_shutdown_function(array('log', 'dump_logs'));
    }

    public static function __callStatic( $name, $args )
    {
        if ( !self::$registered )
        {
            self::register_on_exit();
        }
        self::$data []= $args;
    }
}

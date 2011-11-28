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

require_once ZI_PHP_ROOT . '/utility/regexp.php';

class args
{
    const INT     = 1  ;
    const SCALAR  = 2  ;
    const DOUBLE  = 4  ;
    const STRING  = 8  ;
    const BOOL    = 16 ;
    const DATE    = 32 ;
    const NUMERIC = 64 ;
    const DEFINED = 256;
    const HEX     = 512;


    private static function get_one( $name, $type = self::STRING )
    {

        if ( $type & self::DEFINED )
        {
            return isset( $_REQUEST[ $name ] );
        }

        if ( $type & self::BOOL )
        {
            return isset( $_REQUEST[ $name ] ) && $_REQUEST[ $name ];
        }

        if ( !isset( $_REQUEST[ $name ] ) )
        {
            return null;
        }

        $v = $_REQUEST[ $name ];

        if ( $type & self::INT )
        {
            if ( is_integer( $v ) )
            {
                return (int)$v;
            }
        }

        if ( $type & self::SCALAR )
        {
            if ( regexp::is_scalar( $v ) )
            {
                return $v;
            }
        }

        $v = html_entity_decode( $v, ENT_QUOTES );

        if ( $type & self::DOUBLE )
        {
            if ( is_numeric( $v ) )
            {
                return $v;
            }
        }

        if ( $type & self::HEX )
        {
            if ( regexp::is_hex( $v ) )
            {
                return $v;
            }
        }

        if ( $type & self::STRING )
        {
            return $v;
        }

        return null;
    }


    public static function get( $name /*, ... */ )
    {
        $fn_args = func_get_args();
        $len = count( $fn_args );

        if ( $len == 1 )
        {
            return self::get_one( $name );
        }

        if ( $len == 2 )
        {
            return self::get_one( $name, $fn_args[ 1 ] );
        }

        $res = array();

        for ( $i = 0; $i < $len; $i += 2 )
        {
            if ( $i + 1 < $len )
            {
                $res[ $fn_args[ $i ] ] = self::get_one( $fn_args[ $i ], $fn_args[ $i + 1 ] );
            }
            else
            {
                $res[ $fn_args[ $i ] ] = self::get_one( $fn_args[ $i ] );
            }
        }

        return (object) $res;
    }

    public static function vget( $name )
    {
        $res = array();
        if ( is_array( $name ) )
        {
            foreach ( $name as $sub_name => $sub_type )
            {
                $res[ $sub_name ] = self::get( $sub_name, $sub_type );
            }
        }
        return ( object ) $res;
    }

}

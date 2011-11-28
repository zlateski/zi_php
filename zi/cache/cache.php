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

class cache
{
    const server_address = "127.0.0.1";
    const server_port    = 11211      ;

    private static $initialized = false;
    private static $connected   = false;
    private static $mmc         = false;
    private static $prefix      = "";

    private static function connect()
    {
        if ( !self::$initialized )
        {
            self::$mmc  = new Memcached();
            self::$mmc->setOption( Memcached::OPT_SOCKET_RECV_SIZE, 1024*1024 );
            self::$mmc->setOption( Memcached::OPT_SOCKET_SEND_SIZE, 1024*1024 );

            // self::$mmc->setOption( Memcached::OPT_NO_BLOCK, true );
            // self::$mmc->setOption( Memcached::OPT_TCP_NODELAY, true );
            // self::$mmc->setOption( Memcached::OPT_HASH, Memcached::HASH_MURMUR );
            // self::$mmc->setOption( Memcached::OPT_SERIALIZER, Memcached::SERIALIZER_JSON );

            self::$connected = self::$mmc->addServer( self::server_address, self::server_port );
            self::$initialized = true;
        }
        return self::$connected;
    }

    public static function get_prefix()
    {
        return self::$mmc->getOption(Memcached::OPT_PREFIX_KEY);
    }

    private static function set_prefix($prefix)
    {
        if ( $prefix !== self::$prefix )
        {
            self::$prefix = $prefix;
            if ( self::$mmc->setOption( Memcached::OPT_PREFIX_KEY, $prefix . '/' ) )
            {
                self::$prefix = $prefix;
                return true;
            }
            else
            {
                return false;
            }
        }
        else
        {
            return true;
        }
    }

    public static function get()
    {
        $args   = func_get_args();
        $keys   = array_pop($args);
        $prefix = implode('/', $args);

        self::connect();

        if ( is_array( $keys ) )
        {
            return self::$mmc->getMulti( $keys );
        }
        else
        {
            return self::$mmc->get( $prefix . $keys );
        }
    }

    public static function erase()
    {
        $args = func_get_args();
        $keys = array_pop($args);
        $prefix = implode('/', $args);

        self::connect();
        self::set_prefix($prefix);

        return self::$mmc->delete( $prefix . $keys );
    }

    public static function set()
    {
        $args     = func_get_args();
        $key_vals = array_pop($args);

        self::connect();

        if ( is_array($key_vals) )
        {
            $prefix = implode('/', $args);
            self::set_prefix($prefix);
            return self::$mmc->setMulti( $key_vals );
        }
        else
        {
            $val = $key_vals;
            $key = array_pop($args);
            $prefix = implode('/', $args);
            return self::$mmc->set( $prefix . $key, $val );
        }
    }

    public static function flush()
    {
        self::connect();
        self::$mmc->flush();
    }
}


// var_dump(cache::set('aleks', 2));
// var_dump(cache::get_prefix());
// var_dump(cache::get('aleks'));
// var_dump(cache::get_prefix());
// var_dump(cache::set('pera', 'aleks', 3));
// var_dump(cache::get_prefix());
// var_dump(cache::get('pera', 'aleks'));
// var_dump(cache::get_prefix());
// var_dump(cache::get('aleks'));
// var_dump(cache::get_prefix());

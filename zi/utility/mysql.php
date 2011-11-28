<?php

class mysql
{
    private static $initialized = false;
    private static $good        = false;
    private static $db;

    private static $hostname  = 'localhost';
    private static $database  = 'omni' ;
    private static $username  = 'root' ;
    private static $password  = '' ;
    private static $keep_open = true       ;

    public static function configure( $hostname, $database, $username, $password, $keep_open = false )
    {
        if ( !self::$initialized )
        {
            self::$hostname  = $hostname;
            self::$database  = $database;
            self::$username  = $username;
            self::$password  = $password;
            self::$keep_open = $keep_open;
            return true;
        }
        else
        {
            return false;
        }
    }

    private static function connect( $keep_open = false )
    {
        if ( !self::$initialized )
        {
            self::$db = mysql_connect( self::$hostname, self::$username, self::$password );

            if ( is_resource( self::$db ) )
            {
                mysql_select_db( self::$database, self::$db );
                mysql_set_charset( 'utf8', self::$db );

                // old way
                // mysql_query( "SET CHARACTER SET utf8", self::$db );
                // mysql_query( "SET NAMES utf8", self::$db );

                if ( !self::$keep_open )
                {
                    register_shutdown_function( 'mysql_close', self::$db );
                }

                self::$good = true;
            }

            self::$initialized = true;
        }

        return self::$good;
    }

    static function escape( $value )
    {
        if ( self::connect() )
        {
            return mysql_real_escape_string( $value, self::$db );
        }
        else
        {
            return mysql_escape_string( $value );
        }
    }

    static function deep_escape( $value )
    {
        if ( is_array( $value ) )
        {
            return array_map( array( 'mysql', 'deep_escape' ), $value );
        }
        else
        {
            return self::escape( $value );
        }
    }

    static function vsprintf( $format, $args = array() )
    {
        $format = preg_replace( "/(%[\-\+0\s\#]{0,1}(\d+){0,1}(\.\d+){0,1}[hlI]{0,1})[zZ]{1}/",
                                '"${1}s"', $format );

        return vsprintf( $format, self::deep_escape( $args ) );
    }

    static function sprintf( $format )
    {
        $args = func_get_args();
        array_shift( $args );
        return self::vsprintf( $format, $args );
    }

    static function vqueryf( $format, $args = array() )
    {
        if ( self::connect() )
        {
            return mysql_query( self::vsprintf( $format, $args ), self::$db );
        }
        else
        {
            return false;
        }
    }


    static function queryf( $format )
    {
        if ( self::connect() )
        {
            $args = func_get_args();
            array_shift( $args );
            return mysql_query( self::vsprintf( $format, $args ), self::$db );
        }
        else
        {
            return false;
        }
    }

    static function vexecf( $format, $args = array() )
    {
        if ( self::connect() )
        {
            $res = mysql_query( self::vsprintf( $format, $args ), self::$db );
            return $res === true ? mysql_affected_rows( self::$db ) : $res;
        }
        else
        {
            return false;
        }
    }


    static function execf( $format )
    {
        if ( self::connect() )
        {
            $args = func_get_args();
            array_shift( $args );
            $res = mysql_query( self::vsprintf( $format, $args ), self::$db );
            return $res === true ? mysql_affected_rows( self::$db ) : $res;
        }
        else
        {
            return false;
        }
    }

    static function vinsertf( $format, $args = array() )
    {
        if ( self::connect() )
        {
            $res = mysql_query( self::vsprintf( $format, $args ), self::$db );
            return $res === true ? mysql_insert_id( self::$db ) : $res;
        }
        else
        {
            return false;
        }
    }


    static function insertf( $format )
    {
        if ( self::connect() )
        {
            $args = func_get_args();
            array_shift( $args );
            $res = mysql_query( self::vsprintf( $format, $args ), self::$db );
            return $res === true ? mysql_insert_id( self::$db ) : $res;
        }
        else
        {
            return false;
        }
    }

    static function stat_string()
    {
        if ( self::connect() )
        {
            return mysql_stat( self::$db );
        }
        else
        {
            return false;
        }
    }

    static function stat()
    {
        $stats = self::stat_string();
        if ( $stats )
        {
            $stats = explode( '  ', $stats );
            $ret   = array();

            foreach ( $stats as $stat )
            {
                $x = explode( ':', $stat );
                $ret[ trim( $x[ 0 ] ) ] = trim( $x[ 1 ] );
            }

            return $ret;
        }

        return false;
    }

}

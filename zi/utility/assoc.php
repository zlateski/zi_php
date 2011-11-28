<?php

/*

SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `assoc_properties` (
  `id1`   bigint(20) NOT NULL,
  `id2`   bigint(20) NOT NULL,
  `name`  char(64)   character set utf8 NOT NULL,
  `value` char(255)  character set utf8 NOT NULL,
  PRIMARY KEY  ( `id1`, `id2`, `name` ),
  KEY `id1_index` (`id1`),
  KEY `id2_index` (`id2`),
  KEY `name_index` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

*/

include_once 'mysql.php';
include_once '../cache/cache.php';

class assoc
{
    //const PREFIX = 'assocs';

    private static $all_data = array();

    private $iid1 = null;
    private $iid2 = null;
    private $data;

    private function get_from_db( $name )
    {
        $q = mysql::queryf( 'SELECT value FROM assoc_properties ' .
                            'WHERE id1 = %d ' .
                            'AND   id2 = %d ' .
                            'AND   name = "%s" ',
                            $this->iid1,
                            $this->iid2,
                            $name );

        if ( $q )
        {
            $res = mysql_fetch_object( $q );
            if ( $res )
            {
                return unserialize($res->value);
            }
        }

        return null;
    }

    private function store_to_db( $name, $value )
    {
        $val = serialize($value);
        return mysql::execf( 'INSERT INTO assoc_properties ' .
                             'VALUES ( %d, %d, "%s", "%s" ) ' .
                             'ON DUPLICATE KEY UPDATE value = "%s"',
                             $this->iid1, $this->iid2,
                             $name, $val, $val );
    }

    private function remove_from_db( $name )
    {
        return mysql::execf( 'DELETE FROM assoc_properties ' .
                             'WHERE id1 = %d ' .
                             'AND   id1 = %d ' .
                             'AND   name = "%s"',
                             $this->iid1, $this->iid2,
                             $name );
    }

    private function get_from_cache( $name )
    {
        return cache::get( $this->iid1, $this->iid2, $name );
    }

    private function store_to_cache( $name, $value )
    {
        return cache::set( $this->iid1, $this->iid2, $name, $value );
    }

    private function remove_from_cache( $name )
    {
        return cache::erase( $this->iid1, $this->iid2, $name );
    }

    private function get( $name )
    {
        $r = $this->get_from_cache( $name );

        if ( $r !== false )
        {
            return $r;
        }

        $r = $this->get_from_db( $name );
        $this->store_to_cache( $name, $r );

        return $r;
    }

    private function set( $name, $value )
    {
        $this->store_to_db( $name, $value );
        $this->remove_from_cache( $name, $value );
    }

    public function __construct( $id1, $id2 )
    {
        $key = $id1 . '/' . $id2;
        if ( !isset( self::$all_data[ $key ] ) )
        {
            self::$all_data[ $key ] = array();
        }

        $this->data =& self::$all_data[ $key ];

        $this->iid1 = $id1;
        $this->iid2 = $id2;
    }

    public static function make( $id1, $id2 )
    {
        return new assoc( $id1, $id2 );
    }

    public function id1()
    {
        return $this->iid1;
    }

    public function id2()
    {
        return $this->iid2;
    }

    public function refresh()
    {
        $this->data = array();
    }

    public function __call( $fn_name, $args )
    {
        if ( $args )
        {
            return $this->__set( $fn_name, $args[ 0 ] );
        }
        else
        {
            return $this->__get( $fn_name );
        }
    }

    public function __get( $name )
    {
        if ( !isset( $this->data[ $name ] ) )
        {
            $this->data[ $name ] = $this->get( $name );
        }
        return $this->data[ $name ];
    }

    public function __set( $name, $value )
    {
        if ( $value === false )
        {
            $value = 0;
        }

        if ( isset( $this->data[ $name ] ) && $this->data[ $name ] === $value )
        {
            return;
        }

        $this->set( $name, $value );
        $this->data[ $name ] = $value;
    }

    public function __isset( $name )
    {
        return $this->__get( $name ) !== null;
    }

    public function __unset( $name )
    {
        $this->remove_from_db( $name );
        $this->data[ $name ] = null;
    }

    public function __clone()
    {
        return $this;
    }

}

var_dump( cache::flush() );

//return;

$u = new assoc( 1, 2 );

//$u->z = 1234;


$u->something = 5;

//$u->s2 = array(1,2,3,4,"asdsad");

var_dump($u->qaa);
var_dump($u->something);
var_dump($u->s2);


//echo ( $u->z + $u->z ) . "\n";

///$u->z += $u->z;;

// var_dump( isset($u->z ));
// var_dump( isset($u->qaa ));
// $u->qaa = 3;
// //var_dump( $u->zaa );
// var_dump( isset($u->qaa ));
// unset( $u->qaa );
// var_dump( isset($u->qaa ));
// $u->qaa = 4;
// var_dump( $u->qaa() );
// $u->qaa( 13 );
// var_dump( $u->qaa );
// var_dump( $u->id2() );
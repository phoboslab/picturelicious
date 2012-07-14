<?php

/*
	This class handles all database IO. To prevent SQL-Injections, all variables
	that are passed to the database must be specified as additional parameters to
	your query. e.g.
	$res = DB::query( 'SELECT * FROM images WHERE user = :1 AND tags LIKE :2', 'testuser', 'funny' );
	
	query() and getRow() return a 2-dimensional array instead of a result resource.
*/

require_once( 'lib/config.php' );

class DB {
	private static $link = null;
	private static $result;
	public static $sql;
	public static $numQueries = 0;

	private static function connect() {
		self::$link = @mysql_connect( Config::$db['host'], Config::$db['user'], Config::$db['password'] )
			or die( "Couldn't establish link to database-server: ".Config::$db['host'] );
		mysql_select_db( Config::$db['database'] )
			or die( "Couldn't select Database: ".Config::$db['database'] );
		mysql_query( 'SET NAMES utf8', self::$link );
	}

	public static function foundRows() {
		$r = self::query( 'SELECT FOUND_ROWS() AS foundRows' );
		return $r[0]['foundRows'];
	}

	public static function numRows() {
		return mysql_num_rows( self::$result );
	}

	public static function affectedRows() {
		return mysql_affected_rows( self::$link );
	}

	public static function insertId() {
		return mysql_insert_id( self::$link );
	}

	public static function query( $q, $params = array() ) {
		if( self::$link === null ) {
			self::connect();
		}

		if( !is_array( $params ) ) {
			$params = array_slice( func_get_args(), 1 );
		}

		if( !empty( $params ) ) {
			$q = preg_replace('/:(\d+)/e', 'self::quote($params[$1 - 1])', $q );
		}
		self::$numQueries++;
		self::$sql = $q;
		self::$result = mysql_query( $q, self::$link );
		
		echo self::getError();
		if( !self::$result ) {
			return false;
		}
		else if( !is_resource( self::$result ) ) {
			return true;
		}

		$rset = array();
		while ( $row = mysql_fetch_assoc( self::$result ) ) {
			$rset[] = $row;
		}
		return $rset;
	}

	public static function getRow( $q, $params = array() ) {
		if( !is_array( $params ) ) {
			$params = array_slice( func_get_args(), 1 );
		}

		$r = self::query( $q, $params );
		return array_shift( $r );
	}

	public static function updateRow( $table, $idFields, $updateFields ) {
		$updateString = implode( ',', self::quoteArray( $updateFields, true ) );
		$idString = implode( ' AND ', self::quoteArray( $idFields, true ) );
		return self::query( "UPDATE $table SET $updateString WHERE $idString" );
	}

	public static function insertRow( $table, $insertFields ) {
		$insertString = implode( ',', self::quoteArray( $insertFields, true ) );
		return self::query( "INSERT INTO $table SET $insertString" );
	}

	public static function getError() {
		if( $e = mysql_error( self::$link ) ) {
			return "MySQL reports: '$e' on query\n".self::$sql;
		}
		return false;
	}

	public static function quote( $s ) {
		if( self::$link === null ) {
			self::connect();
		}
		if( !isset($s) || $s === false ) {
			return 0;
		}
		else if( $s === true ) {
			return 1;
		}
		else if( is_numeric( $s ) ) {
			return $s;
		}
		else {
			return "'".mysql_real_escape_string( $s )."'";
		}
	}

	public static function quoteArray( &$fields, $useKeys = false ) {
		$r = array();
		foreach( $fields as $key => &$value ) {
			$r[] = ( $useKeys ? "`$key`=":'' ) . self::quote( $value );
		}
		return $r;
	}
}

?>
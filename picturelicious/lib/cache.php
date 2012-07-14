<?php 

/*
	The Cache class reads and writes complete pages from the cache. 
	The name of a cache file is based on its URL
*/

class Cache {
	private $fileName = '';
	private $path = '';
	private $enabled = true;
	private $captured = false;
	
	public function __construct( $path, $pageName ) {
		$this->path = $path;
		$this->fileName = $this->path . strtr( $pageName, '?&', '--' ) . '.html';
		
		if( $_SERVER['REQUEST_METHOD'] != 'GET' ) {
			$this->enabled = false;
		}
	}
	
	public function disable() {
		$this->enabled = false;
	}
	
	public function forceEnable() {
		$this->enabled = true;
	}
	
	public function lookup() {
		if( $this->enabled && file_exists($this->fileName) ) {
			$fp = fopen($this->fileName, 'rb');
			fpassthru($fp);
			fclose($fp);
			exit();
		}
	}
	
	public function capture() {
		if( $this->enabled ) {
			ob_start();
			$this->captured = true;
		}
	}
	
	public function clear( $pattern = '' ) {
		require_once( 'lib/filesystem.php' );
		if( empty($pattern) && !empty($this->path) ) {
			Filesystem::rmdirr( $this->path, true );
		} else {
			$files = glob( $this->path . 'all/view/' . strtr( $pattern, '?&', '--' ) . '.html' );
			foreach( $files as $f ) {
				unlink( $f  );
			}
			$files = glob( $this->path . 'user/*/view/' . strtr( $pattern, '?&', '--' ) . '.html' );
			foreach( $files as $f ) {
				unlink( $f  );
			}
		}
	}
	
	public function write() {
		if( $this->captured ) {
			require_once( 'lib/filesystem.php' );
			Filesystem::mkdirr( dirname($this->fileName) );
			file_put_contents( $this->fileName, ob_get_contents() );
		}
	}
}

?>
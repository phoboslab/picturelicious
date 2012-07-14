<?php 
require_once( '../lib/config.php' );
header( 'Content-type: text/javascript; charset=utf-8' ); 
?>
function RP_RemotePost( postURL, stylesheet ) {
	this.postURL = postURL;
	this.stylesheet = stylesheet;
	
	this.visible = false;
	this.menu = null;
	this.dialog = null;
	this.iframe = null;
	this.checkSuccessInterval = 0;
	this.minImageSize = 32;
	
	
	this.create = function(){
		var that = this;
		var css = document.createElement('link');
		css.type = 'text/css';
		css.rel = 'stylesheet';
		css.href = this.stylesheet;
		if( document.getElementsByTagName("head").item(0) ) {
			document.getElementsByTagName("head").item(0).appendChild( css );
		} else {
			document.getElementsByTagName("body").item(0).appendChild( css );
		}
		
		var closeButton = document.createElement('a');
		closeButton.appendChild( document.createTextNode("x") );
		closeButton.className = 'close';
		closeButton.onclick = function() { return that.toggle(); }
		closeButton.href = '#';
		
		
		this.dialog = document.createElement('div');
		this.dialog.id = 'RP_Dialog';
		this.iframe = document.createElement('iframe');
		this.iframe.src = 'about:blank';
		this.dialog.appendChild( this.iframe );
		this.dialog.appendChild( closeButton );
		document.body.appendChild( this.dialog );
		
		this.loadIFrame( {} );
	}
	
	
	this.loadIFrame = function( params ) {
		var reqUrl = this.postURL + '?nocache=' + parseInt(Math.random()*10000);
		for( p in params ) {
			reqUrl += '&' + p + '=' + encodeURIComponent( params[p] );
		}
		this.iframe.src = reqUrl;
	}
	
	
	this.selectImage = function( image ) {
		var title = image.title ? image.title : ( image.alt ? image.alt : document.title );
		var imageSrc = image.src;
		if( 
			image.parentNode.tagName.match(/^a$/i) &&
			image.parentNode.href &&
			image.parentNode.href.match(/\.(jpe?g|gif|png)$/i)
		) {
			imageSrc = image.parentNode.href;
		}
		this.loadIFrame( {
			'url': imageSrc,
			'xhrLocation': document.location.href.replace(/#.*$/,'')
		});
		return false;
	}
	
	
	this.checkSuccess = function() {
		if( document.location.href.match(/#RP_Success/) ) {
			var that = this;
			document.location.href = document.location.href.replace(/#.*$/, '#');
			setTimeout( function() { that.hide() }, 500 );
		}
	}
	
	
	this.show = function() {
		this.visible = true;
		var that = this;
		
		this.checkSuccessInterval = setInterval( function() { that.checkSuccess(); }, 500 );
		this.dialog.style.display = 'block';
		
		var images = document.getElementsByTagName('img');
		for( var i=0; i<images.length; i++ ) {
			var img = images[i];
			if( img && img.src && img.src.match(/(space|blank)[^\/]*\.gif$/i) ) {
				img.style.display = 'none';
			}
			else if( img && img.src && img.width > this.minImageSize && img.height > this.minImageSize) {
				img.onclick = function( ev ) { 
					if( ev && ev.stopPropagation ) {
						ev.stopPropagation(); 
					}
					return that.selectImage(this); 
				};
				img.className = img.className ? img.className + ' RP_PostImage' : 'RP_PostImage';
			}
		}
	}
	
	
	this.hide = function() {
		this.visible = false;
		
		clearInterval( this.checkSuccessInterval );
		this.dialog.style.display = 'none';
		
		var images = document.getElementsByTagName('img');
		for( var i=0; i<images.length; i++ ) {
			var img = images[i];
			if( img && img.src && img.width > this.minImageSize && img.height > this.minImageSize) {
				img.onclick = null;
				img.className = img.className.replace(/\s*RP_PostImage/, '');
			}
		}
	}
	
	
	this.toggle = function() {
		if( !this.visible ) {
			this.show();
		} else {
			this.hide();
		}
		return false;
	}
	
	
	this.create();
}

if( typeof(RP_Instance) == 'undefined' )  {
	var RP_Instance = new RP_RemotePost(
		'<?php echo Config::$frontendPath; ?>remotepost.php', 
		'<?php echo Config::$frontendPath; ?>media/remotepost.css'
	);
} 
RP_Instance.toggle();

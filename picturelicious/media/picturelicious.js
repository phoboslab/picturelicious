function $(id){return document.getElementById(id)}
function gridView(id){ gridSolve(id); gridSolve(id); window.setInterval("gridSolve('"+id+"')",250); }
function swap(e, c1, c2) { e.className = e.className == c1 ? c2 : c1; };

// GridSolver ------------------------------------------------------------
var gridSize = 64;
var gridMinWidth = 6;
var gridWidth = 0;
var grid = null;
var boxDef = {
	'b0': {'width': 3, 'height': 3},
	'b1': {'width': 2, 'height': 2},
	'b2': {'width': 1, 'height': 1}
};

function gridSolve( cid ) {
	var container = $(cid);
	var newWidth = parseInt( container.offsetWidth );
	var newGridWidth = parseInt( newWidth / gridSize ); 
	newGridWidth = newGridWidth < gridMinWidth ? gridMinWidth : newGridWidth;

	if( gridWidth == newGridWidth )
		return; 
	else
		gridWidth = newGridWidth;
	
	boxes = new Array();
	while( container.hasChildNodes() ) {
		e = container.removeChild( container.firstChild );
		if( e.tagName == 'DIV' ) {
			boxes.push( e );
		}
	}

	grid = new Array( gridWidth );
	for( var i = 0; i < grid.length; i++ ) {
		grid[i] = 0;
	}
	
	for(var i = 0; i < boxes.length; i++) {
		insertThumb( boxes[i], cid ) 
	}
	
	var maxHeight = 0;
	for( var i = 0; i < grid.length; i++) {
		maxHeight = Math.max( maxHeight,grid[i] );
	}
	container.style.height = (maxHeight * gridSize) + 'px';
	$('images').style.height = (maxHeight * gridSize) + 'px';
}

function insertThumb( box, cid ) {
	var boxWidth = boxDef[box.className]['width'];
	var boxHeight = boxDef[box.className]['height'];
	var maxHeight = 0; 
	var currentHeight = -1;
	var spotWidth = 0;
	var spotLeft = 0;
	var freeSpots = new Array();
	for( var i = 0; i < grid.length; i++) {
		if( currentHeight == grid[i] ) {
			spotWidth++;
		}

		if( (currentHeight != grid[i] || i+1 == grid.length) && spotWidth > 0) {
			freeSpots.push({'width': spotWidth, 'left': spotLeft, 'height': currentHeight});
			
			spotWidth = 1;
			spotLeft = i;
			if(currentHeight != grid[i] && i+1 == grid.length) {
				freeSpots.push({'width': spotWidth, 'left': spotLeft, 'height': grid[i]});
			}
		}

		else if( spotWidth == 0 ) {
			spotWidth = 1;
		}
		
		currentHeight = grid[i];
		if( grid[i] > maxHeight ) {
			maxHeight = grid[i];
		}
	}

	var targetHeight = 0;
	var targetLeft = 0;
	var bestScore = -1;
	for( var j = 0; j < boxWidth; j++ ) {
		if( grid[j] > targetHeight ) {
			targetHeight = grid[j];
		}
	}
	var newHeight = targetHeight + boxHeight;
		
	for( var i = 0; i < freeSpots.length; i++ ) {
		var heightScore = ( maxHeight - freeSpots[i]['height'] );
		var widthScore = boxWidth / freeSpots[i]['width'];
		var score = heightScore * heightScore  + widthScore * 2;

		if( freeSpots[i]['width'] >= boxWidth && score > bestScore ) {
			targetHeight = freeSpots[i]['height'];
			targetLeft = freeSpots[i]['left'];
			newHeight = freeSpots[i]['height'] + boxHeight;
			bestScore = score;
		}
	}	

	for( var j = 0; j < boxWidth; j++ ) {
		grid[targetLeft  + j] = newHeight;
	}

	var container = $( cid );
	container.appendChild( box );
	box.style.left = (targetLeft * gridSize) + 'px';
	box.style.top =  (targetHeight * gridSize) + 'px';
	return true;
}

// Colorpicker -----------------------------------------------------------
function colorpicker( id, size, onchange, onshow ) {
	var that = this;
	this.size = size;
	this.onchange = onchange;
	this.onshow = onshow;
	
	this.mode = 'none';
	
	this.sv = $(id+'SV');
	this.svselect = $(id+'SVSelect');
	this.h = $(id+'H');
	this.hselect = $(id+'HSelect');
	this.value = $(id+'Value');
	this.current = $(id+'Current');
	
	this.cap = function( v, min, max ) {
		return Math.min(Math.max(min,v),max);
	}
	
	this.getMousPos = function( event ) {
		if(event.pageX || event.pageY){
			return { 
				'x': event.pageX, 
				'y': event.pageY 
			};
		}
		return {
			'x': event.clientX + document.body.scrollLeft - document.body.clientLeft,
			'y': event.clientY + document.body.scrollTop  - document.body.clientTop
		};
	}

	this.getObjPos = function( obj ) {
		var curleft = curtop = 0;
		if (obj.offsetParent) {
			curleft = obj.offsetLeft;
			curtop = obj.offsetTop;
			while (obj = obj.offsetParent) {
				curleft += obj.offsetLeft;
				curtop += obj.offsetTop;
			}
		}
		return {
			'x':curleft, 
			'y':curtop
		};
	}	
	
	
	this.release = function() {
		document.onmousemove = '';
		this.mode = 'none';
	}
	
	this.move = function( event ) {
		if (!event) var event = window.event;
		if(event.pageX || event.pageY) {
			var mx = event.pageX;
			var my = event.pageY;
		} else {
			var mx = event.clientX + document.body.scrollLeft - document.body.clientLeft;
			var my = event.clientY + document.body.scrollTop  - document.body.clientTop;
		}
		if( this.mode == 'sv' ) {
			var obj = this.getObjPos( this.sv );
			var x = this.cap(mx - obj.x - 3, -4, this.size - 4);
			var y = this.cap(my - obj.y - 3, -4, this.size - 4);
			this.svselect.style.left = x + "px";
			this.svselect.style.top = y + "px";
		}
		else if ( this.mode == 'h' ) {
			var obj = this.getObjPos( this.sv );
			var y = this.cap(my - obj.y - 2, -2, this.size - 3);
			this.hselect.style.top = y + "px";
			this.sv.style.backgroundColor = '#' + this.rgb2hex(this.hsv2rgb( {'h':1-((y+3)/this.size), 's':1, 'v':1} ));
		}
		else return false;
		
		var hex = this.getHex();
		this.current.style.backgroundColor = '#' + hex;
		this.value.innerHTML = '#' + hex;
		this.onchange( this );
		return false;
	}
	
	this.sv.onmousedown = function() {
		that.mode = 'sv';
		document.onmousemove = function( event ) { that.move( event ) };
		document.onmouseup = that.release;
		return false;
	}
	
	this.h.onmousedown = function() {
		that.mode = 'h';
		document.onmousemove = function( event ) { that.move( event ) };
		document.onmouseup = that.release;
		return false;
	}
	
	
	this.getHSV = function() {
		var svpos = this.getObjPos( this.sv );
		var svselectpos = this.getObjPos( this.svselect );
		
		var hpos = this.getObjPos( this.h );
		var hselectpos = this.getObjPos( this.hselect );
		
		return {
			'h': 1 - (this.cap(hselectpos.y - hpos.y + 3, 0, this.size) / this.size),
			's': this.cap(svselectpos.x - svpos.x + 2, 0, this.size) / this.size,
			'v': 1 - (this.cap(svselectpos.y - svpos.y + 4, 0, this.size) / this.size)
		}
	}
	
	this.getRGB = function() {
		return this.hsv2rgb( this.getHSV() );
	}
	
	this.getHex = function() {
		return this.rgb2hex( this.getRGB() );
	}
	
	this.toHex = function(v) { v=Math.round(Math.min(Math.max(0,v),255)); return("0123456789ABCDEF".charAt((v-v%16)/16)+"0123456789ABCDEF".charAt(v%16)); }
	this.rgb2hex = function(c) { return this.toHex(c.r)+this.toHex(c.g)+this.toHex(c.b); }

	this.hsv2rgb = function( c ) {
		var R, B, G, H = c.h, S = c.s, V = c.v;
		if( S>0 ) {
			if(H>=1) H=0;

			H=6*H; F=H-Math.floor(H);
			A=Math.round(255*V*(1.0-S));
			B=Math.round(255*V*(1.0-(S*F)));
			C=Math.round(255*V*(1.0-(S*(1.0-F))));
			V=Math.round(255*V); 

			switch(Math.floor(H)) {
				case 0: R=V; G=C; B=A; break;
				case 1: R=B; G=V; B=A; break;
				case 2: R=A; G=V; B=C; break;
				case 3: R=A; G=B; B=V; break;
				case 4: R=C; G=A; B=V; break;
				case 5: R=V; G=A; B=B; break;
			}
			return {'r': (R?R:0), 'g': (G?G:0), 'b': (B?B:0)};
		}
		else {
			return {'r': (Math.round(V*255)), 'g': (Math.round(V*255)), 'b': (Math.round(V*255))};
		}
	}
}


// Adjust height of prev/next bars in IE ---------------------------------
var ieAdjustCount = 0;
function ieAdjustHeight( oldHeight ) {
	var pe = ($('viewer') ? $('viewer') : $('images'));
	if( 
		!document.all || // IE
		!( pe ) || 
		(
			pe.scrollHeight < oldHeight + 50 &&
			ieAdjustCount > 10
		)
	) {
		return;
	}
	ieAdjustCount++;
	var newHeight = pe.scrollHeight;
	$('prevBar').style.height = pe.scrollHeight + 'px';
	$('nextBar').style.height = pe.scrollHeight + 'px';
	setTimeout( 'ieAdjustHeight(' + newHeight + ')', 100 );
}


// JSON functions for tagging, rating, deleting... -----------------------
function post( url, params, callback ) {
	if (window.ActiveXObject) { // ie
		try {
			req = new ActiveXObject( 'Msxml2.XMLHTTP' );
		} 
		catch (e) {
			try {
				req = new ActiveXObject( 'Microsoft.XMLHTTP' );
			} 
			catch (e) {}
		}
	}
	else if (window.XMLHttpRequest) { // moz
		req = new XMLHttpRequest();
		req.overrideMimeType( 'text/plain' );
	}
	req.onreadystatechange = function(){ 
		if (req.readyState == 4 && req.status == 200) {
			callback();
		}
	};
	
	if( !req ) return false;
	req.open( 'POST', url, true );
	req.setRequestHeader( 'Content-type', 'application/x-www-form-urlencoded; charset=UTF-8' );
	req.setRequestHeader( 'Content-length', params.length );
	req.setRequestHeader( 'Connection', 'close' );
	req.send( params );
}

function addTags( imageId, inputField, admin ) {
	$( 'loadTags' ).style.display = 'block';
	var tags = encodeURIComponent(inputField.value);
	if( !admin ) {
		inputField.value = ''; 
	}
	post( 
		$('home').href + 'json.php?addTags', 
		'id='+imageId+'&tags='+tags, 
		function(){
			q = ( eval('('+req.responseText+')') );
			if( q.tags ) {
				$('tags').innerHTML = q.tags;
			}
			$('addTag').className='hidden';
			$( 'loadTags' ).style.display = 'none';
		}
	);
	return false;
}

function del( imageId ) {
	if( !confirm( 'Delete this image?' ) ) return false;
	$( 'loadDelete' ).style.display = 'block';
	post( 
		$('home').href + 'json.php?delete', 
		'id='+imageId, 
		function(){ 
			$( 'loadDelete' ).style.display = 'none';
			$( 'del' ).style.display = 'none';
		}
	);
	return false;
}

function delComment( commentId, e ) {
	if( !confirm( 'Delete this comment?' ) ) return false;
	var cdiv = e.parentNode.parentNode.parentNode;
	post( 
		$('home').href + 'json.php?deleteComment', 
		'id='+commentId, 
		function(){ 
			cdiv.style.display = 'none';
		}
	);
	return false;
}

// mouseover for rating stars
function sr( id, scale ) {
	$( id ).style.backgroundPosition = ( (20 * scale) - 100 ) + 'px 0';
}

function rate( imageId, score ) {
	$( 'loadRating' ).style.display = 'block';
	post( 
		$('home').href + 'json.php?rate', 
		'id='+imageId+'&score='+score, 
		function() {
			q = ( eval('('+req.responseText+')') );
			$( 'loadRating' ).style.display = 'none';
			$( 'currentRating' ).style.width = 20 * parseFloat(q.score) + 'px';
			$( 'ratingDescription' ).innerHTML = q.score + ' after ' + q.votes + ' Vote' + ( q.votes > 1 ? 's' : '' );
		}
	);
	return false;
}

function s() {
	var q = $( 'q' );
	if( q && q.value ) {
		window.location = $('home').href + 'search/' + encodeURI( q.value );
	}
	return false;
}
var nextImage = {};
var preload = null;
function quickTag( logId, imageId, inputField ) {
	var tags = encodeURIComponent(inputField.value);
	
	// swap nextImage
	$('imageLocation').href = nextImage['link'];
	$('imageLocation').innerHTML = nextImage['link'];
	$('imageId').value = nextImage['id'];
	$('logId').value = nextImage['logId'];
	$('image').src = nextImage['image'];
	$('tags').innerHTML = nextImage['tags'];
	$('tagText').value = '';
	$('tagText').focus();
	
	if( !nextImage['link'] ) {
		$('imageInfo').innerHTML = 'No more Images found';
	}
	
	post( 
		$('home').href + 'json.php?quickTag', 
		'logId='+logId+'&id='+imageId+'&tags='+tags, 
		function(){ 
			nextImage = ( eval('('+req.responseText+')') );
			preload = new Image(1,1)
			preload.src=nextImage['image'];
		}
	);
	return false;
}

function quickTagInit() {
	swap($('quickTagIntro'), 'article', 'hidden');
	swap($('viewer'), 'hidden', 'visible');
	post( 
		$('home').href + 'json.php?quickTag', 
		'',
		function(){ 
			nextImage = ( eval('('+req.responseText+')') );
			quickTag( 0, 0, $('tagText') );
		}
	);
	return false;
}
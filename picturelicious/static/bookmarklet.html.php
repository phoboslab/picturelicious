<h1>&raquo; Bookmarklet</h1>
<div class="article">
<h2><?php echo Config::$siteName ?> Remote Post Bookmarklet</h2>

<p>
	With the Remote Post Bookmarklet you can easily post images to <?php echo Config::$siteName ?> with one simple click. 
	Please note that you need a decent Browser, like  <a href="http://getfirefox.com/">Firefox</a> or 
	<a href="http://www.opera.com/">Opera</a>, or at least Microsofts IE7 for this to work.
</p>
<p>
	To use the bookmarklet just drag the following link somewhere to your bookmarks (or in Opera right click it
	and choose <em>Bookmark link</em>):
	&raquo; <a onclick="alert('Drag this Link to your Bookmarks!'); return false;" href="javascript:void((function(){var%20e=document.createElement('script');e.setAttribute('type','text/javascript');e.setAttribute('src','<?php echo Config::$frontendPath; ?>media/post.js.php');document.body.appendChild(e)})());">Post to <?php echo Config::$siteName ?></a> &laquo;
</p>

<p>
	Now, if you're on a website where you see an image you want to post, just click on your newly created bookmark 
	and then on the image you want to post. Thats all. Really!
</p>

</div>
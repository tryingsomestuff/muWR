# muWR
a micro web radio : simple, light, just need php on the server side

Just use jquery, bootstrap and typeahead.

A very simple html sample page is given but feel free to instanciate a muWR container in your own page.
```<div id="muWR"></div>
<script>var z = new muWR($('#muWR'));</script>```
is enough to do so.

# How to install muWR :
- ensure php is working on server side
- clone muWR on your server (probably near /var/www directory)
- change constants MP3_ROOT in muWRapi.php to fit your need
- ensure a directory named muWRtmp is present and writable by php in the directory where muWR was cloned
- try it with your browser !

When a new user access the muWR, a json database of your music collection is downloaded and a typeahead object is instanciate. This can be slow depending on your music collection size and your internet bandwidth. Anyway, this only appends once, at the session creation. So, be sure not to refresh the page (using F5 key) and use the "next" and "random" icon instead to switch between songs.

Enjoy!

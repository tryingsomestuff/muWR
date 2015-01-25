function muWR(c,nodb){


       nodb = typeof nodb !== 'undefined' ? nodb : false;

       console.log("nodb is " + nodb);

       // class members
       var warn;
       var muzikfile;
       var muzikindex;
       var dbfile;
       var dbloaded=false;
       var sid=-1;


       // html content of the muWR (often div) container
       var muWR_html = "   "
       +"<h2>Micro Web Radio</h2>" 
       +"<div id=muWR_audio>"
       +"    <audio id=\"muWR_player\" controls autoplay>"
       +"       <source id=\"muWR_src_ogg\"   src=\"\" type=\"audio/ogg\">"
       +"       <source id=\"muWR_src_mpeg\"  src=\"\" type=\"audio/mpeg\">"
       +"       <embed  id=\"muWR_src_embed\" src=\"\" height=\"50\" width=\"100\">"
       +"    </audio>"
       +"</div>"
       +"<p>"
       +"   <a href=\"#\"><img id=\"muWR_next\" width=48 src=\"images/next.png\"></a>"
       +"   <a href=\"#\"><img id=\"muWR_shuffle\" width=48 src=\"images/shuffle.png\"></a>"
       +"</p>"

       if ( nodb ){
          dbloaded=true;
       }
       else{
          muWR_html += "<p><input id=\"muWR_search\" class=\"typeahead\" type=\"text\" placeholder=\"Search for a song here !\"></p>"
       }
       muWR_html +=
                 "<p><div class=\"muWR_songtitle\" id=\"muWR_song\"></div></p>"
                 +"<p><div id=\"muWR_loading\"> ... Loading please wait ... </div></p>";

       // add the html muWR content to the given container
       c.append(muWR_html);

       // set source in audio tag
       function changeSource(){
          $('#muWR_src_ogg').attr('src', muzikfile);
          $('#muWR_src_mpeg').attr('src', muzikfile);
          $('#muWR_src_embed').attr('src', muzikfile);
       }

       // update audio tag with new source
       function setAudio(){
          $('#muWR_song').html(muzikname);
          var audio = $('#muWR_player');
          audio.trigger('pause');
          changeSource();
          console.log("Launching audio player");
          audio.trigger('load');
          audio.trigger('play'); 
       }

       // async call to api to get next song
       function getNew(param){
          if ( sid < 0 ){
             apiurl = "muWRapi.php?"+param;
          }
          else{
             apiurl = "muWRapi.php?"+sid+"&"+param;
          }
          console.log("Api call : " + apiurl);
          
          $('#muWR_loading').show();

          $.getJSON( apiurl, function( data ){
             warn = data['w'];
             muzikfile = data['m'];
             muzikindex = data['i'];
             muzikname = data['s'];
             dbfile = data['d'];
             sid = data['rs'];

             console.log("ri : " + data['ri']);
             console.log("rr : " + data['rr']);
             console.log("sid : " + sid);
             console.log("warn : " + warn);
             console.log("file : " + muzikfile);
             console.log("index : " + muzikindex);
             console.log("db : " + dbfile);

             if ( warn != "" ){
                $('#muWR_song').html("ERROR : " + warn);
                return;
             }

             $('#muWR_loading').hide(); 

             getDB(); // will call setAudio
          });
       }

       function getDB(){

          if (dbloaded){
             setAudio();
          }
          else{
             console.log("Downloading db");

             $('#muWR_loading').show();

             $.getJSON( dbfile , function( data ) {
                console.log("Loading db");
                $('#muWR_search').typeahead({ // this is slow
                    name: 'song',
                    local: data,
                    limit: 20,
                    minLength: 4
                });
                dbloaded=true
                // bind song selection
                $('#muWR_search').on('typeahead:selected', function(event, selection) {
                    i=data.indexOf(selection.value);
                    $('#muWR_search').typeahead('setQuery', '');
                    console.log("Requested song with id : " + i);
                    getNew("i="+i);
                });
                console.log("ok, db is loaded");

                $('#muWR_loading').hide(); 
                setAudio();
             });
          }
       }

       // bind some events
       $("#muWR_player").bind('ended', function(){
            getNew("r=0"); // force next one
       });

       $("#muWR_next").click(function(){
            getNew("r=0"); 
       }).hide().delay(1000).fadeIn(2200);

       $("#muWR_shuffle").click(function(){
            getNew("r=1"); 
       }).hide().delay(1000).fadeIn(2200);

       // when document is loaded, play random song
       $(function(){
          getNew(""); // will be randomized
       });
}

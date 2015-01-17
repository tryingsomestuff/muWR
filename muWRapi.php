<?php

   $warn="";   

   ///////// RAND GEN   /////////
   function make_seed(){
     list($usec, $sec) = explode(' ', microtime());
     return (float) $sec + ((float) $usec * 100000);
   }
   srand(make_seed());

   ///////// SESSION    /////////
   $id=session_id($_GET['PHPSESSID']);
   session_start();

   ///////// PARAMETERS /////////
   define('TMP_DIR', 'muWRtmp/');

   if ( ! is_writable(TMP_DIR) ){
      $warn=TMP_DIR . " is not present or writable. ";
      $json = array('w' => $warn, 
                    'm' => "", 
                    'i' => -1, 
                    'd' => "", 
                    's' => "", 
                    'ri' => -1, 
                    'rr' => -1, 
                    'rs' => SID);
      echo json_encode($json);
      return;
   }

   define('MP3_ROOT', '/mnt/hdd2/MP3/');
   define('FILE_INDEX', TMP_DIR . 'index.' . session_id() . '.id');
   define('JSON_INDEX', TMP_DIR . 'index.' . session_id() . '.json');

   ///////// UPDATE     /////////
   // check if still in use (shall not happend with session)
   exec('lsof ' . TMP_DIR . 'm.' . session_id() . '.mp3', $output, $ret);
   exec('lsof ' . TMP_DIR . 'm.' . session_id() . '.MP3', $output2, $ret2);

   if ( $ret == 0 || $ret2 == 0 ){ // file is used !
      $warn="Currently read by another ... wait for sync... ";
      $json = array('w' => $warn, 
                    'm' => "", 
                    'i' => -1, 
                    'd' => "", 
                    's' => "", 
                    'ri' => -1, 
                    'rr' => -1, 
                    'rs' => SID);
      echo json_encode($json);
      return;
   }
   else{
      // get the api params
      $dorandom=0;
      if(isset($_GET["r"])){
        $dorandom=htmlspecialchars($_GET["r"]);
        if ( $dorandom != 0 && $dorandom != 1 ){
           $warn="Bad r parameter. "; 
           $dorandom=0;
        }
        $dorandom=intval($dorandom);
      }
      $nodb=0;
      if(isset($_GET["d"])){
        $nodb=htmlspecialchars($_GET["d"]);
        if ( $nodb != 0 && $nodb != 1 ){
           $warn="Bad d parameter. "; 
           $nodb=0;
        }
        $nodb=intval($nodb);
      }
      $useri=0;
      if(isset($_GET["i"])){
        $useri=htmlspecialchars($_GET["i"]);
        if ( ! is_numeric($useri) ){
           $warn="Bad i parameter : " . $useri ; 
           $useri=0;
        }
        $useri=intval($useri);
      }
      
      // if index does not exist, create it
      if(!file_exists(FILE_INDEX)){
         // for some reason the sort is not working completly...
         // sed is use to reduce the json size 
         // removing MP3_ROOT from file names
         shell_exec('find ' . MP3_ROOT . ' -type f -iname "*.mp3" -o -iname "*.flac" | sort -fd | sed \'s@' . MP3_ROOT . '@@\' > ' . FILE_INDEX);
         if ( $nodb == 0 ){
            shell_exec('(echo "[" ; while read line ; do echo "\"$line\","; done < ' . FILE_INDEX  . ' ; echo "\"dummy_last\"" ; echo "]") > ' . JSON_INDEX);
            $jsonindex = "";
         }
         else{
            $jsonindex = JSON_INDEX;
         }
      }

      // read the file to fill an array
      $index = file(FILE_INDEX);
      
      // find something we can read
      $ext="";
      while ( $ext != "mp3" && $ext != "MP3" && $ext != "flac" && $ext != "FLAC" ){ // should be right anyway...
         if ( $useri != 0 ){ // if user forced $i
            $i = $useri;
         }
         else{
            if ( $dorandom == 1 ){ // if user force random
                $i = rand(0, count($index) - 1);
            }
            else{ // just get the next one
               if (isset($_SESSION['cur_index'])){ // if it exists
                  $i = intval($_SESSION['cur_index']);
                  $i += 1;
               }
               else{ // if not, randomize the initial $i
                  $i = rand(0, count($index) - 1);
               }
            }
         }
         // save the current $i
         $_SESSION['cur_index']=$i;
         
         // get the musik filename
         $filename=trim($index[$i]);
         // and its extension
         $ext = pathinfo($filename, PATHINFO_EXTENSION);
      } // while

      // remove all previous music file of this session
      shell_exec('rm -rf ' .TMP_DIR . 'm.' . session_id() . '.*');
      // create a tmp file in TMP_DIR 
      if ( $ext == "flac" || $ext == "FLAC" ){ // flac case, convert mp3
         // change $ext to use converted file
         $ext="mp3"; 
         shell_exec('ffmpeg -i "' . MP3_ROOT . "/" . $filename . '" ' . ' ' . TMP_DIR . 'm.' . session_id() . '.mp3');
      }
      else{ // mp3
         //shell_exec('ln -sf "' . $filename . '" ' . TMP_DIR . 'm.' . session_id() . '.' . $ext);
         shell_exec('cp "' . MP3_ROOT . "/" . $filename . '" ' . TMP_DIR . 'm.' . session_id() . '.' . $ext);
      }

      // update list of played song in played.txt
      //$file = TMP_DIR . "played.txt";
      //$fh = fopen($file, 'a') or die("can't open file");
      //$date = date('m/d/Y h:i:s a', time());
      //fwrite($fh, $date . " : " . $filename . "\n");
      //fclose($fh);

      $tobeplayed=TMP_DIR . "m." . session_id() . '.' . $ext . "?" . $i;

      $json = array('w' => $warn, 
                    'm' => $tobeplayed, 
                    'i' => $i, 
                    'd' => $jsonindex, 
                    's' => $filename, 
                    'ri' => $useri, 
                    'rr' => $dorandom, 
                    'rs' => SID);
      echo json_encode($json);

   } // ok file is not used
?>

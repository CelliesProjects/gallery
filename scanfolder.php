<?php
//include("debug.php");

  @$folder = htmlspecialchars($_GET["foldername"]);  
  ///////!!!!!! opletten user input hierboven
  if ( substr($folder, 0, 1 ) === '/' || strpos( $folder, '..' ) !== false  ) {
    echo 'no traversing allowed';
    die();
  }

  if ($folder <> '') { 
	  $folder = $folder.'/'; 
      echo '<p class="folderthumbnail" alt="'.$folder.'" title="'.$folder.'">..</p><script>addFolderToArray("'.$folder.'");</script>'; 
  }
foreach ( glob( $folder."*", GLOB_ONLYDIR)  as $foldername ) {
	echo '<p class="folderthumbnail" alt="'.$foldername.'" title="'.$foldername.'">'.$foldername.'</p><script>addFolderToArray("'.$foldername.'");</script>';
}
foreach ( glob( $folder."*.{jpg,JPG,gif,GIF,png,PNG}", GLOB_BRACE ) as $filename) {
    echo '<img class="thumbnail" src="getthumbV3.php?filename='.urlencode($filename).'" alt="'.$filename.'" title="'. substr( $filename, strrpos( $filename, '/' ) + 1 ). '"><script>addImageToArray("'.$filename.'");</script>';
}    
?><input type="button" class="zoomButtons" id="increaseThumb" value="Groter"></button>
<input type="button" class="zoomButtons" id="decreaseThumb" value="Kleiner"></button>
<script>$(".thumbnail, .folderthumbnail").height( thumbHeight );</script>


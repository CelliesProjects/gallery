<?php
//include('debug.php');

  $thumbnailSize = 80;   //height in pixels

  @$imageLocation = htmlspecialchars($_GET["filename"]);

  if (!$imageLocation) die('geen filename!');
  
  $foldername = substr($imageLocation,0,strrpos($imageLocation, '/'));
  $fileName = substr($imageLocation,strrpos($imageLocation, '/')+1);
  if ( $foldername ) {
    
    $thumb_folder = $foldername.'/.thumbs/';     // folder where the thumbs are stored
    //die( $foldername);
  }
  else
    $thumb_folder = '.thumbs/';                   
  if (!file_exists($thumb_folder)) {
    mkdir($thumb_folder);
    chmod($thumb_folder,0770); //this should be tighter in production
  }

  if (!is_writable($thumb_folder)) errorImage($thumb_folder,$thumbnailSize,"Path not writeable:");  //misschien verplaatsen? Naar daar waar ook geschreven word?
  
  $thumbNAAM = $thumb_folder. $fileName . '.tmb';      
  $image = new Imagick();
if (file_exists($thumbNAAM )) {
  $image->readImage( $thumbNAAM);
  }
else {
  $imageInfoArray = getimagesize($imageLocation);                                              //$imageInfoArray['mime'] bevat nu image mime-type voor header of NULL
  if ( $imageInfoArray == NULL ) errorImage($imageLocation,$thumbnailSize,'Error reading:');   
      
  $image->readImage( $imageLocation);
  if ($imageInfoArray['mime'] == 'image/gif')
    $image = resizeGif($image,$thumbNAAM,$thumbnailSize);
  else
    $image = resizeNormal($image,$thumbNAAM,$thumbnailSize);
  }
header('Content-Type: '. $imageInfoArray['mime'] );
echo ($image);
$image->clear(); 
$image->destroy();
die();  
  
function resizeNormal($normal_image,$url,$newsize){
  //resize alle formaten behalve gif
  $normal_image->resizeImage(0,$newsize,Imagick::FILTER_BOX,1);
  $normal_image->writeImage ($url); 
  die($normal_image); 
  return $normal_image;
}  

function resizeGif($gif_image,$url,$newgifsize){
  //resize gif images
  $gif_image = $gif_image->coalesceImages(); 
  do {
     $gif_image->resizeImage(0, $newgifsize, Imagick::FILTER_BOX, 1);
  } while ($gif_image->nextImage()); 

  $gif_image = $gif_image->deconstructImages(); 
  $gif_image->writeImages($url, true);    
  return $gif_image;
}

function errorImage($url,$errorimgSize,$errorStr) {
    $image = new Imagick();
    $draw = new ImagickDraw();
    $pixel = new ImagickPixel( 'gray' );
    $image->newImage($errorimgSize, $errorimgSize, $pixel);
    $draw->setFillColor('black');
    $draw->setFontSize( 11 );
    $image->annotateImage($draw, 7, 15, 0, $errorStr);
    $image->annotateImage($draw, 7, 25, 0, $url);
    $image->setImageFormat('jpg');
    header('Content-type: image/jpg');
    echo ($image);  
    $image->clear(); 
    $image->destroy();  
    die();
}

?>

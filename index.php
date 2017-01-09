<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<?php $galleryVersion = '3.0010'; ?>
<html>
<head>
<title>Gallery <?php echo $galleryVersion; ?></title>
<!-- ALS DE THUMBNAILS NIET WERKEN: http://wasietsmet.nl/hosting/maak-folder-writeable-in-php/ -->
<meta charset="UTF-8">
<!--<meta name="viewport" content="width=device-width, initial-scale=1">-->
<meta name=viewport content="width=device-width, initial-scale=1, maximum-scale=1">

<link rel="stylesheet" type="text/css" href="gallerystyle.css">
<script
  src="https://code.jquery.com/jquery-3.1.1.min.js"
  integrity="sha256-hVVnYaiADRTO2PzUGmuLJr8BLUSjGIZsDYGmIJLv2b8="
  crossorigin="anonymous">
</script>
<script src="https://code.jquery.com/mobile/1.4.5/jquery.mobile-1.4.5.min.js"></script>
<script src="exif.js"></script>
</head>
<body>
<script>
var imageArray = [];
var folderArray = [];

function addImageToArray(imageName) {
  imageArray.push(escape(imageName));
}  

function addFolderToArray(folderName) {
  folderArray.push(escape(folderName));
}  
        
</script>
<div id="previewport">
<input type="button" class="zoomButtons" id="increaseThumb" value="Groter"></button>
<input type="button" class="zoomButtons" id="decreaseThumb" value="Kleiner"></button>
</div>
<div id="viewport">
<img id="ajax-loader" src="/ajax-loader.gif"/>
</div>
<button type="button" class="navbutton" id="backbutton"><<</button>
<button type="button" class="navbutton" id="forwardbutton">>></button>
<!--</div>-->
<div id="imageSetupscreen">
    <div id="setupmenu">
        <p id="saveButton"></p>
        <p id="setImageInfo">Show image information.</p>
    </div>
</div>
<div id="thumbViewSetupScreen">
    <div id="setupmenu">
        <p id="startslideShow">Start a slideshow.</p>
        <p id="setZoomButtons">Hide zoom buttons.</p>
        <p id="aboutButton">About Gallery.</p>
    </div>  
</div>
<div id="aboutScreen">
    <div id="setupmenu">
        <p>Gallery by Cellie.<br>
        Version: <?php echo $galleryVersion; ?>.<br>
        <p id="totalImages"></p>
    </div>
</div>
<script>

function is_touch_device() {
  return 'ontouchstart' in window // works on most browsers 
      || 'onmsgesturechange' in window; // works on ie10    ------ dus niet op 7
}

function showNavButtons(){
    if ( currentImage == 0 ) { $('#backbutton').hide(); }
    else { $('#backbutton').show(); }
    if ( imageArray[currentImage+1] === undefined) { $('#forwardbutton').hide(); }
    else { $('#forwardbutton').show(); }
    $( ".navbutton" ).prop( "disabled", false );
    
}

var fullScreenImage;

function loadFullScreenImage(filename){
    $('#errorBox').remove();
    $('#viewport').append( '<img id="ajax-loader" src="/ajax-loader.gif"/>' );
    fullScreenImage = new Image();
        
    fullScreenImage.onload = function() { 
        $('#viewport').html( fullScreenImage );
        $('#ajax-loader').hide();   
        showNavButtons();
        showImageInfo( fullScreenImage ); // + '<img id="ajax-loader" src="/ajax-loader.gif"/>');
    };
    fullScreenImage.onerror = function(){
        $('#ajax-loader').hide();
        $('#viewport').append('<p id="errorBox">Error reading: "'+filename+'".</p>');
        showNavButtons();
    };   
    fullScreenImage.src = filename;        
}

//https://github.com/exif-js/exif-js/blob/master/example/example2.html

function showImageInfo( img ){   
    var exifString = '';
    EXIF.getData( img , function() {
      if ( EXIF.getTag( this, "DateTime" ) ) {
        exifString += 'Date: <span class="exifTag">' + EXIF.getTag( this, "DateTime" ) + '</span><br>';
      }
      if ( EXIF.getTag( this, "PixelXDimension" ) ) {
        exifString += 'Dimensions: <span class="exifTag">' + EXIF.getTag(this, "PixelXDimension");      
        
        exifString += ' x ' + EXIF.getTag(this, "PixelYDimension") + '</span><br>';
      }
      if ( EXIF.getTag( this, "Make" ) ) {
        exifString += 'Make: <span class="exifTag">' + EXIF.getTag(this, "Make") + '</span><br>';
      }
      if ( EXIF.getTag( this, "Model" ) ) {
        exifString += 'Model: <span class="exifTag">' + EXIF.getTag(this, "Model") + '</span><br>';
      }

      if ( EXIF.getTag( this, "ExposureTime" ) ) {
        var exposureStr = "1/" + String( 1 / EXIF.getTag( this, "ExposureTime" ) );      
        if ( exposureStr.indexOf('.') > 0 ) {
          exposureStr = exposureStr.substring(0, exposureStr.indexOf('.') );
        }
        exifString += 'Exposure: <span class="exifTag">' + exposureStr + ' sec</span><br>';
      }              
      
      if ( EXIF.getTag( this, "FocalLength" ) ) {
        var focalLength = EXIF.getTag( this, "FocalLength" );
        exifString += 'Focal Length: <span class="exifTag">' + focalLength + ' mm</span><br>';
      }

      if ( EXIF.getTag( this, "FNumber" ) ) {
        exifString += 'F-stop: <span class="exifTag">f/' + EXIF.getTag( this, "FNumber" ) + '</span><br>';
      }
      
      if ( imageInfo ) {  
        //$('#imageInfobox').hide();        
        if ( exifString != '') { exifString = '<br><br>EXIF image data found:<br>' + exifString; }
        $('#viewport').append('<p id="imageInfobox">' + unescape(imageArray[currentImage]) + exifString + '</p>');
      }
    });    
}
    
    
var imageInfo = false;
var thumbHeight = 100; //px

$(document).ready(function(){ 
    
    var showZoombuttons  = true;
    var savedScrollPos   = 0;
    var currentFolder    = "";    

    //jQuery starts here
    
    $(".thumbnail").height( thumbHeight );

    //if (is_touch_device()) alert('Touch!');

    $('#viewport').hide();
    $('.navbutton').hide();
    $('#imageSetupscreen').hide();
    
    console.log('Entering multiview');    
    
    $('#previewport').load( 'scanfolder.php' );
    
    $( '#setImageInfo' ).on( 'click', function() {
        if ( imageInfo ) { 
            imageInfo = false;
            $( '#imageInfobox' ).hide(); 
            $( '#setImageInfo' ).html( 'Show image information' ); 
        }
        else  {
            imageInfo = true;
            showImageInfo(fullScreenImage);
            $('#setImageInfo').html('Hide image information');
        }
    });

    $("body").on("click","#decreaseThumb", function() {
        if ( thumbHeight > 60) {
            thumbHeight -= 10;
            $(".thumbnail, .folderthumbnail").height( thumbHeight );
        }
    });    
    
    $("body").on('click', "#increaseThumb", function() {
        if ( thumbHeight < 200) {
            thumbHeight += 10;
            $(".thumbnail, .folderthumbnail").height( thumbHeight );
        }
    });

    $('#previewport').on('click', '.folderthumbnail', function(){       
        console.log('Folder ' + $(this).html() + ' has been clicked.');
        folderArray = [];
        if ($(this).html() == '..' ) {
                currentFolder = currentFolder.substr(0,currentFolder.lastIndexOf('/'));             
            }
        else {
                currentFolder =  $(this).html();
            };
        imageArray = [];
        $('#previewport').load( 'scanfolder.php?foldername=' + escape( currentFolder ), function() {
          if ( !showZoombuttons ) {
            $('.zoomButtons').hide();
          }
          console.log('Current folder is changed to: ' + currentFolder);
        });                
    });

    $('#previewport').on('click', '.thumbnail', function(){         //maybe disable navbuttons here?        
        currentImage = $.inArray( escape($(this).attr("alt") ) , imageArray );
        savedScrollPos = $(window).scrollTop(); 
        //delete the old image
        //$('#imageInfobox').hide();
        loadFullScreenImage( imageArray[currentImage] ); 
        $('#previewport').hide();
        $('#viewport').show();   
        if (!is_touch_device()) $('.navbutton').show();
        $('#messagebutton').hide();
        console.log("Entering fullscreen view");
    });
    
    $('#viewport').on('swipeleft',function() {
        $('#forwardbutton').click();
    });
    
    $('#viewport').on('swiperight',function() {
        $('#backbutton').click();
    });
    
    $('#forwardbutton').on('click',function( e ) {
        $( ".navbutton" ).prop( "disabled", true );     
        if ( imageArray[ currentImage + 1 ] === undefined ) {   
            // reached the last image so no action
            return;
        } else {
            currentImage++;
            loadFullScreenImage( imageArray[currentImage] ); 
        }
    });  
    
    $('#backbutton').on('click',function( e ){ 
        $( ".navbutton" ).prop( "disabled", true );
        if (currentImage > 0 ) {
            currentImage--;
            loadFullScreenImage( imageArray[currentImage] )
        }
    });   
    
    $('body').on('click','#viewport',function( e ){
        $('#viewport').hide();
        $('#viewport img').remove(); 
        $('#previewport').show();               
        $(window).scrollTop(savedScrollPos); //herstel de scrollpositie
        console.log('Back to multiview');
        $('.navbutton').hide();
        $('#imageInfobox').hide();

    });

    $('#viewport').on('contextmenu taphold', function( e ){   
        e.preventDefault();
        $('.navbutton').hide();
        $('#imageSetupscreen').show();
        $('#saveButton').html('<a id="downloadURL" href="'+imageArray[currentImage]+'" download="">Download image.</a>');
        console.log('Downloadlink for '+imageArray[currentImage]+ ' generated.');       
    });

    $('#imageSetupscreen').on('click',function(){
        $('#imageSetupscreen').hide();
        if (!is_touch_device()) $('.navbutton').show();
        $('#messagebutton').hide();
        showNavButtons();
    });
    
    
    
    
    $('#previewport').on('contextmenu',function( e ){
        e.preventDefault();
        $('.navbutton').hide();
        //$('body').addClass('stop-scrolling');
        $('#thumbViewSetupScreen').show();
    });









    $('#thumbViewSetupScreen').on('click',function(){
        $('#thumbViewSetupScreen').hide();
        $('#messagebutton').hide();
    });
    
    $('#startslideShow').on('click',function(){
        alert('Maybe in the next version...');
    });

    $('#setZoomButtons').on('click',function(){
        if (showZoombuttons) {
            showZoombuttons = false;
            $('.zoomButtons').hide();
            $('#setZoomButtons').html('Show zoom buttons.'); 
        } else {
            showZoombuttons = true;         
            $('.zoomButtons').show();
            $('#setZoomButtons').html('Hide zoom buttons.'); 
        }   
    });
    
    $('#aboutButton').on('click',function(){        
        $('#totalImages').html(imageArray.length+' images found.');
        $('#aboutScreen').show();
    });
    
    $('#aboutScreen').on('click',function(){
        $('#aboutScreen').hide();
    }); 
});
</script>
</body>
</html>

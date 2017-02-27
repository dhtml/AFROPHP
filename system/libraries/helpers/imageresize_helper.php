<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 *  I M A G E  R E S I ZE
 * 
 *  a PHP 5 Image Resize Library
 * 
 *  For more informations: {@link https://github.com/dhtml/image_resize}
 *  
 *  @author Anthony Ogundipe
 *  @e-mail: diltony@yahoo.com
 *  @copyright Copyright (c) 2014 Anthony Ogundipe
 *  @license http://opensource.org/licenses/mit-license.php The MIT License
 *  @package image_resize
 */

class IMAGE_RESIZE {

function __construct($opath=null,$options=null) {

if(!is_null($opath)) {return $this->init($opath,$options);}

return true;
}

function init($opath,$options) {
$i = $this->imagecreatefromfile($opath);

//create thumbnail for each directive
foreach($options as $opt) {
$opt['fit']= isset($opt['fit']) ? $opt['fit'] : false;	
$im = $this->thumbnail_box($i, $opt['w'],$opt['h'],$opt['fit']);
$this->imagetotype($im,$opt['path'],$opt['q']);
imagedestroy($im);
}

imagedestroy($i);
return true;	
}

/*
Create image from any url
*/
function imagecreatefromfile( $filename ) {
    if (!file_exists($filename)) {
        throw new InvalidArgumentException('File "'.$filename.'" not found.');
    }
    switch ( strtolower( pathinfo( $filename, PATHINFO_EXTENSION ))) {
        case 'jpeg':
        case 'jpg':
            return @imagecreatefromjpeg($filename);
        break;

        case 'png':
            return @imagecreatefrompng($filename);
        break;

        case 'gif':
            return @imagecreatefromgif($filename);
        break;

        default:
            throw new InvalidArgumentException('File "'.$filename.'" is not valid jpg, png or gif image.');
        break;
    }
}

function imagetotype($im,$filename,$quality=90) {
   switch ( strtolower( pathinfo( $filename, PATHINFO_EXTENSION ))) {
        case 'jpeg':
        case 'jpg':
            return imagejpeg($im,$filename,$quality);
        break;

        case 'png':
		if($quality>9) {$quality=9;}
            return imagepng($im,$filename,$quality);
        break;

        case 'gif':
            return imagegif($im,$filename,$quality);
        break;

        default:
            throw new InvalidArgumentException('File "'.$filename.'" is not valid jpg, png or gif image.');
        break;
    }
}

function thumbnail_fit($src, $thumbSize) {
 /* Determine the Image Dimensions */
  $width = imagesx( $src );
  $height = imagesy( $src );

 // calculating the part of the image to use for thumbnail
    if ($width > $height) {
        $y = 0;
        $x = ($width - $height) / 2;
        $smallestSide = $height;
    } else {
        $x = 0;
        $y = ($height - $width) / 2;
        $smallestSide = $width;
    }


 /* Create the New Image */
  $new = imagecreatetruecolor( $thumbSize , $thumbSize );
 /* Transcribe the Source Image into the New (Square) Image */
 if(!imagecopyresampled( $new , $src , 0, 0, $x, $y, $thumbSize, $thumbSize, $smallestSide, $smallestSide))
 {
        //copy failed
        imagedestroy($new);
        return null;
 }
 
 return $new;
}

function thumbnail_box($img, $box_w, $box_h,$fit=false) {
	if($fit || ($box_w==$box_h)) {return $this->thumbnail_fit($img, $box_w);}
	
    //create the image, of the required size
    $new = imagecreatetruecolor($box_w, $box_h);
    if($new === false) {
        //creation failed -- probably not enough memory
        return null;
    }

    $fill = imagecolorallocate($new, 255, 255, 255);
    imagefill($new, 0, 0, $fill);

    //compute resize ratio
    $hratio = $box_h / imagesy($img);
    $wratio = $box_w / imagesx($img);
    $ratio = min($hratio, $wratio);

    //if the source is smaller than the thumbnail size, 
    //don't resize -- add a margin instead
    //(that is, dont magnify images)
    if($ratio > 1.0)
        $ratio = 1.0;

    //compute sizes
    $sy = floor(imagesy($img) * $ratio);
    $sx = floor(imagesx($img) * $ratio);

    $m_y = floor(($box_h - $sy) / 2);
    $m_x = floor(($box_w - $sx) / 2);

    //Copy the image data, and resample
    if(!imagecopyresampled($new, $img,
        $m_x, $m_y, //dest x, y (margins)
        0, 0, //src x, y (0,0 means top left)
        $sx, $sy,//dest w, h (resample to this size (computed above)
        imagesx($img), imagesy($img)) //src w, h (the full size of the original)
    ) {
        //copy failed
        imagedestroy($new);
        return null;
    }
    //copy successful
    return $new;
}

/*
Generates a thumbnail resource
*/
function create_thumb_nail($opath,$w=100,$h=100,$fit=true) {
	$i = $this->imagecreatefromfile($opath);
	$im = $this->thumbnail_box($i, $w,$h,$fit);
	return $im;
}


}
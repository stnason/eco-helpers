<?php

namespace ScottNason\EcoHelpers\Classes;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use function App\Classes\base_path;
use function App\Classes\storage_path;

class ehCaptcha
{

    //TODO: if we keep both image fetch and back-end check together here then
    // it needs to be renamed to something that makes more sense.
    // Still using UnderCover to work out the kinks on this.

    public static function captchaImage(Request $request) {


        // Are we submitting the user input for validation?
        // Note: using isset() instead of empty() to catch a submit with not user entry.
        if (isset($request->eh_captcha_input)) {

            if (empty($request->input('eh_captcha_input'))) {
                return ['status'=>'Enter what you see in the blue box.'];
            }

            // other check the input
            return ['status'=>'you entered: '.$request->input('eh_captcha_input')];

        }


        // No input $request is present then we'll just assume this is an image fetch request.

        // https://www.youtube.com/watch?v=vqhIS9GoKJU

        $width = 180;
        $height = 70;
        //$text_x = 12;
        $text_x = $width/4;         // Calculation for centering.
        $text_y = 50;

        //$text = rand(10000,99999);
        $text = self::random_strings(5);

        //TODO: if this was included in eco-helpers could this just be a link to the font file in the package folder (and maybe provide a key in eco-helpers config file to change as desired -- maybe even some of the other constants?)
        $fontfile = storage_path('app/fonts/OpenSans-Medium.ttf');

        // Warping constants:
        $text_angle = rand (-6,10);    // Initial text angle before warping.
        $divide_by = rand(7,11);       // Frequency of warp sine wave.
        $multiply_by = rand(6,9);      // Size of the vertical warp.


        // Original image prior to warping - using just a box with or w/o color.
        $orig_image = imagecreatetruecolor($width, $height);         // Create an image resource

        /* Original image prior to warping - using a jpeg image of your choice.
        $orig_image = imagecreatefromjpeg($imageFileName);           // Create an image resource
        $width = imagesx($orig_image);
        $height = imagesy($orig_image);
        */

        $orig_image_bg_color = imagecolorallocate($orig_image, 255, 0, 255);      // Create a color for use in imagefill()
        $orig_image_fg_color = imagecolorallocate($orig_image, 0, 0, 0);          // Text color
        imagefill($orig_image, 0, 0, $orig_image_bg_color);
        imagettftext($orig_image, 25, $text_angle, $text_x, $text_y, $orig_image_fg_color, $fontfile, $text);


        // Image after warping.
        $warp_image = imagecreatetruecolor($width, $height);         // Create an image resource
        $warp_image_bg_color = imagecolorallocate($warp_image, 255, 0, 255);      // Create a color for use in imagefill()
        $warp_image_fg_color = imagecolorallocate($warp_image, 0, 0, 0);          // Text color
        imagefill($warp_image, 0, 0, $warp_image_bg_color);



        // Loop the original image x-axis.
        for ($x = 0; $x < $width; $x++) {
            // Loop the original image y-axis.
            for ($y = 0; $y < $height; $y++) {
                // Get the color of the pixel we're currently on.
                $index = imagecolorat($orig_image, $x, $y);
                $color_rgb = imagecolorsforindex($orig_image, $index);

                // Set the color to be written to the output (warped) image.
                $color = imagecolorallocate($warp_image, $color_rgb['red'], $color_rgb['green'], $color_rgb['blue']);
                //imagesetpixel($warp_image, $x, $y, $color);     // Paste pixel to the output image - 1:1 w/no change

                // Modify the pixel position before pasting.
                $imageShiftX = $x;
                $imageShiftY = $y + sin($x /$divide_by) * $multiply_by;
                imagesetpixel($warp_image, $imageShiftX, $imageShiftY, $color);     // Paste pixel to the output image - modified

            }

        }


        // Prepare for returning image.
        //header('Content-type: image/jpeg');                  // Set the content type for the return

        //imagejpeg($warp_image);                              // return the image to the browser


        // Save to a tmp file and base64 encode before returning.
        $tmp_file = base_path().'/storage/app/temp/tmp-captcha.jpg';
        Storage::disk('temp')->delete($tmp_file);
        imagejpeg($warp_image, $tmp_file);
        $data = file_get_contents($tmp_file);
        $type = 'jpeg';
        // Note this is the whole src attribute including the data:image portion of the show
        // so no need to put it on the other end. (src="data:image/jpeg;, <--base64 data-->"
        echo 'data:image/' . $type . ';base64,' . base64_encode($data);


        imagedestroy($orig_image);                                   // Remove the image from memory
        imagedestroy($warp_image);                                   // Remove the image from memory

    }


    // Function to generate a random string of specified length
    protected static function random_strings($length_of_string) {
        //$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        // Leave some of the confusing things like l, I, 1, 0, o, O, n, h
        // Plus adding the additional sets of numbers to weight them heavier in the results
        $characters = '123456789abcdefgijkmpqrstuvwxyz123456789ABCDEFGHJKLMNPQRSTUVWXYZ123456789';
        $characters_length = strlen($characters);
        $random_string = '';

        // Generate random characters until the string reaches desired length
        for ($i = 0; $i < $length_of_string; $i++) {
            $random_index = random_int(0, $characters_length - 1);
            $random_string .= $characters[$random_index];
        }

        return $random_string;
    }


}

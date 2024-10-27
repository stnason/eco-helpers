<?php

namespace ScottNason\EcoHelpers\Classes;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;


/**
 *
 *
 *
 * Example of back-end validation rule:
 *      // Final back-end check on the captcha just to make sure nothing changed since the front-end check.
 *      'eh_captcha_input'=>[ function (string $attribute, mixed $value, \Closure $fail) {
 *          if (!ehCaptcha::captcha(Request())['status']) {
 *              $fail('Oops. Captcha input does not match the image.');
 *          }
 *      }
 *      ]
 */
class ehCaptcha
{
    /**
     * Performs to functions:
     *      - 1) if a $request->eh_captcha_input is present, then we're processing/ validating user input.
     *      - 2) if no $request->eh_captcha_input, then we assume this is a call to create the image.
     *
     * Just a note to self: we could re-tool this to pass the ehConfig::get('captcha') as a parameter
     *  and basically remove the dependency on ehConfig. Opening this up to use elsewhere.
     *
     * @param Request $request
     * @return string[]|true[]|void
     * @throws \Exception
     */
    public static function captcha(Request $request) {

        // https://www.youtube.com/watch?v=vqhIS9GoKJU

        ////////////////////////////////////////////////////////////////////////////////////////////
        // 1) Are we submitting the user input for validation?
        // Note: using isset() instead of empty() to catch a submit with no user entry.
        ////////////////////////////////////////////////////////////////////////////////////////////
        if (isset($request->eh_captcha_input)) {

            if (empty($request->input('eh_captcha_input'))) {
                return ['status'=>'Enter what you see in the blue box.'];
            }

            // TESTING: return the input so we can see what's happening in dev tools (network)
            // return ['status'=>'you entered: '.$request->input('eh_captcha_input').' Should be: '.session('eh-captcha-value')];

            // Check the user's input (force both the captcha value and the user input to upper case for simplicity of entry.)
            if (strtoupper($request->input('eh_captcha_input')) <> strtoupper(session('eh-captcha-value'))) {
                return ['status'=>'Sorry. Wrong captcha. Please enter the number on the Captcha image below and try again.'];
            } else {
                return ['status'=>true];
            }

        }

        ////////////////////////////////////////////////////////////////////////////////////////////
        // 2) Since no input $request->eh_captcha_inputis present
        //  then we'll proceed with an image fetch request.
        ////////////////////////////////////////////////////////////////////////////////////////////


        ////////////////////////////////////////////////////////////////////////////////////////////
        // CONFIGURATION VARIABLES.
        // WARPING
        // Text angle before warping.
        // Warping constants:
        /*
        $text_angle = rand (-6,10);    // Initial text angle before warping.
        $divide_by = rand(7,11);       // Frequency of warp sine wave.
        $multiply_by = rand(6,9);      // Size of the vertical warp.
        */
        // Note: these are stored as arrays in the config file: [nn, nn]
        $text_angle = rand(ehConfig::get('captcha.text_angle')[0],ehConfig::get('captcha.text_angle')[1]);
        // Frequency of warp sine wave (horizontal)).
        $divide_by = rand(ehConfig::get('captcha.waviness_x')[0],ehConfig::get('captcha.waviness_x')[1]);
        // Size (amplitude) of the vertical warp.
        $multiply_by = rand(ehConfig::get('captcha.waviness_y')[0], ehConfig::get('captcha.waviness_y')[1]);

        // IMAGE SIZE
        $width = ehConfig::get('captcha.image_width');
        $height = ehConfig::get('captcha.image_height');
        $bg_color = ehConfig::get('captcha.background_color');
        $text_color = ehConfig::get('captcha.text_color');

        // TEXT POSITION
        $text_x = $width/4;         // Calculation for centering.
        $text_y = 50;               // TODO: Can we come up with a center calculation like the width has?

        ////////////////////////////////////////////////////////////////////////////////////////////
        // FONT INFO
        // $fontfile = storage_path('app/fonts/OpenSans-Medium.ttf');
        $font_storage = ehConfig::get('captcha.font_storage');
        $font_file = ehConfig::get('captcha.font_file');
        $font_size = ehConfig::get('captcha.font_size');
        $font_full_pathname = Storage::disk('fonts')->path($font_file);

        // TEMP STORAGE
        // Get the eco-helpers configured temp storage disk name.
        $temp_storage = ehConfig::get('captcha.temp_storage');


        ////////////////////////////////////////////////////////////////////////////////////////////
        // CAPTCHA TEXT
        // Create the text based on the config setting for numbers_only
        if (ehConfig::get('captcha.numbers_only')) {
            // For a captcha with numbers only.
            //$text = rand(10000,99999);  // 5 digits fixed

            // Convert the number of digits into numbers we can use in the rand() function.
            $from_digits = pow(10,ehConfig::get('captcha.character_length')-1);
            $to_digits = pow(10,ehConfig::get('captcha.character_length'))-1;

            // create a 10 with the number of exponents , and 10 with 1 higher (minus -1) exponent
            $text = rand($from_digits,$to_digits);
        } else {
            // For a captcha with numbers and letters.
            $text = self::random_strings(ehConfig::get('captcha.character_length'));
        }



        ////////////////////////////////////////////////////////////////////////////////////////////
        // CREATE THE IMAGE.
        // Image from a text box.
        // Original image prior to warping - using just a box with or w/o color.
        $orig_image = imagecreatetruecolor($width, $height);         // Create an image resource

        // Image from a jpeg image as the background.
        /* Original image prior to warping - using a jpeg image of your choice.
        $orig_image = imagecreatefromjpeg($imageFileName);           // Create an image resource
        $width = imagesx($orig_image);
        $height = imagesy($orig_image);
        */

        // The "before" warping image.
        $orig_image_bg_color = imagecolorallocate($orig_image, $bg_color[0], $bg_color[1], $bg_color[2]);       // Create a color for use in imagefill()
        $orig_image_fg_color = imagecolorallocate($orig_image, $text_color[0], $text_color[1], $text_color[2]); // Text color
        imagefill($orig_image, 0, 0, $orig_image_bg_color);
        imagettftext($orig_image, $font_size, $text_angle, $text_x, $text_y, $orig_image_fg_color, $font_full_pathname, $text);

        // The container image for "after" warping.
        $warp_image = imagecreatetruecolor($width, $height);         // Create an image resource
        $warp_image_bg_color = imagecolorallocate($warp_image, $bg_color[0], $bg_color[1], $bg_color[2]);       // Create a color for use in imagefill()
        $warp_image_fg_color = imagecolorallocate($warp_image, $text_color[0], $text_color[1], $text_color[2]); // Text color
        imagefill($warp_image, 0, 0, $warp_image_bg_color);


        ////////////////////////////////////////////////////////////////////////////////////////////
        // CREATE THE WARPED IMAGE
        // Loop all pixels in the original image x-axis.
        for ($x = 0; $x < $width; $x++) {
            // Loop all pixels in the original image y-axis.
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


        // Prepare for returning image. (when doing a direct return to browser on the captcha() call.
        // header('Content-type: image/jpeg');                  // Set the content type for the return
        // imagejpeg($warp_image);                              // return the image to the browser


        ////////////////////////////////////////////////////////////////////////////////////////////
        // TEMP FILE
        // Save to a tmp file and base64 encode before returning.
        //$tmp_file = base_path().'/storage/app/temp/tmp-captcha.jpg';
        $tmp_filename = 'tmp-captcha.jpg';  // Start out with just the file name.
        // We'll add the full path after managing the laravel Storage.

        // Delete the file before creating a new one (it's just a tmp file anyway).
        Storage::disk('temp')->delete($tmp_filename);

        // Convert the $tmp_filename into a full filename + full path name for use in the raw php below.
        $tmp_full_pathname = Storage::disk('fonts')->path($tmp_filename);

        imagejpeg($warp_image, $tmp_full_pathname);
        $data = file_get_contents($tmp_full_pathname);
        $type = 'jpeg';
        // Note this is the whole src attribute including the "data:image/jpeg;" portion of the show
        // so no need to put this on the other end: (src="data:image/jpeg;, <--base64 data-->"

        // Return the image file and save the value to the session.
        session(['eh-captcha-value' => $text]);
        echo 'data:image/' . $type . ';base64,' . base64_encode($data);

        imagedestroy($orig_image);          // Remove the image from memory
        imagedestroy($warp_image);          // Remove the image from memory

    }


    /**
     * Internal function to generate a random string of specified length.
     * $characters can be populated to control what the pool of possible characters to pull from.
     *
     * @param $length_of_string
     * @return string
     * @throws \Random\RandomException
     */
    protected static function random_strings($length_of_string) {

        // All letters and numbers
        // $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        // Leaving out some of the confusing things like l, I, 1, 0, o, O, n, h
        // Plus adding the additional set of numbers to weight them heavier in the results
        $characters = '123456789abcdefgijkmpqrstuvwxyz123456789ABCDEFGHJKLMNPQRSTUVWXYZ123456789';
        $characters_length = strlen($characters);
        $random_string = '';

        // Generate random characters until the string reaches the desired length
        for ($i = 0; $i < $length_of_string; $i++) {
            $random_index = random_int(0, $characters_length - 1);
            $random_string .= $characters[$random_index];
        }

        return $random_string;
    }


}

<?php

/*
 * BitsyBay Engine
 * A simple PHP CAPTCHA script
 *
 * The MIT License (MIT)
 *
 * Copyright (c) 2015 The BitsyBay Project (http://bitsybay.com)
 * Copyright 2013 by Cory LaViska for A Beautiful Site, LLC
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:

 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.

 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

final class Captcha {

    private $_config = array(
                'code'  => false,
                'min_length' => 4,
                'max_length' => 4,
                'backgrounds' => array(
                    '/backgrounds/45-degree-fabric.png',
                    '/backgrounds/cloth-alike.png',
                    '/backgrounds/grey-sandbag.png',
                    '/backgrounds/kinda-jean.png',
                    '/backgrounds/polyester-lite.png',
                    '/backgrounds/stitched-wool.png',
                    '/backgrounds/white-carbon.png',
                    '/backgrounds/white-wave.png'
                ),
                'fonts' => array(
                    '/fonts/kingthings_willow.ttf'
                ),
                'characters' => 'ABCDEKLPSTUVZ',
                'min_font_size' => 24,
                'max_font_size' => 32,
                'color' => '#666',
                'angle_min' => 0,
                'angle_max' => 7,
                'shadow' => true,
                'shadow_color' => '#fff',
                'shadow_offset_x' => -1,
                'shadow_offset_y' => 1
            );

    public function __construct(array $config = array()) {

        foreach ($config as $key => $value) {
            if (isset($this->_config[$key])) {
                $this->_config[$key] = $value;
            }
        }

        // Generate CAPTCHA code if not set by user
        if (empty($this->_config['code'])) {
            $this->_config['code'] = false;
            $length = rand($this->_config['min_length'], $this->_config['max_length']);
            while (strlen($this->_config['code']) < $length) {
                $this->_config['code'] .= substr($this->_config['characters'], rand() % (strlen($this->_config['characters'])), 1);
            }
        }
    }

    /*
     * Get code
     */
    public function getCode() {
        return $this->_config['code'];
    }

    /**
    * Get image
    *
    * @param string $code Custom image code
    */
    public function getImage($code) {

        // Pick random background, get info, and start captcha
        $background = dirname(__FILE__) . $this->_config['backgrounds'][rand(0, count($this->_config['backgrounds']) - 1)];
        list($bg_width, $bg_height) = getimagesize($background);

        $captcha = imagecreatefrompng($background);

        $color = $this->_hex2rgb($this->_config['color']);
        $color = imagecolorallocate($captcha, $color['r'], $color['g'], $color['b']);

        // Determine text angle
        $angle = rand($this->_config['angle_min'], $this->_config['angle_max']) * (rand(0, 1) == 1 ? -1 : 1);

        // Select font randomly
        $font = dirname(__FILE__) . $this->_config['fonts'][rand(0, count($this->_config['fonts']) - 1)];

        //Set the font size.
        $font_size = rand($this->_config['min_font_size'], $this->_config['max_font_size']);
        $text_box_size = imagettfbbox($font_size, $angle, $font, $code);

        // Determine text position
        $box_width = abs($text_box_size[6] - $text_box_size[2]);
        $box_height = abs($text_box_size[5] - $text_box_size[1]);
        $text_pos_x_min = 0;
        $text_pos_x_max = $bg_width - $box_width;
        $text_pos_x = rand($text_pos_x_min, $text_pos_x_max);
        $text_pos_y_min = $box_height;
        $text_pos_y_max = $bg_height - ($box_height / 5);
        $text_pos_y = rand($text_pos_y_min, $text_pos_y_max);

        // Draw shadow
        if ($this->_config['shadow']) {
            $shadow_color = $this->_hex2rgb($this->_config['shadow_color']);
            $shadow_color = imagecolorallocate($captcha, $shadow_color['r'], $shadow_color['g'], $shadow_color['b']);
            imagettftext($captcha, $font_size, $angle, $text_pos_x + $this->_config['shadow_offset_x'], $text_pos_y + $this->_config['shadow_offset_y'], $shadow_color, $font, $code);
        }

        // Draw text
        imagettftext($captcha, $font_size, $angle, $text_pos_x, $text_pos_y, $color, $font, $code);

        // Output image
        header('Content-type: image/png');
        imagepng($captcha);
    }

    private function _hex2rgb($hex_str, $return_string = false, $separator = ',') {
        $hex_str = preg_replace("/[^0-9A-Fa-f]/", '', $hex_str); // Gets a proper hex string
        $rgb_array = array();
        if( strlen($hex_str) == 6 ) {
            $color_val = hexdec($hex_str);
            $rgb_array['r'] = 0xFF & ($color_val >> 0x10);
            $rgb_array['g'] = 0xFF & ($color_val >> 0x8);
            $rgb_array['b'] = 0xFF & $color_val;
        } elseif( strlen($hex_str) == 3 ) {
            $rgb_array['r'] = hexdec(str_repeat(substr($hex_str, 0, 1), 2));
            $rgb_array['g'] = hexdec(str_repeat(substr($hex_str, 1, 1), 2));
            $rgb_array['b'] = hexdec(str_repeat(substr($hex_str, 2, 1), 2));
        } else {
            return false;
        }
        return $return_string ? implode($separator, $rgb_array) : $rgb_array;
    }

}

<?php

final class Identicon {

    /**
     * @var int
     */
    private $_width;

    /**
     * @var int
     */
    private $_height;

    /**
     * @var string
     */
    private $_hash;

    /**
     * @var int
     */
    private $_spriteZ = 800;

    /**
    * Generate unique image by hash
    *
    * @param string $hash MD5 hash for example
    * @param int $width PX
    * @param int $height PX
    * @param bool|int $filter FALSE or imagefilter constant
    * @return resource|bool generated JPEG resource or FALSE if throw exception
    */
    public function generateImageResource($hash, $width, $height, $filter = false) {

        $this->_width  = $width;
        $this->_height = $height;
        $this->_hash   = $hash;

        /* parse hash string */

        $csh = hexdec(substr($this->_hash, 0, 1)); // corner sprite shape
        $ssh = hexdec(substr($this->_hash, 1, 1)); // side sprite shape
        $xsh = hexdec(substr($this->_hash, 2, 1)) & 7; // center sprite shape

        $cro = hexdec(substr($this->_hash, 3, 1)) & 3; // corner sprite rotation
        $sro = hexdec(substr($this->_hash, 4, 1)) & 3; // side sprite rotation
        $xbg = hexdec(substr($this->_hash, 5, 1)) % 2; // center sprite background

        /* corner sprite foreground color */
        $cfr = hexdec(substr($this->_hash, 6, 2));
        $cfg = hexdec(substr($this->_hash, 8, 2));
        $cfb = hexdec(substr($this->_hash, 10, 2));

        /* side sprite foreground color */
        $sfr = hexdec(substr($this->_hash, 12, 2));
        $sfg = hexdec(substr($this->_hash, 14, 2));
        $sfb = hexdec(substr($this->_hash, 16, 2));

        /* final angle of rotation */
        //$angle = hexdec(substr($this->_hash, 18, 2));

        /* start with blank 3x3 identicon */
        $identicon = imagecreatetruecolor($this->_spriteZ * 3, $this->_spriteZ * 3);
        if (function_exists('imageantialias')) {
            imageantialias($identicon, TRUE);
        }

        /* assign white as background */
        $bg = imagecolorallocate($identicon, 255, 255, 255);
        imagefilledrectangle($identicon, 0, 0, $this->_spriteZ, $this->_spriteZ, $bg);

        /* generate corner sprites */
        $corner = $this->_getSprite($csh, $cfr, $cfg, $cfb, $cro);
        imagecopy($identicon, $corner, 0, 0, 0, 0, $this->_spriteZ, $this->_spriteZ);
        $corner = imagerotate($corner, 90, $bg);
        imagecopy($identicon, $corner, 0, $this->_spriteZ * 2, 0, 0, $this->_spriteZ, $this->_spriteZ);
        $corner = imagerotate($corner, 90, $bg);
        imagecopy($identicon, $corner, $this->_spriteZ * 2, $this->_spriteZ * 2, 0, 0, $this->_spriteZ, $this->_spriteZ);
        $corner = imagerotate($corner, 90, $bg);
        imagecopy($identicon, $corner, $this->_spriteZ * 2, 0, 0, 0, $this->_spriteZ, $this->_spriteZ);

        /* generate side sprites */
        $side = $this->_getSprite($ssh, $sfr, $sfg, $sfb, $sro);
        imagecopy($identicon, $side, $this->_spriteZ, 0, 0, 0, $this->_spriteZ, $this->_spriteZ);
        $side = imagerotate($side, 90, $bg);
        imagecopy($identicon, $side, 0, $this->_spriteZ, 0, 0, $this->_spriteZ, $this->_spriteZ);
        $side = imagerotate($side, 90, $bg);
        imagecopy($identicon, $side, $this->_spriteZ, $this->_spriteZ * 2, 0, 0, $this->_spriteZ, $this->_spriteZ);
        $side = imagerotate($side, 90, $bg);
        imagecopy($identicon, $side, $this->_spriteZ * 2, $this->_spriteZ, 0, 0, $this->_spriteZ, $this->_spriteZ);

        /* generate center sprite */
        $center = $this->_getCenter($xsh, $cfr, $cfg, $cfb, $sfr, $sfg, $sfb, $xbg);
        imagecopy($identicon, $center, $this->_spriteZ, $this->_spriteZ, 0, 0, $this->_spriteZ, $this->_spriteZ);

        // $identicon=imagerotate($identicon,$angle,$bg);

        /* make white transparent */
        imagecolortransparent($identicon, $bg);

        /* create blank image according to specified dimensions */
        $resized = imagecreatetruecolor($this->_width, $this->_height);
        if (function_exists('imageantialias')) {
            imageantialias($resized, TRUE);
        }

        /* assign white as background */
        $bg = imagecolorallocate($resized, 255, 255, 255);
        imagefilledrectangle($resized, 0, 0, $this->_width, $this->_height, $bg);

        /* resize identicon according to specification */
        imagecopyresampled($resized, $identicon, 0, 0, (imagesx($identicon) - $this->_spriteZ * 3) / 2, (imagesx($identicon) - $this->_spriteZ * 3) / 2, $this->_width, $this->_height, $this->_spriteZ * 3, $this->_spriteZ * 3);

        /* make white transparent */
        imagecolortransparent($resized, $bg);

        if ($filter) {
            imagefilter($resized, $filter);
        }

        /* and finally */

        ob_start();
        imagejpeg($resized, null, 100);
        imagedestroy($resized);
        return ob_get_clean();

        //return $resized; // Resources is not supported anymore
    }

    /* generate sprite for corners and sides */
    private function _getSprite($shape, $R, $G, $B, $rotation) {
        $sprite = imagecreatetruecolor($this->_spriteZ, $this->_spriteZ);
        if (function_exists('imageantialias')) {
            imageantialias($sprite, TRUE);
        }
        $fg = imagecolorallocate($sprite, $R, $G, $B);
        $bg = imagecolorallocate($sprite, 255, 255, 255);
        imagefilledrectangle($sprite, 0, 0, $this->_spriteZ, $this->_spriteZ, $bg);
        switch ($shape) {
            case 0: // triangle
                $shape = array(0.5, 1, 1, 0, 1, 1);
                break;
            case 1: // parallelogram
                $shape = array(0.5, 0, 1, 0, 0.5, 1, 0, 1);
                break;
            case 2: // mouse ears
                $shape = array(0.5, 0, 1, 0, 1, 1, 0.5, 1, 1, 0.5);
                break;
            case 3: // ribbon
                $shape = array(0, 0.5, 0.5, 0, 1, 0.5, 0.5, 1, 0.5, 0.5);
                break;
            case 4: // sails
                $shape = array(0, 0.5, 1, 0, 1, 1, 0, 1, 1, 0.5);
                break;
            case 5: // fins
                $shape = array(1, 0, 1, 1, 0.5, 1, 1, 0.5, 0.5, 0.5);
                break;
            case 6: // beak
                $shape = array(0, 0, 1, 0, 1, 0.5, 0, 0, 0.5, 1, 0, 1);
                break;
            case 7: // chevron
                $shape = array(0, 0, 0.5, 0, 1, 0.5, 0.5, 1, 0, 1, 0.5, 0.5);
                break;
            case 8: // fish
                $shape = array(0.5, 0, 0.5, 0.5, 1, 0.5, 1, 1, 0.5, 1, 0.5, 0.5, 0, 0.5);
                break;
            case 9: // kite
                $shape = array(0, 0, 1, 0, 0.5, 0.5, 1, 0.5, 0.5, 1, 0.5, 0.5, 0, 1);
                break;
            case 10: // trough
                $shape = array(0, 0.5, 0.5, 1, 1, 0.5, 0.5, 0, 1, 0, 1, 1, 0, 1);
                break;
            case 11: // rays
                $shape = array(0.5, 0, 1, 0, 1, 1, 0.5, 1, 1, 0.75, 0.5, 0.5, 1, 0.25);
                break;
            case 12: // double rhombus
                $shape = array(0, 0.5, 0.5, 0, 0.5, 0.5, 1, 0, 1, 0.5, 0.5, 1, 0.5, 0.5, 0, 1);
                break;
            case 13: // crown
                $shape = array(0, 0, 1, 0, 1, 1, 0, 1, 1, 0.5, 0.5, 0.25, 0.5, 0.75, 0, 0.5, 0.5, 0.25);
                break;
            case 14: // radioactive
                $shape = array(0, 0.5, 0.5, 0.5, 0.5, 0, 1, 0, 0.5, 0.5, 1, 0.5, 0.5, 1, 0.5, 0.5, 0, 1);
                break;
            default: // tiles
                $shape = array(0, 0, 1, 0, 0.5, 0.5, 0.5, 0, 0, 0.5, 1, 0.5, 0.5, 1, 0.5, 0.5, 0, 1);
                break;
        }
        /* apply ratios */
        for ($i = 0; $i < count($shape); $i++)
            $shape[$i] = $shape[$i] * $this->_spriteZ;
        imagefilledpolygon($sprite, $shape, count($shape) / 2, $fg);
        /* rotate the sprite */
        for ($i = 0; $i < $rotation; $i++)
            $sprite = imagerotate($sprite, 90, $bg);
        return $sprite;
    }

    /* generate sprite for center block */
    private function _getCenter($shape, $fR, $fG, $fB, $bR, $bG, $bB, $usebg) {
        $sprite = imagecreatetruecolor($this->_spriteZ, $this->_spriteZ);
        if (function_exists('imageantialias')) {
            imageantialias($sprite, TRUE);
        }
        $fg = imagecolorallocate($sprite, $fR, $fG, $fB);
        /* make sure there's enough contrast before we use background color of side sprite */
        if ($usebg > 0 && (abs($fR - $bR) > 127 || abs($fG - $bG) > 127 || abs($fB - $bB) > 127))
            $bg = imagecolorallocate($sprite, $bR, $bG, $bB); else
            $bg = imagecolorallocate($sprite, 255, 255, 255);
        imagefilledrectangle($sprite, 0, 0, $this->_spriteZ, $this->_spriteZ, $bg);
        switch ($shape) {
            case 0: // empty
                $shape = array();
                break;
            case 1: // fill
                $shape = array(0, 0, 1, 0, 1, 1, 0, 1);
                break;
            case 2: // diamond
                $shape = array(0.5, 0, 1, 0.5, 0.5, 1, 0, 0.5);
                break;
            case 3: // reverse diamond
                $shape = array(0, 0, 1, 0, 1, 1, 0, 1, 0, 0.5, 0.5, 1, 1, 0.5, 0.5, 0, 0, 0.5);
                break;
            case 4: // cross
                $shape = array(0.25, 0, 0.75, 0, 0.5, 0.5, 1, 0.25, 1, 0.75, 0.5, 0.5, 0.75, 1, 0.25, 1, 0.5, 0.5, 0, 0.75, 0, 0.25, 0.5, 0.5);
                break;
            case 5: // morning star
                $shape = array(0, 0, 0.5, 0.25, 1, 0, 0.75, 0.5, 1, 1, 0.5, 0.75, 0, 1, 0.25, 0.5);
                break;
            case 6: // small square
                $shape = array(0.33, 0.33, 0.67, 0.33, 0.67, 0.67, 0.33, 0.67);
                break;
            case 7: // checkerboard
                $shape = array(0, 0, 0.33, 0, 0.33, 0.33, 0.66, 0.33, 0.67, 0, 1, 0, 1, 0.33, 0.67, 0.33, 0.67, 0.67, 1, 0.67, 1, 1, 0.67, 1, 0.67, 0.67, 0.33, 0.67, 0.33, 1, 0, 1, 0, 0.67, 0.33, 0.67, 0.33, 0.33, 0, 0.33);
                break;
        }
        /* apply ratios */
        for ($i = 0; $i < count($shape); $i++)
            $shape[$i] = $shape[$i] * $this->_spriteZ;
        if (count($shape) > 0)
            imagefilledpolygon($sprite, $shape, count($shape) / 2, $fg);
        return $sprite;
    }
}

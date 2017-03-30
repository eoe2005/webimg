<?php

define("ROOT", __DIR__);
define("DS", DIRECTORY_SEPARATOR);

class Img {

    private static $EXT;
    private static $W = 0;
    private static $H = 0;
    private static $SRC;
    private static $DESC;
    private static $TYPE;

    const CLIP   = 1;
    const RESIZE = 0;

    public static function run() {
        self::parseUrl();
        if (file_exists(self::$SRC) === false) {
            self::error404();
        } elseif (self::$TYPE == self::CLIP) {
            self::clip();
        } else {
            self::resize();
        }
    }

    protected static function clip() {
        $size     = self::getSrcSize();
        $in       = self::getImg($size);
        $out      = self::createImg();
        $clipsize = ['x' => 0, 'y' => 0, 'w' => $size[0], 'h' => $size[1]];
        $sc       = $size[0] / $size[1];
        $dc       = self::$W / self::$H;
        if ($sc > $dc) {
            $clipsize['w'] = $clipsize['h'] * $dc;
            $clipsize['x'] = ($size[1] - $clipsize['h']) / 2;
        } else {
            $clipsize['h'] = $clipsize['w'] / $dc;
            $clipsize['y'] = ($size[1] - $clipsize['h']) / 2;
        }
        imagecopyresampled($out, $in, 0, 0, $clipsize['x'], $clipsize['y'], self::$W, self::$H, $clipsize['w'], $clipsize['h']);
        self::save($out);
        imagedestroy($in);
        imagedestroy($out);
    }

    protected static function resize() {
        $size = self::getSrcSize();
        $im   = self::getImg($size);
        $out  = self::createImg();
        imagecopyresampled($out, $im, 0, 0, 0, 0, self::$W, self::$H, $size[0], $size[1]);
        self::save($out);
        imagedestroy($in);
        imagedestroy($out);
    }

    protected static function parseUrl() {
        $url        = $_SERVER['REQUEST_URI'];
        $dir        = dirname($url);
        $name       = basename($url);
        self::$DESC = ROOT . $url;
        self::$EXT  = strstr(strtolower($name), '.');
        $name       = strstr($name, '.', true);
        $attr       = explode('_', $name);
        self::$SRC  = ROOT . DS . $dir . DS . $attr[0] . self::$EXT;
        self::$TYPE = isset($attr[1]) ? ($attr[1] == 'c' ? self::CLIP : self::RESIZE) : self::RESIZE;
        self::$W    = isset($attr[2]) ? intval($attr[2]) : 0;
        self::$H    = isset($attr[3]) ? intval($attr[3]) : 0;
    }

    protected static function error404() {
        self::$W    = 100;
        self::$H    = 100;
        self::$EXT  = '.png';
        self::$DESC = null;
        $out        = self::createImg();
        self::save($out);
        imagedestroy($out);
    }

    protected static function getSrcSize() {
        return getimagesize(self::$SRC);
    }

    protected static function getImg($size) {
        switch ($size[2]) {
            case IMAGETYPE_GIF:
                return imagecreatefromgif(self::$SRC);
            case IMAGETYPE_PNG:
                return imagecreatefrompng(self::$SRC);
            case IMAGETYPE_JPEG:
                return imagecreatefromjpeg(self::$SRC);
            default :
                return null;
        }
    }

    protected static function createImg() {
        return imagecreatetruecolor(self::$W, self::$H);
    }

    protected static function save($im) {
        if (self::$EXT == '.png') {
            if (self::$DESC) {
                imagepng($im, self::$DESC, 10);
            }
            header("Content-type: image/png");
            imagepng($im, null, 10);
        } elseif (self::$EXT == '.gif') {
            if (self::$DESC) {
                imagegif($im, self::$DESC);
            }
            header("Content-type: image/gif");
            imagegif($im);
        } else {
            if (self::$DESC) {
                imagejpeg($im, self::$DESC, 100);
            }
            header("Content-type: image/jpg");
            imagejpeg($im, null, 100);
        }
    }

}

Img::run();

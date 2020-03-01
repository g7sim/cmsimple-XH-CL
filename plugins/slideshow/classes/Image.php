<?php

/**
 * The images.
 *
 * PHP version 5
 *
 * @category  CMSimple_XH
 * @package   Slideshow
 * @author    Christoph M. Becker <cmbecker69@gmx.de>
 * @copyright 2012-2014 Christoph M. Becker <http://3-magi.net/>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link      http://3-magi.net/?CMSimple_XH/Slideshow_XH
 */

/**
 * The images.
 *
 * @category CMSimple_XH
 * @package  Slideshow
 * @author   Christoph M. Becker <cmbecker69@gmx.de>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://3-magi.net/?CMSimple_XH/Slideshow_XH
 */
class Slideshow_Image
{
    /**
     * Returns the list of image files in the given folder.
     *
     * @param string $path  The path of the folder.
     * @param string $order `fixed'/`sorted'/`random'.
     *
     * @return array
     */
    public static function findAll($path, $order)
    {
        $images = array();
        if (is_dir($path) && $dir = opendir($path)) {
            while (($entry = readdir($dir)) !== false) {
                if (preg_match('/\.(gif|jpg|jpeg|png)$/i', $entry)) {
                    $images[] = new self($path . $entry);
                }
            }
            closedir($dir);
        }
        return self::sort($images, $order);
    }

    /**
     * Sorts an array of images.
     *
     * @param array  $images An array of images.
     * @param string $order  A sort order.
     *
     * @return array
     */
    protected static function sort($images, $order)
    {
        if ($order == 'random') {
            shuffle($images);
        } else {
            usort($images, array(get_class(), 'compareFilenames'));
            if ($order == 'sorted') {
                $n = rand(0, count($images) - 1);
                $images = array_merge(
                    array_slice($images, $n), array_slice($images, 0, $n)
                );
            }
        }
        return $images;
    }

    /**
     * Compares two image filenames.
     *
     * @param Slideshow_Image $image An image.
     * @param Slideshow_Image $other Another image.
     *
     * @return int
     */
    protected static function compareFilenames($image, $other)
    {
        return strnatcasecmp($image->filename, $other->filename);
    }

    /**
     * The filename.
     *
     * @var string
     */
    protected $filename;

    /**
     * Initializes a new instance.
     *
     * @param string $filename A filename.
     */
    public function __construct($filename)
    {
        $this->filename = $filename;
    }

    /**
     * Returns the filename.
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Returns the name (i.e. the basename without the extension).
     *
     * @return string
     */
    public function getName()
    {
        return pathinfo($this->filename, PATHINFO_FILENAME);
    }
}

?>

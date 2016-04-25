<?php
namespace Tesseract\Phpdisplay\Utility;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/**
 * Totally minimal PHP template engine
 *
 * @author Fabien Udriot <fabien.udriot@ecodev.ch>
 * @package TYPO3
 * @subpackage tx_phpdisplay
 */

class Template
{
    private $vars = array(); /// Holds all the template variables

    /**
     * Constructor
     *
     * @param $file string the file name you want to load
     */
    public function __construct($file = null)
    {
        $this->file = $file;
    }

    /**
     * Set a template variable.
     */
    public function set($name, $value)
    {
        $this->vars[$name] = $value;
    }

    /**
     * Open, parse, and return the template file.
     *
     * @param $file string the template file name
     * @return string The content of the template file
     */
    public function fetch($file = null)
    {
        if (!$file) {
            $file = $this->file;
        }

        // Extract the vars to local namespace
        extract(
                $this->vars,
                EXTR_OVERWRITE
        );
        // Start output buffering
        ob_start();
        // Include the file
        include($file);
        // Get the contents of the buffer
        $contents = ob_get_contents();
        // End buffering and discard
        ob_end_clean();
        // Return the contents
        return $contents;
    }
}

<?php
namespace Tesseract\Phpdisplay\Component;

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

use Tesseract\Phpdisplay\Utility\Template;
use Tesseract\Tesseract\Service\FrontendConsumerBase;
use Tesseract\Tesseract\Tesseract;
use Tesseract\Tesseract\Utility\Utilities;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Plugin 'PHP Displayer' for the 'phpdisplay' extension.
 *
 * @author Fabien Udriot <fabien.udriot@ecodev.ch>
 * @author Francois Suter (Cobweb) <typo3@cobweb.ch>
 * @package TYPO3
 * @subpackage tx_phpdisplay
 */
class DataConsumer extends FrontendConsumerBase
{

    public $tsKey = 'tx_phpdisplay';
    public $extKey = 'phpdisplay';
    protected $table; // Name of the table where the details about the data display are stored
    protected $uid; // Primary key of the record to fetch for the details
    protected $structure = array(); // Input standardised data structure
    protected $result = ''; // The result of the processing by the Data Consumer
    protected $counter = array();

    protected $labelMarkers = array();
    protected $datasourceFields = array();
    protected $datasourceObjects = array();
    protected $LLkey = 'default';
    protected $fieldMarkers = array();

    /**
     * This method resets values for a number of properties
     * This is necessary because services are managed as singletons
     *
     * @return    void
     */
    public function reset()
    {
        $this->structure = array();
        $this->result = '';
        $this->uid = '';
        $this->table = '';
        $this->conf = array();
        $this->datasourceFields = array();
        $this->LLkey = 'default';
        $this->fieldMarkers = array();
    }

    /**
     * Return the filter data.
     *
     * @return    array
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     *
     * @var ContentObjectRenderer
     */
    protected $localCObj;

    // Data Consumer interface methods

    /**
     * This method returns the type of data structure that the Data Consumer can use
     *
     * @return string type of used data structures
     */
    public function getAcceptedDataStructure()
    {
        return Tesseract::RECORDSET_STRUCTURE_TYPE;
    }

    /**
     * This method indicates whether the Data Consumer can use the type of data structure requested or not
     *
     * @param string $type Type of data structure
     * @return boolean True if it can use the requested type, false otherwise
     */
    public function acceptsDataStructure($type)
    {
        return $type === Tesseract::RECORDSET_STRUCTURE_TYPE;
    }

    /**
     * This method is used to pass a data structure to the Data Consumer
     *
     * @param array $structure Standardised data structure
     * @return void
     */
    public function setDataStructure($structure)
    {
        $this->structure[$structure['name']] = $structure;
    }

    /**
     * This method is used to pass a filter to the Data Consumer
     *
     * @param array $filter Data Filter structure
     * @return void
     */
    public function setDataFilter($filter)
    {
        $this->filter = $filter;
    }

    /**
     * This method is used to get a data structure
     *
     * @return array Standardised data structure
     */
    public function getDataStructure()
    {
        return $this->structure;
    }

    /**
     * This method returns the result of the work done by the Data Consumer (FE output or whatever else)
     *
     * @return mixed The result of the Data Consumer's work
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * This method sets the result. Useful for hooks.
     *
     * @param mixed $result Predefined result
     * @return void
     */
    public function setResult($result)
    {

        $this->result = $result;
    }

    /**
     * This method starts whatever rendering process the Data Consumer is programmed to do
     *
     * @return void
     */
    public function startProcess()
    {
        $this->controller->addMessage(
                $this->extKey,
                'Received data structure',
                '',
                FlashMessage::INFO,
                $this->structure
        );

        // Get the full path to the template file
        try {
            $filePath = Utilities::getTemplateFilePath($this->consumerData['template']);
            $this->controller->addMessage(
                    $this->extKey,
                    'Template file: ' . $filePath,
                    '',
                    FlashMessage::INFO
            );

            /** @var $template Template */
            $template = GeneralUtility::makeInstance(Template::class);
            $template->set('controller', $this->getController());
            $template->set('filter', $this->getFilter());
            $template->set('datastructure', $this->getDataStructure());
            $this->result = $template->fetch($filePath);
        } catch (\Exception $e) {
            $this->controller->addMessage(
                    $this->extKey,
                    $e->getMessage() . ' (' . $e->getCode() . ')',
                    'Error processing the view',
                    FlashMessage::ERROR
            );
        }
    }
}

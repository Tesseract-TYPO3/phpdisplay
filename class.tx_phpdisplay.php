<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007-2010	Francois Suter (Cobweb) <typo3@cobweb.ch>
*					Fabien Udriot <fabien.udriot@ecodev.ch>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.	 See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

require_once(t3lib_extMgm::extPath('tesseract', 'services/class.tx_tesseract_feconsumerbase.php'));
require_once(t3lib_extMgm::extPath('phpdisplay', 'class.tx_phptemplate.php'));

/**
 * Plugin 'Data Displayer' for the 'phpdisplay' extension.
 *
 * @author		Francois Suter (Cobweb) <typo3@cobweb.ch>
 * @author		Fabien Udriot <fabien.udriot@ecodev.ch>
 * @package		TYPO3
 * @subpackage	tx_phpdisplay
 *
 * $Id$
 */
class tx_phpdisplay extends tx_tesseract_feconsumerbase {

	public $tsKey = 'tx_phpdisplay';
	public $extKey = 'phpdisplay';
	protected $conf;
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
	 * @return	void
	 */
	public function reset(){
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
	 * Return the controller data.
	 *
	 * @return	array
	 */
	public function getController() {
		return $this->pObj;
	}

	/**
	 * Return the filter data.
	 *
	 * @return	array
	 */
	public function getFilter() {
		return $this->filter;
	}

	/**
	 *
	 * @var tslib_cObj
	 */
	protected $localCObj;

	// Data Consumer interface methods

	/**
	 * This method returns the type of data structure that the Data Consumer can use
	 *
	 * @return	string	type of used data structures
	 */
	public function getAcceptedDataStructure() {
		return tx_tesseract::RECORDSET_STRUCTURE_TYPE;
	}

	/**
	 * This method indicates whether the Data Consumer can use the type of data structure requested or not
	 *
	 * @param	string		$type: type of data structure
	 * @return	boolean		true if it can use the requested type, false otherwise
	 */
	public function acceptsDataStructure($type) {
		return $type == tx_tesseract::RECORDSET_STRUCTURE_TYPE;
	}

	/**
	 * This method is used to pass a data structure to the Data Consumer
	 *
	 * @param 	array	$structure: standardised data structure
	 * @return	void
	 */
	public function setDataStructure($structure) {
		$this->structure = $structure;
	}

	/**
	 * This method is used to pass a filter to the Data Consumer
	 *
	 * @param 	array	$filter: Data Filter structure
	 * @return	void
	 */
	public function setDataFilter($filter) {
		$this->filter = $filter;
	}

	/**
	 * This method is used to get a data structure
	 *
	 * @return 	array	$structure: standardised data structure
	 */
	public function getDataStructure() {
		return $this->structure;
	}

	/**
	 * This method returns the result of the work done by the Data Consumer (FE output or whatever else)
	 *
	 * @return	mixed	the result of the Data Consumer's work
	 */
	public function getResult() {
		return $this->result;
	}

	/**
	 * This method sets the result. Useful for hooks.
	 *
	 * @return	void
	 */
	public function setResult($result) {

		$this->result = $result;
	}

	/**
	 * This method starts whatever rendering process the Data Consumer is programmed to do
	 *
	 * @return	void
	 */
	public function startProcess() {

		// ************************************
		// ********** INITIALISATION **********
		// ************************************

		// Initializes local cObj
		$this->localCObj = t3lib_div::makeInstance('tslib_cObj');
		$this->configuration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$this->extKey]);

		$this->setPageTitle($this->conf);


		// Loads the template file
		$templateFile = $this->consumerData['template'];
		if (is_file($templateFile)) {
			$template = t3lib_div::makeInstance('tx_phptemplate');
			$template->set('datastructure',$this->getDataStructure());

			$this->result = $template->fetch($templateFile);
		}
		else {
			$this->result .= '<div style="color :red; font-weight: bold">Template not found at ' . $templateCode . '.</div>';
		}

//		// ****************************************
//		// ********** FETCHES DATASOURCE **********
//		// ****************************************
//
//		// Transforms the string from field mappings into a PHP array.
//		// This array contains the mapping information btw a marker and a field.
//		try {
//			$datasource = json_decode($this->consumerData['mappings'],true);
//
//			// Makes sure $datasource is an array
//			if ($datasource === NULL) {
//				$datasource = array();
//			}
//		}
//		catch (Exception $e) {
//			$this->result .= '<div style="color :red; font-weight: bold">JSON decoding problem for tx_phpdisplay_displays.uid = '.$this->uid . '.</div>';
//			return false;
//		}
//
//		$uniqueMarkers = array();
//
//		// Formats TypoScript configuration as array.
//		$parseObj = t3lib_div::makeInstance('t3lib_TSparser');
//		foreach ($datasource as $data) {
//			if(trim($data['configuration']) != ''){
//
//				// Clears the setup (to avoid typoscript incrementation)
//				$parseObj->setup = array();
//				$parseObj->parse($data['configuration']);
//				$data['configuration'] = $parseObj->setup;
//			}
//			else{
//				$data['configuration'] = array();
//			}
//
//			// Merges some data to create a new marker. Will look like: table.field
//			$_marker = $data['table'] . '.' . $data['field'];
//
//			// IMPORTANT NOTICE:
//			// The idea is to make the field unique and to be able to know which field of the database is associated
//			// Adds to ###FIELD.xxx### the value "table.field"
//			// Ex: [###FIELD.title###] => ###FIELD.title.pages.title###
//			$uniqueMarkers['###' . $data['marker'] . '###'] = '###' . $data['marker'] . '.' . $_marker . '###';
//
//			// Builds the datasource as an associative array.
//			// $data contains the following information: [marker], [table], [field], [type], [configuration]
//			if (preg_match('/FIELD/', $data['marker'])) {
//				$this->datasourceFields[$data['marker']] = $data;
//			}
//			else {
//				$this->datasourceObjects[$data['marker']] = $data;
//			}
//		}
//
//		// ***************************************
//		// ********** BEGINS PROCESSING **********
//		// ***************************************
//
//		// LOCAL DOCUMENTATION:
//		// $templateCode -> HTML template roughly extracted from the database
//		// $templateContent -> HTML that is going to be outputed
//
//		// Loads the template file
//		$templateCode = $this->consumerData['template'];
//		if (preg_match('/^FILE:/isU', $templateCode)) {
//			$filePath = str_replace('FILE:', '' , $templateCode);
//			$filePath = t3lib_div::getFileAbsFileName($filePath);
//			if (is_file($filePath)) {
//				$templateCode = file_get_contents($filePath);
//			}
//		}
//
//		// Hook that enables to pre process the output)
//		if (preg_match_all('/#{3}HOOK\.(.+)#{3}/isU', $templateCode, $matches, PREG_SET_ORDER)) {
//			foreach ($matches as $match) {
//				$hookName = $match[1];
//				if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extKey]['preProcessResult'][$hookName])) {
//					foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extKey]['preProcessResult'][$hookName] as $className) {
//						$preProcessor = &t3lib_div::getUserObj($className);
//						$templateCode = $preProcessor->preProcessResult($templateCode, $hookName, $this);
//					}
//				}
//			}
//		}
//
//		// Begins $templateCode transformation.
//		// *Must* be at the beginning of startProcess()
//		$templateCode = $this->checkPageStatus($templateCode);
//		$templateCode = $this->preProcessIF($templateCode);
//		$templateCode = $this->processOBJECTS($templateCode);
//		$templateCode = $this->preProcessFUNCTIONS($templateCode);
//		$templateCode = $this->processLOOP($templateCode); // Adds a LOOP marker of first level, if it does not exist.
//
//		// Handles possible marker: ###LLL:EXT:myextension/localang.xml:myLable###, ###GP:###, ###TSFE:### etc...
//		$LLLMarkers = $this->getLLLMarkers($templateCode);
//		$expressionMarkers = $this->getAllExpressionMarkers($templateCode);
//		$GPMarkers = $this->getExpressionMarkers('GP', array_merge(t3lib_div::_GET(), t3lib_div::_POST()), $templateCode);
//		$TSFEMarkers = $this->getExpressionMarkers('TSFE', $GLOBALS['TSFE'], $templateCode);
//		$PLUGINMarkers = $this->getExpressionMarkers('PLUGIN', $GLOBALS['TSFE']->tmpl->setup['plugin.'], $templateCode);
//		$VARSMarkers = $this->getExpressionMarkers('VARS', $this->pObj->piVars, $templateCode);
//		$pageMarkers = $this->getExpressionMarkers('page', $GLOBALS['TSFE']->page, $templateCode);
//		$sortMarkers = $this->getSortMarkers($templateCode);
//		$filterMarkers = $this->getFilterMarkers($templateCode);
//		$globalVariablesMarkers = $this->getGlobalVariablesMarkers($templateCode); // Global template variable can be ###TOTAL_RECORDS### ###SUBTOTAL_RECORDS###
//
//		// Merges array, in order to have only one array (performance!)
//		$markers = array_merge($uniqueMarkers, $LLLMarkers, $expressionMarkers, $GPMarkers, $TSFEMarkers, $PLUGINMarkers, $VARSMarkers, $pageMarkers, $sortMarkers, $filterMarkers, $globalVariablesMarkers);
//
//		// Parse evaluation. typically for {config:language} syntax
//		foreach ($markers as &$marker) {
//			$marker = tx_expressions_parser::evaluateString($marker);
//		}
//
//		// First transformation of $templateCode. Substitutes $markers that can be already substituted. (LLL, GP, TSFE, etc...)
//		$templateCode = t3lib_parsehtml::substituteMarkerArray($templateCode, $markers);
//
//		// Cuts out the template into different part and organizes it in an array.
//		$templateStructure = $this->getTemplateStructure($templateCode);
//
//		/* Debug */
//		$this->debug($markers,$templateStructure);
//
//		// Transforms the HTML template to HTML content
//		$templateContent = $templateCode;
//		foreach ($templateStructure as &$_templateStructure) {
//			if (!empty($this->structure['records'])) {
//				$_content = $this->getContent($_templateStructure, $this->structure);
//				$templateContent = str_replace($_templateStructure['template'], $_content, $templateContent);
//			}
//			else {
//				// Checks if an empty value must replace the block.
//				$_content = $this->getEmptyValue($_templateStructure);
//				$templateContent = str_replace($_templateStructure['template'], $_content, $templateContent);
//			}
//		}
//
//		// Useful when the data structure is empty (no records)
//		if (!$this->getLabelMarkers($this->structure['name'])) {
//			$this->setLabelMarkers($this->structure);
//		}
//		// Translates outter labels and fields.
//		$fieldMarkers = array_merge($this->fieldMarkers, $this->getLabelMarkers($this->structure['name']), array('###COUNTER###' => '0'));
//		$templateContent = t3lib_parsehtml::substituteMarkerArray($templateContent, $fieldMarkers);
//
//		// Handles the page browser
//		$templateContent = $this->processPageBrowser($templateContent);
//
//		// Handles the <!--IF(###MARKER### == '')-->
//		// Evaluates the condition and replaces the content whether it is necessary
//		// Must be at the end of startProcess()
//		$templateContent = $this->postProcessFUNCTIONS($templateContent);
//		$this->result = $this->postProcessIF($templateContent);
//
//		// Hook that enables to post process the output)
//		if (preg_match_all('/#{3}HOOK\.(.+)#{3}/isU', $this->result, $matches, PREG_SET_ORDER)) {
//			foreach ($matches as $match) {
//				$hookName = $match[1];
//				if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extKey]['postProcessResult'][$hookName])) {
//					foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extKey]['postProcessResult'][$hookName] as $className) {
//						$postProcessor = &t3lib_div::getUserObj($className);
//						$this->result = $postProcessor->postProcessResult($this->result, $hookName, $this);
//					}
//				}
//			}
//		}
//
//		// Processes markers of type ###RECORD(tt_content,1)###
//		$this->result = $this->processRECORDS($this->result);
//
//		// add debug[display] in order to see the untranslated markers
//		$this->result = $this->clearMarkers($this->result);
	}

	/**
	 * Processes markers of type ###RECORD('tt_content',1)###
	 *
	 * @param	string	$content: the content
	 * @return	string	$content:
	 */
//	protected function processRECORDS($content) {
//
//		if (preg_match_all("/#{3}RECORD\((.+),(.+)\)#{3}/isU", $content, $matches, PREG_SET_ORDER)) {
//
//			// Stores the filter. phpdisplay is a singleton and the filter property will be override by a child call.
//			$GLOBALS['tesseract']['filter']['parent'] = $this->filter;
//
//			foreach ($matches as $match) {
//				$marker = $match[0];
//				$table = trim($match[1]);
//				$uid = trim($match[2]);
//
//				// Avoids recursive call
//				if ($this->pObj->cObj->data['uid'] != $uid) {
//					$conf = array();
//					$conf['source'] = $table.'_'.$uid;
//					$conf['tables'] = $table;
//					$_content = $this->localCObj->RECORDS($conf);
//					$content = str_replace($marker, $_content, $content);
//				}
//			}
//		}
//		return $content;
//	}

	/**
	 * Changes the page title if phpdisplay encounters typoScript configuration.
	 * Typoscript configuration have the insertData syntax e.g. {table.field}
	 * This is done by changing the page title in the tslib_fe object.
	 *
	 * @param	array	$configuration: Local TypoScript configuration
	 * @return	void
	 */
//	protected function setPageTitle($configuration) {
//		// Checks wheter the title of the template need to be changed
//		if ($configuration['substitutePageTitle']) {
//			$pageTitle = $configuration['substitutePageTitle'];
//
//			// extracts the {table.field}
//			if (preg_match_all('/\{(.+)\}/isU', $pageTitle, $matches, PREG_SET_ORDER)) {
//				foreach ($matches as $match) {
//					$expression = $match[0];
//					$expressionInner = $match[1];
//					$values = explode('.', $expressionInner);
//
//					// Checks if table name is given or not.
//					if (count($values) == 1) {
//						$table = $this->structure['name'];
//						$field = $values[0];
//					} elseif (count($values) == 2) {
//						$table = $values[0];
//						$field = $values[1];
//					}
//					$expressionResult = $this->getValueFromStructure($this->structure, 0, $table, $field);
//					$pageTitle = str_replace($expression, $expressionResult, $pageTitle);
//				}
//			}
//			$GLOBALS['TSFE']->page['title'] = $pageTitle;
//		}
//	}

	/**
	 * If found, returns markers of type SORT
	 *
	 * Example of marker: ###SORT###
	 *
	 * @param	string	$content HTML code
	 * @return	string	$content transformed HTML code
	 */
//	protected function getSortMarkers($content) {
//		$markers = array();
//		if (preg_match_all('/#{3}SORT\.(.+)#{3}/isU', $content, $matches, PREG_SET_ORDER)) {
//			foreach($matches as $match){
//				$marker = $match[0];
//				$markerContent = $match[1];
//				// Get the position of the sort
//				if (preg_match('/([0-9])$/is', $markerContent, $positions)) {
//					$position = $positions[0];
//				}
//				else {
//					$position = 1;
//				}
//
//				// Gets whether it is a sort or an order
//				if (strpos($markerContent, 'sort') !== FALSE) {
//					$sortTable = '';
//					if ($this->filter['orderby'][$position * 2 - 1]['table'] != '') {
//						$sortTable = $this->filter['orderby'][$position * 2 - 1]['table'] . '.';
//					}
//					$markers[$marker] = $sortTable . $this->filter['orderby'][$position * 2 - 1]['field'];
//				}
//				else if (strpos($markerContent, 'order') !== FALSE) {
//					$markers[$marker] = $this->filter['orderby'][$position * 2 - 1]['order'];
//				}
//			}
//		}
//		return $markers;
//	}

	/**
	 * If found, returns markers of type SORT
	 *
	 * Example of marker: ###SORT###
	 *
	 * @param	string	$content HTML code
	 * @return	string	$content transformed HTML code
	 */
//	protected function getFilterMarkers($content) {
//		$markers = array();
//		if (preg_match_all('/#{3}FILTER\.(.+)#{3}/isU', $content, $matches, PREG_SET_ORDER)) {
//
//			// Defines the filters array.
//			// It can be the property of the object
//			// But the filter can be given by the caller. @see method processRECORDS();
//			$uid = $this->pObj->cObj->data['uid'];
//			if (isset($GLOBALS['tesseract']['filter']['parent'])) {
//				$filters = $GLOBALS['tesseract']['filter']['parent'];
//			}
//			else {
//				$filters = $this->filter;
//			}
//
//			// Traverse the FILTER markers
//			foreach($matches as $match){
//				$marker = $match[0];
//				$markerInner = $match[1];
//
//				// Traverses the array and finds the value
//				if (isset($filters['parsed']['filters'][$markerInner])) {
//					$_filter = $filters['parsed']['filters'][$markerInner];
//					$_filter = reset($_filter); //retrieve the cell indepantly from the key
//					$markers[$marker] = $_filter['value'];
//				}
//			}
//		}
//		return $markers;
//	}

	/**
	 * If found, returns all markers that correspond to subexpressions
	 * and can be parsed using tx_expressions_parser
	 *
	 * Example of GP marker: ###EXPRESSION.gp|parameter###
	 *
	 * @param	string	$content HTML code
	 * @return	string	$content transformed HTML code
	 */
//	protected function getAllExpressionMarkers($content) {
//		$markers = array();
//		if (preg_match_all('/#{3}EXPRESSION\.(.+)#{3}/isU', $content, $matches, PREG_SET_ORDER)) {
//			$numberOfMatches = count($matches);
//			if ($numberOfMatches > 0) {
//				for ($index = 0; $index < $numberOfMatches; $index ++) {
//					try {
//						$markers[$matches[$index][0]] = tx_expressions_parser::evaluateExpression($matches[$index][1]);
//					}
//					catch (Exception $e) {
//						continue;
//					}
//				}
//			}
//		}
//		return $markers;
//	}

	/**
	 * If found, returns markers, of type global template variable
	 * Global template variable can be ###TOTAL_RECORDS### ###SUBTOTAL_RECORDS###
	 *
	 * @param	string	$content: HTML content
	 * @return	 string	$content: transformed HTML content
	 */
//	protected function getGlobalVariablesMarkers($content) {
//		$markers = array();
//		if (preg_match('/#{3}TOTAL_RECORDS#{3}/isU', $content)) {
//			$markers['###TOTAL_RECORDS###']	= $this->structure['totalCount'];
//		}
//		if (preg_match('/#{3}SUBTOTAL_RECORDS#{3}/isU', $content)) {
//			$markers['###SUBTOTAL_RECORDS###']  = $this->structure['count'];
//		}
//
//		if (preg_match('/#{3}RECORD_OFFSET#{3}/isU', $content)) {
//			if (!$this->pObj->piVars['page']) {
//				$this->pObj->piVars['page'] = 0;
//			}
//
//			// Computes the record offset
//			$recordOffset = ($this->pObj->piVars['page'] + 1) * $this->filter['limit']['max'];
//			if ($recordOffset > $this->structure['totalCount']) {
//				$recordOffset = $this->structure['totalCount'];
//			}
//			$markers['###RECORD_OFFSET###']	= $recordOffset;
//		}
//		return $markers;
//	}

	/**
	 * Handles the page browser
	 *
	 * @param	string	$content HTML code
	 * @return	string	$content transformed HTML code
	 */
//	protected function processPageBrowser($content) {
//		$pattern = '/#{3}PAGE_BROWSER#{3}|#{3}PAGEBROWSER#{3}/isU';
//		if (preg_match($pattern, $content)) {
//
//			// Fetches the configuration
//			$conf = $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_pagebrowse_pi1.'];
//
//			if ($conf != null) {
//
//				// Adds limit to the query and calculates the number of pages.
//				if ($this->filter['limit']['max'] != '' && $this->filter['limit']['max'] != '0') {
//					//$conf['extraQueryString'] .= '&' . $this->pObj->getPrefixId() . '[max]=' . $this->filter['limit']['max'];
//					$conf['numberOfPages'] = ceil($this->structure['totalCount'] / $this->filter['limit']['max']);
//					$conf['items_per_page'] = $this->filter['limit']['max'];
//					$conf['total_items'] = $this->structure['totalCount'];
//					$conf['total_pages'] = $conf['numberOfPages']; // duplicated, because $conf['numberOfPages'] is protected
//				}
//				else {
//					$conf['numberOfPages'] = 1;
//				}
//
//				// Can be tx_displaycontroller_pi1 OR tx_displaycontroller_pi1
//				$conf['pageParameterName'] = $this->pObj->getPrefixId() . '|page';
//
//				// Defines pagebrowse configuration options
//				$values = array('templateFile', 'enableMorePages', 'enableLessPages', 'pagesBefore', 'pagesAfter');
//
//				// Set Page Browser from Flexform config
//				foreach($values as $value) {
//					if ($this->conf['pagebrowse.'][$value] != '') {
//						$conf[$value] = $this->conf['pagebrowse.'][$value];
//					}
//				}
//
//				// Debug pagebrowse
//				if (isset($GLOBALS['_GET']['debug']['pagebrowse']) && isset($GLOBALS['TYPO3_MISC']['microtime_BE_USER_start'])) {
//					t3lib_div::debug($conf);
//				}
//
//				$this->localCObj->start(array(), '');
//				$pageBrowser = $this->localCObj->cObjGetSingle('USER',$conf);
//			}
//			else {
//				$pageBrowser = '<span style="color:red; font-weight: bold">Error: extension pagebrowse not loaded</span>';
//			}
//
//			// Replaces the marker by some HTML content
//			$content = preg_replace($pattern, $pageBrowser, $content);
//		}
//		return $content;
//	}
	
	/**
	 * Usful method that shorten a text according to the parameter $limit.
	 *
	 * @param	string	$text: the input text
	 * @param	int		$limit: the limit of words
	 * @return	string	$text that has been shorten
	 */
	protected function limit($text, $limit) {
		$text = strip_tags($text, '<br><br/><br />');
		$limit = $limit + substr_count($text, '<br>') + substr_count($text, '<br/>') + substr_count($text, '<br />');
		$words = str_word_count($text, 2);
		$pos = array_keys($words);
		if (count($words) > $limit) {
			$text = substr($text, 0, $pos[$limit]) . ' ...';
		}
		return $text;
	}


	/**
	 * Replaces the marker ###OBJECT.userDefined###
	 *
	 * @param	string	$content HTML code
	 * @return	string	$content transformed HTML code
	 */
	protected function processOBJECTS($content) {
		$fieldMarkers = array();
		foreach ($this->datasourceObjects as $key => $datasource) {
			$fieldMarkers['###' . $key . '###'] = $this->getValue($datasource);
		}

		return t3lib_parsehtml::substituteMarkerArray($content, $fieldMarkers);
	}

	/**
	 * Important method! Formats the $value given as input according to the $key.
	 * The variable $key will tell the type of $value. Then format the $value whenever there is TypoScript configuration.
	 *
	 * @param	array	$datasource: can be $this->datasourceObjects or $this->datasourceFields
	 * @param	string	$key: the key of the datasource element e.g. OBJECT.userDefined
	 * @param	string	$value (optional) makes sense for method getContent()
	 * @return	array	$sds: the datastructure
	 */
//	protected function getValue(&$datasource, $value = '', &$sds = array()) {
//
//			// Checks if the page title needs to be changed
//		$this->setPageTitle($datasource['configuration']);
//
//			// Get default rendering configuration for the given type
//		$tsIndex = $datasource['type'] . '.';
//		$baseConfiguration = isset($this->conf['defaultRendering.'][$tsIndex]) ? $this->conf['defaultRendering.'][$tsIndex] : array();
//			// Merge base configuration with local configuration
//		$configuration = array();
//		if (is_array($datasource['configuration'])) {
//			$configuration = t3lib_div::array_merge_recursive_overrule($baseConfiguration, $datasource['configuration']);
//		} else {
//			$configuration = $baseConfiguration;
//		}
//			// Render element based on type
//		switch ($datasource['type']) {
//			case 'text':
//					// Override configuration as needed
//				if (!isset($configuration['value'])) {
//					$configuration['value'] = $value;
//				}
//
//				$output = $this->localCObj->TEXT($configuration);
//				break;
//			case 'richtext':
//					// Override configuration as needed
//				if (!isset($configuration['value'])) {
//					$configuration['value'] = $value;
//				}
//
//				$output = $this->localCObj->TEXT($configuration);
//				break;
//			case 'image':
//					// Override configuration as needed
//				$configuration['file'] = $value;
//
//					// Sets the alt attribute
//				if (!isset($configuration['altText'])) {
//						// Gets the file name
//					$configuration['altText'] = $this->getFileName($configuration['file']);
//				}
//				else {
//					$configuration['altText'] = $this->localCObj->stdWrap($configuration['altText'], $configuration['altText.']);
//				}
//
//					// Sets the title attribute
//				if (!isset($configuration['titleText'])) {
//						// Gets the file name
//					$configuration['titleText'] = $this->getFileName($configuration['file']);
//				}
//				else {
//					$configuration['titleText'] = $this->localCObj->stdWrap($configuration['titleText'], $configuration['titleText.']);
//				}
//
//				$image = $this->localCObj->IMAGE($configuration);
//				if (empty($image)) {
//					// TODO: in production mode, nothing should be displayed. "templateDisplay_imageNotFound"
//					$output = '<img src="'.t3lib_extMgm::extRelPath($this->extKey).'resources/images/missing_image.png'.'" class="templateDisplay_imageNotFound" alt="Image not found"/>';
//				}
//				else {
//					$output = $image;
//				}
//				break;
//			case 'imageResource':
//				$configuration = $datasource['configuration'];
//				$configuration['file'] = $value;
//				$output = $this->localCObj->IMG_RESOURCE($configuration);
//				break;
//			case 'linkToDetail':
//					// Override configuration as needed
//				$configuration['useCacheHash'] = 1;
//				if (!isset($configuration['returnLast'])) {
//					$configuration['returnLast'] = 'url';
//				}
//
//				$additionalParams = '&' . $this->pObj->getPrefixId() . '[table]=' . $sds['trueName'] . '&' . $this->pObj->getPrefixId() .'[showUid]=' . $value;
//				$configuration['additionalParams'] = $additionalParams . $this->localCObj->stdWrap($configuration['additionalParams'], $configuration['additionalParams.']);
//
//					// Generates the link
//				$output = $this->localCObj->typolink('',$configuration);
//				break;
//			case 'linkToPage':
//					// Override configuration as needed
//				$configuration['useCacheHash'] = 1;
//
//					// Defines parameter
//				if (!isset($configuration['parameter'])) {
//					$configuration['parameter'] = $value;
//				}
//
//				if (!isset($configuration['returnLast'])) {
//					$configuration['returnLast'] = 'url';
//				}
//				$configuration['additionalParams'] = $additionalParams . $this->localCObj->stdWrap($configuration['additionalParams'], $configuration['additionalParams.']);
//
//					// Generates the link
//				$output = $this->localCObj->typolink('',$configuration);
//				break;
//			case 'linkToFile':
//					// Override configuration as needed
//				$configuration['useCacheHash'] = 1;
//
//				if (!isset($configuration['returnLast'])) {
//					$configuration['returnLast'] = 'url';
//				}
//
//				if (!isset($configuration['parameter'])) {
//					$configuration['parameter'] = $value;
//				}
//
//					// Replaces white spaces in filename
//				$configuration['parameter'] = str_replace(' ','%20',$configuration['parameter']);
//
//					// Generates the link
//				$output = $this->localCObj->typolink('',$configuration);
//				break;
//			case 'email':
//					// Override configuration as needed
//				if (!isset($configuration['parameter'])) {
//					$configuration['parameter'] = $value;
//				}
//					// Generates the email
//				$output = $this->localCObj->typolink('',$configuration);
//				break;
//			case 'user':
//					// Override configuration as needed
//				if (!isset($configuration['parameter'])) {
//					$configuration['parameter'] = $value;
//				}
//				// Generates the user content
//				$output = $this->localCObj->USER($configuration);
//				break;
//		} // end switch
//
//		return $output;
//	}


	/**
	 * If found, returns markers, of type LLL
	 *
	 * Example of marker: ###LLL:EXT:myextension/localang.xml:myLabel###
	 *
	 * @param	string	$content HTML code
	 * @return	string	$content transformed HTML code
	 */
//	protected function getLLLMarkers($content) {
//		$markers = array();
//		if (preg_match_all('/#{3}(LLL:.+)#{3}/isU', $content, $matches, PREG_SET_ORDER)) {
//			foreach($matches as $marker){
//				$value = $GLOBALS['TSFE']->sL($marker[1]);
//				if ($value != '') {
//					$markers[$marker[0]] = $value;
//				}
//			}
//		}
//		return $markers;
//	}

	/**
	 * Displays in the frontend or in the devlog some debug output
	 *
	 * @param array $markers
	 * @param array $templateStructure
	 */
//	protected function debug($markers, $templateStructure = array()) {
//		if (isset($GLOBALS['_GET']['debug']['markers']) && isset($GLOBALS['TYPO3_MISC']['microtime_BE_USER_start'])) {
//			t3lib_div::debug($markers);
//		}
//
//		if (isset($GLOBALS['_GET']['debug']['template']) && isset($GLOBALS['TYPO3_MISC']['microtime_BE_USER_start'])) {
//			t3lib_div::debug($templateStructure);
//		}
//
//		if (isset($GLOBALS['_GET']['debug']['structure']) && isset($GLOBALS['TYPO3_MISC']['microtime_BE_USER_start'])) {
//			t3lib_div::debug($this->structure);
//		}
//
//		if (isset($GLOBALS['_GET']['debug']['filter']) && isset($GLOBALS['TYPO3_MISC']['microtime_BE_USER_start'])) {
//			t3lib_div::debug($this->filter);
//		}
//
//		if ($this->configuration['debug'] || TYPO3_DLOG) {
//			t3lib_div::devLog('Markers: "' . $this->consumerData['title'] . '"', $this->extKey, -1, $markers);
//			t3lib_div::devLog('Template structure: "' . $this->consumerData['title'] . '"', $this->extKey, -1, $templateStructure);
//			t3lib_div::devLog('Data structure: "' . $this->pObj->cObj->data['header'] . '"', $this->extKey, -1, $this->structure);
//		}
//
//	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/phpdisplay/class.tx_phpdisplay.php']){
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/phpdisplay/class.tx_phpdisplay.php']);
}

?>
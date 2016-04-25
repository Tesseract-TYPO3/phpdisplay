<?php

// Parameters
$parameters = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('tx_displaycontroller');

// Default language of this website is 'fr'
$language = 'fr';
if (!empty($GLOBALS['TSFE']->tmpl->setup['config.']['language'])) {
	$language = $GLOBALS['TSFE']->tmpl->setup['config.']['language'];
}

saveParameters($parameters);
?>

<h2><?php translate('search_result') ?></h2>

<div class="row-fluid">
	<div class="span4">
		<?php numberOfResults($datastructure, $filter); ?>
	</div>
	<div class="span8 pull-right">
		<?php pageBrowser($datastructure, $filter); ?>
	</div>
</div>

<table class="table table-striped">
	<thead>
	<tr>
		<th><?php translate('title');?></th>
		<th><?php translate('location');?></th>
		<th><?php translate('type');?></th>
		<th><?php translate('companyType');?></th>
		<th>
			<a href="<?php linkSort() ?>">
				<?php translate('date');?>
				<?php if ($parameters['sort'] == 'job_publicationdate' && $parameters['order'] == 'desc'): ?>
				<?php image('bullet_arrow_down.png'); ?>
				<?php elseif ($parameters['sort'] == 'job_publicationdate' && $parameters['order'] == 'asc'): ?>
				<?php image('bullet_arrow_up.png'); ?>
				<?php endif ?>
			</a>
		</th>
	</tr>
	</thead>
	<tbody>
	<?php foreach ($datastructure['tx_employer_jobs']['records'] as $job): ?>
	<tr>
		<td>
			<a href="<?php linkToDetail($job['uid']) ?>">
				<?php print $job['job_name'] ?>
			</a>
		</td>
		<td><?php puts($job, 'job_workingplace') ?></td>
		<td><?php puts($job['__substructure']['tx_employer_jobtypes']['records'][0], 'jobtype_name_' . $language); ?></td>
		<td><?php puts($job['__substructure']['tx_employer_businesssectors']['records'][0], 'businesssector_name_' . $language); ?></td>
		<td><?php putsDate($job, 'job_publicationdate') ?></td>
	</tr>
		<?php endforeach ?>
	</tbody>
</table>


<?php

/**
 * Helper function for translating a key
 *
 * @param string $key
 */
function translate($key) {
	$extensionName = 'employer_searchform';
	print \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($key, $extensionName);
}

/**
 * Helper function for displaying a key of a record
 *
 * @param array $record
 * @param string $key
 */
function puts($record, $key) {
	if (!empty($record[$key])) {
		print $record[$key];
	}
}

/**
 * Helper function for displaying a key of a record
 *
 * @param array $record
 * @param string $key
 */
function putsDate($record, $key) {
	if (!empty($record[$key])) {
		$value = $record[$key];
		print date("d.m.Y", $value);
	}
}

/**
 * Helper function for display an image from default location
 *
 * @param string $key
 */
function image($key) {
	$image = '<img src="/fileadmin/templates/general/images/%s" alt="" />';
	print sprintf($image, $key);

}

/**
 * Helper function for displaying a key of a record
 */
function linkSort() {

	// Parameters
	$parameters = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('tx_displaycontroller');

	// Set default sorting value
	if (empty($parameters['sort'])) {
		$parameters['sort'] = 'job_publicationdate';
	}

	// Set ordering value
	if (!empty($parameters['order']) && $parameters['order'] == 'asc') {
		$parameters['order'] = 'desc';
	} else {
		$parameters['order'] = 'asc';
	}

	$additionalParams = '';
	foreach ($parameters as $parameter => $value) {
		$additionalParams .= sprintf("&tx_displaycontroller[%s]=%s", $parameter, $value);
	}

	/** @var $contentObject \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer */
	$contentObject = $GLOBALS['TSFE']->cObj;
	$config['returnLast'] = 'url';
	$config['parameter'] = $GLOBALS['TSFE']->id;
	$config['additionalParams'] = $additionalParams;
	print $contentObject->typoLink('', $config);
}

/**
 * @param $uid
 */
function linkToDetail($uid) {
	/** @var $contentObject \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer */
	$contentObject = $GLOBALS['TSFE']->cObj;
	$config = array();
	$config['returnLast'] = 'url';
	$config['parameter'] = 314;
	$config['useCacheHash'] = 1;
	$config['additionalParams'] = '&tx_displaycontroller[job]=' . $uid;
	print $contentObject->typoLink('', $config);
}

/**
 * @param array $datastructure
 * @param array $filter
 */
function numberOfResults($datastructure, $filter) {
	$extensionName = 'employer_searchform';

	$total = $datastructure['tx_employer_jobs']['totalCount'];
	if ($datastructure['tx_employer_jobs']['totalCount'] > ($filter['limit']['offset'] + 1) * $filter['limit']['max']) {
		$total = ($filter['limit']['offset'] + 1) * $filter['limit']['max'];
	}

	print sprintf('%s %d-%d %s %d',
		\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('advertisements', $extensionName),
		$filter['limit']['offset'] * $filter['limit']['max'] + 1,
		$total,
		\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('on', $extensionName),
		$datastructure['tx_employer_jobs']['totalCount']
	);
}

/**
 * Helper function to display the page browser
 *
 * @param array $datastructure
 * @param array $filter
 */
function pageBrowser($datastructure, $filter) {

	/** @var $contentObject \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer */
	$contentObject = $GLOBALS['TSFE']->cObj;
	$conf = $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_pagebrowse_pi1.'];
	$conf['pageParameterName'] = 'tx_displaycontroller|page';

	// Adds limit to the query and calculates the number of pages.
	if ($filter['limit']['max'] != '' && $filter['limit']['max'] != '0') {

		// Defines other possible pagebrowse configuration options
		$conf['templateFile'] = 'fileadmin/templates/general/ext/pagebrowse_job.html';
		$conf['numberOfPages'] = ceil($datastructure['tx_employer_jobs']['totalCount'] / $filter['limit']['max']);
		$conf['items_per_page'] = $filter['limit']['max'];
		$conf['disableCacheHash'] = TRUE;
		$conf['total_items'] = $datastructure['tx_employer_jobs']['totalCount'];
		$conf['total_pages'] = $conf['numberOfPages']; // duplicated, because $conf['numberOfPages'] is protected
	} else {
		$conf['numberOfPages'] = 1;
	}

	print $contentObject->cObjGetSingle('USER', $conf);
}

/**
 * Save parameters in Session
 *
 * NOTICE: One could argue Tesseract offers a mechanism to store filter in session as well
 * However, it turned out to be fairly complicated to write the correct syntax.
 * Consider the key below which contains a "0" index which is influenced by the position of the filter.
 *
 * E.g job_search|filters|score|0|value
 *
 * This could quickly become  error-prone.
 *
 * @param $parameters array The parameters coming from the URL
 */
function saveParameters($parameters) {
	/** @var $user \TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication */
	$user = $GLOBALS['TSFE']->fe_user;

	if (empty($parameters['sort'])) {
		$parameters['sort'] = 'no_filter.score';
		$parameters['order'] = '';
	}

	$values = array();
	foreach ($parameters as $parameter => $value) {
		$values['filters'][$parameter] = $value;
	}
	$user->setKey('ses', 'job_search', $values);
}

<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "phpdisplay".
 *
 * Auto generated 11-06-2017 18:31
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array (
  'title' => 'Plain PHP based Data Display (Data Consumer) - Tesseract project',
  'description' => 'Use Plain PHP templates to display any kind of data returned by a Data Provider. More info on http://www.typo3-tesseract.com.',
  'category' => 'fe',
  'author' => 'Fabien Udriot',
  'author_email' => 'fabien.udriot@ecodev.ch',
  'state' => 'stable',
  'uploadfolder' => 0,
  'createDirs' => '',
  'clearCacheOnLoad' => 0,
  'author_company' => '',
  'version' => '2.1.0',
  'constraints' => 
  array (
    'depends' => 
    array (
      'typo3' => '7.6.0-8.99.99',
      'tesseract' => '2.0.0-0.0.0',
    ),
    'conflicts' => 
    array (
    ),
    'suggests' => 
    array (
    ),
  ),
  '_md5_values_when_last_written' => 'a:25:{s:9:"ChangeLog";s:4:"d2db";s:11:"LICENSE.txt";s:4:"6404";s:9:"README.md";s:4:"4279";s:13:"composer.json";s:4:"366a";s:12:"ext_icon.png";s:4:"55ad";s:17:"ext_localconf.php";s:4:"c53b";s:14:"ext_tables.php";s:4:"3025";s:14:"ext_tables.sql";s:4:"b2b4";s:34:"Classes/Component/DataConsumer.php";s:4:"19b0";s:28:"Classes/Utility/Template.php";s:4:"9651";s:44:"Configuration/TCA/tx_phpdisplay_displays.php";s:4:"0b44";s:42:"Configuration/TCA/Overrides/tt_content.php";s:4:"01a5";s:54:"Configuration/TCA/Overrides/tx_phpdisplay_displays.php";s:4:"6748";s:26:"Documentation/Includes.txt";s:4:"6d5f";s:23:"Documentation/Index.rst";s:4:"6d51";s:26:"Documentation/Settings.yml";s:4:"2528";s:36:"Documentation/BugReporting/Index.rst";s:4:"1d35";s:41:"Documentation/Images/PhpDisplayRecord.png";s:4:"03ae";s:36:"Documentation/Installation/Index.rst";s:4:"84bd";s:36:"Documentation/Introduction/Index.rst";s:4:"f800";s:34:"Documentation/UserManual/Index.rst";s:4:"349f";s:43:"Resources/Private/Language/locallang_db.xlf";s:4:"1bfe";s:37:"Resources/Public/Icons/PhpDisplay.png";s:4:"55ad";s:20:"Samples/Advanced.php";s:4:"57e4";s:18:"Samples/Simple.php";s:4:"8743";}',
);


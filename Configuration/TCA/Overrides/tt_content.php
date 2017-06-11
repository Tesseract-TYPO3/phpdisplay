<?php

// Add a wizard for adding a phpdisplay
$addTemplateDisplayWizard = array(
        'type' => 'script',
        'title' => 'LLL:EXT:phpdisplay/Resources/Private/Language/locallang_db.xlf:wizards.add_phpdisplay',
        'icon' => 'EXT:phpdisplay/Resources/Public/Icons/PhpDisplay.png',
        'module' => array(
                'name' => 'wizard_add'
        ),
        'params' => array(
                'table' => 'tx_phpdisplay_displays',
                'pid' => '###CURRENT_PID###',
                'setValue' => 'set'
        )
);
$GLOBALS['TCA']['tt_content']['columns']['tx_displaycontroller_consumer']['config']['wizards']['add_phpdisplay'] = $addTemplateDisplayWizard;

// Register phpdisplay with the Display Controller as a Data Consumer
$GLOBALS['TCA']['tt_content']['columns']['tx_displaycontroller_consumer']['config']['allowed'] .= ',tx_phpdisplay_displays';

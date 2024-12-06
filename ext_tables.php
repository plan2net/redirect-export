<?php

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addModule(
    'site',
    'redirects',
    '',
    '',
    [
        'routeTarget' => \Plan2net\RedirectExport\Controller\RedirectExportController::class . '::handleRequest',
        'access' => 'group,user',
        'name' => 'site_redirects',
        'icon' => 'EXT:redirects/Resources/Public/Icons/Extension.svg',
        'labels' => 'LLL:EXT:redirects/Resources/Private/Language/locallang_module_redirect.xlf'
    ]
);

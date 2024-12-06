<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Redirect Export',
    'description' => 'Adds CSV export functionality to TYPO3 redirect module',
    'category' => 'module',
    'author' => 'Wolfgang Klinger',
    'author_email' => 'wk@plan2.net',
    'state' => 'stable',
    'version' => '1.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '9.5.0-9.5.99',
            'redirects' => '*',
        ],
    ],
];

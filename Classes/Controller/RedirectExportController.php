<?php

declare(strict_types=1);

namespace Plan2net\RedirectExport\Controller;

use Plan2net\RedirectExport\Repository\Demand;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Template\Components\ButtonBar;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Http\Stream;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Redirects\Controller\ManagementController;
use TYPO3\CMS\Redirects\Repository\RedirectRepository;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Redirects\Service\UrlService;

final class RedirectExportController extends ManagementController
{
    private const CSV_HEADERS = [
        'source_host',
        'source_path',
        'target',
        'status_code',
        'page_id',
        'disabled',
        'starttime',
        'endtime',
        'hitcount',
        'last_hit',
        'is_regexp'
    ];

    public function handleRequest(ServerRequestInterface $request): ResponseInterface
    {
        if ($request->getQueryParams()['action'] === 'export') {
            return $this->exportAction($request);
        }

        $this->registerExportButton();

        return parent::handleRequest($request);
    }

    protected function registerExportButton(): void
    {
        $buttonBar = $this->moduleTemplate->getDocHeaderComponent()->getButtonBar();
        $iconFactory = GeneralUtility::makeInstance(IconFactory::class);
        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);

        $exportButton = $buttonBar->makeLinkButton()
            ->setHref(
                (string)$uriBuilder->buildUriFromRoute(
                    'site_redirects',
                    ['action' => 'export']
                )
            )
            ->setTitle($this->getLanguageService()->sL(
                'LLL:EXT:redirect_export/Resources/Private/Language/locallang.xlf:export.title'
            ))
            ->setIcon($iconFactory->getIcon('actions-download', Icon::SIZE_SMALL));

        $buttonBar->addButton($exportButton, ButtonBar::BUTTON_POSITION_LEFT, 20);
    }

    protected function exportAction(ServerRequestInterface $request): ResponseInterface
    {
        $redirects = $this->fetchRedirectRecords($request);
        $csvData = $this->generateCsvData($redirects);

        return $this->createCsvResponse($csvData);
    }

    protected function fetchRedirectRecords(ServerRequestInterface $request): array
    {
        $demand = Demand::createFromRequest($request);
        // Essentially remove the limit to fetch all redirects
        $demand->setLimit(999999999);

        $redirectRepository = GeneralUtility::makeInstance(RedirectRepository::class, $demand);
        return $redirectRepository->findRedirectsByDemand();
    }

    protected function generateCsvData(array $redirects): string
    {
        $this->initializeView('Export');
        $this->view->assignMultiple([
            'redirects' => $redirects,
            'defaultUrl' => GeneralUtility::makeInstance(UrlService::class)->getDefaultUrl(),
        ]);

        $output = $this->view->render();
        $rows = array_filter(explode("\n", trim($output)));

        return $this->convertToCsv($rows);
    }

    protected function convertToCsv(array $rows): string
    {
        $csv = fopen('php://temp', 'w+');

        fputcsv($csv, self::CSV_HEADERS);
        foreach ($rows as $row) {
            $data = explode('|', $row);
            fputcsv($csv, $data);
        }

        rewind($csv);
        $content = stream_get_contents($csv);
        fclose($csv);

        return $content;
    }

    protected function createCsvResponse(string $csvContent): ResponseInterface
    {
        $response = new Response();
        $body = new Stream('php://temp', 'w+');
        $body->write($csvContent);

        return $response
            ->withHeader('Content-Type', 'text/csv; charset=utf-8')
            ->withHeader('Content-Disposition', 'attachment; filename="redirects.csv"')
            ->withBody($body);
    }

    protected function initializeView(string $templateName): void
    {
        parent::initializeView($templateName);
        $this->view->setTemplateRootPaths([
            'EXT:redirects/Resources/Private/Templates/Management',
            'EXT:redirect_export/Resources/Private/Templates/Management'
        ]);
    }
}

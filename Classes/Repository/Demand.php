<?php

declare(strict_types=1);

namespace Plan2net\RedirectExport\Repository;

use Psr\Http\Message\ServerRequestInterface;

class Demand extends \TYPO3\CMS\Redirects\Repository\Demand
{
    public static function createFromRequest(ServerRequestInterface $request): \TYPO3\CMS\Redirects\Repository\Demand
    {
        $page = (int)($request->getQueryParams()['page'] ?? $request->getParsedBody()['page'] ?? 1);
        $demand = $request->getQueryParams()['demand'] ?? $request->getParsedBody()['demand'];
        if (empty($demand)) {
            return new self($page);
        }

        $sourceHost = $demand['source_host'] ?? '';
        $sourcePath = $demand['source_path'] ?? '';
        $statusCode = (int)($demand['target_statuscode'] ?? 0);
        $target = $demand['target'] ?? '';

        return new self($page, $sourceHost, $sourcePath, $target, $statusCode);
    }

    public function setLimit(int $limit): void
    {
        $this->limit = $limit;
    }
}

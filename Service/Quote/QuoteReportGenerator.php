<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Quote;

use DateTime;
use Ekyna\Bundle\CommerceBundle\Model\QuoteInterface;
use Ekyna\Component\Commerce\Quote\Repository\QuoteRepositoryInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

/**
 * Class QuoteReportGenerator
 * @package Ekyna\Bundle\CommerceBundle\Service\Quote
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class QuoteReportGenerator
{
    public function __construct(
        private readonly QuoteRepositoryInterface $quoteRepository,
        private readonly TranslatorInterface      $translator,
        private readonly Environment              $twig
    ) {
    }

    public function generate(): array
    {
        $groups = $this->listQuotes();

        $reports = [];
        $today = (new DateTime())->setTime(23, 59, 59, 999999);
        $title = $this->translator->trans('quote.outdated_projects_report', [], 'EkynaCommerce');
        $locale = $this->translator->getLocale();

        foreach ($groups as $email => $quotes) {
            $reports[$email] = $this->twig->render('@EkynaCommerce/Admin/Report/quotes.html.twig', [
                'title'  => $title,
                'quotes' => $quotes,
                'today'  => $today,
                'locale' => $locale,
            ]);
        }

        return $reports;
    }

    private function listQuotes(): array
    {
        $quotes = $this->quoteRepository->findObsoleteProjects();
        if (empty($quotes)) {
            return [];
        }

        $groups = [];

        /** @var QuoteInterface $quote */
        foreach ($quotes as $quote) {
            $email = ($inCharge = $quote->getInCharge()) ? $inCharge->getEmail() : '';

            if (!isset($groups[$email])) {
                $groups[$email] = [];
            }

            $groups[$email][] = $quote;
        }

        return $groups;
    }
}

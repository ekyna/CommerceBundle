<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Report;

use Ekyna\Bundle\AdminBundle\Service\Mailer\MailerHelper;
use Ekyna\Component\Commerce\Common\Util\FormatterFactory;
use Ekyna\Component\Commerce\Report\ReportConfig;
use Ekyna\Component\Commerce\Report\ReportGenerator;
use Ekyna\Component\Commerce\Report\ReportRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

use function array_map;
use function array_slice;
use function end;
use function file_get_contents;
use function implode;
use function sprintf;

/**
 * Class ReportMailer
 * @package Ekyna\Bundle\CommerceBundle\Service\Report
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ReportMailer
{
    public function __construct(
        private readonly ReportGenerator     $generator,
        private readonly ReportRegistry      $registry,
        private readonly FormatterFactory    $formatterFactory,
        private readonly MailerHelper        $mailerHelper,
        private readonly TranslatorInterface $translator,
        private readonly Environment         $twig,
        private readonly MailerInterface     $mailer
    ) {
    }

    public function send(ReportConfig $config, LoggerInterface $logger = null): void
    {
        $path = $this->generator->generate($config, $logger);

        $sections = [];
        foreach ($config->getSections() as $section) {
            $sections[] = $this->registry->findSectionByName($section)->getTitle()->trans($this->translator);
        }

        $strong = fn(string $value): string => sprintf('<strong>%s</strong>', $value);

        $sections = array_map($strong, $sections);

        $sections = sprintf(
            '%s %s %s',
            implode(', ', array_slice($sections, 0, count($sections) - 1)),
            $this->translator->trans('value.and', [], 'EkynaUi'),
            end($sections)
        );

        $formatter = $this->formatterFactory->create($config->locale);

        $subject = $this->translator->trans('report.email.subject', [], 'EkynaCommerce');

        $message = $this->translator->trans('report.email.body', [
            '{sections}' => $sections,
            '{start}'    => $strong($formatter->date($config->range->getStart())),
            '{end}'      => $strong($formatter->date($config->range->getEnd())),
        ], 'EkynaCommerce');

        $body = $this->twig->render('@EkynaCommerce/Email/sales_report.html.twig', [
            'subject' => $subject,
            'message' => $message,
            'locale'  => $config->locale,
        ]);

        $message = new Email();
        $message
            ->from($this->mailerHelper->getNotificationSender())
            ->to($config->email)
            ->subject($subject)
            ->html($body);

        $message->attach(
            file_get_contents($path),
            sprintf(
                'sales_report_%s_%s.xls',
                $config->range->getStart()->format('Y-m-d'),
                $config->range->getEnd()->format('Y-m-d')
            ),
            'application/vnd.ms-excel'
        );

        $this->mailer->send($message);
    }
}

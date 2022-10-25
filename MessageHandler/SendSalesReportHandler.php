<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\MessageHandler;

use Ekyna\Bundle\CommerceBundle\Message\SendSalesReport;
use Ekyna\Bundle\CommerceBundle\Service\Report\ReportMailer;

/**
 * Class SendSalesReportHandler
 * @package Ekyna\Bundle\CommerceBundle\MessageHandler
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SendSalesReportHandler
{
    public function __construct(
        private readonly ReportMailer $mailer,
    ) {
    }

    public function __invoke(SendSalesReport $message): void
    {
        $config = $message->toConfig();

        $this->mailer->send($config);
    }
}

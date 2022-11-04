<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Message;

use DateTime;
use Ekyna\Component\Commerce\Report\ReportConfig;

/**
 * Class SendSalesReport
 * @package Ekyna\Bundle\CommerceBundle\Message
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SendSalesReport
{
    public static function fromConfig(ReportConfig $config): SendSalesReport
    {
        return new SendSalesReport(
            $config->range->getStart()->format('Y-m-d'),
            $config->range->getEnd()->format('Y-m-d'),
            $config->writer,
            $config->getSections(),
            $config->locale,
            $config->email,
            $config->test,
        );
    }

    public function toConfig(): ReportConfig
    {
        $config = new ReportConfig();

        $config->range->setStart(new DateTime($this->start));
        $config->range->setEnd(new DateTime($this->end));
        $config->writer = $this->writer;
        foreach ($this->sections as $section) {
            $config->addSection($section);
        }
        $config->locale = $this->locale;
        $config->email = $this->email;
        $config->test = $this->test;

        return $config;
    }

    public function __construct(
        public readonly string  $start,
        public readonly string  $end,
        public readonly string  $writer,
        public readonly array   $sections,
        public readonly string  $locale,
        public readonly ?string $email = null,
        public readonly bool $test = false,
    ) {
    }
}

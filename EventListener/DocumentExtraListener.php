<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\EventListener;

use Ekyna\Bundle\CommerceBundle\Event\DocumentExtraEvent;
use Ekyna\Bundle\CommerceBundle\Service\Document\DocumentAttributeHelper;

/**
 * Class DocumentExtraListener
 * @package Ekyna\Bundle\CommerceBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class DocumentExtraListener
{
    /**
     * @param array<array{
     *      paths: array{
     *          locale: string,
     *          path: string
     *      },
     *      subjects: array<string>
     *  }> $config
     */
    public function __construct(
        private readonly DocumentAttributeHelper $helper,
        private readonly array                   $config
    ) {
    }

    public function __invoke(DocumentExtraEvent $event): void
    {
        $subject = $event->getSubject();

        $locale = $this->helper->getLocale($subject);
        $default = $this->helper->getDefaultLocale();

        foreach ($this->config as $config) {
            if (!$this->filterConfig($config, $subject)) {
                continue;
            }

            if (isset($config['paths'][$locale])) {
                $event->addPath($config['paths'][$locale]);
                continue;
            }

            if (isset($config['paths'][$default])) {
                $event->addPath($config['paths'][$default]);
                continue;
            }

            $event->addPath(reset($config['paths']));
        }
    }

    private function filterConfig(array $config, object $subject): bool
    {
        if (empty($config['subjects'])) {
            return true;
        }

        foreach ($config['subjects'] as $class) {
            if ($subject instanceof $class) {
                return true;
            }
        }

        return false;
    }
}

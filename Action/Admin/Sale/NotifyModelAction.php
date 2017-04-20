<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Action\Admin\Sale;

use Ekyna\Bundle\CommerceBundle\Model\NotifyModelInterface;
use Ekyna\Bundle\CommerceBundle\Model\OrderInterface;
use Ekyna\Bundle\ResourceBundle\Action\RoutingActionInterface;
use Ekyna\Bundle\ResourceBundle\Action\TranslatorTrait;
use Ekyna\Component\Commerce\Cart\Model\CartInterface;
use Ekyna\Component\Commerce\Common\Model\NotificationTypes;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Quote\Model\QuoteInterface;
use Ekyna\Component\Resource\Action\Permission;
use Ekyna\Component\Resource\Repository\ResourceRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Routing\Route;

use function sprintf;

/**
 * Class NotifyModelAction
 * @package Ekyna\Bundle\CommerceBundle\Action\Admin\Sale
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class NotifyModelAction extends AbstractSaleAction implements RoutingActionInterface
{
    use TranslatorTrait;

    private ResourceRepositoryInterface $notifyModelRepository;
    private string $defaultLocale;

    public function __construct(ResourceRepositoryInterface $notifyModelRepository, string $defaultLocale)
    {
        $this->notifyModelRepository = $notifyModelRepository;
        $this->defaultLocale = $defaultLocale;
    }

    public function __invoke(): Response
    {
        if (!$this->request->isXmlHttpRequest()) {
            return new Response('', Response::HTTP_NOT_FOUND);
        }

        if (!$sale = $this->getSale()) {
            return new Response('', Response::HTTP_NOT_FOUND);
        }

        /** @var NotifyModelInterface $model */
        $model = $this
            ->notifyModelRepository
            ->find($this->request->attributes->getInt('id'));

        if (!$model) {
            return new Response('Model not found', Response::HTTP_NOT_FOUND);
        }

        if ($sale instanceof OrderInterface) {
            $saleType = 'order';
        } elseif ($sale instanceof QuoteInterface) {
            $saleType = 'quote';
        } elseif ($sale instanceof CartInterface) {
            $saleType = 'cart';
        } else {
            throw new InvalidArgumentException("Unexpected sale class.");
        }

        $replacements = [
            '%type%'   => $saleType,
            '%number%' => $sale->getNumber(),
        ];

        $modelType = $model->getType();
        $locale = $this->request->query->getAlpha('_locale', $this->defaultLocale);

        $translation = $model->translate($locale);

        if (!empty($subject = $translation->getSubject())) {
            $subject = strtr($translation->getSubject(), $replacements);
        } elseif ($modelType !== NotificationTypes::MANUAL) {
            $trans = sprintf('notify.type.%s.subject', $modelType);
            if ($trans === $subject = $this->trans($trans, $replacements, 'EkynaCommerce', $locale)) {
                $subject = '';
            }
        }

        if (!empty($message = $translation->getMessage())) {
            $message = strtr($translation->getMessage(), $replacements);
        } elseif ($modelType !== NotificationTypes::MANUAL) {
            $trans = sprintf('notify.type.%s.message', $modelType);
            if ($trans === $message = $this->trans($trans, $replacements, 'EkynaCommerce', $locale)) {
                $message = '';
            }
        }

        return new JsonResponse([
            'subject' => $subject,
            'message' => $message,
        ]);
    }

    public static function configureAction(): array
    {
        return [
            'name'       => 'commerce_sale_notify_model',
            'permission' => Permission::READ,
            'route'      => [
                'name'     => 'admin_%s_notify_model',
                'path'     => '/notify-model/{id}',
                'resource' => true,
                'methods'  => ['GET'],
            ],
        ];
    }

    public static function buildRoute(Route $route, array $options): void
    {
        $route->addRequirements([
            'id' => '\d+',
        ]);
    }
}

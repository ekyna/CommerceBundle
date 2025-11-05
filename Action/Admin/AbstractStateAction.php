<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Action\Admin;

use Ekyna\Bundle\AdminBundle\Action\AdminActionInterface;
use Ekyna\Bundle\AdminBundle\Action\Util\BreadcrumbTrait;
use Ekyna\Bundle\ResourceBundle\Action as RA;
use Ekyna\Bundle\UiBundle\Action\FlashTrait;
use Ekyna\Bundle\UiBundle\Form\Type\ConfirmType;
use Ekyna\Bundle\UiBundle\Form\Type\FormActionsType;
use Ekyna\Component\Resource\Event\ResourceMessage;
use Ekyna\Component\Resource\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatableInterface;

use function Symfony\Component\Translation\t;

/**
 * Class AbstractStateAction
 * @package Ekyna\Bundle\CommerceBundle\Action\Admin
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractStateAction extends RA\AbstractAction implements AdminActionInterface
{
    use RA\FormTrait;
    use RA\HelperTrait;
    use RA\ManagerTrait;
    use RA\TemplatingTrait;
    use BreadcrumbTrait;
    use FlashTrait;

    public function __invoke(): Response
    {
        $resource = $this->context->getResource();

        $transition = $this->configureTransition();

        if (!$resource instanceof $transition['resource']) {
            throw new UnexpectedTypeException($resource, $transition['resource']);
        }

        $redirect = $this->redirect($this->generateResourcePath($resource));

        if (!in_array($resource->getState(), $transition['from_states'], true)) {
            $this->addFlash('Unexpected state', ResourceMessage::TYPE_ERROR);

            return $redirect;
        }

        if (null !== $response = $this->init()) {
            return $response;
        }

        $form = $this->createConfirmForm();

        $form->handleRequest($this->request);

        if ($form->isSubmitted() && $form->isValid()) {
            $resource->setState($transition['to_state']);

            $event = $this->getManager()->save($resource);

            $this->addFlashFromEvent($event);

            if (!$event->hasErrors()) {
                return $redirect;
            }
        }

        $this->breadcrumbFromContext($this->context);

        $config = $this->context->getConfig();

        $parameters = [
            'context'                   => $this->context,
            $config->getCamelCaseName() => $this->context->getResource(),
            'form_template'             => $this->options['form_template'],
            'form'                      => $form->createView(),
            'title'                     => $transition['title'],
        ];

        return $this
            ->render($this->options['template'], $parameters)
            ->setPrivate();
    }

    protected function init(): ?Response
    {
        return null;
    }

    protected function getTransition(): array
    {
        return [
            'message'   => t('production_order.message.schedule', [], 'EkynaCommerce'),
            'form_data' => false,
        ];
    }

    /**
     * @return array{
     *     resource: string,
     *     from_states: array<string>,
     *     to_state: string,
     *     title: string,
     *     form_data: bool,
     *     message: string|TranslatableInterface
     * }
     */
    abstract protected function configureTransition(): array;

    protected function createConfirmForm(): FormInterface
    {
        $resource = $this->context->getResource();

        $config = $this->configureTransition();

        $form = $this->createForm(ConfirmType::class, $config['form_data'] ? $resource : null, [
            'action'            => $this->generateResourcePath($resource, static::class, $this->request->query->all()),
            'method'            => 'POST',
            'admin_mode'        => true,
            '_redirect_enabled' => true,
            'attr'              => ['class' => 'form-horizontal'],
            'message'           => $this->configureTransition()['message'],
        ]);

        $form->add('actions', FormActionsType::class, [
            'buttons' => [
                'submit' => [
                    'type'    => Type\SubmitType::class,
                    'options' => [
                        'button_class' => 'success',
                        'label'        => t('button.confirm', [], 'EkynaUi'),
                        'attr'         => ['icon' => 'ok'],
                    ],
                ],
                'cancel' => [
                    'type'    => Type\ButtonType::class,
                    'options' => [
                        'label'        => t('button.cancel', [], 'EkynaUi'),
                        'button_class' => 'default',
                        'as_link'      => true,
                        'attr'         => [
                            'class' => 'form-cancel-btn',
                            'icon'  => 'remove',
                            'href'  => $this->generateResourcePath($resource),
                        ],
                    ],
                ],
            ],
        ]);

        return $form;
    }

    public static function configureAction(): array
    {
        return [
            'options' => [
                'template'      => '@EkynaCommerce/Admin/change_state.html.twig',
                'form_template' => '@EkynaAdmin/Entity/Crud/_form_confirm.html.twig',
            ],
        ];
    }
}

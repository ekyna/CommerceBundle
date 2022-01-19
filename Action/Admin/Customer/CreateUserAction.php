<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Action\Admin\Customer;

use Ekyna\Bundle\AdminBundle\Action\AdminActionInterface;
use Ekyna\Bundle\AdminBundle\Action\Util\BreadcrumbTrait;
use Ekyna\Bundle\CommerceBundle\Model\CustomerInterface;
use Ekyna\Bundle\ResourceBundle\Action\AbstractAction;
use Ekyna\Bundle\ResourceBundle\Action\FactoryTrait;
use Ekyna\Bundle\ResourceBundle\Action\FormTrait;
use Ekyna\Bundle\ResourceBundle\Action\HelperTrait;
use Ekyna\Bundle\ResourceBundle\Action\ManagerTrait;
use Ekyna\Bundle\ResourceBundle\Action\TemplatingTrait;
use Ekyna\Bundle\UiBundle\Action\FlashTrait;
use Ekyna\Bundle\UiBundle\Form\Type\ConfirmType;
use Ekyna\Bundle\UserBundle\Model\UserInterface;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Resource\Action\Permission;
use Symfony\Component\HttpFoundation\Response;

use function Symfony\Component\Translation\t;

/**
 * Class CreateUserAction
 * @package Ekyna\Bundle\CommerceBundle\Action\Admin\Customer
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class CreateUserAction extends AbstractAction implements AdminActionInterface
{
    use HelperTrait;
    use FlashTrait;
    use FormTrait;
    use ManagerTrait;
    use FactoryTrait;
    use BreadcrumbTrait;
    use TemplatingTrait;

    public function __invoke(): Response
    {
        $customer = $this->context->getResource();

        if (!$customer instanceof CustomerInterface) {
            throw new UnexpectedTypeException($customer, CustomerInterface::class);
        }

        $cancelPath = $this->generateResourcePath($customer);

        if ($customer->getUser()) {
            $this->addFlash(t('customer.message.user_exists', [], 'EkynaCommerce'), 'info');

            return $this->redirect($cancelPath);
        }

        $form = $this->createForm(ConfirmType::class, null, [
            'action'       => $this->generateResourcePath($customer, self::class),
            'method'       => 'POST',
            'message'      => t('customer.message.create_user_confirm', [], 'EkynaCommerce'),
            'cancel_path'  => $cancelPath,
            'submit_class' => 'success',
            'submit_icon'  => 'ok',
        ]);

        $form->handleRequest($this->request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UserInterface $user */
            $user = $this->getFactory(UserInterface::class)->create();
            $user
                ->setSendCreationEmail(true)
                ->setEmail($customer->getEmail())
                ->setEnabled(true);

            $customer->setUser($user);

            $this->getManager(CustomerInterface::class)->persist($customer);

            // TODO Validation ?

            // TODO use ResourceManager
            $event = $this->getManager(UserInterface::class)->create($user);

            $this->addFlashFromEvent($event);

            return $this->redirect($cancelPath);
        }

        $this->breadcrumbFromContext($this->context);

        return $this->render($this->options['template'], [
            'context' => $this->context,
            'form'    => $form->createView(),
        ]);
    }

    public static function configureAction(): array
    {
        return [
            'name'       => 'commerce_customer_create_user',
            'permission' => Permission::READ,
            'route'      => [
                'name'     => 'admin_%s_create_user',
                'path'     => '/create-user',
                'resource' => true,
                'methods'  => ['GET', 'POST'],
            ],
            'button'     => [
                'label'        => 'customer.button.create_user',
                'trans_domain' => 'EkynaCommerce',
                'icon'         => 'user',
            ],
            'options'    => [
                'template' => '@EkynaCommerce/Admin/Customer/create_user.html.twig',
            ],
        ];
    }
}

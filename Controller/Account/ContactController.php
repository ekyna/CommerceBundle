<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Controller\Account;

use Ekyna\Bundle\CommerceBundle\Form\Type\Customer\CustomerContactType;
use Ekyna\Bundle\UiBundle\Form\Type\ConfirmType;
use Ekyna\Bundle\UiBundle\Form\Util\FormUtil;
use Ekyna\Bundle\UiBundle\Service\FlashHelper;
use Ekyna\Component\Commerce\Customer\Model\CustomerContactInterface;
use Ekyna\Component\Commerce\Customer\Repository\CustomerContactRepositoryInterface;
use Ekyna\Component\Commerce\Features;
use Ekyna\Component\Resource\Factory\ResourceFactoryInterface;
use Ekyna\Component\Resource\Manager\ResourceManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

use function Symfony\Component\Translation\t;

/**
 * Class ContactController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Account
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ContactController implements ControllerInterface
{
    use CustomerTrait;

    private Features                           $features;
    private CustomerContactRepositoryInterface $contactRepository;
    private ResourceFactoryInterface           $contactFactory;
    private ResourceManagerInterface           $contactManager;
    private FlashHelper                        $flashHelper;
    private FormFactoryInterface               $formFactory;
    private UrlGeneratorInterface              $urlGenerator;
    private Environment                        $twig;

    public function __construct(
        Features                           $features,
        CustomerContactRepositoryInterface $contactRepository,
        ResourceFactoryInterface           $contactFactory,
        ResourceManagerInterface           $contactManager,
        FlashHelper                        $flashHelper,
        FormFactoryInterface               $formFactory,
        UrlGeneratorInterface              $urlGenerator,
        Environment                        $twig
    ) {
        $this->features = $features;
        $this->contactRepository = $contactRepository;
        $this->contactFactory = $contactFactory;
        $this->contactManager = $contactManager;
        $this->flashHelper = $flashHelper;
        $this->formFactory = $formFactory;
        $this->urlGenerator = $urlGenerator;
        $this->twig = $twig;
    }

    public function index(): Response
    {
        $this->checkFeature();

        $customer = $this->getCustomer();

        $parameters = [];

        $parameters['contacts'] = $this->contactRepository->findByCustomer($customer);

        $content = $this->twig->render('@EkynaCommerce/Account/Contact/index.html.twig', $parameters);

        return (new Response($content))->setPrivate();
    }

    public function add(Request $request): Response
    {
        $this->checkFeature();

        $customer = $this->getCustomer();

        /** @var CustomerContactInterface $contact */
        $contact = $this->contactFactory->create();

        $contact->setCustomer($customer);

        $redirect = $this->urlGenerator->generate('ekyna_commerce_account_contact_index');

        $form = $this->formFactory->create(CustomerContactType::class, $contact, [
            'method' => 'POST',
            'action' => $this->urlGenerator->generate('ekyna_commerce_account_contact_add'),
        ]);

        FormUtil::addFooter($form, [
            'cancel_path' => $redirect,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event = $this->contactManager->save($contact);

            if (!$event->hasErrors()) {
                $this->flashHelper->addFlash(t('account.contact.edit.success', [], 'EkynaCommerce'), 'success');

                return new RedirectResponse($redirect);
            }

            FormUtil::addErrorsFromResourceEvent($form, $event);
        }

        $contacts = $this
            ->contactRepository
            ->findByCustomer($customer);

        $content = $this->twig->render('@EkynaCommerce/Account/Contact/add.html.twig', [
            'form'     => $form->createView(),
            'contacts' => $contacts,
        ]);

        return (new Response($content))->setPrivate();
    }

    public function edit(Request $request): Response
    {
        $this->checkFeature();

        $customer = $this->getCustomer();

        $redirect = $this->urlGenerator->generate('ekyna_commerce_account_contact_index');

        if (null === $contact = $this->findContactByRequest($request)) {
            return new RedirectResponse($redirect);
        }

        $action = $this->urlGenerator->generate('ekyna_commerce_account_contact_edit', [
            'contactId' => $contact->getId(),
        ]);

        $form = $this->formFactory->create(CustomerContactType::class, $contact, [
            'method' => 'post',
            'action' => $action,
        ]);

        FormUtil::addFooter($form, [
            'cancel_path' => $redirect,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event = $this->contactManager->save($contact);

            if (!$event->hasErrors()) {
                $this->flashHelper->addFlash(t('account.contact.edit.success', [], 'EkynaCommerce'), 'success');

                return new RedirectResponse($redirect);
            }

            FormUtil::addErrorsFromResourceEvent($form, $event);
        }

        $contacts = $this->contactRepository->findByCustomer($customer);

        $content = $this->twig->render('@EkynaCommerce/Account/Contact/edit.html.twig', [
            'form'     => $form->createView(),
            'contacts' => $contacts,
        ]);

        return (new Response($content))->setPrivate();
    }

    public function remove(Request $request): Response
    {
        $this->checkFeature();

        $customer = $this->getCustomer();

        $redirect = $this->urlGenerator->generate('ekyna_commerce_account_contact_index');

        if (null === $contact = $this->findContactByRequest($request)) {
            return new RedirectResponse($redirect);
        }

        $action = $this->urlGenerator->generate('ekyna_commerce_account_contact_remove', [
            'contactId' => $contact->getId(),
        ]);

        $form = $this->formFactory->create(ConfirmType::class, null, [
            'method'      => 'post',
            'action'      => $action,
            'message'     => t('account.contact.remove.confirm', [], 'EkynaCommerce'),
            'cancel_path' => $redirect,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event = $this->contactManager->delete($contact);

            if (!$event->hasErrors()) {
                $this->flashHelper->addFlash(t('account.contact.remove.success', [], 'EkynaCommerce'), 'success');

                return new RedirectResponse($redirect);
            }

            FormUtil::addErrorsFromResourceEvent($form, $event);
        }

        $contacts = $this->contactRepository->findByCustomer($customer);

        $content = $this->twig->render('@EkynaCommerce/Account/Contact/remove.html.twig', [
            'contact'  => $contact,
            'form'     => $form->createView(),
            'contacts' => $contacts,
        ]);

        return (new Response($content))->setPrivate();
    }

    protected function findContactByRequest(Request $request): ?CustomerContactInterface
    {
        $this->checkFeature();

        $customer = $this->getCustomer();

        if (0 >= $id = $request->attributes->getInt('contactId')) {
            return null;
        }

        return $this
            ->contactRepository
            ->findOneBy([
                'id'       => $id,
                'customer' => $customer,
            ]);
    }

    /**
     * Checks whether the customer contact feature is enabled in customer account.
     */
    private function checkFeature(): void
    {
        if ($this->features->getConfig(Features::CUSTOMER_CONTACT . '.account')) {
            return;
        }

        throw new NotFoundHttpException('');
    }
}

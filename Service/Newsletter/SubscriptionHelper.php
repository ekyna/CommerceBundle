<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Newsletter;

use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Bundle\CommerceBundle\Model\CustomerInterface;
use Ekyna\Component\Commerce\Newsletter\Event\MemberEvents;
use Ekyna\Component\Commerce\Newsletter\Model\AudienceInterface;
use Ekyna\Component\Commerce\Newsletter\Model\MemberInterface;
use Ekyna\Component\Commerce\Newsletter\Model\MemberStatuses;
use Ekyna\Component\Commerce\Newsletter\Repository\AudienceRepositoryInterface;
use Ekyna\Component\Commerce\Newsletter\Repository\MemberRepositoryInterface;
use Ekyna\Component\Resource\Event\ResourceEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class SubscriptionHelper
 * @package Ekyna\Bundle\CommerceBundle\Service\Newsletter
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SubscriptionHelper
{
    /**
     * @var AudienceRepositoryInterface
     */
    private $audienceRepository;

    /**
     * @var MemberRepositoryInterface
     */
    private $memberRepository;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var EntityManagerInterface
     */
    private $manager;

    /**
     * @var EngineInterface
     */
    private $engine;

    /**
     * @var AudienceInterface[]
     */
    private $audiences;


    /**
     * Constructor.
     *
     * @param AudienceRepositoryInterface $audienceRepository
     * @param MemberRepositoryInterface   $memberRepository
     * @param EventDispatcherInterface    $dispatcher
     * @param ValidatorInterface          $validator
     * @param EntityManagerInterface      $manager
     * @param EngineInterface             $engine
     */
    public function __construct(
        AudienceRepositoryInterface $audienceRepository,
        MemberRepositoryInterface $memberRepository,
        EventDispatcherInterface $dispatcher,
        ValidatorInterface $validator,
        EntityManagerInterface $manager,
        EngineInterface $engine
    ) {
        $this->audienceRepository = $audienceRepository;
        $this->memberRepository   = $memberRepository;
        $this->dispatcher         = $dispatcher;
        $this->validator          = $validator;
        $this->manager            = $manager;
        $this->engine             = $engine;
    }

    /**
     * Renders the newsletter subscription.
     *
     * @param array $parameters
     *
     * @return string
     */
    public function render(array $parameters = []): string
    {
        $parameters = array_replace([
            'public' => true,
            'customer' => null,
            'email' => null,
        ], $parameters);

        $customer = $parameters['customer'];
        if ($customer instanceof CustomerInterface) {
            $parameters['customer'] = $customer->getKey();
            $parameters['email'] = $customer->getEmail();
        }

        $audiences = [];
        foreach ($this->findAudiences($parameters['public']) as $audience) {
            $datum = [
                'key'        => $audience->getKey(),
                'title'      => $audience->getTitle(),
                'subscribed' => false,
            ];

            if (!empty($parameters['email'])) {
                $member = $this->memberRepository->findOneByAudienceAndEmail($audience, $parameters['email']);

                if ($member && $member->getStatus() === MemberStatuses::SUBSCRIBED) {
                    $datum['subscribed'] = true;
                }
            }

            $audiences[] = $datum;
        }

        $parameters['audiences'] = $audiences;

        return $this->engine->render('@EkynaCommerce/Form/newsletter_subscription.html.twig', $parameters);
    }

    /**
     * Subscribes the given to the given audience (by its key).
     *
     * @param string $key   The audience key
     * @param string $email The email address
     *
     * @return array
     */
    public function subscribe(string $key, string $email): array
    {
        $audience = $this->audienceRepository->findOneByKey($key);

        if (!$audience) {
            return [
                'success' => false,
                'errors'  => [
                    $key => 'Audience not found',
                ],
            ];
        }

        $member = $this->memberRepository->findOneByAudienceAndEmail($audience, $email);

        if ($member && ($member->getStatus() === MemberStatuses::SUBSCRIBED)) {
            return [
                'success' => true,
            ];
        }

        if (!$member) {
            /** @var MemberInterface $member */
            $member = $this->memberRepository->createNew();
            $member
                ->setAudience($audience)
                ->setEmail($email);
        }

        $member->setStatus(MemberStatuses::SUBSCRIBED);

        $event = new ResourceEvent();
        $event->setResource($member);
        $this->dispatcher->dispatch(MemberEvents::INITIALIZE, $event);

        $list = $this->validator->validate($member);
        if ($list->count()) {
            $errors = [];
            /** @var \Symfony\Component\Validator\ConstraintViolationInterface $violation */
            foreach ($list as $violation) {
                $index = 'email' === $violation->getPropertyPath() ? 'email' : $key;
                $errors[$index] = $violation->getMessage();
            }

            return [
                'success' => false,
                'errors'  => $errors,
            ];
        }

        try {
            $this->manager->persist($member);
            $this->manager->flush();
        } catch (\Exception $e) {
            return [
                'success' => false,
                'errors'   => [
                    'global' => 'The server encountered an error.',
                ]
            ];
        }

        return [
            'success' => true,
        ];
    }

    /**
     * Subscribes the given to the given audience (by its key).
     *
     * @param string $key   The audience key
     * @param string $email The email address
     *
     * @return array
     */
    public function unsubscribe(string $key, string $email): array
    {
        $audience = $this->audienceRepository->findOneByKey($key);

        if (!$audience) {
            return [
                'success' => true,
            ];
        }

        $member = $this->memberRepository->findOneByAudienceAndEmail($audience, $email);

        if (!$member || ($member->getStatus() === MemberStatuses::UNSUBSCRIBED)) {
            return [
                'success' => true,
            ];
        }

        $member->setStatus(MemberStatuses::UNSUBSCRIBED);

        try {
            $this->manager->persist($member);
            $this->manager->flush();
        } catch (\Exception $e) {
            return [
                'success' => false,
                'errors'   => [
                    'global' => 'The server encountered an error.',
                ]
            ];
        }

        return [
            'success' => true,
        ];
    }

    /**
     * Returns the audiences.
     *
     * @return array
     */
    private function findAudiences(bool $public): array
    {
        if ($public) {
            return $this->audienceRepository->findPublic();
        }

        return $this->audienceRepository->findAll();
    }
}

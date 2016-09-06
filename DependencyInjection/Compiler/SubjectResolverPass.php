<?php

namespace Ekyna\Bundle\CommerceBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class SubjectResolverPass
 * @package Ekyna\Bundle\CommerceBundle\DependencyInjection\Compiler
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SubjectResolverPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('ekyna_commerce.subject.resolver_registry')) {
            return;
        }

        $registryDefinition = $container->getDefinition('ekyna_commerce.subject.resolver_registry');

        $resolvers = $container->findTaggedServiceIds('ekyna_commerce.subject_resolver');

        foreach ($resolvers as $id => $attributes) {
            $resolverDefinition = $container->getDefinition($id);

            // Inject url generator
            $resolverDefinition->addMethodCall(
                'setUrlGenerator',
                [new Reference('router')]
            );

            // Register the resolver
            $registryDefinition->addMethodCall('addResolver', [new Reference($id)]);
        }
    }
}

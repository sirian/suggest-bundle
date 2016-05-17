<?php

namespace Sirian\SuggestBundle;

use Sirian\SuggestBundle\DependencyInjection\Compiler\CustomSuggestersCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class SirianSuggestBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new CustomSuggestersCompilerPass());
    }

}

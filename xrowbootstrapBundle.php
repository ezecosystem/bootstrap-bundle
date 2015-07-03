<?php

namespace xrow\bootstrapBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class xrowbootstrapBundle extends Bundle
{
    /**
     * {@inheritDoc}
     */
    public function getContainerExtension()
    {
        return new DependencyInjection\xrowbootstrapExtension();
    }
}
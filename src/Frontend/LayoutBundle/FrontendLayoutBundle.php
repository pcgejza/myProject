<?php

namespace Frontend\LayoutBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class FrontendLayoutBundle extends Bundle
{   
    public function getParent()
    {
        return 'FOSUserBundle';
    }
}

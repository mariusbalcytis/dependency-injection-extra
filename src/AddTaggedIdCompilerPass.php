<?php

namespace Maba\Component\DependencyInjection;

class AddTaggedIdCompilerPass extends AddTaggedCompilerPass
{

    protected function resolveServiceId($id)
    {
        return $id;
    }
}
<?php

namespace App\Http\Requests\Concerns;

use Symfony\Component\HttpFoundation\Request;

trait HasStoreUpdateLogic
{
    protected function isStore(): bool
    {
        return $this->method() === Request::METHOD_POST;
    }

    protected function isUpdate(): bool
    {
        return in_array($this->method(), [
            Request::METHOD_PUT,
            Request::METHOD_PATCH
        ]);
    }
}

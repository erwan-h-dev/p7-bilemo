<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class VersioningService
{
    private string $defaultVersion;

    public function __construct(
        private RequestStack $requestStack,
        private ParameterBagInterface $params
    ){
        $this->defaultVersion = $this->params->get('default_api_version');
    }

    public function getVersion(): string
    {
        $version = $this->defaultVersion;

        $request = $this->requestStack->getCurrentRequest();
        $accept = $request->headers->get('Accept');

        $entete = explode(',', $accept);

        foreach ($entete as $value) {
            if(strpos($value, 'version') !== false) {
                $version = explode('=', $value)[1];
            }
        }

        return $version;
    }
}
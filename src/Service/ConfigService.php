<?php


namespace App\Service;

use App\Repository\ConfigRepository;

class ConfigService
{
    const CONFIG_NUM_TVA = 1;
    protected $configRepository;

    public function __construct(ConfigRepository $configRepository)
    {
        $this->configRepository = $configRepository;
    }

    public function findTva(){
        $config = $this->configRepository->findConfigByNum(ConfigService::CONFIG_NUM_TVA);
        return $config ? ($config->getVal() ?? 0) : 0;
    }
}
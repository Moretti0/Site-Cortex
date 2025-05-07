<?php
namespace Cortex\Pdf\Api;

use Cortex\Pdf\Model\Template\Config;
use Cortex\Pdf\Model\ResourceModel\Template\Config\Collection;

interface TemplateConfigRepositoryInterface
{
    public function getById(int $id): Config;

    public function save(Config $template): Config;

    public function delete(Config $template): bool;

    public function getList(): Collection;
}

<?php
namespace Cortex\Pdf\Api;

use Cortex\Pdf\Model\Category\Style;
use Cortex\Pdf\Model\ResourceModel\Category\Style\Collection;

interface CategoryStyleRepositoryInterface
{
    public function getByCategoryId(int $categoryId): ?Style;

    public function save(Style $style): Style;

    public function delete(Style $style): bool;

    public function getList(): Collection;
}

<?php
/**
 * @package     NickVulkers\Media
 * @author      <contact@nickvulkers.com>
 * @copyright   2023 Nick Vulkers
 * @license     MIT
 */

declare(strict_types=1);

namespace NickVulkers\CleanMedia\Helper;

use Magento\Catalog\Model\ResourceModel\Product\Gallery;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Filesystem;
use Zend_Db_Select;

class CatalogMediaHelper extends AbstractHelper
{
    /**
     * @var ResourceConnection
     */
    private ResourceConnection $resource;
    /**
     * @var Filesystem
     */
    private Filesystem $filesystem;

    public function __construct(
        Context $context,
        ResourceConnection $resource,
        Filesystem         $filesystem,
    )
    {
        $this->resource = $resource;
        $this->filesystem = $filesystem;

        parent::__construct($context);
    }

    /**
     * @return array
     */
    public function getMediaGalleryPaths(): array
    {
        $connection = $this->resource->getConnection();
        $select = $connection->select()
            ->from($this->resource->getTableName(Gallery::GALLERY_TABLE))
            ->reset(Zend_Db_Select::COLUMNS)->columns('value');

        return $connection->fetchCol($select);
    }

    /**
     * @return string
     */
    private function getMediaPath(): string
    {
        return $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();
    }

    /**
     * @return string
     */
    public function getProductMediaPath(): string
    {
        return $this->getMediaPath() . 'catalog/product';
    }
}
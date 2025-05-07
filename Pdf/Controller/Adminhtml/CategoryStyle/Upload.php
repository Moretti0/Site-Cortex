<?php
namespace Cortex\Pdf\Controller\Adminhtml\CategoryStyle;

use Magento\Backend\App\Action;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\MediaStorage\Model\File\Uploader;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;

class Upload extends Action
{
    protected $uploaderFactory;
    protected $resultRawFactory;
    protected $filesystem;

    public function __construct(
        Action\Context $context,
        UploaderFactory $uploaderFactory,
        RawFactory $resultRawFactory,
        Filesystem $filesystem
    ) {
        parent::__construct($context);
        $this->uploaderFactory = $uploaderFactory;
        $this->resultRawFactory = $resultRawFactory;
        $this->filesystem = $filesystem;
    }

    public function execute()
    {
        $result = ['error' => __('Upload inválido.')];
        try {
            $uploader = $this->uploaderFactory->create(['fileId' => 'header_image']);
            $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png', 'svg']);
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(false);

            $mediaDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
            $target = 'cortex/pdf/style';
            $result = $uploader->save($mediaDirectory->getAbsolutePath($target));
            $result['url'] = $this->_url->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]) . $target . '/' . $result['file'];
        } catch (\Exception $e) {
            $result = ['error' => $e->getMessage()];
        }

        return $this->resultRawFactory->create()->setHeader('Content-Type', 'application/json')->setContents(json_encode($result));
    }
}

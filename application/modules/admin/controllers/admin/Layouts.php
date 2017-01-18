<?php
/**
 * @copyright Ilch 2.0
 * @package ilch
 */

namespace Modules\Admin\Controllers\Admin;

use Modules\Admin\Mappers\Module as ModuleMapper;
use Modules\Admin\Models\Layout as LayoutModel;
use Ilch\Transfer as Transfer;

class Layouts extends \Ilch\Controller\Admin
{
    public function init()
    {
        $items = [
            [
                'name' => 'manage',
                'active' => false,
                'icon' => 'fa fa-th-list',
                'url' => $this->getLayout()->getUrl(['controller' => 'layouts', 'action' => 'index'])
            ],
            [
                'name' => 'menuSearch',
                'active' => false,
                'icon' => 'fa fa-search',
                'url' => $this->getLayout()->getUrl(['controller' => 'layouts', 'action' => 'search'])
            ],
            [
                'name' => 'menuSettings',
                'active' => false,
                'icon' => 'fa fa-cogs',
                'url' => $this->getLayout()->getUrl(['controller' => 'layouts', 'action' => 'settings'])
            ]
        ];

        if ($this->getRequest()->getActionName() == 'index') {
            $items[0]['active'] = true;
        } elseif ($this->getRequest()->getActionName() == 'search' OR $this->getRequest()->getActionName() == 'show') {
            $items[1]['active'] = true;
        } elseif ($this->getRequest()->getActionName() == 'settings') {
            $items[2]['active'] = true;
        } else {
            $items[0]['active'] = true;
        }

        $this->getLayout()->addMenu
        (
            'menuLayouts',
            $items
        );
    }

    public function indexAction()
    {
        $this->getLayout()->getAdminHmenu()
                ->add($this->getTranslator()->trans('menuLayouts'), ['action' => 'index']);

        $layouts = [];
        foreach (glob(APPLICATION_PATH.'/layouts/*') as $layoutPath) {
            $configClass = '\\Layouts\\'.ucfirst(basename($layoutPath)).'\\Config\\Config';
            $config = new $configClass($this->getTranslator());
            $model = new LayoutModel();
            $model->setKey(basename($layoutPath));
            $model->setName($config->config['name']);
            $model->setVersion($config->config['version']);
            $model->setAuthor($config->config['author']);
            if (!empty($config->config['link'])) {
                $model->setLink($config->config['link']);
            }
            $model->setDesc($config->config['desc']);
            if (!empty($config->config['modulekey'])) {
                $model->setModulekey($config->config['modulekey']);
            }
            $layouts[] = $model;
            $versionsOfLayouts[basename($layoutPath)] = $config->config['version'];
        }

        $this->getView()->set('defaultLayout', $this->getConfig()->get('default_layout'));
        $this->getView()->set('layouts', $layouts);
        $this->getView()->set('versionsOfLayouts', $versionsOfLayouts);
    }

    public function defaultAction()
    {
        $this->getConfig()->set('default_layout', $this->getRequest()->getParam('key'));
        $this->redirect(['action' => 'index']);
    }

    public function searchAction()
    {
        $this->getLayout()->getAdminHmenu()
                ->add($this->getTranslator()->trans('menuLayouts'), ['action' => 'index'])
                ->add($this->getTranslator()->trans('menuSearch'), ['action' => 'search']);

        try {
            if ($this->getRequest()->isSecure()) {
                $transfer = new Transfer();
                $transfer->setZipSavePath(ROOT_PATH.'/updates/');
                $transfer->setDownloadUrl($this->getRequest()->getPost('url'));
                $transfer->setDownloadSignatureUrl($this->getRequest()->getPost('url').'-signature.sig');

                if (!$transfer->validateCert(ROOT_PATH.'/certificate/Certificate.crt')) {
                    // Certificate is missing or expired.
                    $this->addMessage('certMissingOrExpired', 'danger');
                    return;
                }

                if (!$transfer->save()) {
                    $this->addMessage('layoutVerificationFailed', 'danger');
                    return;
                }

                if (!$transfer->install()) {
                    $this->addMessage('layoutInstallationFailed', 'danger');
                    return;
                }

                $this->addMessage('downSuccess');
            }
        } finally {
            foreach (glob(ROOT_PATH.'/application/layouts/*') as $layoutPath) {
                $configClass = '\\Layouts\\'.ucfirst(basename($layoutPath)).'\\Config\\Config';
                $config = new $configClass($this->getTranslator());
                $versionsOfLayouts[basename($layoutPath)] = $config->config['version'];
                $layoutsDir[] = basename($layoutPath);
            }

            $this->getView()->set('versionsOfLayouts', $versionsOfLayouts);
            $this->getView()->set('layouts', $layoutsDir);
        }
    }

    public function updateAction()
    {
        if ($this->getRequest()->isSecure()) {
            try {
                $transfer = new Transfer();
                $transfer->setZipSavePath(ROOT_PATH.'/updates/');
                $transfer->setDownloadUrl($this->getRequest()->getPost('url'));
                $transfer->setDownloadSignatureUrl($this->getRequest()->getPost('url').'-signature.sig');

                if (!$transfer->validateCert(ROOT_PATH.'/certificate/Certificate.crt')) {
                    // Certificate is missing or expired.
                    $this->addMessage('certMissingOrExpired', 'danger');
                    return;
                }

                if (!$transfer->save()) {
                    $this->addMessage('layoutVerificationFailed', 'danger');
                    return;
                }
                
                if (!$transfer->update($this->getRequest()->getParam('version'))) {
                    $this->addMessage('layoutUpdateFailed', 'danger');
                    return;
                }

                $this->addMessage('updateSuccess');
            } finally {
                $this->redirect(['action' => $this->getRequest()->getParam('from')]);
            }
        }
    }

    public function showAction()
    {
        $this->getLayout()->getAdminHmenu()
                ->add($this->getTranslator()->trans('menuLayouts'), ['action' => 'index'])
                ->add($this->getTranslator()->trans('menuSearch'), ['action' => 'search'])
                ->add($this->getTranslator()->trans('menuLayout').' '.$this->getTranslator()->trans('info'), ['action' => 'show', 'id' => $this->getRequest()->getParam('id')]);

        foreach (glob(ROOT_PATH.'/application/layouts/*') as $layoutPath) {
            $layoutsDir[] = basename($layoutPath);
        }

        $this->getView()->set('layouts', $layoutsDir);
    }

    public function settingsAction()
    {
        $this->getLayout()->getAdminHmenu()
                ->add($this->getTranslator()->trans('menuLayouts'), ['action' => 'index'])
                ->add($this->getTranslator()->trans('menuSettings'), ['action' => 'settings']);

        if ($this->getRequest()->isPost()) {
            $this->getConfig()->set('favicon', $this->getRequest()->getPost('favicon'));
            $this->getConfig()->set('apple_icon', $this->getRequest()->getPost('appleIcon'));
            $this->getConfig()->set('page_title', $this->getRequest()->getPost('pageTitle'));
            $this->getConfig()->set('description', $this->getRequest()->getPost('description'));

            $this->addMessage('saveSuccess');
        }

        $this->getView()->set('favicon', $this->getConfig()->get('favicon'));
        $this->getView()->set('appleIcon', $this->getConfig()->get('apple_icon'));
        $this->getView()->set('pageTitle', $this->getConfig()->get('page_title'));
        $this->getView()->set('description', $this->getConfig()->get('description'));
    }

    public function deleteAction()
    {
        if ($this->getConfig()->get('default_layout') == $this->getRequest()->getParam('key')) {
            $this->addMessage('cantDeleteDefaultLayout', 'info');
        } else {
            removeDir(APPLICATION_PATH.'/layouts/'.$this->getRequest()->getParam('key'));
            unlink(ROOT_PATH.'/updates/'.$this->getRequest()->getParam('key').'.zip-signature.sig');

            if (is_dir(APPLICATION_PATH.'/modules/'.$this->getRequest()->getParam('key'))) {
                $modules = new ModuleMapper();

                $configClass = '\\Modules\\'.ucfirst($this->getRequest()->getParam('key')).'\\Config\\Config';
                $config = new $configClass();
                $config->uninstall();
                $modules->delete($this->getRequest()->getParam('key'));

                removeDir(APPLICATION_PATH.'/modules/'.$this->getRequest()->getParam('key'));
            }

            $this->addMessage('deleteSuccess');
        }

        $this->redirect(['action' => 'index']);
    }
}

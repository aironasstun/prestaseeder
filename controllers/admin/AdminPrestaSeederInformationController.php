<?php

class AdminPrestaSeederInformationController extends ModuleAdminController
{
    /** @var bool Is bootstrap used */
    public $bootstrap = true;

    public function __construct()
    {
        $this->className = 'Configuration';
        $this->table = 'configuration';
        $this->identifier = 'id_configuration';

        parent::__construct();
        $this->content .= $this->module->getMenu();
        $this->content .= $this->renderView();
    }

    public function renderView()
    {
        $tpl = $this->context->smarty->createTemplate(_PS_MODULE_DIR_.'prestaseeder/views/templates/admin/information.tpl');
        $tpl->assign(
            array(
                'cronPath' => _PS_BASE_URL_.'/modules/prestaseeder/prestaseeder.cron.php'
            )
        );


        return $tpl->fetch();
    }

}
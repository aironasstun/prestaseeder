<?php

class AdminPrestaSeederSettingsController extends ModuleAdminController
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
        $this->content .= $this->initOptions();
    }


    public function initOptions()
    {
        $taxRates = TaxRulesGroup::getTaxRulesGroups();

        $this->fields_options = array(
            'general' => array(
                'title' => $this->l('Parameters'),
                'fields' => array(
                    'SEEDER_IMG_URL' => array(
                        'title' => $this->l('Random image API'),
                        'desc' => $this->l('Enter your random image api here'),
                        'type' => 'text'
                    ),
                    'SEEDER_DEFAULT_TAX' => array(
                        'title' => $this->l('Default Tax Rule'),
                        'desc' => $this->l('Pick default tax rule that will be used while seeding'),
                        'type' => 'select',
                        'identifier' => 'id_tax_rules_group',
                        'list' => $taxRates,
                    ),
                ),
                'submit' => array('title' => $this->l('Save'),
                ),
            ),
        );
    }

}
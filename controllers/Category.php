<?php namespace GemFourMedia\GFAQs\Controllers;

use Backend\Classes\Controller;
use BackendMenu;

class Category extends Controller
{
    public $implement = [        'Backend\Behaviors\ListController',        'Backend\Behaviors\FormController',        'Backend\Behaviors\ReorderController'    ];
    
    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';
    public $reorderConfig = 'config_reorder.yaml';

    public $requiredPermissions = [
        'gfaqs.category.manage', 
        'gfaqs.category.create', 
        'gfaqs.category.update', 
        'gfaqs.category.delete' 
    ];

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('GemFourMedia.GFAQs', 'gfaqs-main-menu', 'gfaqs-menu-category');
    }
}

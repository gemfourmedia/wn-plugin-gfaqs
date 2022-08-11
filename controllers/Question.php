<?php namespace GemFourMedia\GFAQs\Controllers;

use Backend\Classes\Controller;
use BackendMenu;

class Question extends Controller
{
    use \GemFourMedia\GFAQs\Traits\ControllerHelper;

    public $implement = [
        'Backend\Behaviors\ListController',
        'Backend\Behaviors\FormController',
        'Backend\Behaviors\ReorderController',
    ];
    
    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';
    public $reorderConfig = 'config_reorder.yaml';

    public $requiredPermissions = [
        'gfaqs.question.manage', 
        'gfaqs.question.create', 
        'gfaqs.question.update', 
        'gfaqs.question.delete' 
    ];

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('GemFourMedia.GFAQs', 'gfaqs-main-menu', 'gfaqs-menu-question');
    }
}

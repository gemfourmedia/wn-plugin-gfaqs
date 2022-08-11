<?php namespace GemFourMedia\GFAQs;

use Backend;
use System\Classes\PluginBase;
use GemFourMedia\GFAQs\Classes\StaticPageExtend;

class Plugin extends PluginBase
{

    public function pluginDetails()
    {
        return [
            'name' => 'gemfourmedia.gfaqs::lang.plugin.name',
            'description' => 'gemfourmedia.gfaqs::lang.plugin.description',
            'author' => 'Gem Four Media',
            'icon' => 'oc-icon-question-circle',
            'homepage' => 'https://gemfourmedia.com/wintercms/plugins-gfaqs'
        ];
    }

    public function register()
    {
        (new StaticPageExtend)->extend();
    }

    public function registerComponents()
    {
    	return [
    		'GemFourMedia\GFAQs\Components\FaqList'     => 'faqList',
    		'GemFourMedia\GFAQs\Components\FaqDetail'     => 'faqDetail',
    	];
    }

    public function registerSettings()
    {
    }

    public function registerSearchHandlers()
    {

        return [ 
            'gFAQs' => [
                'name' => 'FAQs',
                'model' => \GemFourMedia\GFAQs\Models\Question::class,
                'record' => [
                    'title' => 'name',
                    'image' => 'main_image',
                    'description' => 'introtext',
                    'url' => 'default_url',
                ],
            ]
        ];

        return $searchHandlers;
    }

    public function registerPermissions()
    {
        return [
            'gfaqs.access' => [
                'tab' => 'gemfourmedia.gfaqs::lang.permissions.tab_global',
                'label' => 'gemfourmedia.gfaqs::lang.permissions.access',
            ],
            'gfaqs.setting' => [
                'tab' => 'gemfourmedia.gfaqs::lang.permissions.tab_global',
                'label' => 'gemfourmedia.gfaqs::lang.permissions.access_setting',
            ],
            'gfaqs.category.manage' => [
                'tab' => 'gemfourmedia.gfaqs::lang.permissions.tab_category',
                'label' => 'gemfourmedia.gfaqs::lang.permissions.category.manage',
            ],
            'gfaqs.category.create' => [
                'tab' => 'gemfourmedia.gfaqs::lang.permissions.tab_category',
                'label' => 'gemfourmedia.gfaqs::lang.permissions.category.create',
            ],
            'gfaqs.category.update' => [
                'tab' => 'gemfourmedia.gfaqs::lang.permissions.tab_category',
                'label' => 'gemfourmedia.gfaqs::lang.permissions.category.update',
            ],
            'gfaqs.category.delete' => [
                'tab' => 'gemfourmedia.gfaqs::lang.permissions.tab_category',
                'label' => 'gemfourmedia.gfaqs::lang.permissions.category.delete',
            ],
            'gfaqs.question.manage' => [
                'tab' => 'gemfourmedia.gfaqs::lang.permissions.tab_question',
                'label' => 'gemfourmedia.gfaqs::lang.permissions.question.manage',
            ],
            'gfaqs.question.create' => [
                'tab' => 'gemfourmedia.gfaqs::lang.permissions.tab_question',
                'label' => 'gemfourmedia.gfaqs::lang.permissions.question.create',
            ],
            'gfaqs.question.update' => [
                'tab' => 'gemfourmedia.gfaqs::lang.permissions.tab_question',
                'label' => 'gemfourmedia.gfaqs::lang.permissions.question.update',
            ],
            'gfaqs.question.delete' => [
                'tab' => 'gemfourmedia.gfaqs::lang.permissions.tab_question',
                'label' => 'gemfourmedia.gfaqs::lang.permissions.question.delete',
            ]
        ];
    }
    
    public function registerNavigation()
    {
        return [
            'gfaqs-main-menu' => [
                'label' => 'gemfourmedia.gfaqs::lang.plugin.name',
                'url' => Backend::url('gemfourmedia/gfaqs/question'),
                'icon' => 'icon-question',
                'permissions' => ['gfaqs.access'],
                'sideMenu' => [
                    'gfaqs-menu-question' => [
                        'label' => 'gemfourmedia.gfaqs::lang.menu.question',
                        'url' => Backend::url('gemfourmedia/gfaqs/question'),
                        'icon' => 'icon-question-circle',
                        'permissions' => ['gfaqs.question.manage']
                    ],
                    'gfaqs-menu-category' => [
                        'label' => 'gemfourmedia.gfaqs::lang.menu.category',
                        'url' => Backend::url('gemfourmedia/gfaqs/category'),
                        'icon' => 'icon-sitemap',
                        'permissions' => ['gfaqs.category.manage']
                    ]
                ]
            ]
        ];
    }
}

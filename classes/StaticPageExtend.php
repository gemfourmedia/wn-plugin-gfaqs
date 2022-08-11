<?php namespace GemFourMedia\GFAQs\Classes;

use GemFourMedia\GFAQs\Models\Category;
use GemFourMedia\GFAQs\Models\Question;
use RainLab\Pages\Classes\Page as StaticPage;
use System\Classes\PluginManager;
use Event;

/**
 * Class StaticPageExtend
 * @package GemFourMedia\GFAQs\Classes
 */
class StaticPageExtend
{

    /**
     * @return void
     */
    public function extend()
    {
        if (PluginManager::instance()->exists('RainLab.Pages')) {
            $this->extendPageModel();
        }
    }


    /**
     * @return void
     */
    protected function extendPageModel()
    {
        /*
         * Register menu items for the RainLab.Pages plugin
         */
        Event::listen('pages.menuitem.listTypes', function() {
            return [
                // Category
                'gfaqs-single-category' => '[GFAQs] Single Category',
                'gfaqs-all-categories' => '[GFAQs] All Categories',

                // Question
                'gfaqs-single-item' => '[GFAQs] Single Question',
                'gfaqs-all-items' => '[GFAQs] All Questions',
                'gfaqs-category-items' => '[GFAQs] Questions By Category',
            ];
        });

        Event::listen('pages.menuitem.getTypeInfo', function($type) {
            if ($type == 'gfaqs-single-category' || $type == 'gfaqs-all-categories') {
                return Category::getMenuTypeInfo($type);
            }
            elseif ($type == 'gfaqs-single-item' || $type == 'gfaqs-all-items' || $type == 'gfaqs-category-items') {
                return Question::getMenuTypeInfo($type);
            }
        });

        Event::listen('pages.menuitem.resolveItem', function($type, $item, $url, $theme) {
            if ($type == 'gfaqs-single-category' || $type == 'gfaqs-all-categories') {
                return Category::resolveMenuItem($item, $url, $theme);
            }
            elseif ($type == 'gfaqs-single-item' || $type == 'gfaqs-all-items' || $type == 'gfaqs-category-items') {
                return Question::resolveMenuItem($item, $url, $theme);
            }
        });

    }

}
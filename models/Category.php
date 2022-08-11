<?php namespace GemFourMedia\GFAQs\Models;

use Model;
use Carbon\Carbon;
use Lang;
use BackendAuth;
use ValidationException;
use Str;
use Url;
use Cms\Classes\Page as CmsPage;
use Cms\Classes\Theme;


/**
 * Model
 */
class Category extends Model
{
    use \Winter\Storm\Database\Traits\Validation;
    use \Winter\Storm\Database\Traits\Sortable;
    use \GemFourMedia\GFAQs\Traits\SEOHelper;

    public $implement = [
        '@Winter.Translate.Behaviors.TranslatableModel',
    ];

    /**
     * @var string name of field use for og:image.
     */
    public $ogImageField = 'image';

    /**
     * @var string name of og:type
     */
    public $ogType = 'website';

    /**
     * @var string The database table used by the model.
     */
    public $table = 'gemfourmedia_gfaqs_category';

    /**
     * @var array Validation rules
     */
    public $rules = [
        'name' => 'required|max:255',
        'slug' => ['required', 'max:255','regex:/^[a-z0-9\/\:_\-\*\[\]\+\?\|]*$/i', 'unique:gemfourmedia_gfaqs_category'],
        'featured' => 'boolean',
        'meta_title' => 'max:191',
        'meta_description' => 'max:191',
        'meta_keywords' => 'max:191',
    ];

    /**
     * @var array The translatable table fields.
     */
    public $translatable = [
        'name',
        'desc',
        'meta_title',
        'meta_description',
        'meta_keywords',
        ['slug', 'index' => true]
    ];

    /**
     * @var array jsonable
     */
    protected $jsonable = ['params'];

    /**
     * The attributes on which the list can be ordered
     * @var array
     */
    public static $allowedSortingOptions = [
        'created_at asc' => 'Created (Oldest)',
        'created_at desc' => 'Created (Latest)',
        'sort_order asc' => 'Sort Order (ascending)',
        'sort_order desc' => 'Sort Order (descending)',
        'name asc' => 'Name (ascending)',
        'name desc' => 'Name (descending)',
        'random' => 'Random'
    ];

    /**
     * Relationships
     */
    public $attachOne = [
        'image' => 'System\Models\File',
    ];
    public $hasMany = [
        'questions' => ['GemFourMedia\GFAQs\Models\Question', 'scope' => 'published'],
    ];

    /**
     * Events Handler
     */
    public function beforeValidate() 
    {
        // Generate slug for this model
        $this->slug = isset($this->slug) ? $this->slug : $this->name;
        $this->slug = \Str::slug($this->slug);
    }

    public function beforeSave()
    {
        // Set SEO Meta
        $this->setMetaTags($this->name, $this->desc, null);
    }

    /**
     * Scopes
     */
    public function scopeFeatured($query)
    {
        return $query->whereNotNull('featured')
                     ->where('featured', true);
    }

    /**
     * Sets the "url" attribute with a URL to this object.
     * @param string $pageName
     * @param Controller $controller
     * @param array $params Override request URL parameters
     *
     * @return string
     */
    public function setUrl($pageName, $controller, $params = [])
    {
        $params = array_merge([
            'id'   => $this->id,
            'category' => $this->slug,
        ], $params);

        return $this->url = $controller->pageUrl($pageName, $params);
    }

    /**
     * Handler for the pages.menuitem.getTypeInfo event.
     * Returns a menu item type information. The type information is returned as array
     * with the following elements:
     * - references - a list of the item type reference options. The options are returned in the
     *   ["key"] => "title" format for options that don't have sub-options, and in the format
     *   ["key"] => ["title"=>"Option title", "items"=>[...]] for options that have sub-options. Optional,
     *   required only if the menu item type requires references.
     * - nesting - Boolean value indicating whether the item type supports nested items. Optional,
     *   false if omitted.
     * - dynamicItems - Boolean value indicating whether the item type could generate new menu items.
     *   Optional, false if omitted.
     * - cmsPages - a list of CMS pages (objects of the Cms\Classes\Page class), if the item type requires a CMS page reference to
     *   resolve the item URL.
     * @param string $type Specifies the menu item type
     * @return array Returns an array
     */
    public static function getMenuTypeInfo($type)
    {
        $result = [];

        if ($type == 'gfaqs-single-category') {
            $result = [
                'references'   => self::lists('name', 'id'),
                'nesting'      => true,
                'dynamicItems' => true
            ];
        }

        if ($type == 'gfaqs-all-categories') {
            $result = [
                'dynamicItems' => true
            ];
        }

        if ($result) {
            $theme = Theme::getActiveTheme();

            $pages = CmsPage::listInTheme($theme, true);
            $cmsPages = [];
            foreach ($pages as $page) {
                if (!$page->hasComponent('faqList')) {
                    continue;
                }

                /*
                 * Component must use a category filter with a routing parameter
                 * eg: categoryFilter = "{{ :somevalue }}"
                 */
                $properties = $page->getComponentProperties('faqList');

                if (!isset($properties['categoryFilter']) || !preg_match('/{{\s*:/', $properties['categoryFilter'])) {
                // if (!isset($properties['categoryFilter'])) {
                    continue;
                }

                $cmsPages[] = $page;
            }

            $result['cmsPages'] = $cmsPages;
        }

        return $result;
    }

    /**
     * Handler for the pages.menuitem.resolveItem event.
     * Returns information about a menu item. The result is an array
     * with the following keys:
     * - url - the menu item URL. Not required for menu item types that return all available records.
     *   The URL should be returned relative to the website root and include the subdirectory, if any.
     *   Use the Url::to() helper to generate the URLs.
     * - isActive - determines whether the menu item is active. Not required for menu item types that
     *   return all available records.
     * - items - an array of arrays with the same keys (url, isActive, items) + the title key.
     *   The items array should be added only if the $item's $nesting property value is TRUE.
     * @param \RainLab\Pages\Classes\MenuItem $item Specifies the menu item.
     * @param \Cms\Classes\Theme $theme Specifies the current theme.
     * @param string $url Specifies the current page URL, normalized, in lower case
     * The URL is specified relative to the website root, it includes the subdirectory name, if any.
     * @return mixed Returns an array. Returns null if the item cannot be resolved.
     */
    public static function resolveMenuItem($item, $url, $theme)
    {
        $result = null;

        if ($item->type == 'gfaqs-single-category') {
            if (!$item->reference || !$item->cmsPage) {
                return;
            }

            $category = self::find($item->reference);
            if (!$category) {
                return;
            }

            $pageUrl = self::getCategoryPageUrl($item->cmsPage, $category, $theme);
            if (!$pageUrl) {
                return;
            }

            $pageUrl = Url::to($pageUrl);

            $result = [];
            $result['url'] = $pageUrl;
            $result['isActive'] = $pageUrl == $url;
            $result['mtime'] = $category->updated_at;

        }
        elseif ($item->type == 'gfaqs-all-categories') {
            $result = [
                'items' => []
            ];

            $categories = self::orderBy('name')->get();
            foreach ($categories as $category) {
                $categoryItem = [
                    'title' => $category->name,
                    'url'   => self::getCategoryPageUrl($item->cmsPage, $category, $theme),
                    'mtime' => $category->updated_at
                ];

                $categoryItem['isActive'] = $categoryItem['url'] == $url;

                $result['items'][] = $categoryItem;
            }
        }

        return $result;
    }

    /**
     * Returns URL of a category page.
     *
     * @param $pageCode
     * @param $category
     * @param $theme
     */
    protected static function getCategoryPageUrl($pageCode, $category, $theme)
    {
        $page = CmsPage::loadCached($theme, $pageCode);
        if (!$page) {
            return;
        }

        $properties = $page->getComponentProperties('faqList');
        if (!isset($properties['categoryFilter'])) {
            return;
        }

        /*
         * Extract the routing parameter name from the category filter
         * eg: {{ :someRouteParam }}
         */
        if (!preg_match('/^\{\{([^\}]+)\}\}$/', $properties['categoryFilter'], $matches)) {
            return;
        }

        $paramName = substr(trim($matches[1]), 1);

        $url = CmsPage::url($page->getBaseFileName(), [$paramName => $category->slug]);
        return $url;
    }
}

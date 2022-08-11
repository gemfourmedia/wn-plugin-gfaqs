<?php namespace GemFourMedia\GFAQs\Models;

use Model;
use Str;
use Url;
use Cms\Classes\Page as CmsPage;
use Cms\Classes\Theme;
use GemFourMedia\GFAQs\Models\Category;

/**
 * Model
 */
class Question extends Model
{
    use \Winter\Storm\Database\Traits\Validation;
    use \Winter\Storm\Database\Traits\Sortable;
    use \GemFourMedia\GFAQs\Traits\SEOHelper;

    public $implement = [
        '@Winter.Translate.Behaviors.TranslatableModel',
        '@Winter\Search\Behaviors\Searchable'
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
    public $table = 'gemfourmedia_gfaqs_question';

    /**
     * @var array Validation rules
     */
    public $rules = [
        'question' => 'required|max:255',
        'slug' => ['required', 'max:255','regex:/^[a-z0-9\/\:_\-\*\[\]\+\?\|]*$/i', 'unique:gemfourmedia_gfaqs_question'],
        'featured' => 'boolean',
        'published' => 'boolean',
        'sort_order' => 'numeric',
        'meta_title' => 'max:191',
        'meta_description' => 'max:191',
        'meta_keywords' => 'max:191',
    ];

    /**
     * @var array The translatable table fields.
     */
    public $translatable = [
        'question',
        'answer',
        ['slug', 'index' => true]
    ];

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public $searchable = [
        'question',
        'answer',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    /**
     * The attributes on which the list can be ordered
     * @var array
     */
    public static $allowedSortingOptions = [
        'created_at asc' => 'Created (Oldest)',
        'created_at desc' => 'Created (Latest)',
        'sort_order asc' => 'Sort Order (ascending)',
        'sort_order desc' => 'Sort Order (descending)',
        'question asc' => 'Question (ascending)',
        'question desc' => 'Question (descending)',
        'random' => 'Random'
    ];

    /*
     * Relationships
     * ===
     */
    public $attachOne = [
        'image' => 'System\Models\File',
    ];
    public $belongsTo = [
        'category' => ['GemFourMedia\GFAQs\Models\Category'],
    ];

    /**
     * Events Handler
     */
    public function beforeValidate()
    {
        // Generate slug for this model
        $this->slug = isset($this->slug) ? $this->slug : $this->question;
        $this->slug = \Str::slug($this->slug);
    }

    public function beforeSave()
    {
        // Set SEO Meta
        $this->setMetaTags($this->question, $this->answer_short, null);
    }

    /**
     * Accessors
     */
    public function getAnswerShortAttribute()
    {
        if ($this->answer) {
            return str_limit(strip_tags($this->answer), 255);
        }
    }

    /**
     * Scopes
     */
    public function scopePublished($query)
    {
        return $query->whereNotNull('published')
                     ->where('published', true);
    }
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
            'slug' => $this->slug,
        ], $params);

        // Expose published year, month and day as URL parameters.
        if ($this->published) {
            $params['year']  = $this->created_at->format('Y');
            $params['month'] = $this->created_at->format('m');
            $params['day']   = $this->created_at->format('d');
        }

        return $this->url = $controller->pageUrl($pageName, $params);
    }

    //
    // Menu helpers
    //

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
     *
     * @param string $type Specifies the menu item type
     * @return array Returns an array
     */
    public static function getMenuTypeInfo($type)
    {
        $result = [];

        if ($type == 'gfaqs-single-item') {
            $references = [];

            $items = self::orderBy('created_at', 'asc')->get();
            foreach ($items as $item) {
                $references[$item->id] = $item->question;
            }

            $result = [
                'references'   => $references,
                'nesting'      => false,
                'dynamicItems' => false
            ];
        }

        if ($type == 'gfaqs-all-items') {
            $result = [
                'dynamicItems' => true
            ];
        }

        if ($type == 'gfaqs-category-items') {
            $references = [];

            $categories = Category::orderBy('name')->get();
            foreach ($categories as $category) {
                $references[$category->id] = $category->name;
            }

            $result = [
                'references'   => $references,
                'dynamicItems' => true
            ];
        }

        if ($result) {
            $theme = Theme::getActiveTheme();

            $pages = CmsPage::listInTheme($theme, true);
            $cmsPages = [];

            foreach ($pages as $page) {
                if (!$page->hasComponent('faqDetail')) {
                    continue;
                }

                /*
                 * Component must use a categoryPage filter with a routing parameter and post slug
                 * eg: categoryPage = "{{ :somevalue }}", slug = "{{ :somevalue }}"
                 */
                $properties = $page->getComponentProperties('faqDetail');

                if (!isset($properties['categoryPage']) || !preg_match('/{{\s*:/', $properties['slug'])) {
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
     *
     * @param \RainLab\Pages\Classes\MenuItem $item Specifies the menu item.
     * @param \Cms\Classes\Theme $theme Specifies the current theme.
     * @param string $url Specifies the current page URL, normalized, in lower case
     * The URL is specified relative to the website root, it includes the subdirectory name, if any.
     * @return mixed Returns an array. Returns null if the item cannot be resolved.
     */
    public static function resolveMenuItem($item, $url, $theme)
    {
        $result = null;

        if ($item->type == 'gfaqs-single-item') {
            if (!$item->reference || !$item->cmsPage) {
                return;
            }

            $record = self::find($item->reference);
            if (!$record) {
                return;
            }

            $pageUrl = self::getDetailPageUrl($item->cmsPage, $record, $theme);
            if (!$pageUrl) {
                return;
            }

            $pageUrl = Url::to($pageUrl);

            $result = [];
            $result['url'] = $pageUrl;
            $result['isActive'] = $pageUrl == $url;
            $result['mtime'] = $record->updated_at;
        }
        elseif ($item->type == 'gfaqs-all-items') {
            $result = [
                'items' => []
            ];

            $records = self::published()
            ->orderBy('question')
            ->get();

            foreach ($records as $record) {
                $recordItem = [
                    'title' => $record->question,
                    'url'   => self::getDetailPageUrl($item->cmsPage, $record, $theme),
                    'mtime' => $record->updated_at
                ];

                $recordItem['isActive'] = $recordItem['url'] == $url;

                $result['items'][] = $recordItem;
            }
        }
        elseif ($item->type == 'gfaqs-category-items') {
            if (!$item->reference || !$item->cmsPage) {
                return;
            }

            $category = Category::find($item->reference);
            if (!$category) {
                return;
            }

            $result = [
                'items' => []
            ];

            $query = self::published()->where('category_id', $category->id)->orderBy('name');

            $categories = $query->get();

            foreach ($categories as $category) {
                $categoryItem = [
                    'title' => $category->name,
                    'url'   => self::getItemPageUrl($item->cmsPage, $category, $theme),
                    'mtime' => $category->updated_at
                ];

                $categoryItem['isActive'] = $categoryItem['url'] == $url;

                $result['items'][] = $categoryItem;
            }
        }

        return $result;
    }

    /**
     * Returns URL of a post page.
     *
     * @param $pageCode
     * @param $category
     * @param $theme
     */
    protected static function getDetailPageUrl($pageCode, $item, $theme)
    {
        $page = CmsPage::loadCached($theme, $pageCode);
        if (!$page) {
            return;
        }

        $properties = $page->getComponentProperties('faqDetail');
        if (!isset($properties['slug'])) {
            return;
        }

        /*
         * Extract the routing parameter name from the category filter
         * eg: {{ :someRouteParam }}
         */
        if (!preg_match('/^\{\{([^\}]+)\}\}$/', $properties['slug'], $matches)) {
            return;
        }

        $paramName = substr(trim($matches[1]), 1);
        $params = [
            $paramName => $item->slug,
            'year'  => $item->created_at->format('Y'),
            'month' => $item->created_at->format('m'),
            'day'   => $item->created_at->format('d')
        ];
        $url = CmsPage::url($page->getBaseFileName(), $params);

        return $url;
    }
}

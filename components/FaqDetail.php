<?php namespace GemFourMedia\GFAQs\Components;

use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use GemFourMedia\GFAQs\Models\Question;
use GemFourMedia\GFAQs\Models\Category;
use Event;

class FaqDetail extends ComponentBase
{
    public $item;
    public $categoryPage;

    public function componentDetails()
    {
        return [
            'name'        => 'gemfourmedia.gfaqs::lang.components.faqDetail.name',
            'description' => 'gemfourmedia.gfaqs::lang.components.faqDetail.desc'
        ];
    }

    public function defineProperties()
    {
        return [
            'slug' => [
                'title'       => 'gemfourmedia.gfaqs::lang.components.faqDetail.props.slug',
                'description' => 'gemfourmedia.gfaqs::lang.components.faqDetail.props.slug_description',
                'default'     => '{{ :slug }}',
                'type'        => 'string',
            ],
            'categoryPage' => [
                'title'       => 'gemfourmedia.gfaqs::lang.components.faqDetail.props.categoryPage',
                'description' => 'gemfourmedia.gfaqs::lang.components.faqDetail.props.categoryPage_description',
                'type'        => 'dropdown',
                'group'       => 'gemfourmedia.gfaqs::lang.components.faqDetail.props.group_link',
                'showExternalParam' => false,
            ],
        ];
    }

    public function getCategoryPageOptions()
    {
        return [''=> 'Unset'] + Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }

    public function init()
    {
        Event::listen('translate.localePicker.translateParams', function ($page, $params, $oldLocale, $newLocale) {
            $newParams = $params;

            if (isset($params['slug'])) {
                $records = Question::transWhere('slug', $params['slug'], $oldLocale)->first();
                if ($records) {
                    $records->translateContext($newLocale);
                    $newParams['slug'] = $records['slug'];
                }
            }

            return $newParams;
        });
    }

    public function onRun()
    {
        $this->prepareVars();
        $this->item = $this->page['item'] = $this->loadItem();
    }

    public function onRender()
    {
        if (!$this->item) {
            $this->prepareVars();
            $this->item = $this->page['item'] = $this->loadItem();
        }
    }

    public function prepareVars()
    {
        $this->categoryPage = $this->page['categoryPage'] = $this->property('categoryPage');
    }

    protected function loadItem()
    {
        $slug = $this->property('slug');

        $item = new Question;
        $query = $item->query();

        if ($item->isClassExtendedWith('Winter.Translate.Behaviors.TranslatableModel')) {
            $query->transWhere('slug', $slug);
        } else {
            $query->where('slug', $slug);
        }
        
        $item = $query->first();

        /*
         * Add a "url" helper attribute for linking to each category
         */
        if (!$item) return null;
        
        $item->url = $item->setUrl($this->getPage()->getBaseFileName(), $this->controller, ['slug'=> $item->slug]);
        if ($item->category && $this->categoryPage) {

            $item->category->url = $item->category->setUrl($this->categoryPage, $this->controller, ['categorySlug' => $item->category->slug]);
        }

        return $item;
    }
}

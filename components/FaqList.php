<?php namespace GemFourMedia\GFAQs\Components;

use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use GemFourMedia\GFAQs\Models\Question;
use GemFourMedia\GFAQs\Models\Category;
use Redirect;

class FaqList extends ComponentBase
{
    public $category;
    public $questions;
    public $detailPage;
    public $categoryPage;
    public $pageNumber = 1;

    public function componentDetails()
    {
        return [
            'name'        => 'gemfourmedia.gfaqs::lang.components.faqList.name',
            'description' => 'gemfourmedia.gfaqs::lang.components.faqList.desc'
        ];
    }

    public function defineProperties()
    {
        return [
            'perPage' => [
                'title'       => 'gemfourmedia.gfaqs::lang.components.faqList.props.perPage',
                'description' => 'gemfourmedia.gfaqs::lang.components.faqList.props.perPage_description',
                'validationPattern' => '^(0|[1-9][0-9]*)$',
                'validationMessage' => 'gemfourmedia.gfaqs::lang.components.faqList.props.numeric_validation',
                'default'     => '12',
                'type'        => 'string',
                'showExternalParam' => false,
            ],
            'pageNumber' => [
                'title'       => 'gemfourmedia.gfaqs::lang.components.faqList.props.pageNumber',
                'description' => 'gemfourmedia.gfaqs::lang.components.faqList.props.pageNumber_description',
                'validationPattern' => '^(0|[1-9][0-9]*)$',
                'validationMessage' => 'gemfourmedia.gfaqs::lang.components.faqList.props.numeric_validation',
                'default' => '{{:page}}',
                'type'        => 'string',
            ],
            'sortOrder' => [
                'title'       => 'gemfourmedia.gfaqs::lang.components.faqList.props.sortOrder',
                'description' => 'gemfourmedia.gfaqs::lang.components.faqList.props.sortOrder_description',
                'default'     => 'published_at desc',
                'type'        => 'dropdown',
                'showExternalParam' => false,
            ],
            'featured' => [
                'title'       => 'gemfourmedia.gfaqs::lang.components.faqList.props.featured',
                'description' => 'gemfourmedia.gfaqs::lang.components.faqList.props.featured_description',
                'type' => 'checkbox',
                'default' => 0,
                'group'       => 'gemfourmedia.gfaqs::lang.components.faqList.props.group_filter',
            ],
            'categoryFilter' => [
                'title'       => 'gemfourmedia.gfaqs::lang.components.faqList.props.categoryFilter',
                'description' => 'gemfourmedia.gfaqs::lang.components.faqList.props.categoryFilter_description',
                'type' => 'dropdown',
                'group'       => 'gemfourmedia.gfaqs::lang.components.faqList.props.group_filter',
            ],
            'detailPage' => [
                'title'       => 'gemfourmedia.gfaqs::lang.components.faqList.props.detailPage',
                'description' => 'gemfourmedia.gfaqs::lang.components.faqList.props.categoryPage_description',
                'type'        => 'dropdown',
                'group'       => 'gemfourmedia.gfaqs::lang.components.faqList.props.group_link',
                'showExternalParam' => false,
            ],
            'categoryPage' => [
                'title'       => 'gemfourmedia.gfaqs::lang.components.faqList.props.categoryPage',
                'description' => 'gemfourmedia.gfaqs::lang.components.faqList.props.categoryPage_description',
                'type'        => 'dropdown',
                'group'       => 'gemfourmedia.gfaqs::lang.components.faqList.props.group_link',
                'showExternalParam' => false,
            ],
        ];
    }

    /**
     * Dropdown options
     */
    // Sort order
    public function getSortOrderOptions()
    {
        return Question::$allowedSortingOptions;
    }

    //Category Filter
    public function getCategoryFilterOptions()
    {
        return [''=>'Unset'] + Category::get()->lists('name', 'slug');
    }

    // CMS Page list
    public function getDetailPageOptions()
    {
        return Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }
    public function getCategoryPageOptions()
    {
        return Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }

    public function onRun()
    {
        $this->prepareVars();

        $this->questions = $this->page['questions'] = $this->loadQuestions();

        if ($pageNumberParam = $this->paramName('pageNumber')) {
            $currentPage = $this->property('pageNumber');

            if ($currentPage > ($lastPage = $this->questions->lastPage()) && $currentPage > 1) {
                return Redirect::to($this->currentPageUrl([$pageNumberParam => $lastPage]));
            }
        }
    }

    public function onRender()
    {
        if (!$this->questions) {

            $this->prepareVars();

            $this->questions = $this->page['questions'] = $this->loadQuestions();


            if ($pageNumberParam = $this->paramName('pageNumber')) {
                $currentPage = $this->property('pageNumber');

                if ($currentPage > ($lastPage = $this->questions->lastPage()) && $currentPage > 1) {
                    return Redirect::to($this->currentPageUrl([$pageNumberParam => $lastPage]));
                }
            }
        }
    }

    public function prepareVars()
    {
        $this->detailPage = $this->page['detailPage'] = $this->property('detailPage');
        $this->categoryPage = $this->page['categoryPage'] = $this->property('categoryPage');
        if ($this->property('categoryFilter')) {
            $this->category = $this->page['category'] = $this->loadCategory();
        }
    }

    protected function loadCategory() {
        $slug = ($this->property('categoryFilter')) ? $this->property('categoryFilter') : input('category');
        if (!$slug) {
            return null;
        }

        $category = new Category;

        $category = $category->isClassExtendedWith('Winter.Translate.Behaviors.TranslatableModel')
            ? $category->transWhere('slug', $slug)
            : $category->where('slug', $slug);

        $category = $category->first();

        return $category ?: null;
    }

    public function loadQuestions()
    {
        $questions = Question::published();
        
        if ($this->property('featured')) {
            $questions->featured();
        }

        if ($this->category) {
            $questions->where('category_id', $this->category->id);
        }

        $sortOrder = $this->property('sortOrder');

        if (in_array($sortOrder, array_keys($this->getSortOrderOptions()))) {
            if ($sortOrder == 'random') {
                $questions->inRandomOrder();
            } else {
                @list($sortField, $sortDirection) = explode(' ', $sortOrder);

                if (is_null($sortDirection)) {
                    $sortDirection = "desc";
                }

                $questions->orderBy($sortField, $sortDirection);
            }
        }

        $questions = $questions->paginate($this->property('perPage'), post('page', $this->property('pageNumber')));

        if ($questions) {
            $questions->each(function($question) {
                $question->url = $question->setUrl($this->detailPage, $this->controller, ['slug'=> $question->slug]);
            });
        }
        return $questions ? $questions : null;
    }
}

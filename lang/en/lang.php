<?php return [
    'plugin' => [
        'name' => 'FAQs',
        'description' => 'Frequently Asked Questions'
    ],
    'features' => [
    	'category' => 'Category',
    	'question' => 'Question',
    	'questions' => 'Questions',
    ],
    'menu' => [
    	'category' => 'Category',
    	'question' => 'Question',
    ],
	'seo' => [
		'tab' => 'SEO',
		'meta_title' => 'Meta Title',
		'meta_description' => 'Meta Description',
		'meta_keywords' => 'Meta Keywords',
	],
    'permissions' => [
    	'access' => 'Access GFAQs',
    	'access_setting' => 'Access setting GFAQs',
		'tab_global' => 'GFAQs Setting',
		'tab_category' => 'GFAQs Categories',
		'tab_question' => 'GFAQs Question',
    	'category' => [
    		'manage' => 'Category manage',
    		'create' => 'Category create',
    		'update' => 'Category update',
    		'delete' => 'Category delete',
    	],
		'question' => [
			'manage' => 'question manage',
			'create' => 'question create',
			'update' => 'question update',
			'delete' => 'question delete',
		],
    ],
    'category' => [
    	'fields' => [
    		'id' => 'Category ID',
			'name' => 'Name',
			'slug' => 'URL Slug',
			'image' => 'Image',
			'desc' => 'Description',
			'sort_order' => 'Sort Order',
			'params' => 'Parameters',
			'featured' => 'Featured?',
			'created_at' => 'Created At',
			'updated_at' => 'Updated At',
			'questions' => 'Questions',
    	],
    ],
    'question' => [
    	'fields' => [
    		'id' => 'Question ID',
			'question' => 'Question',
			'slug' => 'URL Slug',
			'image' => 'Image',
			'answer' => 'Answer',
			'published' => 'Published?',
			'sort_order' => 'Sort Order',
			'featured' => 'Featured?',
			'created_at' => 'Created At',
			'updated_at' => 'Updated At',
			'category_id' => 'Category',
    	],
    ],
    'components' => [
	    'faqList' => [
	    	'name' => 'FAQ List',
	    	'desc' => 'List of FAQs',
	    	'props' => [
	    		'perPage' => '# Per page',
                'perPage_description' => 'Number of items per page',
                'numeric_validation' => 'Numeric input only',
	    		'pageNumber' => 'Page Number',
	    		'pageNumber_description' => 'Use for pagination',
	    		'categoryFilter' => 'Category Filter',
	    		'categoryFilter_description' => 'Category Filter for listing FAQs',
	    		'sortOrder' => 'Sort Order',
                'sortOrder_description' => 'Sort order',
                'group_link' => 'Link Setting',
                'group_filter' => 'Filter Setting',
	    		'detailPage' => 'Detail Page',
                'detailPage_description' => 'CMS Detail FAQs page',
	    		'categoryPage' => 'List Page',
                'categoryPage_description' => 'CMS List FAQs page',
                'featured' => 'Featured?',
				'featured_description' => 'Display featured items only',
	    	],
	    ],
	    'faqDetail' => [
	    	'name' => 'FAQ Detail',
	    	'desc' => 'Detail of FAQ',
	    	'props' => [
	    		'slug' => 'Slug',
	    		'detailPage' => 'Detail Page',
                'detailPage_description' => 'CMS Detail FAQs page',
	    		'categoryPage' => 'Category Page',
                'categoryPage_description' => 'CMS Category FAQs page',
                'group_link' => 'Link Setting',
	    	],
	    ],
    ],
];
<?php namespace Sitesforchurch\Ministries\Components;

use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use Sitesforchurch\Ministries\Models\Ministry as MinistryModel;
use Illuminate\Pagination\LengthAwarePaginator;

class Ministries extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name'        => 'Ministries Component',
            'description' => 'Displays Ministries List'
        ];
    }

    public function defineProperties()
    {
        return [
            'items_per_page' => [
                 'title'             => 'Items per page',
                 'type'              => 'string',
                 'validationPattern' => '^[0-9]+$',
                 'default'           => 10,
                 'validationMessage' => 'Items per page property can contain only numeric symbols'
            ],
            'sorting' => [
                'title'       => 'Order by data created',
                'type'        => 'dropdown',
                'default'     => 'desc',
                'options'     => ['desc'=>'desc', 'asc'=>'asc']
            ],
            'ministryPage' => [
                'title'       => 'Ministry page',
                'type'        => 'dropdown',
                'default'     => 'ministry',
                'group'       => 'Links',
            ],
        ];
    }
    
    public function getMinistryPageOptions()
    {
        return Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }
    
    public function onRun()
    {
        $this->ministryPage = $this->page['ministryPage'] = $this->property('ministryPage');
        $items_per_page = $this->property('items_per_page');
        if( $items_per_page > 0 ){
            $ministries = MinistryModel::orderBy('created_at', $this->property('sorting'))->paginate($items_per_page);
        }
        else{
            $ministries = MinistryModel::orderBy('created_at', $this->property('sorting'))->all();
        }
        $this->page['ministries'] = $ministries;
    }

}
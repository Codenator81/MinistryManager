<?php namespace Sitesforchurch\Ministries\Components;

use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use Sitesforchurch\Ministries\Models\Ministry as MinistryModel;
use System\Models\File;
use Illuminate\Database\Eloquent\Collection;
use Cookie;

class Gallery extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name'        => 'Gallery',
            'description' => 'Gallery component'
        ];
    }

    public function defineProperties()
    {
        return [
            'items_per_page' => [
                 'title'             => 'Items per page',
                 'type'              => 'string',
                 'default'           => 0,
                 'validationPattern' => '^[0-9]+$',
                 'validationMessage' => 'Items per page property can contain only numeric symbols'
            ],
            'life_time_cookie' => [
                 'title'             => 'Life time cookie for filters',
                 'type'              => 'string',
                 'default'           => 10,
                 'validationPattern' => '^[0-9]+$',
                 'validationMessage' => 'Life time property can contain only numeric symbols'
            ],
            'useIncludingGallery' => [
                 'title'             => 'Use including gallery',
                 'type'              => 'checkbox',
                 'default'           => 1
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
            ],
        ];
    }
    
    public function getMinistryPageOptions()
    {
        return Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }
    
    public function onRun( )
    {
        
        if( $this->property('useIncludingGallery') )
            {
                $this->addCss('/plugins/sitesforchurch/ministries/assets/css/featherlight.min.css');
                $this->addCss('/plugins/sitesforchurch/ministries/assets/css/featherlight.gallery.min.css');
                
                $this->addJs('/plugins/sitesforchurch/ministries/assets/js/featherlight.min.js');
                $this->addJs('/plugins/sitesforchurch/ministries/assets/js/featherlight.gallery.min.js');
            }
        
        $ministry_id = post('ministry_id', $default = null);
        if( null === $ministry_id ){
            $ministry_id = Cookie::get('gallery_filter');
        }
        else{
            Cookie::queue('gallery_filter', $ministry_id, $this->property('life_time_cookie') );
        }
         
        $this->page['filter_by'] = $ministry_id;
        $this->page['ministryPage'] = $this->property('ministryPage');
        
        if($this->property('items_per_page') > 0)
            {
                if(0 == $ministry_id)
                {
                    $photos = File::where('attachment_type', 'Sitesforchurch\Ministries\Models\Ministry')
                        ->where('field', 'photos')
                        ->orderBy('created_at', $this->property('sorting'))
                        ->paginate($this->property('items_per_page'));
                }
                else {
                    $photos = File::where('attachment_type', 'Sitesforchurch\Ministries\Models\Ministry')
                        ->where('field', 'photos')
                        ->where('attachment_id', $ministry_id)
                        ->orderBy('created_at', $this->property('sorting'))
                        ->paginate($this->property('items_per_page'));
                }
            }
        else
            {
                if(0 == $ministry_id)
                {
                    $photos = File::where('attachment_type', 'Sitesforchurch\Ministries\Models\Ministry')
                        ->where('field', 'photos')
                        ->orderBy('system_files.created_at', $this->property('sorting'))
                        ->get();
                }
                else{
                    $photos = File::where('attachment_type', 'Sitesforchurch\Ministries\Models\Ministry')
                        ->where('field', 'photos')
                        ->where('attachment_id', $ministry_id)
                        ->orderBy('system_files.created_at', $this->property('sorting'))
                        ->get();
                }
                    
            }

        $this->page['ministries'] = $ministries = $this->getMinistryFilter();
        $this->page['photos'] = $photos;
            
    }
    
    public function getMinistry($ministry_id)
        {
            return MinistryModel::find($ministry_id);
        }
        
    private function getMinistryFilter()
        {
            return MinistryModel::has('photos', '>', 0)->get();
        }

}
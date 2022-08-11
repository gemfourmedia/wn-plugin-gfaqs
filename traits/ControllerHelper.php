<?php

namespace GemFourMedia\GFAQs\Traits;

use Illuminate\Support\Facades\Event;
use Backend\Behaviors\FormController;
use Db;
use Flash;
use Lang;

/**
 * Class ControllerHelper
 * @package GemFourMedia\GFAQs\Traits
 */
trait ControllerHelper
{
	use \Backend\Traits\FormModelSaver;

    // Bulk Delete
    public function index_onDelete()
    {
        if(!$this->checkPermissions('delete')) {
            return \Response::make('Access Denined', 403);
        }
        $listConfig = $this->listGetConfig();
        if ($listConfig && isset($listConfig->modelClass))
            $model = new $listConfig->modelClass;
        if ($model) {
            if (($checkedIds = post('checked')) && is_array($checkedIds) && count($checkedIds)) {
                foreach ($checkedIds as $recordID) {
                    if ((!$record = $model->find($recordID))) {
                        continue;
                    }

                    $record->delete();
                }
                Flash::success('Delete Success');
            }
            
            return $this->listRefresh();
        }
    }

    // Save a copy of current record
    public function onSaveCopy()
    {

        $model = $this->formCreateModelObject();
        $model = $this->formExtendModel($model) ?: $model;

        $this->initForm($model);

        $this->formBeforeSave($model);
        $this->formBeforeCreate($model);

        $widget = $this->formGetWidget();
        $modelsToSave = $this->prepareModelsToSave($model, $widget->getSaveData());

        Db::transaction(function () use ($modelsToSave, $widget) {
            foreach ($modelsToSave as  $k => $modelToSave) {
                $modelToSave->save(null, $widget->getSessionKey());
            }
        });

        $this->formAfterSave($model);
        $this->formAfterCreate($model);

        Flash::success('Save as copy success');

        if ($redirect = $this->makeRedirect('create', $model)) {
            return $redirect;
        }
    }

    protected function checkPermissions($action) {
    	$permission = 'soccertips.'.strtolower(class_basename(get_class($this))).'.'.$action;
    	$requiredPermission = in_array($permission, $this->requiredPermissions);
    	if ($requiredPermission) {
    		($this->user->hasAccess($permission));
    		return $this->user->hasAccess($permission);
    	}
    	return true;
    }

    public function create()
    {
    	if(!$this->checkPermissions('create')) {
    		return \Response::make('Access Denined', 403);
    	}
        return $this->asExtension('FormController')->create();
    }

    public function update($recordId = null)
    {
        if(!$this->checkPermissions('update')) {
    		return \Response::make('Access Denined', 403);
    	}
        return $this->asExtension('FormController')->update($recordId);
    }

    public function update_onDelete($recordId = null)
    {
        if(!$this->checkPermissions('delete')) {
    		return \Response::make('Access Denined', 403);
    	}
        return $this->asExtension('FormController')->update_onDelete($recordId);
    }
}
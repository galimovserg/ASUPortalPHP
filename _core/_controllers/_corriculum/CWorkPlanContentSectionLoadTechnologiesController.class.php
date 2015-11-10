<?php
class CWorkPlanContentSectionLoadTechnologiesController extends CBaseController{
    public function __construct() {
        if (!CSession::isAuth()) {
            $action = CRequest::getString("action");
            if ($action == "") {
                $action = "index";
            }
            if (!in_array($action, $this->allowedAnonymous)) {
                $this->redirectNoAccess();
            }
        }

        $this->_smartyEnabled = true;
        $this->setPageTitle("Управление образовательными технологиями");

        parent::__construct();
    }
    public function actionIndex() {
        $set = new CRecordSet();
        $query = new CQuery();
        $set->setQuery($query);
        $query->select("t.*")
            ->from(TABLE_WORK_PLAN_CONTENT_TECHNOLOGIES." as t")
            ->condition("load_id=".CRequest::getInt("load_id"))
            ->order("t.ordering asc");
        $objects = new CArrayList();
        foreach ($set->getPaginated()->getItems() as $ar) {
            $object = new CWorkPlanContentSectionLoadTechnology($ar);
            $objects->add($object->getId(), $object);
        }
        $this->setData("objects", $objects);
        $this->setData("paginator", $set->getPaginator());
        /**
         * Генерация меню
         */
        $this->addActionsMenuItem(array(
            "title" => "Обновить",
            "link" => "workplancontenttechnologies.php?action=index&load_id=".CRequest::getInt("load_id"),
            "icon" => "actions/view-refresh.png"
        ));
        $this->addActionsMenuItem(array(
            "title" => "Добавить технологию",
            "link" => "workplancontenttechnologies.php?action=add&id=".CRequest::getInt("load_id"),
            "icon" => "actions/list-add.png"
        ));
        /**
         * Отображение представления
         */
        $this->renderView("_corriculum/_workplan/contentTechnology/index.tpl");
    }
    public function actionAdd() {
        $object = new CWorkPlanContentSectionLoadTechnology();
        $object->load_id = CRequest::getInt("id");
        $load = CBaseManager::getWorkPlanContentSectionLoad(CRequest::getInt("id"));
        $object->ordering = $load->technologies->getCount() + 1;
        $this->setData("object", $object);
        /**
         * Генерация меню
         */
        $this->addActionsMenuItem(array(
            "title" => "Назад",
            "link" => "workplancontentloads.php?action=edit&id=".$object->load_id,
            "icon" => "actions/edit-undo.png"
        ));
        /**
         * Отображение представления
         */
        $this->renderView("_corriculum/_workplan/contentTechnology/add.tpl");
    }
    public function actionEdit() {
        $object = CBaseManager::getWorkPlanContentSectionLoadTechnology(CRequest::getInt("id"));
        $this->setData("object", $object);
        /**
         * Генерация меню
         */
        $this->addActionsMenuItem(array(
            "title" => "Назад",
            "link" => "workplancontentloads.php?action=edit&id=".$object->load_id,
            "icon" => "actions/edit-undo.png"
        ));
        /**
         * Отображение представления
         */
        $this->renderView("_corriculum/_workplan/contentTechnology/edit.tpl");
    }
    public function actionDelete() {
    	$object = CBaseManager::getWorkPlanContentSectionLoadTechnology(CRequest::getInt("id"));
    	if (!is_null($object)) {
    		$load = $object->load_id;
    		$item = CBaseManager::getWorkPlanContentSectionLoad($load);
    		$object->remove();
    		$order = 1;
    		foreach ($item->technologies as $technology) {
    			$technology->ordering = $order++;
    			$technology->save();
    		}
    		$this->redirect("workplancontentloads.php?action=edit&id=".$load);
    	}
    	$items = CRequest::getArray("selectedInView");
    	$load = CRequest::getInt("load_id");
    	foreach ($items as $id){
    		$object = CBaseManager::getWorkPlanContentSectionLoadTechnology($id);
    		$object->remove();
    	}
    	$item = CBaseManager::getWorkPlanContentSectionLoad($load);
    	$order = 1;
    	foreach ($item->technologies as $technology) {
    		$technology->ordering = $order++;
    		$technology->save();
    	}
    	$this->redirect("workplancontentloads.php?action=edit&id=".$load);
    }
    public function actionSave() {
        $object = new CWorkPlanContentSectionLoadTechnology();
        $object->setAttributes(CRequest::getArray($object::getClassName()));
        if ($object->validate()) {
            $object->save();
            if ($this->continueEdit()) {
                $this->redirect("workplancontenttechnologies.php?action=edit&id=".$object->getId());
            } else {
                $this->redirect("workplancontentloads.php?action=edit&id=".$object->load_id);
            }
            return true;
        }
        $this->setData("object", $object);
        $this->renderView("_corriculum/_workplan/contentTechnology/edit.tpl");
    }
}
<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksandr
 * Date: 24.02.13
 * Time: 17:19
 * To change this template use File | Settings | File Templates.
 */
class CDiplomsController extends CBaseController {
    public function __construct() {
        if (!CSession::isAuth()) {
            if (!in_array(CRequest::getString("action"), $this->allowedAnonymous)) {
                $this->redirectNoAccess();
            }
        }

        $this->_smartyEnabled = true;
        $this->setPageTitle("Дипломные темы студентов");

        parent::__construct();
    }
    public function actionIndex() {
        $set = new CRecordSet();
        $query = new CQuery();
        $query->select("diplom.*")
            ->from(TABLE_DIPLOMS." as diplom")
            ->order("diplom.id desc");
        $set->setQuery($query);
        $diploms = new CArrayList();
        foreach ($set->getPaginated()->getItems() as $item) {
            $diplom = new CDiplom($item);
            $diploms->add($diplom->getId(), $diplom);
        }
        $this->setData("diploms", $diploms);
        $this->setData("paginator", $set->getPaginator());
        $this->renderView("_diploms/index.tpl");
    }
    public function actionAdd() {
        $diplom = new CDiplom();
        $commissions = array();
        foreach (CSABManager::getCommissionsList() as $id=>$c) {
            $commission = CSABManager::getCommission($id);
            $nv = $commission->title;
            if (!is_null($commission->manager)) {
                $nv .= " ".$commission->manager->getName();
            }
            if (!is_null($commission->secretar)) {
                $nv .= " (".$commission->secretar->getName().")";
            }
            $cnt = 0;
            foreach ($commission->diploms->getItems() as $d) {
                if (strtotime($diplom->date_act) == strtotime($d->date_act)) {
                    $cnt++;
                }
            }
            $nv .= " ".$cnt;
            $commissions[$commission->getId()] = $nv;
        }
        $students = CStaffManager::getAllStudentsThisYearList();
        $reviewers = CStaffManager::getPersonsListWithType(TYPE_REVIEWER);
        $this->setData("reviewers", $reviewers);
        $this->setData("students", $students);
        $this->addJSInclude("_core/jquery-ui-1.8.20.custom.min.js");
        $this->addCSSInclude("_core/jUI/jquery-ui-1.8.2.custom.css");
        $this->addJSInclude("_core/jquery.ui.timepicker.js");
        $this->addCSSInclude("_core/jquery.ui.timepicker.css");
        $this->setData("commissions", $commissions);
        $this->setData("diplom", $diplom);
        $this->renderView("_diploms/add.tpl");
    }
    public function actionEdit() {
        $diplom = CStaffManager::getDiplom(CRequest::getInt("id"));
        // сконвертим дату из MySQL date в нормальную дату
        $diplom->date_act = date("d.m.Y", strtotime($diplom->date_act));
        $commissions = array();
        foreach (CSABManager::getCommissionsList() as $id=>$c) {
            $commission = CSABManager::getCommission($id);
            $nv = $commission->title;
            if (!is_null($commission->manager)) {
                $nv .= " ".$commission->manager->getName();
            }
            if (!is_null($commission->secretar)) {
                $nv .= " (".$commission->secretar->getName().")";
            }
            $cnt = 0;
            foreach ($commission->diploms->getItems() as $d) {
                if (strtotime($diplom->date_act) == strtotime($d->date_act)) {
                    $cnt++;
                }
            }
            $nv .= " ".$cnt;
            $commissions[$commission->getId()] = $nv;
        }
        if (!array_key_exists($diplom->gak_num, $commissions)) {
        	$diplom->gak_num = null;
        }
        $students = CStaffManager::getAllStudentsThisYearList();
        if (!array_key_exists($diplom->student_id, $students)) {
            $student = CStaffManager::getStudent($diplom->student_id);
            if (!is_null($student)) {
                $nv = $student->getName();
                if (!is_null($student->getGroup())) {
                    $nv .= " (".$student->getGroup()->getName().")";
                }
                $students[$student->getId()] = $nv;
            }
        }
        $reviewers = CStaffManager::getPersonsListWithType(TYPE_REVIEWER);
        if (!array_key_exists($diplom->recenz_id, $reviewers)) {
            $reviewer = CStaffManager::getPerson($diplom->recenz_id);
            if (!is_null($reviewer)) {
                $reviewers[$reviewer->getId()] = $reviewer->getName();
            }
        }
        $this->setData("reviewers", $reviewers);
        $this->setData("students", $students);
        $this->addJSInclude("_core/jquery-ui-1.8.20.custom.min.js");
        $this->addCSSInclude("_core/jUI/jquery-ui-1.8.2.custom.css");
        $this->addJSInclude("_core/jquery.ui.timepicker.js");
        $this->addCSSInclude("_core/jquery.ui.timepicker.css");
        $this->setData("commissions", $commissions);
        $this->setData("diplom", $diplom);
        $this->renderView("_diploms/edit.tpl");
    }
    public function actionSave() {
        $diplom = new CDiplom();
        $diplom->setAttributes(CRequest::getArray($diplom::getClassName()));
        if ($diplom->validate()) {
            // дату нужно сконвертить в MySQL date
            $diplom->date_act = date("Y-m-d", strtotime($diplom->date_act));
            $diplom->save();
            //$this->redirect("?action=index");
            $this->redirect(WEB_ROOT."diploms_view.php");
            return true;
        }
        $students = CStaffManager::getAllStudentsThisYearList();
        if (!array_key_exists($diplom->student_id, $students)) {
            $student = CStaffManager::getStudent($diplom->student_id);
            if (!is_null($student)) {
                $nv = $student->getName();
                if (!is_null($student->getGroup())) {
                    $nv .= " (".$student->getGroup()->getName().")";
                }
                $students[$student->getId()] = $nv;
            }
        }
        $reviewers = CStaffManager::getPersonsListWithType(TYPE_REVIEWER);
        if (!array_key_exists($diplom->recenz_id, $reviewers)) {
            $reviewer = CStaffManager::getPerson($diplom->recenz_id);
            if (!is_null($reviewer)) {
                $reviewers[$reviewer->getId()] = $reviewer->getName();
            }
        }
        $this->setData("reviewers", $reviewers);
        $this->setData("students", $students);
        $this->addJSInclude("_core/jquery-ui-1.8.20.custom.min.js");
        $this->addCSSInclude("_core/jUI/jquery-ui-1.8.2.custom.css");
        $this->addJSInclude("_core/jquery.ui.timepicker.js");
        $this->addCSSInclude("_core/jquery.ui.timepicker.css");
        // сконвертим дату из MySQL date в нормальную дату
        $diplom->date_act = date("d.m.Y", strtotime($diplom->date_act));
        $this->setData("diplom", $diplom);
        $this->renderView("_diploms/edit.tpl");
    }
    public function actionGetAverageMark() {
    	$mark = 0;
    	$diplom = CStaffManager::getDiplom(CRequest::getInt("id"));
    	if (!is_null($diplom)) {
    		$student = $diplom->student;
    		if (!is_null($student)) {
    			$query = new CQuery();
    			$query->select("n.*")
    			->from(TABLE_STUDENTS_ACTIVITY." as m")
    			->innerJoin(TABLE_MARKS." as n", "m.study_mark = n.id")
    			->condition("student_id = ".$student->getId()." AND study_mark in (1, 2, 3, 4) AND kadri_id = 380");
    			$items = $query->execute();
    			foreach ($items->getItems() as $item) {
    				if (mb_strtolower($item["name"]) == "удовлетворительно") {
    					$mark += 3;
    				} elseif (mb_strtolower($item["name"]) == "хорошо") {
    					$mark += 4;
    				} elseif (mb_strtolower($item["name"]) == "отлично") {
    					$mark += 5;
    				} elseif (mb_strtolower($item["name"]) == "неудовлетворительно") {
    					$mark += 2;
    				}
    			}
    			$mark = round(($mark / ($items->getCount())), 2);
    		}
    	}
    	if ($mark !== 0) {
    		echo $mark;
    	}
    }
}

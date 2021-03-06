<?php

class CIndPlanScienceLoad extends CAbstractPrintClassField{
    public function getFieldName()
    {
        return "Научно-методическая и госбюджетная научно-исследовательская работа";
    }

    public function getFieldDescription()
    {
        return "Используется при печати индивидуального плана, принимает параметр planId с Id плана";
    }

    public function getParentClassField()
    {

    }

    public function getFieldType()
    {
        return self::FIELD_TABLE;
    }

    public function execute($contextObject)
    {
        $result = array();
        $studyLoad = new CArrayList();
        if (!is_null(CIndPlanManager::getLoad(CRequest::getInt("planId")))) {
            $load = CIndPlanManager::getLoad(CRequest::getInt("planId"));
        } else {
            $load = CIndPlanManager::getLoad($contextObject->getId());
        }
        $studyLoad = $load->getWorksByType(3);
        foreach ($studyLoad->getItems() as $row) {
        	$dataRow = array();
        	$dataRow[0] = count($result) + 1;
        	$dataRow[1] = $row->getTitle();
        	$dataRow[2] = $row->plan_amount;
        	$dataRow[3] = $row->plan_hours;
        	$dataRow[4] = $row->plan_expiration_date;
        	$dataRow[5] = $row->plan_report_type;
        	$dataRow[6] = $row->comment;
        	$result[] = $dataRow;
        }
        for ($i = count($result); $i <= 7; $i++) {
        	$row = array();
        	for ($j = 0; $j <= 6; $j++) {
        		$row[$j] = "";
        	}
        	$result[] = $row;
        }
        return $result;
    }
}
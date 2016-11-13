<?php

class CWorkPlanSelfWorkValueOfLoad extends CAbstractPrintClassField {
    public function getFieldName()
    {
        return "Значение нагрузки по самостоятельной работе";
    }

    public function getFieldDescription()
    {
        return "Используется при печати рабочей программы, принимает параметр id с Id рабочей программы";
    }

    public function getParentClassField()
    {

    }

    public function getFieldType()
    {
        return self::FIELD_TEXT;
    }

    public function execute($contextObject)
    {
    	$result = 0;
		foreach ($contextObject->corriculumDiscipline->labors->getItems() as $labor) {
        	if ($labor->type->getAlias() == CWorkPlanLoadTypeConstants::CURRICULUM_LABOR_SELF_WORK) {
        		$result = $labor->value;
        	}
        }
		return $result;
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: ABarmin
 * Date: 12.02.2015
 * Time: 12:43
 */

class CIndPlanPesonsReportTable extends CAbstractPrintClassField {
    public function getFieldName()
    {
        return "Отчет по индивидуальному плану за указанное время по указанным людям";
    }

    public function getFieldDescription()
    {
        return "Данные берутся из CStatefullBean по beanId";
    }

    public function getParentClassField()
    {
        // TODO: Implement getParentClassField() method.
    }

    public function getFieldType()
    {
        return self::FIELD_TABLE;
    }

    public function execute($contextObject)
    {
        /**
         * @var $bean CStatefullBean
         */
        $bean = CApp::getApp()->beans->getStatefullBean(CRequest::getString("beanId"));
        $persons = new CArrayList();
        foreach ($bean->getItem("id")->getItems() as $person_id) {
            $person = CStaffManager::getPerson($person_id);
            if (!is_null($person)) {
                $persons->add($person->getId(), $person);
            }
        }
        /**
         * Отфильтруем нужные планы
         */
        $targetPlans = new CArrayList();
        /**
         * @var $person CPerson
         */
        foreach ($persons->getItems() as $person) {
            foreach ($person->getIndPlansByYears($bean->getItem("year_id"))->getItems() as $year_id=>$plans) {
                foreach ($plans->getItems() as $plan) {
                    if (in_array($plan->type, $bean->getItem("types")->getItems())) {
                        $targetPlans->add($plan->getId(), $plan);
                    }
                }
            }
        }
        $months = $bean->getItem("months");
        $value = array();
        /**
         * @var $plan CIndPlanPersonLoad
         */
        foreach ($targetPlans->getItems() as $plan) {
            $row = array();
            if (array_key_exists($plan->person_id, $value)) {
                $row = $value[$plan->person_id];
            }
            $row[0] = count($value) + 1;
            $row[1] = "";
            if (!is_null($plan->person)) {
                if (!is_null($plan->person->getPost())) {
                    $row[1] = $plan->person->getPost()->name_short;
                }
            }
            $row[2] = "";
            if (!is_null($plan->person)) {
                if (!is_null($plan->person->degree)) {
                    $row[2] = $plan->person->degree->comment;
                }
            }
            $row[3] = "";
            if (!is_null($plan->person)) {
                $row[3] = $plan->person->fio_short;
            }
            // запланировано на год
            if (!array_key_exists(4, $row)) {
                $row[4] = 0;
            }
            $preparedData = array();
            $table = $plan->getStudyLoadTable()->getTable();
            foreach ($table as $r) {
                if ($plan->isSeparateContract()) {
                    // если есть бюджет-контракт, то суммируем их
                    $preparedRow = array();
                    $preparedRow[0] = $r[0];
                    for ($i = 1; $i <= 17; $i++) {
                        $preparedRow[$i] = $r[($i * 2)] + $r[($i * 2 - 1)];
                    }
                    $preparedData[] = $preparedRow;
                } else {
                    // нет разделения на бюджет-контракт, копируем
                    $preparedData[] = $r;
                }
            }
            $plannedForYear = 0;
            foreach ($preparedData as $preparedRow) {
                // план на весенний семестр
                $plannedForYear += $preparedRow[1];
                // план на осенний семестр
                $plannedForYear += $preparedRow[8];
            }
            $row[4] = $plannedForYear;
            $rows = array(
            	/**
            	 * Столбцы для изменений
            	 *
            	 * Номер слева соответствует номеру столбца в шаблоне, начиная с 0
            	 * Номер справа соответствует порядковому номеру нагрузки из справочника, начиная с 0
            	 * -1 означает, что столбец будет пропущен
            	 */	
                5 => 0, //лекц
                6 => 1, //прак
                7 => 2, //лаб
                8 => -1, 
                9 => 4, //кп
                10 => 8, //коллоквиум
                11 => 14, //ргр
                12 => 3, //конс
                13 => 15, //уч. прак.
                14 => 7, //произв. прак.
                15 => 5, //зач
                16 => 6, //экз
                17 => 9, //дип. проект.
                18 => 10, //ГЭК
                19 => 16, //КСР
                20 => 13, //занятия с аспирантами
            	21 => 12 //асп
            );
            foreach ($rows as $target=>$source) {
            	if (!array_key_exists($target, $row)) {
    				$row[$target] = 0;
    			}
    			if (!array_key_exists(22, $row)) {
    				$row[22] = 0;
    			}
    			if ($source != -1) {
    				foreach ($months as $month) {
    					$row[$target] += $preparedData[$source][$month];
    					$row[22] += $preparedData[$source][$month];
    				}
    			}
    			if ($row[$target] == 0) {
    				$row[$target] = "";
    			}
    		}
            $value[$plan->person_id] = $row;
        }
        return $value;
    }

} 
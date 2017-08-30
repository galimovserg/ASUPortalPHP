<?php

/**
 * Сервис по работе с учебной нагрузкой
 *
 */
class CStudyLoadService {
  
    /**
     * Учебная нагрузка
     *
     * @param $key
     * @return CStudyLoad
     */
    public static function getStudyLoad($key) {
        $ar = CActiveRecordProvider::getById(TABLE_WORKLOAD, $key);
        if (!is_null($ar)) {
            $studyLoad = new CStudyLoad($ar);
        }
        return $studyLoad;
    }
    
    /**
     * Удаление учебной нагрузки
     *
     * @param CStudyLoad $studyLoad
     */
    public static function deleteStudyLoad(CStudyLoad $studyLoad) {
    	// удаляем данные по значениям видов работ нагрузки
    	foreach (CActiveRecordProvider::getWithCondition(TABLE_WORKLOAD_WORKS, "workload_id=".$studyLoad->getId())->getItems() as $ar) {
    		$ar->remove();
    	}
    	// удаляем саму нагрузку
    	$studyLoad->remove();
    }
    
    /**
     * Лист нагрузок преподавателя по году
     *
     * @param CPerson $person
     * @param CTerm $year
     * @return CArrayList
     */
    public static function getStudyLoadsByYear(CPerson $person, CTerm $year) {
        $loads = new CArrayList();
        foreach (CActiveRecordProvider::getWithCondition(TABLE_WORKLOAD, "person_id = ".$person->getId()." AND year_id = ".$year->getId()." AND _is_last_version = 1")->getItems() as $item) {
            $study = new CStudyLoad($item);
            $loads->add($study->getId(), $study);
        }
        return $loads;
    }
    
    /**
     * Лист нагрузок всех преподавателей по году
     *
     * @param CTerm $year
     * @return CArrayList
     */
    public static function getAllStudyLoadsByYear(CTerm $year) {
    	$loads = new CArrayList();
    	foreach (CActiveRecordProvider::getWithCondition(TABLE_WORKLOAD, "year_id = ".$year->getId()." AND _is_last_version = 1")->getItems() as $item) {
    		$study = new CStudyLoad($item);
    		$loads->add($study->getId(), $study);
    	}
    	return $loads;
    }
    
    /**
     * Лист нагрузок преподавателя по году
     *
     * @param CPerson $person - преподаватель
     * @param CTerm $year - учебный год
     * @return CArrayList
     */
    public static function getAllStudyLoadsByYearAndPerson(CPerson $person, CTerm $year) {
    	$loads = new CArrayList();
    	foreach (CActiveRecordProvider::getWithCondition(TABLE_WORKLOAD, "person_id = ".$person->getId()." AND year_id = ".$year->getId()." AND _is_last_version = 1")->getItems() as $item) {
    		$study = new CStudyLoad($item);
    		$loads->add($study->getId(), $study);
    	}
    	return $loads;
    }
    
    /**
     * Лист нагрузок преподавателя по году и типу нагрузки
     *
     * @param CPerson $person - преподаватель
     * @param CTerm $year - учебный год
     * @param array $loadTypes - типы нагрузок
     * @return CArrayList
     */
    public static function getStudyLoadsByYearAndLoadType(CPerson $person, CTerm $year, $loadTypes) {
    	$loads = new CArrayList();
    	foreach (CActiveRecordProvider::getWithCondition(TABLE_WORKLOAD, "person_id = ".$person->getId()." AND year_id = ".$year->getId()." AND load_type_id IN (".implode($loadTypes, ", ").") AND _is_last_version = 1")->getItems() as $item) {
    		$study = new CStudyLoad($item);
    		$loads->add($study->getId(), $study);
    	}
    	return $loads;
    }
    
    /**
     * Список преподавателей, у которых есть нагрузка по дисциплине
     *
     * @param CTerm $discipline
     * @return CArrayList
     */
    public static function getLecturersNameByDiscipline(CTerm $discipline) {
    	$lecturers = new CArrayList();
    	foreach (CActiveRecordProvider::getWithCondition(TABLE_WORKLOAD, "discipline_id = ".$discipline->getId()." AND _is_last_version = 1")->getItems() as $item) {
    		$study = new CStudyLoad($item);
    		$lecturers->add($study->lecturer->getId(), $study->lecturer);
    	}
    	$comparator = new CDefaultComparator("fio");
    	$sorted = CCollectionUtils::sort($lecturers, $comparator);
    	return $sorted;
    }
    
    /**
     * Тип нагрузки из справочника учебных работ по названию
     * 
     * @param $nameHours
     * @return CStudyLoadWorkType
     */
    public static function getStudyLoadWorkTypeByNameHours($nameHours) {
    	$types = new CArrayList();
    	foreach (CActiveRecordProvider::getWithCondition(TABLE_WORKLOAD_WORK_TYPES, "name_hours_kind = ".$nameHours)->getItems() as $item) {
    		$type = new CStudyLoadWorkType($item);
    		$types->add($type->getId(), $type);
    	}
    	return $types->getFirstItem();
    }
    
    /**
     * Сотрудники с нагрузкой в указанном году
     *
     * @param int $isBudget - вид работы нагрузки: бюджет
     * @param int $isContract - вид работы нагрузки: контракт
     * @param int $selectedYear - выбранный учебный год
     * @return array
     */
    public static function getPersonsWithLoadByYear($isBudget, $isContract, $selectedYear, $person = null) {
    	// id типов учебной нагрузки (основная, дополнительная, надбавка, почасовка)
    	$baseLoadId = CStudyLoadService::getStudyLoadTypeByAlias(CStudyLoadTypeConstants::BASE)->getId();
    	$additionalLoadId = CStudyLoadService::getStudyLoadTypeByAlias(CStudyLoadTypeConstants::ADDITIONAL)->getId();
    	$premiumLoadId = CStudyLoadService::getStudyLoadTypeByAlias(CStudyLoadTypeConstants::PREMIUM)->getId();
    	$byTimeLoadId = CStudyLoadService::getStudyLoadTypeByAlias(CStudyLoadTypeConstants::BY_TIME)->getId();
    	
    	if ($isBudget) {
    		$cacheBudget = "isBudget";
    	} else {
    		$cacheBudget = "notBudget";
    	}
    	if ($isContract) {
    		$cacheContract = "isContract";
    	} else {
    		$cacheContract = "notContract";
    	}
    	if (is_null($person)) {
    		$cachePerson = "null";
    	} else {
    		$cachePerson = $person->getId();
    	}
    	$cacheKey = "cachePersonsWithLoadByYear_".$cacheBudget."_".$cacheContract."_".$selectedYear."_".$cachePerson;
    	if (CApp::getApp()->cache->hasCache($cacheKey)) {
    		return CApp::getApp()->cache->get($cacheKey);
    	} else {
    		$personsWithLoad = array();
    		 
    		// текущая дата для расчета ставки по актуальным приказам ОК
    		$dateFrom = date('Y.m.d', mktime(0, 0, 0, date("m"), date("d"), date("Y")));
    		 
    		if ($isBudget or $isContract) {
    			$query = new CQuery();
    			$query->select("kadri.id as kadri_id,
						loads.year_id as year_id,
						kadri.fio as fio,
						kadri.fio_short,
						dolgnost.name_short as dolgnost,
						hr.rate");
    			$query->from(TABLE_PERSON." as kadri");
    			$query->leftJoin(TABLE_WORKLOAD." as loads", "loads.person_id = kadri.id");
    			$query->leftJoin(TABLE_WORKLOAD_WORKS." as hours", "hours.workload_id = loads.id");
    			$query->leftJoin(TABLE_POSTS." as dolgnost", "dolgnost.id = kadri.dolgnost");
    			$query->leftJoin(TABLE_HOURS_RATE." as hr", "hr.dolgnost_id = kadri.dolgnost");
    			if (!is_null($person)) {
    				$query->condition("loads.year_id = ".$selectedYear." and loads.person_id = ".$person->getId()." AND loads._is_last_version = 1");
    			} else {
    				$query->condition("loads.year_id = ".$selectedYear." AND loads._is_last_version = 1");
    			}
    			$query->group("kadri.id");
    			$query->order("kadri.fio_short asc");
    			$personsWithLoad = $query->execute()->getItems();
    			$i = 0;
    			foreach ($personsWithLoad as $person) {
    				$queryOrders = new CQuery();
    				$queryOrders->select("round(sum(rate),2) as rate_sum, count(id) as ord_cnt")
    				->from(TABLE_STAFF_ORDERS." as orders")
    				->condition('concat(substring(date_end, 7, 4), ".", substring(date_end, 4, 2), ".", substring(date_end, 1, 2)) >= "'.$dateFrom.'" and kadri_id = "'.$person['kadri_id'].'"');
    				foreach ($queryOrders->execute()->getItems() as $order) {
    					$personsWithLoad[$i]['rate_sum'] = $order['rate_sum'];
    					$personsWithLoad[$i]['ord_cnt'] = $order['ord_cnt'];
    					$i++;
    				}
    			}
    			$i = 0;
    			foreach ($personsWithLoad as $person) {
    				$groupsCountSum = 0;
    				$studentsCountSum = 0;
    				$hoursSumBase = 0;
    				$hoursSumAdditional = 0;
    				$hoursSumPremium = 0;
    				$hoursSumByTime = 0;
    				$hoursSum = 0;
    				$year = CTaxonomyManager::getYear($selectedYear);
    				$studyLoads = CStudyLoadService::getStudyLoadsByYear(CStaffManager::getPerson($person['kadri_id']), $year);
    				foreach ($studyLoads->getItems() as $studyLoad) {
    					$groupsCountSum += $studyLoad->groups_count;
    					if ($isBudget) {
    						$studentsCountSum += $studyLoad->students_count;
    						$kind = CTaxonomyManager::getTaxonomy(CStudyLoadKindsConstants::TAXONOMY_HOURS_KIND)->getTerm(CStudyLoadKindsConstants::BUDGET)->getId();
    						foreach ($studyLoad->getWorksByKind($kind) as $work) {
    							$hoursSum += $work->workload;
    							$hoursSumBase += $work->getSumWorkHoursByLoadTypeId($baseLoadId);
    							$hoursSumAdditional += $work->getSumWorkHoursByLoadTypeId($additionalLoadId);
    							$hoursSumPremium += $work->getSumWorkHoursByLoadTypeId($premiumLoadId);
    							$hoursSumByTime += $work->getSumWorkHoursByLoadTypeId($byTimeLoadId);
    						}
    					}
    					if ($isContract) {
    						$studentsCountSum += $studyLoad->students_contract_count;
    						$kind = CTaxonomyManager::getTaxonomy(CStudyLoadKindsConstants::TAXONOMY_HOURS_KIND)->getTerm(CStudyLoadKindsConstants::CONTRACT)->getId();
    						foreach ($studyLoad->getWorksByKind($kind) as $work) {
    							$hoursSum += $work->workload;
    							$hoursSumBase += $work->getSumWorkHoursByLoadTypeId($baseLoadId);
    							$hoursSumAdditional += $work->getSumWorkHoursByLoadTypeId($additionalLoadId);
    							$hoursSumPremium += $work->getSumWorkHoursByLoadTypeId($premiumLoadId);
    							$hoursSumByTime += $work->getSumWorkHoursByLoadTypeId($byTimeLoadId);
    						}
    					}
    				}
    				 
    				$personsWithLoad[$i]['groups_cnt_sum_'] = $groupsCountSum;
    				$personsWithLoad[$i]['stud_cnt_sum_'] = $studentsCountSum;
    				$personsWithLoad[$i]['hours_sum_base'] = $hoursSumBase;
    				$personsWithLoad[$i]['hours_sum_additional'] = $hoursSumAdditional;
    				$personsWithLoad[$i]['hours_sum_premium'] = $hoursSumPremium;
    				$personsWithLoad[$i]['hours_sum_by_time'] = $hoursSumByTime;
    				$personsWithLoad[$i]['hours_sum'] = $hoursSum;
    				 
    				$i++;
    			}
    		}
    		CApp::getApp()->cache->set($cacheKey, $personsWithLoad);
    		return CApp::getApp()->cache->get($cacheKey);
    	}
    }
    
    /**
     * Значения для общей суммы по типам нагрузки по преподавателю
     * 
     * @param int $kadriId - id преподавателя
     * @param int $yearId - id года
     * @param int $isBudget - вид работы нагрузки: бюджет
     * @param int $isContract - вид работы нагрузки: контракт
     * @return array
     */
    public static function getStudyWorksTotalValues($kadriId, $yearId, $isBudget, $isContract) {
    	$result = array();
    	foreach (CTaxonomyManager::getLegacyTaxonomy(TABLE_WORKLOAD_WORK_TYPES)->getTerms()->getItems() as $term) {
    		if ($term->is_total) {
    			$row = array();
    			
    			// тип работы
    			$row[0] = $term->getValue();
    			
    			$person = CStaffManager::getPerson($kadriId);
    			$year = CTaxonomyManager::getYear($yearId);
    			$sum = 0;
    			
    			// бюджет
    			if ($isBudget and !$isContract) {
    				foreach (CStudyLoadService::getStudyLoadsByYear($person, $year)->getItems() as $studyLoad) {
    					$sum += $studyLoad->getLoadByKindAndType(CTaxonomyManager::getTaxonomy(CStudyLoadKindsConstants::TAXONOMY_HOURS_KIND)->getTerm(CStudyLoadKindsConstants::BUDGET)->getId(), $term->getId());
    				}
    			}
    			 
    			// коммерция
    			if ($isContract and !$isBudget) {
    				foreach (CStudyLoadService::getStudyLoadsByYear($person, $year)->getItems() as $studyLoad) {
    					$sum += $studyLoad->getLoadByKindAndType(CTaxonomyManager::getTaxonomy(CStudyLoadKindsConstants::TAXONOMY_HOURS_KIND)->getTerm(CStudyLoadKindsConstants::CONTRACT)->getId(), $term->getId());
    				}
    			}
    			 
    			// бюджет и коммерция
    			if ($isContract and $isBudget) {
    				foreach (CStudyLoadService::getStudyLoadsByYear($person, $year)->getItems() as $studyLoad) {
    					$sum += $studyLoad->getLoadByType($term->getId());
    				}
    			}
    			
    			$row[1] = $sum;
    			 
    			$result[$term->getId()] = $row;
    		}
    	}
    	return $result;
    }
    
    /**
     * Значения для общей суммы по преподавателю и семестру
     *
     * @param CPerson $lecturer - преподаватель
     * @param CTerm $year - учебный год
     * @param CYearPart $part - семестр
     * @param array $loadTypes - типы нагрузок
     * 
     * @return CArrayList
     */
    public static function getStudyWorksTotalValuesByLecturerAndPart(CPerson $lecturer, CTerm $year, CYearPart $part, $loadTypes) {
    	$result = new CArrayList();
    	foreach (CTaxonomyManager::getLegacyTaxonomy(TABLE_WORKLOAD_WORK_TYPES)->getTerms()->getItems() as $term) {
    		$row = array();
    		
    		// тип работы
    		$row[0] = $term->getValue();
    		
    		$sum = 0;
    		foreach (CStudyLoadService::getStudyLoadsByYearAndLoadType($lecturer, $year, $loadTypes)->getItems() as $studyLoad) {
    			if ($studyLoad->year_part_id == $part->getId()) {
    				$sum += $studyLoad->getLoadByType($term->getId());
    			}
    		}
    		
    		$row[1] = $sum;
    		
    		$result->add($term->getId(), $row);
    	}
    	return $result;
    }
    

    /**
     * Значения для общей суммы по преподавателю за оба семестра
     *
     * @param CPerson $lecturer - преподаватель
     * @param CTerm $year - учебный год
     * @param array $loadTypes - типы нагрузок
     *
     * @return CArrayList
     */
    public static function getStudyWorksTotalValuesByLecturer(CPerson $lecturer, CTerm $year, $loadTypes) {
    	$result = new CArrayList();
    	foreach (CTaxonomyManager::getLegacyTaxonomy(TABLE_WORKLOAD_WORK_TYPES)->getTerms()->getItems() as $term) {
    		$row = array();
    
    		// тип работы
    		$row[0] = $term->getValue();
    
    		$sum = 0;
    		foreach (CStudyLoadService::getStudyLoadsByYearAndLoadType($lecturer, $year, $loadTypes)->getItems() as $studyLoad) {
    			$sum += $studyLoad->getLoadByType($term->getId());
    		}
    
    		$row[1] = $sum;
    
    		$result->add($term->getId(), $row);
    	}
    	return $result;
    }
    
    /**
     * Значения для столбца "Всего" по преподавателю, году, семестру и типам нагрузок
     *
     * @param CPerson $lecturer - преподаватель
     * @param CTerm $year - учебный год
     * @param CYearPart $part - семестр
     * @param array $loadTypes - типы нагрузок
     *
     * @return int
     */
    public static function getAllStudyWorksTotalValuesByLecturerAndPart(CPerson $lecturer, CTerm $year, CYearPart $part, $loadTypes) {
    	$sum = 0;
    	foreach (CStudyLoadService::getStudyLoadsByYearAndLoadType($lecturer, $year, $loadTypes)->getItems() as $studyLoad) {
    		if ($studyLoad->year_part_id == $part->getId()) {
    			$sum += $studyLoad->getSumWorksValue();
    		}
    	}
    	return $sum;
    }
    
    /**
     * Значения для столбца "Надбавка за филиалы" по преподавателю, году, семестру и типам нагрузок
     *
     * @param CPerson $lecturer - преподаватель
     * @param CTerm $year - учебный год
     * @param CYearPart $part - семестр
     * @param array $loadTypes - типы нагрузок
     *
     * @return int
     */
    public static function getAllStudyWorksTotalValuesByLecturerAndPartWithFilials(CPerson $lecturer, CTerm $year, CYearPart $part, $loadTypes) {
    	$sum = 0;
    	foreach (CStudyLoadService::getStudyLoadsByYearAndLoadType($lecturer, $year, $loadTypes)->getItems() as $studyLoad) {
    		if ($studyLoad->year_part_id == $part->getId()) {
    			$sum += $studyLoad->getWorkWithFilialsTotals();
    		}
    	}
    	return $sum;
    }
    
    /**
     * Значение "Всего за год" (без учёта филиалов) по преподавателю, году и типам нагрузок
     *
     * @param CPerson $lecturer - преподаватель
     * @param CTerm $year - учебный год
     * @param array $loadTypes - типы нагрузок
     *
     * @return int
     */
    public static function getAllStudyWorksTotalValuesByLecturer(CPerson $lecturer, CTerm $year, $loadTypes) {
    	$sum = 0;
    	foreach (CStudyLoadService::getStudyLoadsByYearAndLoadType($lecturer, $year, $loadTypes)->getItems() as $studyLoad) {
    		$sum += $studyLoad->getSumWorksValue();
    	}
    	return $sum;
    }
    
    /**
     * Значение "Всего за год" (с учётом филиалов) по преподавателю, году и типам нагрузок
     *
     * @param CPerson $lecturer - преподаватель
     * @param CTerm $year - учебный год
     * @param array $loadTypes - типы нагрузок
     *
     * @return int
     */
    public static function getAllStudyWorksTotalValuesByLecturerWithFilials(CPerson $lecturer, CTerm $year, $loadTypes) {
    	$sum = 0;
    	foreach (CStudyLoadService::getStudyLoadsByYearAndLoadType($lecturer, $year, $loadTypes)->getItems() as $studyLoad) {
    		$sum += $studyLoad->getSumWorksValue();
    		$sum += $studyLoad->getWorkWithFilialsTotals();
    	}
    	return $sum;
    }
    
    /**
     * Значения для общей суммы по типам нагрузки по всем преподавателям
     *
     * @param $yearId - id года
     * @param int $isBudget - вид работы нагрузки: бюджет
     * @param int $isContract - вид работы нагрузки: контракт
     * @return CArrayList
     */
    public static function getAllStudyWorksTotalValues($yearId, $isBudget, $isContract) {
    	$result = new CArrayList();
    	foreach (CTaxonomyManager::getLegacyTaxonomy(TABLE_WORKLOAD_WORK_TYPES)->getTerms()->getItems() as $term) {
    		if ($term->is_total) {
    			$row = array();
    			 
    			// тип работы
    			$row[0] = $term->getValue();
    			
    			$year = CTaxonomyManager::getYear($yearId);
    			$sum = 0;
    			 
    			// бюджет
    			if ($isBudget and !$isContract) {
    				foreach (CStudyLoadService::getAllStudyLoadsByYear($year)->getItems() as $studyLoad) {
    					$sum += $studyLoad->getLoadByKindAndType(CTaxonomyManager::getTaxonomy(CStudyLoadKindsConstants::TAXONOMY_HOURS_KIND)->getTerm(CStudyLoadKindsConstants::BUDGET)->getId(), $term->getId());
    				}
    			}
    	
    			// коммерция
    			if ($isContract and !$isBudget) {
    				foreach (CStudyLoadService::getAllStudyLoadsByYear($year)->getItems() as $studyLoad) {
    					$sum += $studyLoad->getLoadByKindAndType(CTaxonomyManager::getTaxonomy(CStudyLoadKindsConstants::TAXONOMY_HOURS_KIND)->getTerm(CStudyLoadKindsConstants::CONTRACT)->getId(), $term->getId());
    				}
    			}
    			
    			// бюджет и коммерция
    			if ($isContract and $isBudget) {
    				foreach (CStudyLoadService::getAllStudyLoadsByYear($year)->getItems() as $studyLoad) {
    					$sum += $studyLoad->getLoadByType($term->getId());
    				}
    			}
    			$row[1] = $sum;
    	
    			$result->add($term->getId(), $row);
    		}
    	}
    	return $result;
    }
    
    /**
     * Значения для общей суммы по типам нагрузки по одному преподавателю
     *
     * @param $personId - id преподавателя
     * @param $yearId - id года
     * @param int $isBudget - вид работы нагрузки: бюджет
     * @param int $isContract - вид работы нагрузки: контракт
     * @return CArrayList
     */
    public static function getAllStudyWorksTotalValuesByPerson($personId, $yearId, $isBudget, $isContract) {
    	$result = new CArrayList();
    	foreach (CTaxonomyManager::getLegacyTaxonomy(TABLE_WORKLOAD_WORK_TYPES)->getTerms()->getItems() as $term) {
    		if ($term->is_total) {
    			$row = array();
    
    			// тип работы
    			$row[0] = $term->getValue();
    			 
    			$person = CStaffManager::getPerson($personId);
    			$year = CTaxonomyManager::getYear($yearId);
    			$sum = 0;
    
    			// бюджет
    			if ($isBudget and !$isContract) {
    				foreach (CStudyLoadService::getAllStudyLoadsByYearAndPerson($person, $year)->getItems() as $studyLoad) {
    					$sum += $studyLoad->getLoadByKindAndType(CTaxonomyManager::getTaxonomy(CStudyLoadKindsConstants::TAXONOMY_HOURS_KIND)->getTerm(CStudyLoadKindsConstants::BUDGET)->getId(), $term->getId());
    				}
    			}
    			 
    			// коммерция
    			if ($isContract and !$isBudget) {
    				foreach (CStudyLoadService::getAllStudyLoadsByYearAndPerson($person, $year)->getItems() as $studyLoad) {
    					$sum += $studyLoad->getLoadByKindAndType(CTaxonomyManager::getTaxonomy(CStudyLoadKindsConstants::TAXONOMY_HOURS_KIND)->getTerm(CStudyLoadKindsConstants::CONTRACT)->getId(), $term->getId());
    				}
    			}
    			 
    			// бюджет и коммерция
    			if ($isContract and $isBudget) {
    				foreach (CStudyLoadService::getAllStudyLoadsByYearAndPerson($person, $year)->getItems() as $studyLoad) {
    					$sum += $studyLoad->getLoadByType($term->getId());
    				}
    			}
    			$row[1] = $sum;
    			 
    			$result->add($term->getId(), $row);
    		}
    	}
    	return $result;
    }
    
    /**
     * Заголовки для общей суммы по типам нагрузки
     * 
     * @return array
     */
    public static function getStudyWorksTotalTitles() {
    	$result = array();
    	foreach (CTaxonomyManager::getLegacyTaxonomy(TABLE_WORKLOAD_WORK_TYPES)->getTerms()->getItems() as $term) {
    		if ($term->is_total) {
    			$result[$term->getId()] = $term->getValue();
    		}
    	}
    	return $result;
    }
    
    /**
     * Сотрудники без нагрузки в указанном году
     *
     * @param int $selectedYear
     * @return CArrayList
     */
    public static function getPersonsWithoutLoadByYear($selectedYear) {
    	$personsWithoutLoad = new CArrayList();
    	$query = new CQuery();
    	$query->select("person.*")
	    	->from(TABLE_PERSON." as person")
	    	->condition("person.id NOT IN (SELECT person_id from ".TABLE_WORKLOAD." WHERE year_id='".$selectedYear."')")
	    	->order("person.fio_short asc");
    	
    	$set = new CRecordSet(false);
    	$set->setQuery($query);
    	foreach ($set->getItems() as $item) {
    		$person = new CPerson($item);
    		if ($person->hasPersonType(TYPE_PPS)) {
    			$personsWithoutLoad->add($person->getId(), $person);
    		}
    	}
    	
    	return $personsWithoutLoad;
    }
    
    /**
     * Лист нагрузок преподавателя по году и семестру
     *
     * @param CArrayList $loads - лист нагрузок
     * @param CYearPart $part - семестр
     *
     * @return CArrayList
     */
    public static function getStudyLoadsByPart($loads, CYearPart $part) {
    	$result = new CArrayList();
    	foreach ($loads as $study) {
    		if ($study->year_part_id == $part->getId()) {
    			$result->add($study->getId(), $study);
    		}
    	}
    	// сортируем нагрузки по названию дисциплин
    	$comparator = new CCorriculumDisciplinesComparator();
    	$sorted = CCollectionUtils::sort($result, $comparator);
    	return $sorted;
    }
    
    /**
     * Очистка кэша учебной нагрузки
     * 
     * @param CStudyLoad $studyLoad
     */
    public static function clearCache(CStudyLoad $studyLoad) {
        CApp::getApp()->cache->delete("cachePersonsWithLoadByYear_isBudget_isContract_".$studyLoad->year_id."_null");
        CApp::getApp()->cache->delete("cachePersonsWithLoadByYear_notBudget_isContract_".$studyLoad->year_id."_null");
        CApp::getApp()->cache->delete("cachePersonsWithLoadByYear_isBudget_notContract_".$studyLoad->year_id."_null");
        CApp::getApp()->cache->delete("cachePersonsWithLoadByYear_notBudget_notContract_".$studyLoad->year_id."_null");
        
        CApp::getApp()->cache->delete("cachePersonsWithLoadByYear_isBudget_isContract_".$studyLoad->year_id."_".$studyLoad->person_id);
        CApp::getApp()->cache->delete("cachePersonsWithLoadByYear_notBudget_isContract_".$studyLoad->year_id."_".$studyLoad->person_id);
        CApp::getApp()->cache->delete("cachePersonsWithLoadByYear_isBudget_notContract_".$studyLoad->year_id."_".$studyLoad->person_id);
        CApp::getApp()->cache->delete("cachePersonsWithLoadByYear_notBudget_notContract_".$studyLoad->year_id."_".$studyLoad->person_id);
    }
    
    /**
     * Копировать выбранные нагрузки
     * 
     * @param int $choice - способ копирования (0 - копирование с перемещением, 1 - только копирование)
     * @param int $lecturerId - id преподавателя, которому копируем нагрузку
     * @param int $yearId - id года, в который копируем
     * @param int $partId - id семестра, в который копируем
     * @param array $loadsToCopy - массив из id нагрузок, выбранных для копирования 
     */
    public static function copySelectedLoads($choice, $lecturerId, $yearId, $partId, $loadsToCopy) {
    	foreach ($loadsToCopy as $loadId) {
    		$studyLoad = CStudyLoadService::getStudyLoad($loadId);
    	
    		// очистка кэша
    		CStudyLoadService::clearCache($studyLoad);
    	
    		if ($choice == 0) {
    			// копирование с перемещением
    			$newLoad = $studyLoad->copy();
    			$newLoad->person_id = $lecturerId;
    			$newLoad->year_id = $yearId;
    			$newLoad->year_part_id = $partId;
    			$newLoad->comment = $newLoad->comment." копия от ".
    						CStaffManager::getPerson($lecturerId)->getNameShort().", ".
    						CTaxonomyManager::getYear($yearId)->getValue().", ".
    						CTaxonomyManager::getYearPart($partId)->getValue();
    			$newLoad->save();
    			/**
    			 * Копируем значения по видам работ нагрузки
    			 * @var CStudyLoadWork $work
    			 */
    			foreach ($studyLoad->works->getItems() as $work) {
    				$newWork = $work->copy();
    				$newWork->workload_id = $newLoad->getId();
    				$newWork->save();
    			}
    	
    			// удаляем оригинал нагрузки
    			CStudyLoadService::deleteStudyLoad($studyLoad);
    	
    		} elseif ($choice == 1) {
    			// только копирование
    			$newLoad = $studyLoad->copy();
    			$newLoad->person_id = $lecturerId;
    			$newLoad->year_id = $yearId;
    			$newLoad->year_part_id = $partId;
    			$newLoad->comment = $newLoad->comment." копия от ".
    						CStaffManager::getPerson($lecturerId)->getNameShort().", ".
    						CTaxonomyManager::getYear($yearId)->getValue().", ".
    						CTaxonomyManager::getYearPart($partId)->getValue();
    			$newLoad->save();
    			/**
    			 * Копируем значения по видам работ нагрузки
    			 * @var CStudyLoadWork $work
    			 */
    			foreach ($studyLoad->works->getItems() as $work) {
    				$newWork = $work->copy();
    				$newWork->workload_id = $newLoad->getId();
    				$newWork->save();
    			}
    		}
    	}
    }
    
    /**
     * Тип нагрузки (лекция, практика и др.) из справочника учебных работ по псевдониму
     * 
     * @param string $alias
     * @return CStudyLoadWork
     */
    public static function getWorktypeByAlias($alias) {
    	$works = new CArrayList();
    	foreach (CActiveRecordProvider::getWithCondition(TABLE_WORKLOAD_WORK_TYPES, "name_hours_kind = '".$alias."'")->getItems() as $item) {
    		$work = new CStudyLoadWork($item);
    		$works->add($work->getId(), $work);
    	}
    	return $works->getFirstItem();
    }
    
    /**
     * Вид нагрузки (основная, дополнительная, надбавка, почасовка) по псевдониму
     *
     * @param string $alias
     * @return CStudyLoadType
     */
    public static function getStudyLoadTypeByAlias($alias) {
    	$types = new CArrayList();
    	foreach (CActiveRecordProvider::getWithCondition(TABLE_IND_PLAN_PLANNED_TYPES, "comment = '".$alias."'")->getItems() as $item) {
    		$type = new CStudyLoadType($item);
    		$types->add($type->getId(), $type);
    	}
    	return $types->getFirstItem();
    }
    
    /**
     * Учебный семестр по псевдониму
     *
     * @param string $alias
     * @return CYearPart
     */
    public static function getYearPartByAlias($alias) {
    	$parts = new CArrayList();
    	foreach (CActiveRecordProvider::getWithCondition(TABLE_YEAR_PARTS, "comment = '".$alias."'")->getItems() as $item) {
    		$part = new CYearPart($item);
    		$parts->add($part->getId(), $part);
    	}
    	return $parts->getFirstItem();
    }
}
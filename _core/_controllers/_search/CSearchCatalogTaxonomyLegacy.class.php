<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksandr
 * Date: 27.02.14
 * Time: 21:08
 * To change this template use File | Settings | File Templates.
 */

class CSearchCatalogTaxonomyLegacy implements ISearchCatalogInterface {
    private $_catalog;
    function __construct($catalog)
    {
        $this->_catalog = $catalog;
    }

    public function actionTypeAhead($lookup)
    {
        $result = array();
        // унаследованная таксономия
        $taxonomy = CTaxonomyManager::getLegacyTaxonomy($this->_catalog);
        $query = new CQuery();
        $query->select("distinct(taxonomy.id) as id, taxonomy.name as name")
            ->from($taxonomy->getTableName()." as taxonomy")
            ->condition("taxonomy.name like '%".$lookup."%'")
            ->limit(0, 10);
        foreach ($query->execute()->getItems() as $item) {
            $result[$item["id"]] = $item["name"];
        }
        return $result;
    }

    public function actionGetItem($id)
    {
        $result = array();
        // унаследованная таксономия
        $taxonomy = CTaxonomyManager::getLegacyTaxonomy($this->_catalog);
        $term = $taxonomy->getTerm($id);
        if (!is_null($term)) {
            $result[$term->getId()] = $term->getValue();
        }
        return $result;
    }

    public function actionGetViewData()
    {
        $result = array();
        // унаследованная таксономия
        $taxonomy = CTaxonomyManager::getLegacyTaxonomy($this->_catalog);
        foreach ($taxonomy->getTerms()->getItems() as $term) {
            $result[$term->getId()] = $term->getValue();
        }
        return $result;
    }

}
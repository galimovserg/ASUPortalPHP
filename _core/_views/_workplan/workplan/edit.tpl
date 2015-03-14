{extends file="_core.3col.tpl"}

{block name="asu_center"}
    <h2>Редактирование рабочей программы</h2>
    {CHtml::helpForCurrentPage()}


    <form action="index.php" method="post" class="form-horizontal" ng-controller="WorkPlanController as ctrl" ng-init="init({$plan->getId()})">
        <ul class="nav nav-tabs">
            <li class="active"><a data-toggle="tab" href="#common">Общая информация</a></li>
        </ul>
        <div class="tab-content">
            <div id="common" class="tab-pane active">
                {include file="_workplan/workplan/subform.common.tpl"}
            </div>
        </div>
    </form>
{/block}

{block name="asu_right"}
    {include file="_workplan/workplan/common.right.tpl"}
{/block}

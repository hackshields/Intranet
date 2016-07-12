<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?

$APPLICATION->IncludeComponent(
	"bitrix:report.list",
	"",
	array(
		"USER_ID" => $arParams["USER_ID"],
		"GROUP_ID" => $arParams["GROUP_ID"],
		"PATH_TO_REPORT_LIST" => $arParams["PATH_TO_TASKS_REPORT"],
		"PATH_TO_REPORT_CONSTRUCT" => $arParams["PATH_TO_TASKS_REPORT_CONSTRUCT"],
		"PATH_TO_REPORT_VIEW" => $arParams["PATH_TO_TASKS_REPORT_VIEW"],
		"REPORT_HELPER_CLASS" => "CTasksReportHelper"
	),
	false
);

?>

<?php $this->SetViewTarget("sidebar_tools_1", 100);?>
<div class="sidebar-block task-filter task-filter-report">
	<b class="r2"></b><b class="r1"></b><b class="r0"></b>
	<div class="sidebar-block-inner">
		<ul class="task-filter-items">
			<li class="task-filter-item task-filter-item-selected">
				<a href="<?php echo CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_TASKS_REPORT"], array());?>" class="task-filter-item-link"><span
					class="task-filter-item-left"></span><span class="task-filter-item-text"><?php echo GetMessage("TASKS_REPORT_REPORTS")?></span></a>
			</li>
			<li class="task-filter-item">
				<a href="<?php echo CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_TASKS"], array());?>" class="task-filter-item-link"><span
					class="task-filter-item-left"></span><span class="task-filter-item-text"><?php echo GetMessage("TASKS_REPORT_TASKS")?></span></a>
			</li>
		</ul>
	</div>
	<i class="r0"></i><i class="r1"></i><i class="r2"></i>
</div>
<?php $this->EndViewTarget();?>


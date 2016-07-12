<?
require($_SERVER["DOCUMENT_ROOT"]."/mobile/headers.php");
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
//viewport rewrite
CMobile::getInstance()->setLargeScreenSupport(false);
CMobile::getInstance()->setScreenCategory("NORMAL");

CModule::IncludeModule('pull');
CJSCore::Init(array('pull'));

$APPLICATION->IncludeComponent("bitrix:mobile.menu", "", array(), false, Array("HIDE_ICONS" => "Y"));
$APPLICATION->IncludeComponent("bitrix:mobile.im.messenger", "", array(), false, Array("HIDE_ICONS" => "Y"));
// PUSH Module Event
?>
<script type="text/javascript">
	BX.addCustomEvent("onPullExtendWatch", function(data) {
		BX.PULL.extendWatch(data.id);
	});
	BX.addCustomEvent("thisPageWillDie", function(data) {
		BX.PULL.clearWatch(data.page_id);
	});
	BX.addCustomEvent("onPullEvent", function(module_id,command,params) {
		app.onCustomEvent('onPull', {'module_id': module_id, 'command':command, 'params':params});
	});
    app.enableSliderMenu(true);
	app.getToken();
	ReadyDevice(function() {
		BX.PULL.start(<?=(defined('BX_PULL_SKIP_LS')? "{LOCAL_STORAGE: 'N'}": '')?>);
	});
</script>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php")?>
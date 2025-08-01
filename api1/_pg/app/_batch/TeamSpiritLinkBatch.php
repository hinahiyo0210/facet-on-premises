<?php

define("BATCH", true);
require_once __DIR__.'/../_service/TeamSpirit/oauth.php';
include(dirname(__FILE__)."/../../procedural_php/ProceduralPhp.php");

// TeamSpiritを契約しているcontractorを取得
$teamspiritContractors = DB::selectArray('select contractor_id from m_contractor where teamspirit_flag <> 0');

foreach ($teamspiritContractors as $teamspiritContractor) {

    infoLog('TeamSpiritの連携を開始します：contractor_id['.$teamspiritContractor['contractor_id'].']');
    $result = AttendanceLogService::attendanceBatchAlignment($teamspiritContractor['contractor_id']);

    if ($result['result']) {
        infoLog($result['message']);
    } else {
        errorLog($result['message']);
    }

}

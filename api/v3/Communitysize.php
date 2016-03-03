<?php

function _civicrm_api3_communitysize_getcount_spec(&$params) {
  $params['group_id']['api.required'] = 1;
  $params['group_id']['api.default'] = CRM_Core_BAO_Setting::getItem('Community Size', 'member_group_id');
}


function civicrm_api3_communitysize_getcount($params) {
  $groupId = $params['group_id'];
  $results = CRM_Communitysize_Logic::getCount($groupId);
  return civicrm_api3_create_success($results, $params);
}


function _civicrm_api3_communitysize_cleanup_spec(&$params) {
  $params['group_id']['api.required'] = 1;
  $params['group_id']['api.default'] = CRM_Core_BAO_Setting::getItem('Community Size', 'member_group_id');
}


function civicrm_api3_communitysize_cleanup($params) {
  $tx = new CRM_Core_Transaction();
  try {
    $groupId = $params['group_id'];
    CRM_Communitysize_Logic::truncateTemporary();
    CRM_Communitysize_Logic::loadTemporary($groupId);
    CRM_Communitysize_Logic::cleanUp($groupId);
    $results = CRM_Communitysize_Logic::countTemporaryContacts();
    CRM_Communitysize_Logic::truncateTemporary();

    $tx->commit();
    return civicrm_api3_create_success($results, $params);
  } catch (Exception $ex) {
    $tx->rollback()->commit();
    throw $ex;
  }
}

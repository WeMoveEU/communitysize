<?php

function _civicrm_api3_communitysize_getcount_spec(&$params) {
  $params['group_id']['api.required'] = 1;
  $params['group_id']['api.default'] = CRM_Core_BAO_Setting::getItem('Community Size', 'member_group_id');
}

function civicrm_api3_communitysize_getcount($params) {
  $groupId = $params['group_id'];
  $query = "SELECT count(c.id)
            FROM civicrm_contact c
              JOIN civicrm_group_contact gc ON c.id = gc.contact_id AND gc.group_id = %1 AND gc.status = 'Added'
              JOIN civicrm_email e ON c.id = e.contact_id AND e.is_primary = 1 AND e.on_hold = 0
            WHERE c.is_deleted = 0 AND c.is_opt_out = 0;";
  $params = array(
    1 => array($groupId, 'Integer'),
  );
  $results = CRM_Core_DAO::singleValueQuery($query, $params);
  CRM_Core_Error::debug_var('$results', $results, false, true);
  return civicrm_api3_create_success($results, $params);
}

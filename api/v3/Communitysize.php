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

    $query = "TRUNCATE civicrm_communitysize_ids";
    CRM_Core_DAO::executeQuery($query);

    $query = "INSERT IGNORE INTO civicrm_communitysize_ids
              SELECT c.id
              FROM civicrm_contact c
                JOIN civicrm_group_contact gc ON c.id = gc.contact_id AND gc.group_id = %1 AND gc.status = 'Added'
              WHERE c.is_opt_out = 1
              UNION
              SELECT c.id
              FROM civicrm_contact c
                JOIN civicrm_group_contact gc ON c.id = gc.contact_id AND gc.group_id = %1 AND gc.status = 'Added'
              WHERE c.is_deleted = 1
              UNION
              SELECT c.id
              FROM civicrm_contact c
                JOIN civicrm_group_contact gc ON c.id = gc.contact_id AND gc.group_id = %1 AND gc.status = 'Added'
                JOIN civicrm_email e ON c.id = e.contact_id AND e.on_hold > 0";
    $params = array(
      1 => array($groupId, 'Integer'),
    );
    $dao = CRM_Core_DAO::executeQuery($query, $params);
    CRM_Core_Error::debug_var('$dao', $dao, false, true);

    $query = "UPDATE civicrm_group_contact gc
              JOIN civicrm_communitysize_ids i ON gc.contact_id = i.id AND gc.group_id = %1
              SET gc.status = 'Removed'";
    $params = array(
      1 => array($groupId, 'Integer'),
    );
    CRM_Core_DAO::executeQuery($query, $params);

    $query = "INSERT INTO civicrm_subscription_history (contact_id, group_id, date, method, status)
              SELECT id, %1, NOW(), 'Admin', 'Removed'
              FROM civicrm_communitysize_ids";
    $params = array(
      1 => array($groupId, 'Integer'),
    );
    CRM_Core_DAO::executeQuery($query, $params);

    $query = "TRUNCATE civicrm_communitysize_ids";
    CRM_Core_DAO::executeQuery($query);

    $tx->commit();
    return civicrm_api3_create_success(1, $params);
  } catch (Exception $ex) {
    $tx->rollback()->commit();
    throw $ex;
  }
}

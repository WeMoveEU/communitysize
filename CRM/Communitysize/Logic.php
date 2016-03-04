<?php

class CRM_Communitysize_Logic {


  /**
   * Get count of membership in group
   *
   * @param $groupId
   *
   * @return int
   */
  public static function getCount($groupId) {
    $query = "SELECT count(DISTINCT c.id)
              FROM civicrm_contact c
                JOIN civicrm_group_contact gc ON c.id = gc.contact_id AND gc.group_id = %1 AND gc.status = 'Added'
                JOIN civicrm_email e ON c.id = e.contact_id AND e.is_primary = 1 AND e.on_hold = 0
              WHERE c.is_deleted = 0 AND c.is_opt_out = 0;";
    $params = array(
      1 => array($groupId, 'Integer'),
    );
    return (int)CRM_Core_DAO::singleValueQuery($query, $params);
  }


  /**
   * Clean up membership in group
   *
   * @param $groupId
   */
  public static function cleanUp($groupId) {
    $query = "UPDATE civicrm_group_contact gc
              JOIN civicrm_communitysize_ids i ON gc.contact_id = i.id AND gc.group_id = %1
              SET gc.status = 'Removed'";
    $params = array(
      1 => array($groupId, 'Integer'),
    );
    CRM_Core_DAO::executeQuery($query, $params);

    $query = "INSERT INTO civicrm_subscription_history (contact_id, group_id, date, method, status)
              SELECT DISTINCTROW id, %1, NOW(), 'Admin', 'Removed'
              FROM civicrm_communitysize_ids";
    $params = array(
      1 => array($groupId, 'Integer'),
    );
    CRM_Core_DAO::executeQuery($query, $params);
  }


  /**
   * Truncate temporary table
   */
  public static function truncateTemporary() {
    $query = "TRUNCATE civicrm_communitysize_ids";
    CRM_Core_DAO::executeQuery($query);
  }


  /**
   * Load temporary ids for specific group
   *
   * @param $groupId
   */
  public static function loadTemporary($groupId) {
    $query = "INSERT IGNORE INTO civicrm_communitysize_ids
              SELECT c.id, 'is_opt_out'
              FROM civicrm_contact c
                JOIN civicrm_group_contact gc ON c.id = gc.contact_id AND gc.group_id = %1 AND gc.status = 'Added'
              WHERE c.is_opt_out = 1
              UNION
              SELECT c.id, 'is_deleted'
              FROM civicrm_contact c
                JOIN civicrm_group_contact gc ON c.id = gc.contact_id AND gc.group_id = %1 AND gc.status = 'Added'
              WHERE c.is_deleted = 1
              UNION
              SELECT c.id, CONCAT('on_hold:', e.on_hold)
              FROM civicrm_contact c
                JOIN civicrm_group_contact gc ON c.id = gc.contact_id AND gc.group_id = %1 AND gc.status = 'Added'
                JOIN civicrm_email e ON c.id = e.contact_id AND e.on_hold > 0
              UNION
              SELECT c.id, 'no_email'
              FROM civicrm_contact c
                JOIN civicrm_group_contact gc ON c.id = gc.contact_id AND gc.group_id = %1 AND gc.status = 'Added'
                LEFT JOIN civicrm_email e ON c.id = e.contact_id
              WHERE e.id IS NULL";
    $params = array(
      1 => array($groupId, 'Integer'),
    );
    CRM_Core_DAO::executeQuery($query, $params);
  }


  /**
   * Count temporary contacts
   *
   * @return int
   */
  public static function countTemporaryContacts() {
    $query = "SELECT count(DISTINCT id) FROM civicrm_communitysize_ids";
    return (int)CRM_Core_DAO::singleValueQuery($query);
  }
}

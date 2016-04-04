-- run this script after installation
START TRANSACTION ;
DELIMITER #
DROP FUNCTION IF EXISTS updateJoinActivities#
CREATE FUNCTION updateJoinActivities(groupId INT, activityType INT, nlimit INT) RETURNS INT
  BEGIN
    DECLARE cid, results INT;
    DECLARE done INT DEFAULT FALSE;
    DECLARE cur1 CURSOR FOR
      SELECT DISTINCT sh.contact_id
      FROM civicrm_subscription_history sh
        LEFT JOIN civicrm_communitysize_join t ON sh.contact_id = t.id
      WHERE group_id = groupId AND sh.status IN ('Added') AND t.id IS NULL
      ORDER BY sh.contact_id
      LIMIT nlimit;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;
    SET results = 0;

    DELETE FROM civicrm_communitysize_join;
    INSERT INTO civicrm_communitysize_join (id)
      SELECT DISTINCT sh.contact_id
      FROM civicrm_subscription_history sh
        JOIN civicrm_activity_contact ac ON sh.contact_id = ac.contact_id AND ac.record_type_id = 2
        JOIN civicrm_activity a ON ac.activity_id = a.id AND a.activity_type_id = activityType AND ABS(TIME_TO_SEC(TIMEDIFF(sh.date, a.activity_date_time))) <= 60
      WHERE sh.group_id = groupId AND sh.status IN ('Added');

    OPEN cur1;
    loop_contacts: LOOP
      FETCH cur1 INTO cid;
      IF done THEN
        CLOSE cur1;
        LEAVE loop_contacts;
      END IF;

      BEGIN
        DECLARE id2, naid, campaignId INT;
        DECLARE aDate DATETIME;
        DECLARE done2 INT DEFAULT FALSE;
        DECLARE cur2 CURSOR FOR
          SELECT date
          FROM civicrm_subscription_history sh
            LEFT JOIN (SELECT sh.id AS history_id
            FROM civicrm_subscription_history sh
              JOIN civicrm_activity_contact ac ON sh.contact_id = ac.contact_id AND ac.record_type_id = 2
              JOIN civicrm_activity a ON ac.activity_id = a.id AND a.activity_type_id = activityType AND sh.date = a.activity_date_time
            WHERE sh.group_id = groupId AND sh.contact_id = cid AND sh.status IN ('Added')) t ON sh.id = t.history_id
          WHERE group_id = groupId AND contact_id = cid AND sh.status IN ('Added')
          ORDER BY sh.date ASC
          LIMIT 1; -- only first subscription can have a Join activity
        DECLARE CONTINUE HANDLER FOR NOT FOUND SET done2 = 1;
        OPEN cur2;
        loop_history: LOOP
          FETCH cur2 INTO aDate;
          IF done2 THEN
            CLOSE cur2;
            LEAVE loop_history;
          END IF;

          SELECT a.campaign_id INTO campaignId
          FROM civicrm_activity a JOIN civicrm_activity_contact ac ON a.id = ac.activity_id
          WHERE ac.contact_id = cid AND a.activity_type_id = 32 and a.activity_date_time <= aDate
          ORDER BY a.activity_date_time ASC
          LIMIT 1;

          IF campaignId > 0 THEN
            INSERT INTO civicrm_activity (activity_type_id, subject, activity_date_time, status_id, campaign_id)
            VALUES (activityType, 'updateBySQL', aDate, 2, campaignId);
            SET campaignId = 0;
          ELSE
            INSERT INTO civicrm_activity (activity_type_id, subject, activity_date_time, status_id)
            VALUES (activityType, 'updateBySQL', aDate, 2);
          END IF;
          SET naid = last_insert_id();
          INSERT INTO civicrm_activity_contact (activity_id, contact_id, record_type_id) VALUES (naid, cid, 2);
          SET results = results + 1;
        END LOOP loop_history;
      END;
    END LOOP loop_contacts;
    DELETE FROM civicrm_communitysize_join;
    RETURN results;
  END#

DELIMITER ;
COMMIT ;

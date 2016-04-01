DROP TABLE IF EXISTS civicrm_communitysize_ids;
CREATE TABLE `civicrm_communitysize_ids` (
  `id` int(10) unsigned NOT NULL,
  `subject` varchar(255) NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

DROP TABLE IF EXISTS civicrm_communitysize_join;
CREATE TABLE `civicrm_communitysize_join` (
  `id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

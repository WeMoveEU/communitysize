DROP TABLE IF EXISTS civicrm_communitysize_cleanup;
CREATE TABLE `civicrm_communitysize_cleanup` (
  `id` int(10) unsigned NOT NULL,
  `subject` varchar(255) NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

DROP TABLE IF EXISTS civicrm_communitysize_join;
CREATE TABLE `civicrm_communitysize_join` (
  `id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

DROP TABLE IF EXISTS civicrm_communitysize_ids;
CREATE TABLE `civicrm_communitysize_ids` (
  `id` int(10) unsigned NOT NULL,
  `reason` varchar(255) NULL,
  PRIMARY KEY (`id`, `reason`)
) ENGINE=InnoDB;

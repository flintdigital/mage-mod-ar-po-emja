<?php

$installer = $this;

$installer->startSetup();

$installer->run("
-- DROP TABLE IF EXISTS {$this->getTable('emja_ar_comments')};
CREATE TABLE {$this->getTable('emja_ar_comments')} (
  `entity_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `increment_id` varchar(20) CHARACTER SET utf8 NOT NULL,
  `comment` text NOT NULL,
  PRIMARY KEY (`entity_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
");

$installer->endSetup(); 
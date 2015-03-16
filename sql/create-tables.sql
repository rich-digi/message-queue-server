DROP TABLE IF EXISTS `Messages`;
CREATE TABLE `Messages` (
  `MsgID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ToDMID` varchar(255) NOT NULL DEFAULT '',
  `From` varchar(255) DEFAULT NULL,
  `ReplyTo` varchar(255) DEFAULT NULL,
  `Template` varchar(255) DEFAULT NULL,
  `Creator` varchar(16) DEFAULT NULL,
  `Priority` tinyint(11) unsigned NOT NULL DEFAULT '5',
  `CreatedGMT` datetime NOT NULL,
  `ReadGMT` datetime DEFAULT NULL,
  `DeleteAfterDays` int(11) unsigned NOT NULL DEFAULT '90',
  `Subject` varchar(255) NOT NULL,
  `Content` text NOT NULL,
  `Deleted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`MsgID`),
  KEY `ToDMID` (`ToDMID`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
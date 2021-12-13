CREATE TABLE `tblAuthSessions` (
  `intAuthID` int(11) NOT NULL AUTO_INCREMENT,
  `txtSessionKey` varchar(255) DEFAULT NULL,
  `dtExpires` datetime DEFAULT NULL,
  `txtRedir` varchar(255) DEFAULT NULL,
  `txtRefreshToken` text DEFAULT NULL,
  `txtCodeVerifier` varchar(255) DEFAULT NULL,
  `txtToken` text DEFAULT NULL,
  `txtIDToken` text DEFAULT NULL,
  PRIMARY KEY (`intAuthID`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;
CREATE TABLE `tblDomains` (
  `intDomainID` int(11) NOT NULL AUTO_INCREMENT,
  `txtDomain` varchar(255) DEFAULT NULL,
  `txtOwner` varchar(255) DEFAULT NULL,
  `txtDefaultURL` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`intDomainID`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;
CREATE TABLE `tblUrls` (
  `intLinkID` int(11) NOT NULL AUTO_INCREMENT,
  `txtUrl` smalltext DEFAULT NULL,
  `txtSlug` varchar(255) DEFAULT NULL,
  `intHits` int(11) DEFAULT 0,
  `dtCreated` timestamp DEFAULT current_timestamp(),
  `txtCreator` varchar(255) DEFAULT NULL,
  `intDomainID` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`intLinkID`)
  ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

CREATE TABLE `tblSettings` (
  `intSettingID` int(11) NOT NULL AUTO_INCREMENT,
  `txtName` varchar(255) DEFAULT NULL,
  `txtValue` TEXT DEFAULT NULL,
  PRIMARY KEY (`intSettingID`)
);

INSERT INTO `tblSettings` (`txtName`, `txtValue`) VALUES ('listItems', '25');

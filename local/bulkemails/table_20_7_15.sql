/*
SQLyog Ultimate v10.00 Beta1
MySQL - 5.6.16 : Database - vlmsinfo
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`vlmsinfo` /*!40100 DEFAULT CHARACTER SET latin1 */;

USE `vlmsinfo`;

/*Table structure for table `mdl_bulkemails` */

DROP TABLE IF EXISTS `mdl_bulkemails`;

CREATE TABLE `mdl_bulkemails` (
  `id` bigint(10) NOT NULL DEFAULT '0',
  `email` longtext NOT NULL,
  `from` longtext NOT NULL,
  `to` longtext NOT NULL,
  `subject` longtext NOT NULL,
  `message` longtext NOT NULL,
  `timestart` longtext NOT NULL,
  `timeend` longtext NOT NULL,
  `created` bigint(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='manage Bulk emails';

/*Data for the table `mdl_bulkemails` */

/*Table structure for table `mdl_local_bulkemails_cron` */

DROP TABLE IF EXISTS `mdl_local_bulkemails_cron`;

CREATE TABLE `mdl_local_bulkemails_cron` (
  `id` bigint(10) NOT NULL DEFAULT '0',
  `timecreated` bigint(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='manage Bulk emails';

/*Data for the table `mdl_local_bulkemails_cron` */

/*Table structure for table `mdl_local_bulkemails_messages` */

DROP TABLE IF EXISTS `mdl_local_bulkemails_messages`;

CREATE TABLE `mdl_local_bulkemails_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subject` text,
  `message` longtext,
  `created` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=latin1;

/*Data for the table `mdl_local_bulkemails_messages` */

insert  into `mdl_local_bulkemails_messages`(`id`,`subject`,`message`,`created`) values (16,'sample subject1','<p>sample message1</p>','1437408517');

/*Table structure for table `mdl_local_bulkmails_mails_sent_queue` */

DROP TABLE IF EXISTS `mdl_local_bulkmails_mails_sent_queue`;

CREATE TABLE `mdl_local_bulkmails_mails_sent_queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `from_userid` int(11) NOT NULL,
  `to_userid` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `messageid` int(11) NOT NULL,
  `status` enum('pending','process','completed') DEFAULT NULL,
  `createdtime` varchar(255) DEFAULT NULL,
  `mail_cron_start_time` varchar(255) DEFAULT NULL,
  `mail_cron_end_time` varchar(255) DEFAULT NULL,
  `email_time_delay_next_mail` int(11) DEFAULT NULL,
  `email_responce` longtext,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=latin1;

/*Data for the table `mdl_local_bulkmails_mails_sent_queue` */

insert  into `mdl_local_bulkmails_mails_sent_queue`(`id`,`from_userid`,`to_userid`,`email`,`messageid`,`status`,`createdtime`,`mail_cron_start_time`,`mail_cron_end_time`,`email_time_delay_next_mail`,`email_responce`) values (19,146,2,'batchadmin01@mgrm.com',16,'completed','1437408517','1437409517','',2,'0'),(20,147,2,'batchadmin02@mgrm.com',16,'completed','1437408517','1437409527','',2,'0'),(21,148,2,'batchadmin03@mgrm.com',16,'completed','1437408517','1437409537','',2,'0'),(22,149,2,'batchadmin04@mgrm.com',16,'completed','1437408517','1437409547','',2,'0'),(23,150,2,'batchadmin05@mgrm.com',16,'completed','1437408517','1437409557','',2,'0'),(24,151,2,'batchadmin06@mgrm.com',16,'completed','1437408517','1437409567','',2,'0');

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

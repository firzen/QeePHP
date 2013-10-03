/*
SQLyog Community v10.11 
MySQL - 5.0.45-community-nt : Database - todo_db
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`todo_db` /*!40100 DEFAULT CHARACTER SET utf8 */;

USE `todo_db`;

/*Table structure for table `tasks` */

DROP TABLE IF EXISTS `tasks`;

CREATE TABLE `tasks` (
  `task_id` int(11) NOT NULL auto_increment,
  `subject` varchar(200) NOT NULL,
  `description` text,
  `created` int(11) NOT NULL,
  `updated` int(11) NOT NULL,
  `is_completed` tinyint(1) NOT NULL,
  `completed_at` int(11) default NULL,
  `owner_id` int(11) NOT NULL,
  PRIMARY KEY  (`task_id`),
  KEY `owner_id` (`owner_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

/*Data for the table `tasks` */

insert  into `tasks`(`task_id`,`subject`,`description`,`created`,`updated`,`is_completed`,`completed_at`,`owner_id`) values (1,'完成快速入门的pdf制作','PDF适合打印，chm适合阅读',1376403301,1376403301,0,NULL,1),(2,'第二个任务','按照主题整理文档后重新发布',1376403713,1376405039,1,1376405039,1);

/*Table structure for table `users` */

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL auto_increment,
  `username` varchar(20) NOT NULL,
  `password` varchar(80) NOT NULL,
  PRIMARY KEY  (`user_id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

/*Data for the table `users` */

insert  into `users`(`user_id`,`username`,`password`) values (1,'steven','848b9ac3515a03c39bd1ec66de7662d5');

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

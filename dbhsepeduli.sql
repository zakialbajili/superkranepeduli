/*
SQLyog Ultimate v12.5.1 (64 bit)
MySQL - 10.1.21-MariaDB : Database - dbhsepeduli
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`dbhsepeduli` /*!40100 DEFAULT CHARACTER SET latin1 */;

USE `dbhsepeduli`;

/*Table structure for table `thsedata_master` */

DROP TABLE IF EXISTS `thsedata_master`;

CREATE TABLE `thsedata_master` (
  `pk_hsedatamaster_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `param_1` varchar(50) DEFAULT NULL,
  `param_2` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `created_by` varchar(100) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `updated_by` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`pk_hsedatamaster_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*Data for the table `thsedata_master` */

/*Table structure for table `thsepelaporanbahaya` */

DROP TABLE IF EXISTS `thsepelaporanbahaya`;

CREATE TABLE `thsepelaporanbahaya` (
  `pk_hsepelaporanbahaya_id` int(11) NOT NULL AUTO_INCREMENT,
  `tgl_pelaporan` date DEFAULT NULL COMMENT 'mandatory',
  `lokasi_bahaya` varchar(50) DEFAULT NULL COMMENT 'mandatory',
  `shift` varchar(50) DEFAULT NULL COMMENT 'Pagi, Malam (mandatory)',
  `data_pelaporan` varchar(50) DEFAULT NULL COMMENT 'Inspection, Hazard Report (mandatory)',
  `kategori_bahaya` varchar(50) DEFAULT NULL COMMENT 'Tindakan Tidak Aman, Kondisi Tidak Aman',
  `desc_kategori_bahaya` text,
  `desc_temuan_bahaya` text COMMENT 'mandatory',
  `rekomendasi_perbaikan` text COMMENT 'mandatory',
  `dept_penanggungjwb` varchar(30) DEFAULT NULL COMMENT 'mandatory',
  `nama_pengawas` varchar(100) DEFAULT NULL COMMENT 'mandatory',
  `due_date` date DEFAULT NULL COMMENT 'mandatory',
  `status_pelaporan` varchar(30) DEFAULT NULL COMMENT 'Open/Mulai, On Progress/Sedang proses,Closed/selesai (mandatory)',
  `created_date` datetime DEFAULT NULL,
  `created_by` varchar(100) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `updated_by` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`pk_hsepelaporanbahaya_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*Data for the table `thsepelaporanbahaya` */

/*Table structure for table `thseuser` */

DROP TABLE IF EXISTS `thseuser`;

CREATE TABLE `thseuser` (
  `pk_hseuser_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `employee_no` varchar(20) DEFAULT NULL,
  `password` varchar(100) DEFAULT NULL,
  `login_last` datetime DEFAULT NULL,
  `created_by` varchar(100) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(100) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  PRIMARY KEY (`pk_hseuser_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

/*Data for the table `thseuser` */

insert  into `thseuser`(`pk_hseuser_id`,`name`,`employee_no`,`password`,`login_last`,`created_by`,`created_date`,`updated_by`,`updated_date`) values 
(1,'ADMIN','001621','$2y$10$vuzL66db4.uzQsXDIb0bV.qNNts7N5H.I/dlTGWDuT1wPYGD3rXlW',NULL,NULL,NULL,NULL,NULL);

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

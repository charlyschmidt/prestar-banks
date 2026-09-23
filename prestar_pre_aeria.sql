-- MySQL dump 10.13  Distrib 5.7.44, for Linux (x86_64)
--
-- Host: localhost    Database: prestar_db
-- ------------------------------------------------------
-- Server version	5.7.44

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `account_daily_balances`
--

DROP TABLE IF EXISTS `account_daily_balances`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `account_daily_balances` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `financial_day_id` bigint(20) unsigned NOT NULL,
  `account_id` bigint(20) unsigned NOT NULL,
  `initial_balance` decimal(15,2) NOT NULL,
  `current_balance` decimal(15,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `account_daily_balances_financial_day_id_account_id_unique` (`financial_day_id`,`account_id`),
  KEY `account_daily_balances_account_id_foreign` (`account_id`),
  CONSTRAINT `account_daily_balances_account_id_foreign` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `account_daily_balances_financial_day_id_foreign` FOREIGN KEY (`financial_day_id`) REFERENCES `financial_days` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `account_daily_balances`
--

LOCK TABLES `account_daily_balances` WRITE;
/*!40000 ALTER TABLE `account_daily_balances` DISABLE KEYS */;
INSERT INTO `account_daily_balances` VALUES (1,1,1,1000000.00,282000.00,'2026-09-11 17:22:54','2026-09-12 02:35:35'),(2,1,2,1000000.00,900000.00,'2026-09-11 17:22:54','2026-09-11 18:01:39'),(3,1,3,1000000.00,1000000.00,'2026-09-11 17:22:54','2026-09-11 17:22:54'),(4,1,4,1000000.00,1000000.00,'2026-09-11 17:22:54','2026-09-11 17:22:54'),(5,2,1,92000000.00,15829723.91,'2026-09-14 12:47:48','2026-09-14 17:40:00'),(6,2,2,127000000.00,50646500.23,'2026-09-14 12:47:48','2026-09-14 17:09:23'),(7,2,3,117000000.00,65020235.68,'2026-09-14 12:47:48','2026-09-14 17:25:28'),(8,2,4,0.00,127200.00,'2026-09-14 12:47:48','2026-09-14 14:59:07'),(9,3,1,138000000.00,30591400.63,'2026-09-15 11:46:25','2026-09-15 17:32:22'),(10,3,2,102000000.00,47970589.43,'2026-09-15 11:46:25','2026-09-15 17:23:05'),(11,3,3,64000000.00,24749220.52,'2026-09-15 11:46:25','2026-09-15 17:48:50'),(12,3,4,0.00,800000.00,'2026-09-15 11:46:25','2026-09-15 14:27:55'),(13,4,1,174000000.00,1815818.06,'2026-09-16 12:01:08','2026-09-16 17:42:09'),(14,4,2,66000000.00,2649005.98,'2026-09-16 12:01:08','2026-09-16 17:33:16'),(15,4,3,25000000.00,3084628.41,'2026-09-16 12:01:08','2026-09-16 17:11:21'),(16,4,4,100000.00,100000.00,'2026-09-16 12:01:08','2026-09-16 12:01:08'),(17,5,1,109000000.00,32259258.30,'2026-09-17 12:11:33','2026-09-17 16:12:04'),(18,5,2,71000000.00,59210441.00,'2026-09-17 12:11:33','2026-09-17 16:37:15'),(19,5,3,3000000.00,20000000.00,'2026-09-17 12:11:33','2026-09-17 17:15:19'),(20,5,4,100000.00,100000.00,'2026-09-17 12:11:33','2026-09-17 12:11:33'),(21,6,1,187000000.00,21304363.95,'2026-09-18 12:22:52','2026-09-18 16:00:20'),(22,6,2,82000000.00,80000000.00,'2026-09-18 12:22:52','2026-09-18 16:12:26'),(23,6,3,19000000.00,10730442.87,'2026-09-18 12:22:52','2026-09-18 15:23:26'),(24,6,4,100000.00,100000.00,'2026-09-18 12:22:52','2026-09-18 12:22:52'),(25,7,1,58000000.00,3726254.97,'2026-09-21 12:08:05','2026-09-21 18:05:09'),(26,7,2,99000000.00,407159.99,'2026-09-21 12:08:05','2026-09-21 17:32:14'),(27,7,3,15000000.00,4253127.05,'2026-09-21 12:08:05','2026-09-21 17:04:12'),(28,7,4,100000.00,600000.00,'2026-09-21 12:08:05','2026-09-21 16:09:09'),(29,8,1,183000000.00,6339119.87,'2026-09-22 12:26:43','2026-09-22 15:53:20'),(30,8,2,120000000.00,24202089.60,'2026-09-22 12:26:43','2026-09-22 18:00:26'),(31,8,3,3000000.00,8446300.00,'2026-09-22 12:26:43','2026-09-22 16:25:47'),(32,8,4,100000.00,2860000.00,'2026-09-22 12:26:43','2026-09-22 15:27:30');
/*!40000 ALTER TABLE `account_daily_balances` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `accounts`
--

DROP TABLE IF EXISTS `accounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `accounts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'bank',
  `logo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `accounts`
--

LOCK TABLES `accounts` WRITE;
/*!40000 ALTER TABLE `accounts` DISABLE KEYS */;
INSERT INTO `accounts` VALUES (1,'COMAFI','bank','accounts/c98SdxuAP0I5RO3rGMze3io9zrsZz7sNkXyrK4lW.png','2026-09-11 17:19:24','2026-09-11 20:00:14'),(2,'PAMPA','bank','accounts/QdtJ5hfByXvLucLhKozt0zfD3K5a5lFyJVwfKS59.png','2026-09-11 17:20:12','2026-09-11 20:00:27'),(3,'FRANCES','bank','accounts/sDJxAOTd1yRWdXpDX3wuuFBkwmLIlkR67MhIpEak.png','2026-09-11 17:20:21','2026-09-11 20:00:35'),(4,'PROVINCIA','bank','accounts/OQTp5yMK9CutsmeOuyXlx8LIiLHb2fXCKzzz0Ofz.webp','2026-09-11 17:20:31','2026-09-11 20:00:43');
/*!40000 ALTER TABLE `accounts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint(20) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint(20) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  KEY `failed_jobs_connection_queue_failed_at_index` (`connection`,`queue`,`failed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `financial_days`
--

DROP TABLE IF EXISTS `financial_days`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `financial_days` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `status` enum('open','closed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open',
  `opened_by` bigint(20) unsigned DEFAULT NULL,
  `opened_at` timestamp NULL DEFAULT NULL,
  `closed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `financial_days_date_unique` (`date`),
  KEY `financial_days_opened_by_foreign` (`opened_by`),
  CONSTRAINT `financial_days_opened_by_foreign` FOREIGN KEY (`opened_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `financial_days`
--

LOCK TABLES `financial_days` WRITE;
/*!40000 ALTER TABLE `financial_days` DISABLE KEYS */;
INSERT INTO `financial_days` VALUES (1,'2026-09-11','open',1,'2026-09-11 17:22:54',NULL,'2026-09-11 17:22:54','2026-09-11 17:22:54'),(2,'2026-09-14','open',1,'2026-09-14 12:47:48',NULL,'2026-09-14 12:47:48','2026-09-14 12:47:48'),(3,'2026-09-15','open',5,'2026-09-15 11:46:25',NULL,'2026-09-15 11:46:25','2026-09-15 11:46:25'),(4,'2026-09-16','open',5,'2026-09-16 12:01:08',NULL,'2026-09-16 12:01:08','2026-09-16 12:01:08'),(5,'2026-09-17','open',5,'2026-09-17 12:11:33',NULL,'2026-09-17 12:11:33','2026-09-17 12:11:33'),(6,'2026-09-18','open',5,'2026-09-18 12:22:52',NULL,'2026-09-18 12:22:52','2026-09-18 12:22:52'),(7,'2026-09-21','open',5,'2026-09-21 12:08:05',NULL,'2026-09-21 12:08:05','2026-09-21 12:08:05'),(8,'2026-09-22','open',5,'2026-09-22 12:26:43',NULL,'2026-09-22 12:26:43','2026-09-22 12:26:43');
/*!40000 ALTER TABLE `financial_days` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` smallint(5) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_09_08_142020_create_accounts_table',1),(5,'2026_09_08_142109_create_transactions_table',1),(6,'2026_09_08_143400_add_logo_to_accounts_table',1),(7,'2026_09_08_180359_change_date_to_datetime_in_transactions_table',1),(8,'2026_09_09_101416_create_financial_days_table',1),(9,'2026_09_09_101922_create_account_daily_balances_table',1),(10,'2026_09_09_102541_add_financial_day_id_to_transactions_table',1),(11,'2026_09_09_150333_add_balance_after_to_transactions_table',1),(12,'2026_09_10_135151_add_opened_at_to_financial_days_table',1),(13,'2026_09_14_120134_add_user_id_and_deleted_at_to_transactions_table',2),(14,'2026_09_14_135402_change_financial_amounts_to_decimal',3),(15,'2026_09_14_135656_change_financial_amounts_to_decimal',3),(16,'2026_09_14_151836_add_role_to_users_table',4),(17,'2026_09_14_154033_add_deleted_at_to_users_table',4),(18,'2026_09_14_211627_add_destination_bank_to_transactions_table',4),(19,'2026_09_14_221613_add_execution_fields_to_transactions_table',4),(20,'2026_09_15_104018_add_reserve_type_to_transactions',5);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES ('2ax4ZeDSdvh0axwD57Vfq9fsM86YiDhsu8X5hHxw',NULL,'184.194.23.2','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36','eyJfdG9rZW4iOiJoTmNXemx3dk51R2tGZjlIV0ZWU2RIV24yTWhNR1BBZDRPNDBIbUlIIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC93d3cuYWVyaWFmaW5hbmNlLmNvbS5hciIsInJvdXRlIjoiZ2VuZXJhdGVkOjpwQ1F0Y2FsZG53Vk9mVVo4In0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1790127264),('3Ts9F8CHP5iDw0nnHqcHmF3NDku3uNylbghbrqKA',NULL,'142.147.225.206','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','eyJfdG9rZW4iOiJoVHFCVlFMZE81ZzJVc3EyYmtySlRKZ3FobVlGRWdGa3NnVFNHRGpHIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9hZXJpYWZpbmFuY2UuY29tLmFyXC9sb2dpbiIsInJvdXRlIjoibG9naW4ifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1790100472),('3yOtgbZkMPqxsLwpVujg3u74AGFWzDlzWeYBwbb7',NULL,'216.235.222.4','Mozilla/5.0 (compatible; NRDChecker/1.0)','eyJfdG9rZW4iOiI4NXZkMUlZem05anUyMTJYb01oNDJjZVJ3a2xlM2x3YkV3MlYzM290IiwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1790108942),('4z91jpFP1bh4rmxTDJMtlzq45MKvq20Zu9SbdqMv',4,'201.251.61.12','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36','eyJfdG9rZW4iOiJxUHhBY0hEbmdFTlh5ZExVZ25iQ3VMdXNjQTd5WElYNDBaZVVYR25sIiwibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiOjQsIl9wcmV2aW91cyI6eyJ1cmwiOiJodHRwOlwvXC9zZC0zMTk4NDcwLWgwMDAxMC5mZXJvem8ubmV0XC90cmFuc2FjdGlvbnMiLCJyb3V0ZSI6InRyYW5zYWN0aW9ucy5pbmRleCJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19',1790100263),('72cUM5L6seVzZ0KhTQo0R93LRLdzqadKStKxR4Ug',NULL,'3.220.151.36','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:139.0) Gecko/20100101 Firefox/139.0','eyJfdG9rZW4iOiJhV3BBWUxCbDkxQTViRWRqa2ZSRVFtek44OU9HVWZ3OUYxUjVXZ1pGIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC93d3cuYWVyaWFmaW5hbmNlLmNvbS5hclwvbG9naW4iLCJyb3V0ZSI6ImxvZ2luIn0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1790106080),('7eGtJiNfbjWumkf9g2NNXsE4dI8DzpcKC9XVMncy',NULL,'3.220.151.36','Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/100.0.4896.60 Safari/537.36','eyJfdG9rZW4iOiJhOGpUazJ6SE9xbHVpb0xyWE15WHJjQ3FDSEZOd2RCU0hTcWRuUjc1IiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC93d3cuYWVyaWFmaW5hbmNlLmNvbS5hclwvbG9naW4iLCJyb3V0ZSI6ImxvZ2luIn0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1790102618),('7ObJTBiLn1lNbJFfVyjaVoUl5D0Nxf3UxgaEHwxZ',1,'201.251.61.12','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36 Edg/153.0.0.0','eyJfdG9rZW4iOiJvWEhaZ3FxNmg2dlJCTjVHVlpLOHhaaEJTTG1xU2FBalFpeUdQT252IiwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJ1cmwiOnsiaW50ZW5kZWQiOiJodHRwOlwvXC9zZC0zMTk4NDcwLWgwMDAxMC5mZXJvem8ubmV0XC9kYXNoYm9hcmQifSwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL3NkLTMxOTg0NzAtaDAwMDEwLmZlcm96by5uZXRcL2Rhc2hib2FyZCIsInJvdXRlIjoiZGFzaGJvYXJkIn0sImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjoxfQ==',1790099341),('a7kfP04Y4SfdsCVQ5HNgxopaNQehOGyy4ck3mMJD',NULL,'201.251.61.12','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36','eyJfdG9rZW4iOiJocGlnR1NaRFRlVm8waUptOVkwa2g2VVhqUkRJa2dmWjl3UnpyMFZaIiwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJfcHJldmlvdXMiOnsidXJsIjoiaHR0cDpcL1wvc2QtMzE5ODQ3MC1oMDAwMTAuZmVyb3pvLm5ldFwvbG9naW4iLCJyb3V0ZSI6ImxvZ2luIn19',1790101440),('afETHl4Mea2JiMu4yDNnOhLrCKNGKT9PC23hlFf5',NULL,'54.81.151.22','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36','eyJfdG9rZW4iOiJPMlJaWVlEcTQ5QWVmVVFTNzZKQjdLQ29XdDV1T2FtSWFGcFZEM29EIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC93d3cuYWVyaWFmaW5hbmNlLmNvbS5hclwvbG9naW4iLCJyb3V0ZSI6ImxvZ2luIn0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1790099169),('B7zmdEcrSB6Ziwrd0lT6rn9JjGtF8RtA20yOvslw',NULL,'100.50.94.209','Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/536.3 (KHTML, like Gecko) Chrome/19.0.1063.0 Safari/536.3','eyJfdG9rZW4iOiIyVWc1bDdZb2xudDhRUTJySHU1Tm1nTDRSckNwSGtZY2Qyb3Y4NU9KIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9hZXJpYWZpbmFuY2UuY29tLmFyXC9sb2dpbiIsInJvdXRlIjoibG9naW4ifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1790106055),('ddQLNZjscOse8PUyefZFgB0xRkU9EoOnnsDV8XTs',NULL,'94.154.43.129','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','eyJfdG9rZW4iOiJ5N2FLeUZnN2xzV2QzWGZFRkNKVzhsVENQS2tsSkFmV3g4blhpTXFJIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9hZXJpYWZpbmFuY2UuY29tLmFyIiwicm91dGUiOiJnZW5lcmF0ZWQ6OnBDUXRjYWxkbndWT2ZVWjgifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1790095900),('EmPTthVbWklxiPfPzWY77HqW7HOIOimsPeljFlys',NULL,'213.169.207.29','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','eyJfdG9rZW4iOiI5YjhTbjFWOTJ4b0pXeDJ6NllyQWg1NW5ObkdKYjhRcnZCZmFPRURGIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9hZXJpYWZpbmFuY2UuY29tLmFyXC9sb2dpbiIsInJvdXRlIjoibG9naW4ifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1790107586),('fUEZvLnstq8IZniQduHomAx7soYEeTLaEDSKW8HG',7,'201.251.61.12','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36','eyJfdG9rZW4iOiJTUkFLc2pyanpzTXpkb3N5OGlOeTY2MXljY2tUZTZFRVFsbkpvNW1VIiwidXJsIjp7ImludGVuZGVkIjoiaHR0cDpcL1wvc2QtMzE5ODQ3MC1oMDAwMTAuZmVyb3pvLm5ldFwvZGFzaGJvYXJkIn0sIl9wcmV2aW91cyI6eyJ1cmwiOiJodHRwOlwvXC9zZC0zMTk4NDcwLWgwMDAxMC5mZXJvem8ubmV0XC90cmFuc2FjdGlvbnMiLCJyb3V0ZSI6InRyYW5zYWN0aW9ucy5pbmRleCJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX0sImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjo3fQ==',1790097913),('gAc5LVNEPqMznITA7HY1XIgVWt4x4V6xCEI24HaI',3,'201.251.61.12','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36','eyJfdG9rZW4iOiIxZEVUTWd4NjNoY1ViUmpjTzVtVlZqVkt6d2xvSGhXMmxETzc3VU5NIiwibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiOjMsIl9wcmV2aW91cyI6eyJ1cmwiOiJodHRwOlwvXC9zZC0zMTk4NDcwLWgwMDAxMC5mZXJvem8ubmV0XC9kYXNoYm9hcmQiLCJyb3V0ZSI6ImRhc2hib2FyZCJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19',1790099979),('gK3j8r9zw4ZTzBzQ0RL3RRVBNHt613dO3CVENQ2C',NULL,'100.50.94.209','Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/536.3 (KHTML, like Gecko) Chrome/19.0.1063.0 Safari/536.3','eyJfdG9rZW4iOiJLbDA3SHQ0U1lRV3JpUEhranFTOWdTVWZyeHoyWWI5aERiek9nZzlUIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9hZXJpYWZpbmFuY2UuY29tLmFyIiwicm91dGUiOiJnZW5lcmF0ZWQ6OnBDUXRjYWxkbndWT2ZVWjgifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1790106054),('gYLA5lIy8R79QMfXwIqTYKPn14Q930537mInvi2G',NULL,'201.251.61.12','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36','eyJfdG9rZW4iOiJEb01QTnJ3cVFIQnZ4WGprR1VkZVcwWFRxcFVaYU9Ra2tSZmxCN21qIiwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJfcHJldmlvdXMiOnsidXJsIjoiaHR0cDpcL1wvc2QtMzE5ODQ3MC1oMDAwMTAuZmVyb3pvLm5ldFwvbG9naW4iLCJyb3V0ZSI6ImxvZ2luIn19',1790100738),('HQrai9lyYwtNrgRAeSVzMgjXTVkGvepn5codA2RZ',NULL,'54.81.151.22','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36','eyJfdG9rZW4iOiJzMWNRSElKS3RPMDZzN1NHbjlBeThuSFJiU3hqQmFRb0pWR25kV3ZFIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC93d3cuYWVyaWFmaW5hbmNlLmNvbS5hciIsInJvdXRlIjoiZ2VuZXJhdGVkOjpwQ1F0Y2FsZG53Vk9mVVo4In0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1790099169),('IKhFDoIQtudWkpWn4OtTS5L5ah4Neq5rkl8z5k5O',NULL,'54.81.151.22','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36','eyJfdG9rZW4iOiJyYmc2NnpCaURONVRMR2FZdThGcEpRcXpJa0VZYUJMRm95Y2o5NzNaIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC93d3cuYWVyaWFmaW5hbmNlLmNvbS5hciIsInJvdXRlIjoiZ2VuZXJhdGVkOjpwQ1F0Y2FsZG53Vk9mVVo4In0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1790109686),('JkprDtzz0MKMCesHm3O9uS6hIGN8j4Ny6K17NAyg',NULL,'3.220.151.36','Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/100.0.4896.60 Safari/537.36','eyJfdG9rZW4iOiI2bVVseFpYMmxudEhqaWQyVThkTHQzcVZNTlpEVnlISWg3TmR6T2NPIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC93d3cuYWVyaWFmaW5hbmNlLmNvbS5hciIsInJvdXRlIjoiZ2VuZXJhdGVkOjpwQ1F0Y2FsZG53Vk9mVVo4In0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1790102618),('Jt5nezH2rMcSXM0S2UYyDJnDlaSU5PHxqZWqYd2S',NULL,'54.226.11.165','SAMSUNG-S8000/S8000XXIF3 SHP/VPP/R5 Jasmine/1.0 Nextreaming SMM-MMS/1.2.0 profile/MIDP-2.1 configuration/CLDC-1.1 FirePHP/0.3','eyJfdG9rZW4iOiI5SlViOW1HWVRGWmNGb0dWRVRTM1h5SUZvQWo1UFhZSmJqNmJpaTBnIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9hZXJpYWZpbmFuY2UuY29tLmFyIiwicm91dGUiOiJnZW5lcmF0ZWQ6OnBDUXRjYWxkbndWT2ZVWjgifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1790098110),('N0m47CSHlhnZNtemzv4BHpbQKsldUwWu14DagIVR',NULL,'54.226.11.165','Mozilla/5.0 (iPhone; CPU OS 11_0 like Mac OS X) AppleWebKit/604.1.25 (KHTML, like Gecko) Version/11.0 Mobile/15A372 Safari/604.1','eyJfdG9rZW4iOiJYTVZKbTlGZ3gxSTFhWHNuNFQ5NGp3bWIyY1l1cEtUbFpCY3NPU0pEIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9hZXJpYWZpbmFuY2UuY29tLmFyXC9sb2dpbiIsInJvdXRlIjoibG9naW4ifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1790098111),('OCgwbDnfv7EJW5tsBE5ew1Ltq3bRPWR6BkJaACaO',NULL,'100.50.94.209','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36','eyJfdG9rZW4iOiJsTzNLVjZKU0tlbjhjV1ZjYk9rU21wMW1BWXRVQmZaeFRhT3pPbGFwIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9hZXJpYWZpbmFuY2UuY29tLmFyIiwicm91dGUiOiJnZW5lcmF0ZWQ6OnBDUXRjYWxkbndWT2ZVWjgifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1790109643),('pmmid6szo0EY52YtYeo2z693YsMETmo2UuTj01rx',NULL,'152.32.209.62','Mozilla/5.0 (Windows NT 8_2_2; Win64; x64) AppleWebKit/535.41 (KHTML, like Gecko) Chrome/52.0.2542 Safari/537.36','eyJfdG9rZW4iOiJhZmNtT1JUWGFLUFA5WXRBU3pSek41eFpDWlE2Z0dudGhsV3pwdkxqIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC93d3cuYWVyaWFmaW5hbmNlLmNvbS5hciIsInJvdXRlIjoiZ2VuZXJhdGVkOjpwQ1F0Y2FsZG53Vk9mVVo4In0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1790120266),('PR23zHnuNBL1E5hNPs9o9ezO6CSQ3WICfGwF4QRU',NULL,'216.235.222.4','Mozilla/5.0 (compatible; NRDChecker/1.0)','eyJfdG9rZW4iOiJjaVNjMXQ1RVhhZ2VqNTBwbW5hYWVyYzFnYWVSTTJyRUxrWWh4bGY5IiwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1790108942),('prfod7AkypshDMnpU5720nE5qTZDaNZywzQVznjd',NULL,'94.154.43.135','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','eyJfdG9rZW4iOiJHMDljQUlRVkRCMTVoaVBsQ1BqQXZuVXNycEVKNG45UkU1ZWRPR2VKIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9hZXJpYWZpbmFuY2UuY29tLmFyXC9sb2dpbiIsInJvdXRlIjoibG9naW4ifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1790095902),('QKSp8YJqwS9R0MYEBmjyAkYItFBR2hvSlBeD1j5d',5,'201.251.61.12','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36','eyJfdG9rZW4iOiI4MlJjNEJxam5YVzVuQ3k3UmZLZXRuV1pGRFNlMERWWVE2clV0SzRzIiwibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiOjUsIl9wcmV2aW91cyI6eyJ1cmwiOiJodHRwOlwvXC9zZC0zMTk4NDcwLWgwMDAxMC5mZXJvem8ubmV0XC90cmFuc2FjdGlvbnNcL2V4cG9ydCIsInJvdXRlIjoidHJhbnNhY3Rpb25zLmV4cG9ydCJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19',1790100110),('Rfbir78nrVBKTwixHDXh8btTOSUUD22GYzo3Khch',NULL,'54.81.151.22','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36','eyJfdG9rZW4iOiIwSUFmSXp3SWF5UW5BZVRHbHRRU044b252YTFicWtZN2FWU3RoV0toIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC93d3cuYWVyaWFmaW5hbmNlLmNvbS5hclwvbG9naW4iLCJyb3V0ZSI6ImxvZ2luIn0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1790109687),('rgJonZeV1AHgIHOXU2wPYRoODpXZKKR34EwuGkkn',NULL,'3.220.151.36','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:139.0) Gecko/20100101 Firefox/139.0','eyJfdG9rZW4iOiJJV2ZTVmZvZGE3bFh0MXdOM1g3MmtFU0Z4UXltMHRmbnRBZHE4QWVGIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC93d3cuYWVyaWFmaW5hbmNlLmNvbS5hciIsInJvdXRlIjoiZ2VuZXJhdGVkOjpwQ1F0Y2FsZG53Vk9mVVo4In0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1790106080),('sNb9Z3nny6EgQPhWcvrLhURm3IMWllSoyTD7zW9m',NULL,'104.253.228.126','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','eyJfdG9rZW4iOiJVTVVCYnppZXE2Wmt6bzEyUW91aG5FNTlncmdma2o0bUwxSTJCdDRjIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9hZXJpYWZpbmFuY2UuY29tLmFyXC9sb2dpbiIsInJvdXRlIjoibG9naW4ifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1790099509),('VdNp1Sz2waEeecH99VYpOvKaZRm076RIUjSdPsTK',NULL,'100.50.94.209','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36','eyJfdG9rZW4iOiJuVUtqQnRrS0NqOGdCUlNDSkJYYTB0UlRrbVh3NVJLSTA5MzhqRFhnIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9hZXJpYWZpbmFuY2UuY29tLmFyXC9sb2dpbiIsInJvdXRlIjoibG9naW4ifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1790109644),('yjUFg06cZESZeWYPelZuhPcmvkLDMObyqhJFF1I6',6,'201.251.61.12','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36','eyJfdG9rZW4iOiJObzNUUlU2cUZxWnE4aEZNdlh3SEZ1VmY3cndMdzNSSU5IUmdEcVNlIiwidXJsIjp7ImludGVuZGVkIjoiaHR0cDpcL1wvc2QtMzE5ODQ3MC1oMDAwMTAuZmVyb3pvLm5ldFwvZGFzaGJvYXJkIn0sIl9wcmV2aW91cyI6eyJ1cmwiOiJodHRwOlwvXC9zZC0zMTk4NDcwLWgwMDAxMC5mZXJvem8ubmV0XC90cmFuc2FjdGlvbnMiLCJyb3V0ZSI6InRyYW5zYWN0aW9ucy5pbmRleCJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX0sImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjo2fQ==',1790101330),('zu86qWMem45Id3RR9L88n3F9rMweYQ7FEFs8vRCw',NULL,'184.194.23.2','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36','eyJfdG9rZW4iOiJDcjBZclF0MlJ4a3JXNnR6R095NEpVSnZ4ZjhHTGxNTU52UFVmb3FqIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC93d3cuYWVyaWFmaW5hbmNlLmNvbS5hclwvbG9naW4iLCJyb3V0ZSI6ImxvZ2luIn0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1790127264),('Zx8VhELuwdiFi30cysFfwMUA5yibm9NtoCOwlkbi',NULL,'152.32.209.62','Mozilla/5.0 (Windows NT 7_1; Win64; x64) AppleWebKit/575.38 (KHTML, like Gecko) Chrome/107.0.2116 Safari/537.36','eyJfdG9rZW4iOiJVejdXTVI1bmJ4Y0h4QnhEMUlXYTlMN0dCV295cDJpenlOZWl3blJTIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC93d3cuYWVyaWFmaW5hbmNlLmNvbS5hclwvbG9naW4iLCJyb3V0ZSI6ImxvZ2luIn0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1790120273);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transactions`
--

DROP TABLE IF EXISTS `transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `transactions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `account_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `financial_day_id` bigint(20) unsigned DEFAULT NULL,
  `type` enum('income','expense','reserve','transfer_in','transfer_out') COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `balance_after` decimal(15,2) DEFAULT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `destination_bank` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `executed_at` timestamp NULL DEFAULT NULL,
  `executed_by` bigint(20) unsigned DEFAULT NULL,
  `transfer_id` bigint(20) unsigned DEFAULT NULL,
  `date` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `transactions_account_id_foreign` (`account_id`),
  KEY `transactions_financial_day_id_foreign` (`financial_day_id`),
  KEY `transactions_user_id_foreign` (`user_id`),
  KEY `transactions_executed_by_foreign` (`executed_by`),
  CONSTRAINT `transactions_account_id_foreign` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `transactions_executed_by_foreign` FOREIGN KEY (`executed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `transactions_financial_day_id_foreign` FOREIGN KEY (`financial_day_id`) REFERENCES `financial_days` (`id`) ON DELETE CASCADE,
  CONSTRAINT `transactions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=229 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transactions`
--

LOCK TABLES `transactions` WRITE;
/*!40000 ALTER TABLE `transactions` DISABLE KEYS */;
INSERT INTO `transactions` VALUES (1,1,NULL,1,'expense',100000.00,NULL,'TTE SIERRA',NULL,NULL,NULL,NULL,'2026-09-11 14:24:00','2026-09-11 17:24:54','2026-09-11 17:24:54',NULL),(3,2,NULL,1,'expense',100000.00,NULL,'IVAN',NULL,NULL,NULL,NULL,'2026-09-11 15:01:00','2026-09-11 18:01:39','2026-09-11 18:01:39',NULL),(4,1,NULL,1,'income',150000.00,NULL,'Test tiempo real',NULL,NULL,NULL,NULL,'2026-09-11 23:05:00','2026-09-12 02:06:20','2026-09-12 02:06:20',NULL),(5,1,NULL,1,'expense',768000.00,NULL,'Test',NULL,NULL,NULL,NULL,'2026-09-11 23:34:00','2026-09-12 02:35:35','2026-09-12 02:35:35',NULL),(6,3,NULL,2,'income',240000.00,NULL,'jime pino',NULL,NULL,NULL,NULL,'2026-09-14 10:03:00','2026-09-14 13:03:24','2026-09-14 13:03:24',NULL),(7,2,NULL,2,'expense',2163160.00,NULL,'guimaraez',NULL,NULL,NULL,NULL,'2026-09-14 10:14:00','2026-09-14 13:15:20','2026-09-14 13:15:20',NULL),(8,2,NULL,2,'expense',3315662.19,NULL,'de pablo',NULL,NULL,NULL,NULL,'2026-09-14 10:15:00','2026-09-14 13:16:14','2026-09-14 13:16:14',NULL),(9,3,NULL,2,'expense',688223.00,NULL,'romero',NULL,NULL,NULL,NULL,'2026-09-14 10:24:00','2026-09-14 13:25:04','2026-09-14 13:25:04',NULL),(10,3,NULL,2,'expense',1400000.00,NULL,'romero',NULL,NULL,NULL,NULL,'2026-09-14 10:25:00','2026-09-14 13:25:17','2026-09-14 13:25:17',NULL),(11,3,NULL,2,'expense',1300000.00,NULL,'romero',NULL,NULL,NULL,NULL,'2026-09-14 10:25:00','2026-09-14 13:25:28','2026-09-14 13:25:28',NULL),(12,3,NULL,2,'expense',4391541.32,NULL,'romero',NULL,NULL,NULL,NULL,'2026-09-14 10:25:00','2026-09-14 13:26:04','2026-09-14 13:26:33',NULL),(13,1,NULL,2,'expense',3238800.00,NULL,'MENESES',NULL,NULL,NULL,NULL,'2026-09-14 10:27:00','2026-09-14 13:28:09','2026-09-14 13:28:09',NULL),(14,2,NULL,2,'expense',6180298.10,NULL,'gennaker',NULL,NULL,NULL,NULL,'2026-09-14 10:31:00','2026-09-14 13:31:58','2026-09-14 13:31:58',NULL),(17,1,NULL,2,'expense',1560517.77,NULL,'MARTINO',NULL,NULL,NULL,NULL,'2026-09-14 11:28:00','2026-09-14 14:29:52','2026-09-14 14:29:52',NULL),(18,1,NULL,2,'expense',4000000.00,NULL,'VALSOL',NULL,NULL,NULL,NULL,'2026-09-14 11:33:00','2026-09-14 14:34:04','2026-09-14 14:34:04',NULL),(19,1,NULL,2,'expense',1944000.00,NULL,'SCHMIDT',NULL,NULL,NULL,NULL,'2026-09-14 11:42:00','2026-09-14 14:43:15','2026-09-14 14:43:15',NULL),(20,1,NULL,2,'expense',17808346.23,NULL,'RASTELLI',NULL,NULL,NULL,NULL,'2026-09-14 11:47:00','2026-09-14 14:49:13','2026-09-14 14:49:13',NULL),(21,1,NULL,2,'expense',1436475.00,NULL,'SALAZAR',NULL,NULL,NULL,NULL,'2026-09-14 11:53:00','2026-09-14 14:55:09','2026-09-14 14:55:09',NULL),(22,4,NULL,2,'income',127200.00,NULL,'SOMOZA',NULL,NULL,NULL,NULL,'2026-09-14 11:58:00','2026-09-14 14:59:07','2026-09-14 14:59:07',NULL),(24,2,NULL,2,'expense',11000000.00,NULL,'gallucci',NULL,NULL,NULL,NULL,'2026-09-14 12:02:00','2026-09-14 15:02:43','2026-09-14 15:02:43',NULL),(25,1,NULL,2,'expense',2295000.00,NULL,'MARTIN GUILLERMO',NULL,NULL,NULL,NULL,'2026-09-14 12:06:00','2026-09-14 15:06:42','2026-09-14 15:06:42',NULL),(29,1,NULL,2,'expense',1026206.29,NULL,'ARELLANO',NULL,NULL,NULL,NULL,'2026-09-14 12:35:00','2026-09-14 15:36:36','2026-09-14 15:36:36',NULL),(30,2,NULL,2,'expense',32719279.07,NULL,'rodovia',NULL,NULL,NULL,NULL,'2026-09-14 12:47:00','2026-09-14 15:47:37','2026-09-14 15:47:37',NULL),(31,3,NULL,2,'income',6460000.00,NULL,'FINAR',NULL,NULL,NULL,NULL,'2026-09-14 12:54:00','2026-09-14 15:55:03','2026-09-14 15:55:03',NULL),(32,2,NULL,2,'expense',9451492.33,NULL,'FRITZ',NULL,NULL,NULL,NULL,'2026-09-14 13:11:00','2026-09-14 16:11:54','2026-09-14 16:11:54',NULL),(33,2,4,2,'expense',4712086.30,57458022.01,'lapiru',NULL,NULL,NULL,NULL,'2026-09-14 14:00:00','2026-09-14 17:00:27','2026-09-14 17:01:01',NULL),(34,3,6,2,'expense',50000000.00,65920235.68,'tanos',NULL,NULL,NULL,NULL,'2026-09-14 14:02:00','2026-09-14 17:03:15','2026-09-14 17:03:15',NULL),(35,2,4,2,'expense',6811521.78,50646500.23,'artic sur',NULL,NULL,NULL,NULL,'2026-09-14 14:05:00','2026-09-14 17:05:33','2026-09-14 17:05:33',NULL),(36,1,7,2,'expense',12000000.00,46690654.71,'AYESTA. Transferir a NOVOSELCOV MICAELA - NACION - CC$',NULL,NULL,NULL,NULL,'2026-09-14 14:06:00','2026-09-14 17:06:45','2026-09-14 17:07:55',NULL),(37,1,3,2,'expense',30188130.80,16502523.91,'AMBROSETTO',NULL,NULL,NULL,NULL,'2026-09-14 14:11:00','2026-09-14 17:12:31','2026-09-14 17:12:31',NULL),(38,3,3,2,'expense',900000.00,65020235.68,'CERVINSKY (DEVIZIA)',NULL,NULL,NULL,NULL,'2026-09-14 14:23:00','2026-09-14 17:25:28','2026-09-14 17:25:28',NULL),(39,1,3,2,'expense',672800.00,15829723.91,'CERVINSKY (KEES)',NULL,NULL,NULL,NULL,'2026-09-14 14:39:00','2026-09-14 17:40:00','2026-09-14 17:40:00',NULL),(40,1,6,3,'income',100000.00,138100000.00,NULL,NULL,NULL,NULL,NULL,'2026-09-15 08:53:00','2026-09-15 11:53:28','2026-09-15 11:53:47','2026-09-15 11:53:47'),(41,1,6,3,'expense',100000.00,137900000.00,'gallucci nico','Banco Macro',NULL,NULL,NULL,'2026-09-15 09:01:00','2026-09-15 12:04:26','2026-09-15 12:04:45','2026-09-15 12:04:45'),(42,1,1,3,'expense',7360000.00,130640000.00,'AUDITEST ARG','Banco Credicoop','2026-09-15 12:47:49',9,NULL,'2026-09-15 09:14:00','2026-09-15 12:14:37','2026-09-15 12:47:49',NULL),(43,1,7,3,'expense',18000000.00,88129046.00,'\"ICBC - CC$ - 5390210358122 - HALBERG PABLO ANDRES -  20254470113 - 0150539902000103581228\"','ICBC Argentina','2026-09-15 12:47:53',9,NULL,'2026-09-15 09:16:00','2026-09-15 12:17:22','2026-09-15 13:14:37',NULL),(44,1,3,3,'expense',5998624.00,106641376.00,'DUARTE','Banco Patagonia','2026-09-15 12:58:52',9,NULL,'2026-09-15 09:35:00','2026-09-15 12:36:26','2026-09-15 12:58:52',NULL),(45,1,1,3,'expense',8000000.00,98641376.00,'LA ALDEA CULTIVOS','Banco de la Nación Argentina','2026-09-15 12:47:17',9,NULL,'2026-09-15 09:43:00','2026-09-15 12:45:48','2026-09-15 12:47:17',NULL),(46,1,7,3,'reserve',9000000.00,56463731.88,'Buen Beber',NULL,NULL,NULL,NULL,'2026-09-15 09:47:00','2026-09-15 12:48:16','2026-09-15 17:31:55','2026-09-15 17:31:55'),(47,1,8,3,'expense',1512330.00,88129046.00,'pago','Banco de la Provincia de Buenos Aires','2026-09-15 13:15:57',8,NULL,'2026-09-15 10:04:00','2026-09-15 13:07:02','2026-09-15 13:15:57',NULL),(48,1,3,3,'expense',6472586.78,81656459.22,'GYG (APERTURA BANCO WHATSAPP ANTONELLA GOMEZ)','Banco Santander Argentina','2026-09-15 14:05:30',8,NULL,'2026-09-15 10:57:00','2026-09-15 13:58:27','2026-09-15 14:05:30',NULL),(49,1,3,3,'expense',942981.82,80713477.40,'MARTINO JORGE CA','Banco Santander Argentina','2026-09-15 14:11:07',8,NULL,'2026-09-15 11:06:00','2026-09-15 14:06:52','2026-09-15 14:11:07',NULL),(50,2,4,3,'expense',3056586.60,98943413.40,'transporte somos arg','Banco de la Nación Argentina','2026-09-15 14:27:17',9,NULL,'2026-09-15 11:18:00','2026-09-15 14:19:01','2026-09-15 14:27:17',NULL),(51,4,3,3,'income',800000.00,800000.00,'PINO HUMBERTO RODOLFO',NULL,NULL,NULL,NULL,'2026-09-15 11:27:00','2026-09-15 14:27:55','2026-09-15 14:27:55',NULL),(52,3,1,3,'income',50000.00,64050000.00,'lizaso',NULL,NULL,NULL,NULL,'2026-09-15 11:29:00','2026-09-15 14:31:04','2026-09-15 14:31:04',NULL),(53,3,1,3,'income',2000000.00,66050000.00,'lizaso',NULL,NULL,NULL,NULL,'2026-09-15 11:31:00','2026-09-15 14:31:50','2026-09-15 14:45:29',NULL),(54,3,1,3,'income',3100000.00,69150000.00,'lizaso',NULL,NULL,NULL,NULL,'2026-09-15 11:31:00','2026-09-15 14:32:05','2026-09-15 14:45:45',NULL),(55,1,7,3,'expense',20000000.00,51363731.88,'Pampa - CC$ - 03000100003152753 - EL OREJANO SA - 30709933759 - 0930330810100031527532','Banco de La Pampa','2026-09-15 14:49:44',8,NULL,'2026-09-15 11:34:00','2026-09-15 14:35:40','2026-09-15 14:54:04',NULL),(56,1,3,3,'expense',9349745.52,56463731.88,'INEQ','Banco Credicoop','2026-09-15 14:49:42',8,NULL,'2026-09-15 11:35:00','2026-09-15 14:35:51','2026-09-15 14:49:42',NULL),(57,3,3,3,'income',57900.00,69207900.00,'SCHENA CTA BLINT',NULL,NULL,NULL,NULL,'2026-09-15 11:50:00','2026-09-15 14:50:39','2026-09-15 14:50:39',NULL),(58,2,6,3,'expense',2000000.00,96943413.40,'Lucanera, cta cte provincia','Banco de la Provincia de Buenos Aires','2026-09-15 15:01:42',9,NULL,'2026-09-15 11:52:00','2026-09-15 14:53:57','2026-09-15 15:01:42',NULL),(59,3,3,3,'expense',1882800.00,67325100.00,'CERVINSKY WHATSAPP MP DE VIZIA BRUNO','Cuenta Virtual','2026-09-15 15:05:38',9,NULL,'2026-09-15 11:52:00','2026-09-15 14:54:55','2026-09-15 15:05:38',NULL),(60,2,4,3,'expense',11338913.00,85604500.40,'passeggi alejandro C/C','Banco de la Nación Argentina','2026-09-15 15:01:32',9,NULL,'2026-09-15 11:56:00','2026-09-15 14:57:25','2026-09-15 15:01:32',NULL),(61,1,3,3,'expense',4929644.65,46434087.23,'DROG IB 68800/4','Banco Credicoop','2026-09-15 15:28:48',9,NULL,'2026-09-15 12:11:00','2026-09-15 15:11:59','2026-09-15 15:28:48',NULL),(62,2,4,3,'expense',15000000.00,70604500.40,'COFINCO','Banco de la Provincia de Buenos Aires','2026-09-15 15:28:36',9,NULL,'2026-09-15 12:13:00','2026-09-15 15:14:41','2026-09-15 15:28:36',NULL),(63,1,3,3,'expense',2555753.19,43878334.04,'LOG Y TTE PACINI CC','Banco de la Provincia de Buenos Aires','2026-09-15 15:29:01',9,NULL,'2026-09-15 12:13:00','2026-09-15 15:15:21','2026-09-15 15:29:01',NULL),(64,1,3,3,'expense',4333838.27,39544495.77,'META PACINI CC','Banco de la Provincia de Buenos Aires','2026-09-15 15:29:13',9,NULL,'2026-09-15 12:15:00','2026-09-15 15:16:16','2026-09-15 15:29:13',NULL),(65,3,5,3,'expense',597879.48,66727220.52,'pity.cony.kary','Cuenta Virtual','2026-09-15 15:41:51',8,NULL,'2026-09-15 12:27:00','2026-09-15 15:29:42','2026-09-15 15:41:51',NULL),(66,2,1,3,'expense',1915300.00,68689200.40,'VELILLA PABLO','Banco Patagonia','2026-09-15 15:33:08',8,NULL,'2026-09-15 12:30:00','2026-09-15 15:30:45','2026-09-15 15:33:08',NULL),(67,1,3,3,'expense',1340528.63,38203967.14,'SCHMIDT MARTINIANO','Banco de la Provincia de Buenos Aires','2026-09-15 15:37:02',8,NULL,'2026-09-15 12:32:00','2026-09-15 15:32:41','2026-09-15 15:37:02',NULL),(68,1,7,3,'expense',9300000.00,28903967.14,'Nacion - CC$ - 20843070023316 - NOVOSELCOV SOLANGE MICAELA - 27263909939 - 0110307420030700233165','Banco de la Nación Argentina','2026-09-15 15:45:28',8,NULL,'2026-09-15 12:39:00','2026-09-15 15:40:12','2026-09-15 15:48:59',NULL),(69,2,1,3,'expense',2192000.00,66497200.40,'MANEIRO MARCOS CTA NUEVA','Banco Macro','2026-09-15 15:45:26',8,NULL,'2026-09-15 12:41:00','2026-09-15 15:42:26','2026-09-15 15:45:26',NULL),(70,2,6,3,'income',3245800.00,69743000.40,'haarriet',NULL,NULL,NULL,NULL,'2026-09-15 13:08:00','2026-09-15 16:08:41','2026-09-15 16:08:41',NULL),(71,3,6,3,'expense',2000000.00,64727220.52,'carrizo aguirre/ LEMON','Cuenta Virtual','2026-09-15 16:26:20',9,NULL,'2026-09-15 13:16:00','2026-09-15 16:16:51','2026-09-15 16:26:20',NULL),(72,2,1,3,'expense',15516453.00,54226547.40,'TALLER VALENCIA','Banco Credicoop','2026-09-15 16:32:17',9,NULL,'2026-09-15 13:19:00','2026-09-15 16:20:42','2026-09-15 16:32:17',NULL),(73,2,4,3,'expense',4552963.02,49673584.38,'transp lapiru','Banco Supervielle','2026-09-15 16:32:27',9,NULL,'2026-09-15 13:20:00','2026-09-15 16:21:38','2026-09-15 16:32:27',NULL),(74,1,3,3,'expense',1570036.66,27333930.48,'LOG Y TTE PACINI CC','Banco de la Provincia de Buenos Aires','2026-09-15 16:48:16',9,NULL,'2026-09-15 13:44:00','2026-09-15 16:45:24','2026-09-15 16:48:16',NULL),(75,1,3,3,'expense',4621173.21,22712757.27,'META PACINI CC','Banco de la Provincia de Buenos Aires','2026-09-15 17:01:24',8,NULL,'2026-09-15 13:51:00','2026-09-15 16:52:39','2026-09-15 17:01:24',NULL),(76,2,4,3,'expense',1702994.95,47970589.43,'artic sur','ICBC Argentina','2026-09-15 17:29:33',9,NULL,'2026-09-15 14:22:00','2026-09-15 17:23:05','2026-09-15 17:29:33',NULL),(77,3,5,3,'expense',37000000.00,27727220.52,'SAP','Banco Galicia','2026-09-15 17:29:37',9,NULL,'2026-09-15 14:23:00','2026-09-15 17:24:26','2026-09-15 17:29:37',NULL),(78,3,1,3,'expense',3000000.00,24727220.52,'PAVON AGUSTIN','Banco de la Provincia de Buenos Aires','2026-09-15 17:31:18',9,NULL,'2026-09-15 14:27:00','2026-09-15 17:27:47','2026-09-15 17:31:18',NULL),(79,1,3,3,'expense',1121356.64,30591400.63,'LAURITO RODRIGO','BBVA Argentina','2026-09-15 17:34:39',9,NULL,'2026-09-15 14:31:00','2026-09-15 17:32:22','2026-09-15 17:34:39',NULL),(80,3,3,3,'income',22000.00,24749220.52,'PINO LAURA',NULL,NULL,NULL,NULL,'2026-09-15 14:46:00','2026-09-15 17:48:50','2026-09-15 17:48:50',NULL),(81,1,5,4,'expense',1900000.00,172100000.00,'TODASLASMOTOS','Banco de la Provincia de Buenos Aires','2026-09-16 13:29:45',8,NULL,'2026-09-16 10:26:00','2026-09-16 13:27:33','2026-09-16 13:29:45',NULL),(82,1,5,4,'expense',640565.52,171459434.48,'ADLONGONI','Banco Galicia','2026-09-16 13:39:31',9,NULL,'2026-09-16 10:33:00','2026-09-16 13:34:02','2026-09-16 13:39:31',NULL),(83,2,4,4,'expense',1730572.00,64269428.00,'transp lapiru','Banco Supervielle','2026-09-16 13:53:34',9,NULL,'2026-09-16 10:49:00','2026-09-16 13:50:02','2026-09-16 13:53:34',NULL),(84,1,7,4,'expense',47000000.00,123040934.48,'BBVA - CC$ - 0803356210 - BAHIA MOVILITY SAS - 30716826682 - 0170080020000033562106','BBVA Argentina','2026-09-16 14:24:02',9,NULL,'2026-09-16 11:10:00','2026-09-16 14:10:58','2026-09-16 14:24:02',NULL),(85,1,1,4,'expense',1418500.00,123040934.48,'LEON GUSTAVO','Banco de la Provincia de Buenos Aires','2026-09-16 14:24:07',9,NULL,'2026-09-16 11:10:00','2026-09-16 14:11:12','2026-09-16 14:24:07',NULL),(86,1,3,4,'expense',12660339.36,110380595.12,'LACI CC','Banco de la Provincia de Buenos Aires','2026-09-16 14:25:50',9,NULL,'2026-09-16 11:20:00','2026-09-16 14:21:21','2026-09-16 14:25:50',NULL),(87,1,1,4,'expense',4685000.00,105695595.12,'FERREYRA LUCIANO','ICBC Argentina','2026-09-16 14:29:25',9,NULL,'2026-09-16 11:26:00','2026-09-16 14:27:13','2026-09-16 14:29:25',NULL),(88,1,1,4,'expense',6000000.00,99695595.12,'FERREYRA LUCIANO','Banco Santander Argentina','2026-09-16 14:38:30',9,NULL,'2026-09-16 11:33:00','2026-09-16 14:34:12','2026-09-16 14:38:30',NULL),(89,1,3,4,'expense',5497388.54,94198206.58,'ELECTROMECANICA VALSOL','Banco Credicoop','2026-09-16 14:38:33',9,NULL,'2026-09-16 11:35:00','2026-09-16 14:36:05','2026-09-16 14:38:33',NULL),(90,2,4,4,'expense',2560981.49,61708446.51,'artic sur','ICBC Argentina','2026-09-16 15:03:15',9,NULL,'2026-09-16 11:54:00','2026-09-16 14:55:21','2026-09-16 15:03:15',NULL),(91,3,5,4,'income',500000.00,25500000.00,'TECNO NAVAL',NULL,NULL,NULL,NULL,'2026-09-16 11:59:00','2026-09-16 15:00:08','2026-09-16 15:00:08',NULL),(92,1,6,4,'expense',8601000.00,85597206.58,'Gallucci e hijos , cbu termina en 47059','Banco Galicia','2026-09-16 15:31:31',9,NULL,'2026-09-16 12:26:00','2026-09-16 15:28:27','2026-09-16 15:31:31',NULL),(93,1,1,4,'expense',5000000.00,80597206.58,'FERREYRA LUCIANO','Banco Santander Argentina','2026-09-16 15:34:26',9,NULL,'2026-09-16 12:30:00','2026-09-16 15:30:32','2026-09-16 15:34:26',NULL),(94,1,4,4,'expense',75000000.00,4403224.62,'rodovia','BBVA Argentina','2026-09-16 16:22:37',9,NULL,'2026-09-16 12:40:00','2026-09-16 15:41:27','2026-09-16 16:22:37',NULL),(95,3,4,4,'expense',5000000.00,5084628.41,'rodovia','BBVA Argentina','2026-09-16 16:22:40',9,NULL,'2026-09-16 12:41:00','2026-09-16 15:41:38','2026-09-16 16:22:40',NULL),(96,3,4,4,'expense',10335208.00,164792.00,'alias: Vasco1729.mp','Cuenta Virtual','2026-09-16 16:26:59',9,NULL,'2026-09-16 12:52:00','2026-09-16 15:53:41','2026-09-16 16:26:59',NULL),(97,2,5,4,'expense',14000000.00,47708446.51,'MASSER SRL','Brubank','2026-09-16 16:17:37',9,NULL,'2026-09-16 12:56:00','2026-09-16 15:57:27','2026-09-16 16:17:37',NULL),(98,2,5,4,'reserve',40000000.00,7708446.51,'ADMIRAL',NULL,NULL,NULL,NULL,'2026-09-16 12:58:00','2026-09-16 15:58:42','2026-09-16 16:09:05','2026-09-16 16:09:05'),(99,1,3,4,'expense',1193981.96,4403224.62,'ARELLANO DARIO','Banco de la Provincia de Buenos Aires','2026-09-16 16:17:42',9,NULL,'2026-09-16 13:11:00','2026-09-16 16:12:16','2026-09-16 16:17:42',NULL),(100,2,5,4,'expense',40000000.00,7708446.51,'BELONA PRIETO BPN','Cuenta Virtual','2026-09-16 16:17:40',9,NULL,'2026-09-16 13:09:00','2026-09-16 16:12:29','2026-09-16 16:17:40',NULL),(101,3,4,4,'expense',5080163.59,5084628.41,'rodovia','BBVA Argentina','2026-09-16 16:22:48',9,NULL,'2026-09-16 13:15:00','2026-09-16 16:15:45','2026-09-16 16:22:48',NULL),(102,1,3,4,'expense',2367406.56,2035818.06,'SALAZAR RUBEN CC','Banco Credicoop','2026-09-16 16:40:03',9,NULL,'2026-09-16 13:30:00','2026-09-16 16:30:49','2026-09-16 16:40:03',NULL),(103,2,7,4,'expense',3600000.00,4108446.51,'Nacion - CC$ - 20843070023316 - NOVOSELCOV SOLANGE MICAELA - 27263909939 - 0110307420030700233165','Banco de la Nación Argentina','2026-09-16 17:10:11',8,NULL,'2026-09-16 14:01:00','2026-09-16 17:01:50','2026-09-16 17:10:11',NULL),(104,3,3,4,'expense',2000000.00,3084628.41,'WHATSAPP MP MARZIALI MARINA','Cuenta Virtual','2026-09-16 17:15:43',8,NULL,'2026-09-16 14:06:00','2026-09-16 17:09:02','2026-09-16 17:15:43',NULL),(105,2,5,4,'expense',1459440.53,2649005.98,'ADMIRAL','Banco de La Pampa','2026-09-16 17:42:00',9,NULL,'2026-09-16 14:31:00','2026-09-16 17:33:16','2026-09-16 17:42:00',NULL),(106,1,3,4,'expense',220000.00,1815818.06,'FERNANDEZ KOSTOFF CAROLINA','Banco de la Provincia de Buenos Aires','2026-09-16 17:47:16',9,NULL,'2026-09-16 14:41:00','2026-09-16 17:42:09','2026-09-16 17:47:16',NULL),(107,1,5,5,'expense',5000000.00,104000000.00,'LUCIANO FERREYRA','ICBC Argentina','2026-09-17 12:35:38',9,NULL,'2026-09-17 09:24:00','2026-09-17 12:25:29','2026-09-17 12:35:38',NULL),(108,1,5,5,'expense',5000000.00,99000000.00,'LUCIANO FERREYRA','BBVA Argentina','2026-09-17 12:35:40',9,NULL,'2026-09-17 09:25:00','2026-09-17 12:27:05','2026-09-17 12:35:40',NULL),(109,1,3,5,'expense',7752679.26,91247320.74,'DUARTE JORGE','Banco Patagonia','2026-09-17 13:40:21',9,NULL,'2026-09-17 09:56:00','2026-09-17 12:57:39','2026-09-17 13:40:21',NULL),(110,1,3,5,'expense',1500000.00,89747320.74,'ELECTROMECANICA VALSOL CC','Banco Credicoop','2026-09-17 13:54:07',9,NULL,'2026-09-17 10:46:00','2026-09-17 13:46:52','2026-09-17 13:54:07',NULL),(111,3,3,5,'expense',3000000.00,0.00,'MP MARZIALI MARINA','Cuenta Virtual','2026-09-17 14:29:41',9,NULL,'2026-09-17 11:13:00','2026-09-17 14:13:56','2026-09-17 14:29:41',NULL),(112,2,1,5,'expense',3233559.00,67766441.00,'velilla pablo cbu: 19008','Banco Patagonia','2026-09-17 14:21:24',9,NULL,'2026-09-17 11:14:00','2026-09-17 14:14:50','2026-09-17 14:21:24',NULL),(113,1,7,5,'expense',500000.00,78924470.74,'Patagonia -  -  - ZAMUDIO - 20206911167 - 0340053108530031062000','Banco Patagonia','2026-09-17 15:17:26',9,NULL,'2026-09-17 11:18:00','2026-09-17 14:18:54','2026-09-17 15:17:26',NULL),(114,1,7,5,'expense',500000.00,81893320.74,'Provincia -  -  - RANGO AMANDA NELLY - 27014390850 - 0140479503622804820562','Banco de la Provincia de Buenos Aires','2026-09-17 15:14:19',9,NULL,'2026-09-17 11:18:00','2026-09-17 14:19:04','2026-09-17 15:14:19',NULL),(115,1,7,5,'expense',200000.00,81893320.74,'Provincia - CA$ - 62280165719 - CHADA HAURIA ALICIA BELEN - 27251679296 - 0140479503622801657190','Banco de la Provincia de Buenos Aires','2026-09-17 15:14:04',9,NULL,'2026-09-17 11:19:00','2026-09-17 14:19:11','2026-09-17 15:14:04',NULL),(116,1,7,5,'expense',654000.00,81893320.74,'Galicia - CA$ - 407841090827 - HOLOWINIEC JAVIER - 20244508430 - 0070082530004078410971','Banco Galicia','2026-09-17 15:14:11',9,NULL,'2026-09-17 11:19:00','2026-09-17 14:19:18','2026-09-17 15:14:11',NULL),(117,1,3,5,'expense',3000000.00,84893320.74,'PASE BCO COMAFI A FRANCES','BBVA Argentina','2026-09-17 14:26:29',9,NULL,'2026-09-17 11:20:00','2026-09-17 14:21:15','2026-09-17 14:26:29',NULL),(118,1,9,5,'expense',3000000.00,81893320.74,'PASE PARA TRANSF OSCAR','BBVA Argentina',NULL,NULL,NULL,'2026-09-17 11:36:00','2026-09-17 14:36:56','2026-09-17 14:54:56','2026-09-17 14:54:56'),(119,1,6,5,'expense',3000000.00,78893320.74,'27432624396/  luna perez/ 0070303930004031071953','Banco Galicia','2026-09-17 15:21:38',9,NULL,'2026-09-17 11:37:00','2026-09-17 14:48:24','2026-09-17 15:21:38',NULL),(120,1,6,5,'expense',2745000.00,76148320.74,'mismo destino anterior, luna perez','Banco Galicia','2026-09-17 15:21:40',9,NULL,'2026-09-17 11:48:00','2026-09-17 14:49:19','2026-09-17 15:21:40',NULL),(121,3,9,5,'income',3000000.00,3000000.00,'INGRESO DEL COMAFI',NULL,NULL,NULL,NULL,'2026-09-17 11:51:00','2026-09-17 14:51:53','2026-09-17 14:51:53',NULL),(122,1,5,5,'expense',223850.00,75924470.74,'GUILLERMO BRUNNER','Banco Credicoop','2026-09-17 15:21:43',9,NULL,'2026-09-17 11:52:00','2026-09-17 14:53:29','2026-09-17 15:21:43',NULL),(123,3,6,5,'expense',3000000.00,0.00,'andreagitto.mp / CVU: 0000003100057833439785','Cuenta Virtual','2026-09-17 15:30:08',9,NULL,'2026-09-17 11:57:00','2026-09-17 15:00:52','2026-09-17 15:30:08',NULL),(124,1,1,5,'expense',37427812.44,41496658.30,'rodovia','BBVA Argentina','2026-09-17 15:27:16',9,NULL,'2026-09-17 12:13:00','2026-09-17 15:14:10','2026-09-17 15:27:16',NULL),(125,1,3,5,'expense',437400.00,41059258.30,'KEES MAURICIO CA (HORMIGON CERVINSKY)','Banco de la Provincia de Buenos Aires','2026-09-17 15:27:13',9,NULL,'2026-09-17 12:14:00','2026-09-17 15:14:42','2026-09-17 15:27:13',NULL),(126,1,5,5,'expense',8800000.00,32259258.30,'CARDO.CINTO.JUJUY','Banco Patagonia','2026-09-17 16:16:57',9,NULL,'2026-09-17 13:11:00','2026-09-17 16:12:04','2026-09-17 16:16:57',NULL),(127,2,6,5,'expense',8556000.00,59210441.00,'Gallucci e hijos , cta cte','Banco de la Nación Argentina','2026-09-17 17:05:25',9,NULL,'2026-09-17 13:36:00','2026-09-17 16:36:35','2026-09-17 17:05:25',NULL),(128,3,3,5,'income',5000000.00,20000000.00,'RUGGET SRL OP 3042954 (PERAL MARIANO)',NULL,NULL,NULL,NULL,'2026-09-17 14:03:00','2026-09-17 17:05:16','2026-09-17 17:14:51',NULL),(129,3,3,5,'income',5000000.00,20000000.00,'BRAWNY OP 79276 (PERAL MARIANO)',NULL,NULL,NULL,NULL,'2026-09-17 14:05:00','2026-09-17 17:05:39','2026-09-17 17:15:10',NULL),(130,3,3,5,'income',5000000.00,20000000.00,'BRAWNY OP 79277 (PERAL MARIANO)',NULL,NULL,NULL,NULL,'2026-09-17 14:10:00','2026-09-17 17:11:10','2026-09-17 17:15:19',NULL),(131,3,3,5,'income',5000000.00,20000000.00,'RUGGET SRL OP 3042955 (PERAL MARIANO)',NULL,NULL,NULL,NULL,'2026-09-17 14:12:00','2026-09-17 17:12:48','2026-09-17 17:14:33',NULL),(132,1,3,6,'expense',500000.00,186500000.00,'PERAL MARIANO CA','Banco de la Provincia de Buenos Aires','2026-09-18 13:01:35',9,NULL,'2026-09-18 09:28:00','2026-09-18 12:31:11','2026-09-18 13:01:35',NULL),(133,1,5,6,'expense',100000000.00,86500000.00,NULL,'Banco Comafi','2026-09-18 13:00:30',9,NULL,'2026-09-18 09:45:00','2026-09-18 12:45:50','2026-09-18 13:00:30',NULL),(134,1,3,6,'income',18139000.00,104639000.00,'DROGUERIA IB N463571756',NULL,NULL,NULL,NULL,'2026-09-18 09:53:00','2026-09-18 12:55:22','2026-09-18 12:55:22',NULL),(135,1,5,6,'expense',10000000.00,94639000.00,'OLIMPO','Banco Credicoop','2026-09-18 13:01:41',9,NULL,'2026-09-18 09:54:00','2026-09-18 12:55:56','2026-09-18 13:01:41',NULL),(136,3,3,6,'expense',1651107.21,17348892.79,'MUGNOLO MELANY MP alias melany.780.levar.mp cvu 0000003100026452605653','Cuenta Virtual','2026-09-18 13:27:51',9,NULL,'2026-09-18 10:10:00','2026-09-18 13:12:33','2026-09-18 13:27:51',NULL),(137,1,5,6,'expense',2225084.53,92413915.47,'ARTOLA','Banco de la Provincia de Buenos Aires','2026-09-18 13:23:33',9,NULL,'2026-09-18 10:07:00','2026-09-18 13:13:03','2026-09-18 13:23:33',NULL),(138,1,3,6,'expense',651500.00,91762415.47,'KEES MAURICIO CA (HORMIGON CERVINSKY)','Banco de la Provincia de Buenos Aires','2026-09-18 13:23:35',9,NULL,'2026-09-18 10:14:00','2026-09-18 13:17:20','2026-09-18 13:23:35',NULL),(139,1,5,6,'expense',1223449.92,90538965.55,'pity.cony.kary','Cuenta Virtual',NULL,NULL,NULL,'2026-09-18 10:30:00','2026-09-18 13:31:40','2026-09-18 13:36:13','2026-09-18 13:36:13'),(140,3,5,6,'expense',1223449.92,16125442.87,'pity.cony.kary','Cuenta Virtual','2026-09-18 13:38:11',9,NULL,'2026-09-18 10:36:00','2026-09-18 13:36:44','2026-09-18 13:38:11',NULL),(141,1,7,6,'expense',19000000.00,72762415.47,'Nacion - CA$ - 27104001323764 - REÑONES EMILIANO (TRANSPORTE) - 20260015735 - 0110400830040013237647','Banco de la Nación Argentina','2026-09-18 14:08:43',9,NULL,'2026-09-18 10:45:00','2026-09-18 13:45:15','2026-09-18 14:08:43',NULL),(142,1,7,6,'expense',443400.00,72319015.47,'Provincia - CA$ - 62075291526 - SEPULVEDA JUAN CRUZ - 20275376818 - 0140460303620752915268','Banco de la Provincia de Buenos Aires','2026-09-18 14:44:57',9,NULL,'2026-09-18 11:31:00','2026-09-18 14:31:55','2026-09-18 14:44:57',NULL),(143,1,7,6,'expense',450000.00,71869015.47,'ICBC -  -  - MARIANELLI MARCELO - 20211520273 - 0150539901000121213308','ICBC Argentina','2026-09-18 14:44:58',9,NULL,'2026-09-18 11:33:00','2026-09-18 14:33:39','2026-09-18 14:44:58',NULL),(144,3,3,6,'expense',5395000.00,10730442.87,'MP MARZIALI MARINA alias marina.794 cvu 0000003100016948346373','Cuenta Virtual','2026-09-18 15:29:03',9,NULL,'2026-09-18 12:19:00','2026-09-18 15:23:26','2026-09-18 15:29:03',NULL),(145,1,4,6,'expense',1791063.73,70077951.74,'maldonado guillermo c/c','BBVA Argentina','2026-09-18 15:44:30',9,NULL,'2026-09-18 12:35:00','2026-09-18 15:36:29','2026-09-18 15:44:30',NULL),(146,1,3,6,'expense',47084397.26,22993554.48,'SARDI Y CIA CC','Banco Macro','2026-09-18 16:05:07',9,NULL,'2026-09-18 12:56:00','2026-09-18 15:57:32','2026-09-18 16:05:07',NULL),(147,1,3,6,'expense',1689190.53,21304363.95,'AMBROSETTO CC','Banco Patagonia','2026-09-18 16:05:09',9,NULL,'2026-09-18 12:59:00','2026-09-18 16:00:20','2026-09-18 16:05:09',NULL),(148,2,5,6,'expense',2000000.00,80000000.00,'RAMIRO DAILOFF','Banco de la Provincia de Buenos Aires','2026-09-18 16:15:50',9,NULL,'2026-09-18 13:11:00','2026-09-18 16:12:26','2026-09-18 16:15:50',NULL),(149,2,5,7,'expense',25000000.00,74000000.00,'MARCELO DALCEGGIO','Banco de La Pampa','2026-09-21 12:21:00',9,NULL,'2026-09-21 09:11:00','2026-09-21 12:11:52','2026-09-21 12:21:00',NULL),(150,2,4,7,'expense',3283561.21,70716438.79,'valdez jorge cbu: 2876','BBVA Argentina','2026-09-21 12:21:02',9,NULL,'2026-09-21 09:11:00','2026-09-21 12:12:06','2026-09-21 12:21:02',NULL),(151,2,5,7,'income',62000.00,70778438.79,'PAOLILLO DIANA',NULL,NULL,NULL,NULL,'2026-09-21 09:12:00','2026-09-21 12:12:28','2026-09-21 12:17:09',NULL),(152,2,3,7,'expense',23600000.00,47178438.79,'LIFSITZ JUAN CC','BBVA Argentina','2026-09-21 12:33:27',9,NULL,'2026-09-21 09:27:00','2026-09-21 12:28:55','2026-09-21 12:33:27',NULL),(153,2,5,7,'expense',890000.00,46288438.79,'BERET ADRIAN RAUL','Banco de la Nación Argentina','2026-09-21 13:16:26',9,NULL,'2026-09-21 10:02:00','2026-09-21 13:03:11','2026-09-21 13:16:26',NULL),(154,2,5,7,'expense',1880000.00,44408438.79,'RAAMI.GARCIA','Banco Macro','2026-09-21 13:16:38',9,NULL,'2026-09-21 10:03:00','2026-09-21 13:04:13','2026-09-21 13:16:38',NULL),(155,3,3,7,'income',3881250.00,18881250.00,'TRANSGESTIONA SA (IRASTORZA F)',NULL,NULL,NULL,NULL,'2026-09-21 10:21:00','2026-09-21 13:22:43','2026-09-21 13:22:43',NULL),(156,1,3,7,'expense',15000000.00,3573973.53,'INEQ CC','Banco Credicoop','2026-09-21 15:34:05',9,NULL,'2026-09-21 10:23:00','2026-09-21 13:23:18','2026-09-21 15:34:05',NULL),(157,1,3,7,'expense',1000000.00,42000000.00,'DON ORESTE CC','Banco Credicoop','2026-09-21 13:36:59',9,NULL,'2026-09-21 10:31:00','2026-09-21 13:31:51','2026-09-21 13:36:59',NULL),(158,2,3,7,'expense',3797278.80,40611159.99,'METALURGICA PACINI CC','Banco de la Provincia de Buenos Aires','2026-09-21 13:55:27',9,NULL,'2026-09-21 10:46:00','2026-09-21 13:48:14','2026-09-21 13:55:27',NULL),(159,3,3,7,'income',766000.00,19647250.00,'GODOY CESAR N00423554',NULL,NULL,NULL,NULL,'2026-09-21 10:48:00','2026-09-21 13:50:19','2026-09-21 13:50:19',NULL),(160,1,7,7,'expense',4070000.00,37930000.00,'Credicoop - CC$ - 1350065419 - CID GONZALO RODOLFO - 20348085493 - 1910135655013500654196','Banco Credicoop','2026-09-21 14:02:13',9,NULL,'2026-09-21 10:56:00','2026-09-21 13:57:17','2026-09-21 14:02:13',NULL),(161,1,6,7,'expense',20000000.00,12930000.00,'SIDEPA/  30707463232/   19101301-55013000235682','Banco Credicoop','2026-09-21 14:40:22',9,NULL,'2026-09-21 11:00:00','2026-09-21 14:01:03','2026-09-21 14:40:22',NULL),(162,2,3,7,'expense',4104000.00,36507159.99,'LUCHO FERREYRA CA','Banco Santander Argentina','2026-09-21 14:04:36',9,NULL,'2026-09-21 11:01:00','2026-09-21 14:02:51','2026-09-21 14:04:36',NULL),(163,1,7,7,'expense',5000000.00,12930000.00,'Santander - CA$ - 1350563529 - CIUCCI CARLOS RODRIGO - 20351019582 - 0720135288000005635290','Banco Santander Argentina','2026-09-21 14:18:56',9,NULL,'2026-09-21 11:15:00','2026-09-21 14:16:10','2026-09-21 14:18:56',NULL),(164,1,7,7,'expense',4200000.00,1980000.00,'Nacion - CA$ - 26703930988271 - LENCINA RODOLFO RODRIGO JULIAN - 20363224025 - 0110393730039309882715','Banco Comafi','2026-09-21 15:07:37',9,NULL,'2026-09-21 11:33:00','2026-09-21 14:33:39','2026-09-21 15:07:37',NULL),(165,3,7,7,'reserve',16000000.00,3647250.00,NULL,NULL,NULL,NULL,NULL,'2026-09-21 11:34:00','2026-09-21 14:34:44','2026-09-21 15:24:38','2026-09-21 15:24:38'),(166,2,5,7,'reserve',36000000.00,507159.99,'ADMIRAL',NULL,NULL,NULL,NULL,'2026-09-21 11:36:00','2026-09-21 14:36:38','2026-09-21 14:38:26','2026-09-21 14:38:26'),(167,2,5,7,'expense',36000000.00,507159.99,'b m inspecciones srl','Banco Credicoop','2026-09-21 14:42:24',9,NULL,'2026-09-21 11:38:00','2026-09-21 14:39:41','2026-09-21 14:42:24',NULL),(168,1,4,7,'expense',1406026.47,3573973.53,'tisani cbu: 230013','Banco de la Provincia de Neuquen','2026-09-21 15:26:54',9,NULL,'2026-09-21 11:39:00','2026-09-21 14:40:10','2026-09-21 15:26:54',NULL),(169,3,4,7,'expense',3669122.53,12937944.05,'Vasco1729.mp','Cuenta Virtual','2026-09-21 16:04:12',9,NULL,'2026-09-21 11:42:00','2026-09-21 14:42:45','2026-09-21 16:04:12',NULL),(170,3,3,7,'expense',3040183.42,607066.58,'PEÑA ETCHEGOYEN CC','Banco de la Nación Argentina','2026-09-21 14:48:16',9,NULL,'2026-09-21 11:43:00','2026-09-21 14:45:33','2026-09-21 14:48:16',NULL),(171,1,7,7,'expense',100000.00,3473973.53,'Provincia - CC$ - 62290526951 - PALAZZOLO FERNANDO - 20300622659 - 0140305101622905269514','Banco de la Provincia de Buenos Aires','2026-09-21 15:45:20',9,NULL,'2026-09-21 12:33:00','2026-09-21 15:33:46','2026-09-21 15:45:20',NULL),(172,4,3,7,'income',500000.00,600000.00,'ALEGRE ANTO',NULL,NULL,NULL,NULL,'2026-09-21 13:08:00','2026-09-21 16:09:09','2026-09-21 16:09:09',NULL),(173,3,4,7,'expense',150000.00,12787944.05,'branbon cbu:695404','Banco de la Nación Argentina','2026-09-21 17:09:09',9,NULL,'2026-09-21 13:25:00','2026-09-21 16:25:33','2026-09-21 17:09:09',NULL),(174,1,3,7,'expense',1000000.00,6223973.53,'WHATSAPP CHAVEZ OSCAR (CTA SIMONE)','Banco Credicoop','2026-09-21 16:34:52',8,NULL,'2026-09-21 13:26:00','2026-09-21 16:30:25','2026-09-21 16:34:52',NULL),(175,1,5,7,'reserve',1500000.00,4723973.53,'AGUSTIN',NULL,NULL,NULL,NULL,'2026-09-21 13:42:00','2026-09-21 16:42:41','2026-09-21 17:25:39','2026-09-21 17:25:39'),(176,3,4,7,'expense',8534817.00,4253127.05,'arquiframme cbu: 9381','Banco Macro','2026-09-21 17:09:12',9,NULL,'2026-09-21 14:03:00','2026-09-21 17:04:12','2026-09-21 17:09:12',NULL),(177,1,4,7,'expense',1218560.00,3505413.53,'guimaraez joan','Banco de la Nación Argentina','2026-09-21 17:25:02',9,NULL,'2026-09-21 14:18:00','2026-09-21 17:19:28','2026-09-21 17:25:02',NULL),(178,2,8,7,'expense',100000.00,407159.99,'LARRIBITE LEANDRO','Banco de la Provincia de Buenos Aires','2026-09-21 17:38:22',8,NULL,'2026-09-21 14:31:00','2026-09-21 17:32:14','2026-09-21 17:38:22',NULL),(179,1,5,7,'expense',1079158.56,3926254.97,'ADMIRAL PAMPA','Banco de La Pampa','2026-09-21 17:55:47',9,NULL,'2026-09-21 14:49:00','2026-09-21 17:49:47','2026-09-21 17:55:47',NULL),(180,1,6,7,'expense',200000.00,3726254.97,'cafu/ 0140305101622906961778','Banco de la Provincia de Buenos Aires','2026-09-21 18:10:08',9,NULL,'2026-09-21 15:03:00','2026-09-21 18:05:09','2026-09-21 18:10:08',NULL),(181,1,4,8,'expense',6009658.30,176990341.70,'transporte somos arg','Banco de la Nación Argentina','2026-09-22 12:48:27',9,NULL,'2026-09-22 09:26:00','2026-09-22 12:27:41','2026-09-22 12:48:27',NULL),(182,1,6,8,'expense',20001853.64,156988488.06,'SIDEPA/  30707463232/   19101301-55013000235682','Banco Credicoop','2026-09-22 12:48:30',9,NULL,'2026-09-22 09:27:00','2026-09-22 12:27:57','2026-09-22 12:48:30',NULL),(183,2,5,8,'expense',2000000.00,118000000.00,'FURLONG','Banco de la Nación Argentina','2026-09-22 12:50:35',9,NULL,'2026-09-22 09:26:00','2026-09-22 12:27:59','2026-09-22 12:50:35',NULL),(184,1,3,8,'expense',2959428.36,154029059.70,'WHATSAPP COLOMBIL MARIA CA (ETCHEGOYEN)','Banco de la Nación Argentina','2026-09-22 12:48:33',9,NULL,'2026-09-22 09:27:00','2026-09-22 12:30:09','2026-09-22 12:48:33',NULL),(185,1,3,8,'expense',2407095.21,151621964.49,'DUARTE JORGE CC','Banco Patagonia','2026-09-22 12:48:35',9,NULL,'2026-09-22 09:30:00','2026-09-22 12:31:32','2026-09-22 12:48:35',NULL),(186,1,7,8,'expense',263900.00,151358064.49,'Provincia - CC$ - 62290526951 - PALAZZOLO FERNANDO - 20300622659 - 0140305101622905269514','Banco de la Provincia de Buenos Aires','2026-09-22 12:48:37',9,NULL,'2026-09-22 09:45:00','2026-09-22 12:46:27','2026-09-22 12:48:37',NULL),(187,1,4,8,'expense',1767528.65,149590535.84,'mauri patricia','Banco de la Provincia de Buenos Aires','2026-09-22 12:57:26',9,NULL,'2026-09-22 09:51:00','2026-09-22 12:51:52','2026-09-22 12:57:26',NULL),(188,3,1,8,'income',2446300.00,5446300.00,'FINAR',NULL,NULL,NULL,NULL,'2026-09-22 09:55:00','2026-09-22 12:56:09','2026-09-22 12:56:20',NULL),(189,2,7,8,'reserve',30000000.00,56810000.00,'PROARCO',NULL,NULL,NULL,NULL,'2026-09-22 09:58:00','2026-09-22 12:58:22','2026-09-22 15:14:29','2026-09-22 15:14:29'),(190,1,6,8,'expense',5012000.00,6339119.87,'cafu/ 0140305101622906961778','Banco de la Provincia de Buenos Aires','2026-09-22 15:56:19',9,NULL,'2026-09-22 10:15:00','2026-09-22 13:16:07','2026-09-22 15:56:19',NULL),(191,1,1,8,'expense',27038317.00,115263904.63,'TTES FIGUEROA','Banco de Santa Cruz','2026-09-22 14:25:19',9,NULL,'2026-09-22 10:17:00','2026-09-22 13:18:04','2026-09-22 14:25:19',NULL),(192,1,3,8,'expense',2276314.21,112302221.63,'INEQ CC','Banco Credicoop','2026-09-22 13:21:29',9,NULL,'2026-09-22 10:18:00','2026-09-22 13:18:58','2026-09-22 13:21:29',NULL),(193,2,7,8,'expense',7000000.00,58206275.78,'Credicoop - CC$ - 1260082889 - RISSONE GUILLERMO PEDRO - 20173605588 - 1910126455012600828896','Banco Credicoop','2026-09-22 15:56:22',9,NULL,'2026-09-22 10:27:00','2026-09-22 13:27:54','2026-09-22 15:56:22',NULL),(194,2,7,8,'expense',6190000.00,41810000.00,'Santander - CC$ - 10810120917 - LEONHARDT ALAN - 20357981353 - 0720108688000001209172','Banco Santander Argentina','2026-09-22 14:25:07',9,NULL,'2026-09-22 10:27:00','2026-09-22 13:28:09','2026-09-22 14:25:07',NULL),(195,2,7,8,'expense',15000000.00,40000000.00,'Nacion - CC$ - 11301300121664 - PROARCO PATAGONIA SA - 30709534994 - 0110130620013001216648','Banco de la Nación Argentina','2026-09-22 13:47:16',9,NULL,'2026-09-22 10:41:00','2026-09-22 13:41:54','2026-09-22 13:47:16',NULL),(196,2,7,8,'reserve',15000000.00,41810000.00,'JAVIER',NULL,NULL,NULL,NULL,'2026-09-22 11:09:00','2026-09-22 14:09:41','2026-09-22 14:46:03','2026-09-22 14:46:03'),(197,2,7,8,'expense',7000000.00,35690000.00,'Santander - CA$ - 3893569944 - CEBALLES FEDERICO ADRIAN - 20333694361 - 0720389188000035699440','Banco Santander Argentina','2026-09-22 15:06:39',9,NULL,'2026-09-22 11:12:00','2026-09-22 14:12:26','2026-09-22 15:06:39',NULL),(198,1,1,8,'expense',6000000.00,109263904.63,'FERREYRA LUCIANO','Banco Santander Argentina','2026-09-22 14:25:26',9,NULL,'2026-09-22 11:13:00','2026-09-22 14:13:26','2026-09-22 14:25:26',NULL),(199,1,1,8,'expense',6000000.00,103263904.63,'FERREYRA LUCIANO','BBVA Argentina','2026-09-22 14:25:28',9,NULL,'2026-09-22 11:13:00','2026-09-22 14:13:46','2026-09-22 14:25:28',NULL),(200,1,1,8,'expense',2000000.00,101263904.63,'FERREYRA LUCIANO','ICBC Argentina',NULL,NULL,NULL,'2026-09-22 11:13:00','2026-09-22 14:14:04','2026-09-22 14:15:11','2026-09-22 14:15:11'),(201,1,3,8,'expense',4047108.48,99216796.15,'VALDEZ JORGE','BBVA Argentina','2026-09-22 14:25:30',9,NULL,'2026-09-22 11:19:00','2026-09-22 14:19:38','2026-09-22 14:25:30',NULL),(202,1,1,8,'expense',28365025.00,70851771.15,'TALLER VALENCIA','Banco Credicoop','2026-09-22 14:39:56',9,NULL,'2026-09-22 11:36:00','2026-09-22 14:36:37','2026-09-22 14:39:56',NULL),(203,1,4,8,'expense',1308774.81,69542996.34,'saavedra matias cbu:0140336503685151611696','Banco de la Provincia de Buenos Aires','2026-09-22 14:48:08',9,NULL,'2026-09-22 11:39:00','2026-09-22 14:40:29','2026-09-22 14:48:08',NULL),(204,2,7,8,'expense',870000.00,33940000.00,'Provincia -  -  - INGENIERIA BAHIA SRL - 30710411820 - 0140479501622805048231','Banco de la Provincia de Buenos Aires','2026-09-22 14:47:56',9,NULL,'2026-09-22 11:41:00','2026-09-22 14:42:09','2026-09-22 14:47:56',NULL),(205,2,7,8,'expense',1250000.00,32690000.00,'Macro -  -  - FERNANDEZ MARIA ROSA - 23230075794 - 2850593030094158690351','Banco Macro','2026-09-22 14:51:54',9,NULL,'2026-09-22 11:43:00','2026-09-22 14:44:13','2026-09-22 14:51:54',NULL),(206,1,7,8,'expense',1500000.00,68042996.34,'Galicia - CA$ - 407841090827 - HOLOWINIEC JAVIER - 20244508430 - 0070082530004078410971','Banco Galicia','2026-09-22 14:48:12',9,NULL,'2026-09-22 11:44:00','2026-09-22 14:45:13','2026-09-22 14:48:12',NULL),(207,2,7,8,'reserve',12000000.00,20690000.00,'JAVIER',NULL,NULL,NULL,NULL,'2026-09-22 11:45:00','2026-09-22 14:45:57','2026-09-22 15:53:31','2026-09-22 15:53:31'),(208,1,3,8,'expense',6006012.16,62036984.18,'MARZIALI MARINA CC','Banco de la Nación Argentina','2026-09-22 15:05:00',9,NULL,'2026-09-22 11:55:00','2026-09-22 14:56:49','2026-09-22 15:05:00',NULL),(209,1,6,8,'expense',9500000.00,52536984.18,'sstandinger dario/ cuit 20337172882/ 0110299630029911993615','Banco de la Nación Argentina','2026-09-22 15:21:22',9,NULL,'2026-09-22 12:11:00','2026-09-22 15:12:01','2026-09-22 15:21:22',NULL),(210,2,7,8,'expense',10000000.00,25690000.00,'Bibank - CA$ - 0000002000000081886 - RIOS PABLO - 20235342139 - 1470000810000000818862','Cuenta Virtual','2026-09-22 15:21:24',9,NULL,'2026-09-22 12:12:00','2026-09-22 15:13:30','2026-09-22 15:21:24',NULL),(211,2,7,8,'reserve',20000000.00,35690000.00,'PROARCO',NULL,NULL,NULL,NULL,'2026-09-22 12:14:00','2026-09-22 15:14:45','2026-09-22 15:14:45',NULL),(212,1,5,8,'reserve',52500000.00,36984.18,'RODOVIA',NULL,NULL,NULL,NULL,'2026-09-22 12:20:00','2026-09-22 15:20:39','2026-09-22 15:41:49','2026-09-22 15:41:49'),(213,2,3,8,'expense',4169198.14,31520801.86,'FERREYRA LUCIANO','ICBC Argentina','2026-09-22 15:32:46',9,NULL,'2026-09-22 12:24:00','2026-09-22 15:25:18','2026-09-22 15:32:46',NULL),(214,2,5,8,'reserve',3044546.01,28476255.85,'ECHECHOYEN',NULL,NULL,NULL,NULL,'2026-09-22 12:24:00','2026-09-22 15:25:20','2026-09-22 16:47:00','2026-09-22 16:47:00'),(215,2,3,8,'expense',8412793.22,20063462.63,'DIGITAL IMPORT CC','Banco Galicia','2026-09-22 15:32:53',9,NULL,'2026-09-22 12:25:00','2026-09-22 15:26:34','2026-09-22 15:32:53',NULL),(216,4,1,8,'income',2760000.00,2860000.00,'LA ALDEA CULTIVOS',NULL,NULL,NULL,NULL,'2026-09-22 12:27:00','2026-09-22 15:27:30','2026-09-22 15:27:30',NULL),(217,2,1,8,'income',39000000.00,59063462.63,'GRISKAN',NULL,NULL,NULL,NULL,'2026-09-22 12:27:00','2026-09-22 15:27:46','2026-09-22 15:27:46',NULL),(218,2,4,8,'expense',3857186.85,55206275.78,'trans lapiru','Banco Supervielle','2026-09-22 15:53:54',9,NULL,'2026-09-22 12:41:00','2026-09-22 15:42:23','2026-09-22 15:53:54',NULL),(219,1,4,8,'expense',46197864.31,6339119.87,'rodovia','BBVA Argentina','2026-09-22 15:53:57',9,NULL,'2026-09-22 12:42:00','2026-09-22 15:42:39','2026-09-22 15:53:57',NULL),(220,2,4,8,'expense',7318444.36,29083889.60,'iron med','BBVA Argentina','2026-09-22 17:25:14',9,NULL,'2026-09-22 13:02:00','2026-09-22 16:02:23','2026-09-22 17:25:14',NULL),(221,3,1,8,'income',3000000.00,8446300.00,'CRISCI',NULL,NULL,NULL,NULL,'2026-09-22 13:24:00','2026-09-22 16:25:47','2026-09-22 16:25:47',NULL),(222,2,1,8,'expense',9400000.00,53506275.78,'RIOS JAVIER BAHIA BLANCA','Banco Patagonia','2026-09-22 16:50:44',9,NULL,'2026-09-22 13:40:00','2026-09-22 16:41:11','2026-09-22 16:50:44',NULL),(223,2,5,8,'expense',3044546.01,53506275.78,'echegoyen','Banco de la Provincia de Buenos Aires','2026-09-22 16:50:46',9,NULL,'2026-09-22 13:47:00','2026-09-22 16:47:47','2026-09-22 16:50:46',NULL),(224,2,7,8,'expense',8700000.00,29083889.60,'Provincia - CC$ - 62020564041 - CID GONZALO RODOLFO - 20348085493 - 0140416001620205640413','Banco de la Provincia de Buenos Aires','2026-09-22 17:27:13',9,NULL,'2026-09-22 14:00:00','2026-09-22 17:00:26','2026-09-22 17:27:13',NULL),(225,2,3,8,'expense',8648667.82,34857607.96,'LOG Y TTE PACINI CC','Banco de la Provincia de Buenos Aires','2026-09-22 17:23:42',9,NULL,'2026-09-22 14:06:00','2026-09-22 17:10:37','2026-09-22 17:23:42',NULL),(226,2,4,8,'expense',2755274.00,32102333.96,'velilla pablo','Banco Patagonia','2026-09-22 17:23:43',9,NULL,'2026-09-22 14:08:00','2026-09-22 17:10:41','2026-09-22 17:23:43',NULL),(227,2,7,8,'expense',4300000.00,29083889.60,'Credicoop - CC$ - 1350065419 - CID GONZALO RODOLFO - 20348085493 - 1910135655013500654196','Banco Credicoop','2026-09-22 17:27:15',9,NULL,'2026-09-22 14:10:00','2026-09-22 17:10:55','2026-09-22 17:27:15',NULL),(228,2,6,8,'expense',4881800.00,24202089.60,'20329789919, tello/0140416003620253185997','Banco de la Provincia de Buenos Aires','2026-09-22 18:02:39',9,NULL,'2026-09-22 14:53:00','2026-09-22 18:00:26','2026-09-22 18:02:39',NULL);
/*!40000 ALTER TABLE `transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'operator',
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_admin` tinyint(1) NOT NULL DEFAULT '0',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_username_unique` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Ivan Torio','ivantorio','operator','ivantorio@local',NULL,'$2y$12$0U6XMTuy4Qb0eQ9xDcQPfOCy6ZmkcH0CILKkAS84doLygErYhGeqq',1,'3Whpf4HWsOrjwONbcaogAg2OCGQzoGI94sRxGXZzlhwskJvMtwVvaiKoVkhW','2026-09-11 13:59:03','2026-09-11 17:11:55',NULL),(2,'charly','charly','operator','charly@local',NULL,'$2y$12$5e5kTnbWAjZMUqA/aEXAzeklj3eStiwiyg59TpbHo5IymdPEVsgg2',0,'2QRAHAE5vZwkwhOqLi5mjtsswY53iDY1PGBHsIG8ZHcD5Q9Q0NGKYvw6M5iy','2026-09-12 02:04:30','2026-09-12 02:04:30',NULL),(3,'Jimena14','Jimena14','operator','Jimena14@local',NULL,'$2y$12$Zls/AR79IVF39eKHUon3huoripFKAa9BQrISIi6FAMJn1NelFtOYe',0,'bkm5mHFuAY6FZM1k4rSh1L781OeOAZsTpmNNJf6RVBHdaExAbiMTM0Y4dock','2026-09-14 12:53:29','2026-09-14 12:53:29',NULL),(4,'Agustin15','Agustin15','operator','Agustin15@local',NULL,'$2y$12$SFHfcHa8bQjSRY9wD/v1NOOuudYR0iHNDSn80cXPyBZKRtIqZ7d7S',0,'1gFVCWCrngj46AVFxSvdeNs8s1HF3WZOnFGuoaPwEEalW1bC1qGtYO0kXujR','2026-09-14 12:59:32','2026-09-14 12:59:32',NULL),(5,'Melisa12','Melisa12','operator','Melisa12@local',NULL,'$2y$12$Z8sZTWX4O1almu5uT9F0/u.cBMYCwd9nICob/QYybems88qrr6TiK',1,'S382vfce4ABkQE9p8BGHJUQib4lSF14Wy9ucwg9sfIwHZp8J61ndLNdwyBKJ','2026-09-14 13:00:11','2026-09-15 16:50:41',NULL),(6,'Oscar18','Oscar18','operator','Oscar18@local',NULL,'$2y$12$Ri8ev87kr5I/Rx8KLKzB9uyH3vSmhPnOqyrVUHoTwT3E6d7Bx5X92',1,NULL,'2026-09-14 13:43:05','2026-09-22 17:48:58',NULL),(7,'Gerardo19','Gerardo19','operator','Gerardo19@local',NULL,'$2y$12$GRqkN/VKVpu/8.PAvLwytunSs0V5E4djgeD0RpKUfwGRvmmLdndKW',0,'bhRHYGIJ6pIzfqfZ8TWDYUkzfrzsXHnEsyujzgv5BfqkDdW7J5hvCG6vV7DW','2026-09-14 13:43:30','2026-09-14 13:43:30',NULL),(8,'Jesi29','Jesi29','administration','Jesi29@local',NULL,'$2y$12$U/ivMl4B376hZvyc9OHr4.RnZXFGBTm53kNFWU0QQ3j4eRF4AXZ1C',0,NULL,'2026-09-14 15:21:44','2026-09-15 12:43:33',NULL),(9,'Yeye28','Yeye28','administration','Yeye28@local',NULL,'$2y$12$kfvk/VKb38kZso3SzQLkfu/SinMYuIh70Yky/T02VPTXpnKGSWkE6',0,'gxUqD1OJbuUgLE89vY9od6qY3nPo0VDtMzwiZYOr2EMFeJLJufBhvJO5gaTp','2026-09-15 11:53:04','2026-09-15 12:43:26',NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'prestar_db'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-09-22 22:53:39

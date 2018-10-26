-- MySQL dump 10.13  Distrib 5.7.23, for Linux (x86_64)
--
-- Host: localhost    Database: ListiWiki
-- ------------------------------------------------------
-- Server version	5.7.23

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
-- Table structure for table `Category`
--

DROP TABLE IF EXISTS `Category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Category` (
  `id` varchar(255) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `data` text,
  `lastUpdate` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `lastAuthor` varchar(255) NOT NULL DEFAULT 'ghost',
  `count` int(11) DEFAULT NULL,
  `equalTo` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`),
  KEY `title` (`title`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Category`
--

LOCK TABLES `Category` WRITE;
/*!40000 ALTER TABLE `Category` DISABLE KEYS */;
INSERT INTO `Category` VALUES ('SW.1','Cellular processes',NULL,'2018-08-24 12:42:35','ghost',10,NULL),('SW.1.1','Cell envelope and cell division',NULL,'2018-08-24 12:42:36','ghost',2,NULL),('SW.1.1.1','Cell wall synthesis','{\"important original publication\":[\"<pubmed>21999535,21636744,21636745,22343529,23600697,29203279<\\/pubmed>\"],\"important reviews\":[\"<pubmed>20060721,16689786,16101993,21388439,21255102,23551458,23848140,23949602,24035761,24024634,24405365,24819367,24024634,25427009,26029191,28975672,29355854,29560261<\\/pubmed>\"]}','2018-08-24 12:42:36','Jstuelk',9,NULL),('SW.1.1.1.1','Biosynthesis of peptidoglycan','{\"important reviews\":[\"<pubmed>28975672<\\/pubmed>\"]}','2018-08-24 12:42:37','Jstuelk',4,'SW.2.6.1.1'),('SW.1.1.1.2','Autolytic activity required for peptidoglycan synthesis (cell elongation)',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.1.1.1.3','Biosynthesis of lipoteichoic acid',NULL,'2018-07-10 07:25:26','ghost',1,'SW.2.6.1.2'),('SW.1.1.1.4','Biosynthesis of teichoic acid','{\"description\":\"\"}','2018-06-18 08:07:43','Bzhu',0,'SW.2.6.1.3'),('SW.1.1.1.5','Biosynthesis of teichuronic acid',NULL,'2018-06-18 08:07:43','ghost',0,'SW.2.6.1.4'),('SW.1.1.1.6','Export of anionic polymers and attachment to peptidoglycan',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.1.1.1.7','Penicillin-binding proteins',NULL,'2018-08-08 07:31:24','ghost',1,NULL),('SW.1.1.1.8','Biosynthesis of the carrier lipid undecaprenylphosphate',NULL,'2018-07-12 11:55:29','ghost',1,'SW.2.6.1.5'),('SW.1.1.2','Cell shape','{\"important Original Publications\":[\"<pubmed>21636744,21636745,22343529,25544609,28602657,29203279<\\/pubmed>\"],\"important Reviews\":[\"<pubmed>12471245,12914007,16101993,17981078,15661522,20825347,21047262,22166997,22014508,22092065,22652894,23848140,21371139,26106381,25957405,28500523,29560261,29355854,29522747<\\/pubmed>\"]}','2018-07-10 07:25:30','Jstuelk',1,NULL),('SW.1.1.3','Cell wall degradation/ turnover','{\"important Original Publications\":[\"<pubmed>23600697, 23746506<\\/pubmed>\"],\"important Reviews\":[\"<pubmed> 19019149,18792692,10708363,17468031,18266855,21796380,24035761,22944244 <\\/pubmed>\"]}','2018-06-18 08:07:43','ghost',0,NULL),('SW.1.1.3.1','Autolysis',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.1.1.3.2','Autolysis/ based on similarity',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.1.1.3.3','Utilization of cell wall components',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.1.1.3.4','Endopeptidases',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.1.1.3.5','N-acetyl-β-D-glucosaminidases','{\"description\":\"\"}','2018-06-18 08:07:43','Bzhu',0,NULL),('SW.1.1.3.6','Cell wall degradation/ turnover/ Additional genes','[]','2018-06-18 08:07:43','Jstuelk',0,NULL),('SW.1.1.4','Capsule biosynthesis and degradation','{\"important original publications\":[\"<pubmed> 16091050 <\\/pubmed>\"],\"key reviews\":[\"<pubmed> 16689787,21377358<\\/pubmed>\"]}','2018-06-18 08:07:43','ghost',0,NULL),('SW.1.1.5','Cell wall/ other',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.1.1.6','Cell wall/ other/ based on similarity',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.1.1.7','Membrane dynamics','{\"Original publications\":[\"<pubmed>28542493,29504925<\\/pubmed>\"],\"key reviews\":[\"<pubmed>23429192,24819366,25652542,26124753,28697671<\\/pubmed>\"]}','2018-06-18 08:07:43','Jstuelk',0,NULL),('SW.1.1.8','Cell division','{\"Important Original Publications\":[\"<pubmed> 25176632,25544609<\\/pubmed>\"],\"Important Reviews\":[\"<pubmed>12471245,12626683,16005287,20182599,19884039,18396093,17326815,16005287,15922599,21047262,22575476,23190137,23848140,23949602,24550892,26029202,25427009,26029191,25957405,26706151,28500523,28697666,29355854,29522747<\\/pubmed>\"]}','2018-06-18 08:07:43','Jstuelk',0,NULL),('SW.1.1.8.1','The Min system','','2018-06-18 08:07:43','Bzhu',0,NULL),('SW.1.1.8.2','Cell division / Other genes','{}','2018-08-22 10:20:26','Bzhu',NULL,NULL),('SW.1.1.9','Cell division/ based on similarity',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.1.2','Transporters',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.1.2.1','ABC transporters','{\"relevant publications\":[\"<pubmed> 10092453,1282354,18535149,20497229,21665979,23106164<\\/pubmed>\"]}','2018-06-18 08:07:43','ghost',0,NULL),('SW.1.2.1.1','Importers',NULL,'2018-08-22 12:43:45','ghost',3,NULL),('SW.1.2.1.1.1','Uptake of carbon sources',NULL,'2018-08-22 12:43:07','ghost',2,NULL),('SW.1.2.1.1.2','Uptake of amino acids',NULL,'2018-08-08 13:48:03','ghost',1,NULL),('SW.1.2.1.1.3','Uptake of peptides',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.1.2.1.1.4','Uptake of compatible solutes for osmoprotection',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.1.2.1.1.5','Uptake of iron/ siderophores',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.1.2.1.1.6','Uptake of ions',NULL,'2018-08-08 13:47:58','ghost',3,NULL),('SW.1.2.1.1.8','ABC transporters of unknown function',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.1.2.1.2','Exporters',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.1.2.1.2.1','Efflux of antibiotics',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.1.2.1.2.2','Export of antibiotic substances',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.1.2.1.2.3','Export of peptides','[]','2018-06-18 08:07:43','Jstuelk',0,NULL),('SW.1.2.1.2.4','Export of cell wall components',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.1.2.1.2.5','Export of ions',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.1.2.1.2.6','Exporters of unknown function',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.1.2.1.3','Regulatory ABC transporters','[]','2018-06-18 08:07:43','Jstuelk',0,NULL),('SW.1.2.2','Phosphotransferase system','{\"relevant Reviews\":[\"<pubmed>17158705,9871918,9663674,10627040,11532441,16339738,24847021 <\\/pubmed>\"]}','2018-06-18 08:07:43','Jstuelk',0,NULL),('SW.1.2.2.1','General PTS proteins',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.1.2.2.2','Sugar specific PTS proteins',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.1.2.3','ECF transporter','{\"important reviews and publications\":[\"<pubmed>23584589 , 21135102, 20497229, 18931129,22574898,24362466,24156876 <\\/pubmed>\"]}','2018-06-18 08:07:43','Jstuelk',0,NULL),('SW.1.2.3.1','The general components of the ECF transporters',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.1.2.3.2','The substrate-specific S components of the ECF transporters',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.1.2.3.3','Class I ECF transporter','[]','2018-06-04 10:32:24','Jstuelk',0,NULL),('SW.1.2.4','Transporters/ other','{\"relevant publications\":[\"<pubmed> 20497229,11763970,15247498,10839820,12787345,10943556,9529885,24225317 <\\/pubmed>\"]}','2018-06-18 08:07:43','ghost',0,NULL),('SW.1.2.4.1','Amino acid transporters',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.1.2.4.1.1','Solute:sodium symporter family',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.1.2.4.1.2','Other amino acid transporters',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.1.2.4.10','Siderophore exporters',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.1.2.4.11','Other exporters',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.1.2.4.12','Other transporters',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.1.2.4.13','Metal ion exporters',NULL,'2018-06-18 08:07:43','Bzhu',0,NULL),('SW.1.2.4.14','Multidrug exporters',NULL,'2018-06-18 08:07:43','Bzhu',0,NULL),('SW.1.2.4.2','Peptide transporter',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.1.2.4.3','Carbohydrate transporter',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.1.2.4.4','Transporter for organic acids',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.1.2.4.5','Metal ion transporter',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.1.2.4.6','Nucleotide/ nucleoside transporter',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.1.2.4.7','Transporter for cofactors',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.1.2.4.8','Uptake of other small ions',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.1.2.4.9','Uptake of compatible solutes',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.1.3','Homeostasis',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.1.3.1','Metal ion homeostasis (K, Na, Ca, Mg)','{\"Reviews and important original publications\":[\"<pubmed> 15802251, 24415722     28344348<\\/pubmed>\"]}','2018-06-18 08:07:43','Jstuelk',0,NULL),('SW.1.3.1.1','Magnesium uptake/ efflux',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.1.3.1.2','Sodium uptake/ export','[]','2018-06-18 08:07:43','Jstuelk',0,NULL),('SW.1.3.1.3','Potassium uptake/ export','[]','2018-06-04 10:32:29','Jstuelk',0,NULL),('SW.1.3.1.4','Metal ion homeostasis/ Other','[]','2018-06-04 10:32:31','Jstuelk',0,NULL),('SW.1.3.2','Trace metal homeostasis (Cu, Zn, Ni, Mn, Mo)','{\"reviews\":[\"<pubmed> 15802251, 11831459, 25213645, 25160631     28344348<\\/pubmed>\"]}','2018-06-18 08:07:43','Jstuelk',0,NULL),('SW.1.3.2.1','Copper',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.1.3.2.2','Manganese',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.1.3.2.3','Zinc',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.1.3.2.4','Trace metals/ Other','[]','2018-06-18 08:07:43','Jstuelk',0,NULL),('SW.1.3.3','Acquisition of iron','{\"reviews\":[\"<pubmed> 15802251 ,20466767, 23192658 ,25160631,12829269<\\/pubmed>\"]}','2018-06-18 08:07:43','Jstuelk',0,NULL),('SW.1.3.3.1','ABC transporters for the uptake of iron/ siderophores',NULL,'2018-06-18 08:07:43','ghost',0,'SW.2.6.5.4'),('SW.1.3.3.2','Elemental iron transport system',NULL,'2018-06-18 08:07:43','ghost',0,'SW.2.6.5.5'),('SW.1.3.3.3','Acquisition of iron / Other','[]','2018-06-18 08:07:43','Bzhu',0,'SW.2.6.5.1'),('SW.1.3.4','Acquisition of iron/ based on similarity',NULL,'2018-06-18 08:07:43','ghost',0,'SW.2.6.5.2'),('SW.1.3.5','PH homeostasis','{\"important original publications\":[\"<pubmed> 22427503 <\\/pubmed>\"],\"key reviews\":[\"<pubmed> 7823040, 6277371, 21464825 <\\/pubmed>\"]}','2018-06-18 08:07:43','ghost',0,NULL),('SW.2','Metabolism','{\"models of metabolism\":[\"<pubmed>19555510,17573341,18302748,21219666,24204596,24123514<\\/pubmed>\"],\"important original publications\":[\"<pubmed>19917605,21531833,22383848,21266987,21998563,24281055,24584250,24727859<\\/pubmed>\"],\"minimal genome projects\":[\"<pubmed>19943949<\\/pubmed>\"],\"reviews\":[\"<pubmed>11018147,17982469,19202299,17919287<\\/pubmed>\"],\"relevant papers on other organisms\":[\"<pubmed>19561621,19690571,22538926<\\/pubmed>\"],\"additional publications\":[\"<PubMed>19762644<\\/pubmed>\"]}','2018-08-22 10:17:17','ghost',2,NULL),('SW.2.1','Electron transport and ATP synthesis','{\"important original publications\":[\"<pubmed> 21255555, 22790590, 23880299<\\/pubmed>\"],\"key reviews\":[\"<pubmed>9891797, 9418235, 23046954<\\/pubmed>\"]}','2018-06-18 08:07:43','ghost',0,NULL),('SW.2.1.1','Regulators of electron transport',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.1.2','Respiration','{\"References\":{\"Important original publications\":[\"<pubmed> 21255555 22790590 23880299<\\/pubmed>\"],\"Key reviews\":[\"<pubmed>9891797 9418235 23046954<\\/pubmed>\"]}}','2018-06-18 08:07:43','Bzhu',0,NULL),('SW.2.1.2.1','Terminal oxidases','null','2018-08-22 09:54:21','Bzhu',1,NULL),('SW.2.1.2.2','Anaerobic respiration',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.1.2.3','Anaerobic respiration/based on similarity',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.1.2.4','Respiration/ other',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.1.3','Electron transport/ other',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.1.4','Electron transport/ other/ based on similarity',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.1.5','ATP synthesis','{\"important reviews\":[\"<pubmed> 19489730,10838052,10600673 <\\/pubmed>\"],\"additional reviews\":[\"<pubmed>20972431,20871600<\\/pubmed>\"]}','2018-06-18 08:07:43','ghost',0,NULL),('SW.2.1.5.1','ATPase','{\"related pages\":[\"[http:\\/\\/pdb101.rcsb.org\\/motm\\/72 Discussion of the strucure of ATPase]\"]}','2018-06-18 08:07:43','Bzhu',0,NULL),('SW.2.1.5.2','Substrate-level phosphorylation',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.2','Carbon metabolism',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.2.1','Carbon core metabolism','{\"important original publications\":[\"<pubmed>20933603, 19193632 ,12850135,11489127,19917605,18586936,9131624 ,18302748,24727859 <\\/pubmed>\"],\"reviews\":[\"<pubmed> 11018147,19202299,17982469,16102602,22545791<\\/pubmed>\"]}','2018-06-18 08:07:43','ghost',0,NULL),('SW.2.2.1.1','Glycolysis',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.2.1.2','Gluconeogenesis',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.2.1.3','Pentose phosphate pathway',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.2.1.4','TCA cycle',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.2.1.5','Overflow metabolism',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.2.2','Utilization of specific carbon sources','{\"reviews\":[\"<pubmed> 11018147, 19202299, 17982469 <\\/pubmed>\"]}','2018-06-18 08:07:43','ghost',0,NULL),('SW.2.2.2.1','Utilization of organic acids',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.2.2.10','Utilization of mannitol',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.2.2.11','Utilization of glucitol',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.2.2.12','Utilization of rhamnose',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.2.2.13','Utilization of gluconate',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.2.2.14','Utilization of glucarate/galactarate',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.2.2.15','Utilization of hexuronate',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.2.2.16','Utilization of inositol',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.2.2.17','Utilization of amino sugars',NULL,'2018-06-18 08:07:43','ghost',0,'SW.2.3.3.3'),('SW.2.2.2.18','Utilization of beta-glucosides',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.2.2.19','Utilization of sucrose',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.2.2.2','Utilization of acetoin',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.2.2.20','Utilization of trehalose',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.2.2.21','Utilization of melibiose',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.2.2.22','Utilization of maltose',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.2.2.23','Utilization of starch/ maltodextrin',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.2.2.24','Utilization of galactan',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.2.2.25','Utilization of glucomannan',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.2.2.26','Utilization of pectin',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.2.2.27','Utilization of other polymeric carbohydrates',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.2.2.28','Utilization of other pentoses and hexoses',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.2.2.3','Utilization of glycerol/ glycerol-3-phosphate',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.2.2.4','Utilization of ribose',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.2.2.5','Utilization of xylan/ xylose',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.2.2.6','Utilization of arabinan/ arabinose/ arabitol',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.2.2.7','Utilization of fructose',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.2.2.8','Utilization of galactose',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.2.2.9','Utilization of mannose',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.3','Amino acid/ nitrogen metabolism',NULL,'2018-08-22 10:17:15','ghost',2,NULL),('SW.2.3.1','Biosynthesis/ acquisition of amino acids','{\"important publications\":[\"<pubmed>24163341 <\\/pubmed>\"]}','2018-06-18 08:07:43','ghost',0,NULL),('SW.2.3.1.1','Biosynthesis/ acquisition of glutamate/ glutamine/ ammonium assimilation','{\"Important reviews\":[\"<pubmed>22625175<\\/pubmed>\"]}','2018-06-18 08:07:43','Jstuelk',0,NULL),('SW.2.3.1.10','Biosynthesis/ acquisition of methionine/ S-adenosylmethionine',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.3.1.11','Biosynthesis/ acquisition of methionine/ S-adenosylmethionine/ based on similarity',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.3.1.12','Biosynthesis/ acquisition of branched-chain amino acids',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.3.1.13','Biosynthesis/ acquisition of aromatic amino acids',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.3.1.14','Biosynthesis/ acquisition of histidine',NULL,'2018-08-22 10:16:54','ghost',1,NULL),('SW.2.3.1.2','Biosynthesis/ acquisition of proline',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.3.1.3','Biosynthesis/ acquisition of proline/ based on similarity',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.3.1.4','Biosynthesis/ acquisition of arginine',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.3.1.5','Biosynthesis/ acquisition of aspartate/ asparagine',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.3.1.6','Biosynthesis/ acquisition of lysine/ threonine',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.3.1.7','Biosynthesis/ acquisition of lysine/ threonine/ based on similarity',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.3.1.8','Biosynthesis/ acquisition of serine/ glycine/ alanine',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.3.1.9','Biosynthesis/ acquisition of cysteine',NULL,'2018-08-22 12:37:04','ghost',1,NULL),('SW.2.3.2','Utilization of amino acids','{\"important reviews\":[\"<pubmed> 22933560 <\\/pubmed>\"]}','2018-06-18 08:07:43','ghost',0,NULL),('SW.2.3.2.1','Utilization of glutamine/ glutamate',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.3.2.10','Utilization of gamma-amino butyric acid',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.3.2.2','Utilization of proline',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.3.2.3','Utilization of proline/ based on similarity',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.3.2.4','Utilization of arginine/ ornithine',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.3.2.5','Utilization of histidine',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.3.2.6','Utilization of  asparagine/ aspartate',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.3.2.7','Utilization of alanine/ serine',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.3.2.8','Utilization of threonine/ glycine',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.3.2.9','Utilization of branched-chain amino acids',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.3.3','Utilization of nitrogen sources other than amino acids','{\"important reviews\":[\"<pubmed> 22103536 <\\/pubmed>\"]}','2018-06-18 08:07:43','ghost',0,NULL),('SW.2.3.3.1','Utilization of nitrate/ nitrite',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.3.3.2','Utilization of urea',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.3.3.3','Utilization of amino sugars',NULL,'2018-06-18 08:07:43','ghost',0,'SW.2.2.2.17'),('SW.2.3.3.4','Utilization of peptides','null','2018-06-18 08:07:43','Jstuelk',0,NULL),('SW.2.3.3.5','Utilization of proteins',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.3.4','Putative amino acid transporter',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.4','Lipid metabolism',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.4.1','Utilization of lipids',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.4.1.1','Utilization of phospholipids',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.4.1.2','Utilization of phospholipids/ based on similarity',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.4.1.3','Utilization of fatty acids',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.4.1.4','Utilization of lipids/ other',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.4.2','Biosynthesis of lipids','{\"key reviews and publications\":[\"<pubmed> 15952903, 17919287, 22146731, 23840410, 23746261, 23614721 <\\/pubmed>\"]}','2018-06-18 08:07:43','ghost',0,NULL),('SW.2.4.2.1','Biosynthesis of fatty acids',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.4.2.2','Biosynthesis of phospholipids',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.4.2.3','Biosynthesis of isoprenoids',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.4.3','Lipid metabolism/ other','{\"Reviews\":[\"<pubmed>28993557<\\/pubmed>\"]}','2018-06-18 08:07:43','Jstuelk',0,NULL),('SW.2.5','Nucleotide metabolism',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.5.1','Utilization of nucleotides','{\"important publications\":[\"<pubmed> 18712276,11006546,25890046<\\/pubmed>\"]}','2018-06-18 08:07:43','ghost',0,NULL),('SW.2.5.2','Biosynthesis/ acquisition of nucleotides',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.5.2.1','Biosynthesis/ acquisition of purine nucleotides',NULL,'2018-08-22 10:17:48','ghost',3,NULL),('SW.2.5.2.2','Biosynthesis/ acquisition of purine nucleotides/ based on similarity',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.5.2.3','Purine salvage and interconversion',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.5.2.4','Biosynthesis/ acquisition of pyrimidine nucleotides',NULL,'2018-08-22 10:17:52','ghost',1,NULL),('SW.2.5.2.5','Biosynthesis/ acquisition of nucleotides/ other',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.5.2.6','Biosynthesis/ acquisition of nucleotides/ other/ based on similarity',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.5.3','Metabolism of signalling nucleotides','{\"important original publications\":[\"<pubmed>21566650,22981860,23192352,23671116,23893111,24141192,24163341,25433025,25605304,26951678<\\/pubmed>\"],\"important reviews\":[\"<pubmed>21255104,16045609,17208514,19756011,19287449,18714086,23023210,23812326,25636134,25869574,26773214,28783096,28965724<\\/pubmed>\"]}','2018-06-18 08:07:43','Jstuelk',0,NULL),('SW.2.5.4','Nucleotide metabolism/ other',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.6','Additional metabolic pathways',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.6.1','Biosynthesis of cell wall components','{\"important Reviews\":[\"<pubmed>21388439,24024634,21255102,28975672<\\/pubmed>\"]}','2018-06-18 08:07:43','Jstuelk',0,NULL),('SW.2.6.1.1','Biosynthesis of peptidoglycan','{\"important reviews\":[\"<pubmed>28975672<\\/pubmed>\"]}','2018-06-18 08:07:43','Jstuelk',0,'SW.1.1.1.1'),('SW.2.6.1.2','Biosynthesis of lipoteichoic acid',NULL,'2018-06-18 08:07:43','ghost',0,'SW.1.1.1.3'),('SW.2.6.1.3','Biosynthesis of teichoic acid',NULL,'2018-06-18 08:07:43','ghost',0,'SW.1.1.1.4'),('SW.2.6.1.4','Biosynthesis of teichuronic acid',NULL,'2018-06-18 08:07:43','ghost',0,'SW.1.1.1.5'),('SW.2.6.1.5','Biosynthesis of the carrier lipid undecaprenylphosphate',NULL,'2018-06-18 08:07:43','ghost',0,'SW.1.1.1.8'),('SW.2.6.2','Biosynthesis of cofactors','{\"important original publications\":[\"<pubmed>24972371,28504670<\\/pubmed>\"],\"important reviews\":[\"<pubmed>11153271,22616866,19348578,18314013,14675553,10382260,24442413,21646432,21437340,26758294<\\/pubmed>\"]}','2018-06-18 08:07:43','Jstuelk',0,NULL),('SW.2.6.2.1','Biosynthesis/ acquisition of biotin',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.6.2.10','Biosynthesis of molybdopterin',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.6.2.11','Biosynthesis of NAD(P)',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.6.2.12','Biosynthesis of pyridoxal phosphate',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.6.2.2','Biosynthesis/ acquisition of riboflavin/ FAD',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.6.2.3','Biosynthesis/ acquisition of thiamine',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.6.2.4','Biosynthesis of coenzyme A',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.6.2.5','Biosynthesis of folate',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.6.2.6','Biosynthesis of heme/ siroheme','{\"Reviews\":[\"<pubmed>28123057<\\/pubmed>\"]}','2018-06-18 08:07:43','Jstuelk',0,NULL),('SW.2.6.2.7','Biosynthesis of lipoic acid',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.6.2.8','Biosynthesis of menaquinone',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.6.2.9','Biosynthesis of menaquinone/ based on similarity',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.6.3','Phosphate metabolism',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.6.4','Sulfur metabolism',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.6.4.1','Conversion of S-methyl cysteine to cysteine',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.6.4.2','sulfur metabolism/ general','[]','2018-06-04 10:32:56','Jstuelk',0,NULL),('SW.2.6.4.3','Conversion of S-(2-succino)cysteine to cysteine','[]','2018-06-04 10:32:58','Jstuelk',0,NULL),('SW.2.6.5','Iron metabolism','{\"key reviews\":[\"<pubmed> 20467446,16211402,12732309,23192658,25160631,26259870,26488283,12829269<\\/pubmed>\"]}','2018-06-18 08:07:43','Jstuelk',0,NULL),('SW.2.6.5.1','Acquisition of iron / Other','[]','2018-06-18 08:07:43','Bzhu',0,'SW.1.3.3.3'),('SW.2.6.5.2','Acquisition of iron/ based on similarity',NULL,'2018-06-18 08:07:43','ghost',0,'SW.1.3.4'),('SW.2.6.5.3','Biosynthesis of iron-sulfur clusters',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.6.5.4','ABC transporters for the uptake of iron/ siderophores',NULL,'2018-06-18 08:07:43','ghost',0,'SW.1.3.3.1'),('SW.2.6.5.5','Elemental iron transport system',NULL,'2018-06-18 08:07:43','ghost',0,'SW.1.3.3.2'),('SW.2.6.5.6','Iron export','[]','2018-06-04 10:33:00','Jstuelk',0,NULL),('SW.2.6.6','Miscellaneous metabolic pathways',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.6.6.1','Biosynthesis of antibacterial compounds',NULL,'2018-06-18 08:07:43','ghost',0,'SW.4.3.15'),('SW.2.6.6.2','Biosynthesis of bacillithiol','{\"features of bacillithiol\":[\"<pubmed> 24115506,25213752 <\\/pubmed>\"]}','2018-06-18 08:07:43','ghost',0,NULL),('SW.2.6.6.3','Biosynthesis of dipicolinate',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.6.6.4','Biosynthesis of glycine betaine',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.6.6.5','Biosynthesis of glycogen',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.6.6.6','Metabolism of polyamines',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.6.6.7','Biosynthesis of rhamnose (for the exosporium)',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.2.7','Detoxification reactions','[]','2018-06-04 10:33:05','Jstuelk',0,NULL),('SW.3','Information processing',NULL,'2018-08-22 12:40:22','ghost',2,NULL),('SW.3.1','Genetics',NULL,'2018-08-22 09:51:33','ghost',1,NULL),('SW.3.1.1','DNA replication','{\"important publications\":[\"<pubmed>21350489,22797751,15652974,21675919,24914187,24946150,25176632,25340815,26097470,26706151,28575448<\\/pubmed>\"]}','2018-08-22 09:57:24','Jstuelk',4,NULL),('SW.3.1.2','DNA replication/ based on similarity',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.3.1.3','DNA condensation/ segregation','{\"Important Reviews\":[\"<pubmed>22201788,22047950,21763138,22934648,23400100,26706151,29522747<\\/pubmed>\"],\"Important Original Publications\":[\"<pubmed>26295962<\\/pubmed>\"]}','2018-08-22 09:54:03','Jstuelk',2,NULL),('SW.3.1.4','DNA restriction/ modification',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.3.1.5','DNA repair/ recombination','{\"key reviews\":[\"<pubmed>12045091,21517913,22749141,22933559,23046409,23202527,23380520,16132081,10961463,25731766,26354434<\\/pubmed>\"],\"key original publications\":[\"<pubmed> 24670664 <\\/pubmed>\"]}','2018-06-18 08:07:43','ghost',0,NULL),('SW.3.1.5.1','Excision of prophages',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.3.1.5.2','A/P endonucleases',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.3.1.5.3','Spore-encoded non-homologous end joining system',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.3.1.5.4','Double strand breaks repair',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.3.1.5.5','Oxidized guanine (GO) DNA repair system',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.3.1.5.6','Other proteins',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.3.1.6','DNA repair/ recombination/ based on similarity',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.3.1.7','Genetic competence','{\"additional information\":[\"The wild type strain NCIB3610 is only poorly competent because the small protein ComI encoded on the endogenous plasmid pBS32 inhibits the competence DNA uptake machinery. [pubmed|23836866]\"],\"important original publications\":[\"<pubmed> 23836866,24012503 <\\/pubmed>\"],\"important reviews\":[\"<pubmed>15083159,19228200,10607621,9224890,1943994,19995980,12576575,23046409,22146301,23551850,23572583,23693123,23669271,25547840 <\\/pubmed>\"]}','2018-06-18 08:07:43','Bzhu',0,'SW.4.1.3'),('SW.3.1.8','Genetics/ other/ based on similarity',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.3.2','RNA synthesis and degradation',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.3.2.1','Transcription','{\"important publications\":[\"<pubmed>21350489,22087258,22383849,24789973,28723971<\\/pubmed>\"],\"reviews\":[\"<pubmed>18599813,14527287,12455702,12213655,12073657,7708009,19489723,10329121,21478900,22210308,24237659,26132790,28657884,25002089<\\/pubmed>\"]}','2018-06-18 08:07:43','Jstuelk',0,NULL),('SW.3.2.1.1','RNA polymerase',NULL,'2018-08-22 12:40:37','ghost',2,NULL),('SW.3.2.1.2','Sigma factors','{\"Key publications\":[\"<pubmed>29343670<\\/pubmed>\"]}','2018-06-18 08:07:43','Jstuelk',0,'SW.3.4.1.1'),('SW.3.2.1.3','Transcription elongation/ termination',NULL,'2018-08-22 12:39:10','ghost',1,NULL),('SW.3.2.1.4','Prophage/ phage transcription','[]','2018-06-18 08:07:43','Jstuelk',0,NULL),('SW.3.2.2','RNA chaperones','{\"key reviews\":[\"<pubmed>12110176 <\\/pubmed>\"]}','2018-06-18 08:07:43','ghost',0,NULL),('SW.3.2.3','DEAD-box RNA helicases','{\"important original publications\":[\"<pubmed>23175651,12535527 <\\/pubmed>\"],\"key reviews\":[\"<pubmed>20206133,19747077,16936318,16337753,14991003,12695678,12110176,21378185,21779027,23064154,21705526,26808656,25579577,25907111,29651979<\\/pubmed>\"]}','2018-06-18 08:07:43','Jstuelk',0,NULL),('SW.3.2.4','RNases','{\"key original publications\":[\"<pubmed>12884008,22537947,25099370<\\/pubmed>\"],\"reviews on RNases in \'\'Bacillus subtilis\'\'\":[\"<pubmed>21334965,19767421,21976285,22550495,19215774,12794188,12490701,20659169,23403287,21957024,22568516,24064983,25292357,25878039,29314657,29651979<\\/pubmed>\"]}','2018-06-18 08:07:43','Jstuelk',0,NULL),('SW.3.2.4.1','Exoribonucleases',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.3.2.4.2','Endoribonucleases',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.3.2.4.3','RNA pyrophosphohydrolase',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.3.2.4.4','RNases/ Other','[]','2018-06-18 08:07:43','Jstuelk',0,NULL),('SW.3.2.5','RNase/ based on similarity',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.3.3','Protein synthesis, modification and degradation',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.3.3.1','Translation','{\"important publications\":[\"<pubmed>22456704,22848659,22905870,2,809820,24789973,25796611,26518335<\\/pubmed>\"]}','2018-06-18 08:07:43','ghost',0,NULL),('SW.3.3.1.1','Ribosomal RNA','{\"important publications\":[\"<pubmed> 20634236,23970567 <\\/pubmed>\"]}','2018-06-18 08:07:43','ghost',0,NULL),('SW.3.3.1.10','Aminoacyl-tRNA synthetases',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.3.3.1.11','Translation factors',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.3.3.1.12','Translation/ other',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.3.3.1.13','Translation/ other/ based on similarity','[]','2018-06-18 08:07:43','Jstuelk',0,NULL),('SW.3.3.1.14','Translation factor modification and maturation','[]','2018-06-04 10:33:13','Jstuelk',0,NULL),('SW.3.3.1.2','rRNA modification and maturation',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.3.3.1.3','rRNA modification and maturation/ based on similarity','[]','2018-06-18 08:07:43','Jstuelk',0,NULL),('SW.3.3.1.4','Ribosomal proteins',NULL,'2018-08-22 12:39:34','ghost',3,NULL),('SW.3.3.1.5','Ribosomal protein/ based on similarity',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.3.3.1.6','Ribosome assembly',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.3.3.1.7','tRNA','{\"important publications\":[\"<pubmed>25796611,25780175,24966867<\\/pubmed>\"]}','2018-06-18 08:07:43','ghost',0,NULL),('SW.3.3.1.8','tRNA modification and maturation',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.3.3.1.9','tRNA modification and maturation/ based on similarity',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.3.3.2','Chaperones/ protein folding',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.3.3.3','Chaperone/ protein folding/ based on similarity',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.3.3.4','Protein modification','{\"key Reviews\":[\"<pubmed>21372323,20487279,25625314,25852656<\\/pubmed>\"]}','2018-06-18 08:07:43','ghost',0,NULL),('SW.3.3.4.1','Protein maturation',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.3.3.4.2','Protein kinases',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.3.3.4.3','Protein kinase/ based on similarity',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.3.3.4.4','Protein phosphatases',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.3.3.4.5','Protein acetylases/ deacetylases',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.3.3.4.6','Protein acetylase/ deacetylase/ based on similarity',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.3.3.4.7','Protein deaminase',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.3.3.4.8','Protein modification/ other',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.3.3.5','Protein secretion','{\"important reviews\":[\"The structure of a Tat-dependent signal peptide ([[gene|597771E2E8EC31ED9B2CC8C0E4D888DEEA80F689]]) can be found here: [PubMed|22960285]\",\"<pubmed>17005968,15187182,18182292,21920479,18078384,15502345,14618254,22471582,22411983,22683878,22688815,19155186,11973144,24140208,25975269,25494301<\\/pubmed>\"]}','2018-06-18 08:07:43','ghost',0,NULL),('SW.3.3.6','Protein secretion/ based on similarity',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.3.3.7','Proteolysis','{\"important reviews\":[\"<pubmed>19421188,23375660,23479438,22688815,24099006,24115457<\\/pubmed>\"]}','2018-06-18 08:07:43','ghost',0,NULL),('SW.3.3.7.1','Protein quality control',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.3.3.7.2','Extracellular feeding proteases','{\"additional information\":[\"A mutant strain with deletions of all feeding proteases and the three major protein quality control proteases ([[gene|76FA52D5A5F9FCA14BA9B45872FE3D99D89B211B]], [[gene|796216BE9CD6DADFEADC29497253C81BF496A2ED]], [[gene|DC0FDCA3FA6742B023E6877CE9554AA1D47012BB]])  (BRB14) is available in [SW|Colin Harwood]\'s lab [PubMed|24115457]\"]}','2018-06-18 08:07:43','Bzhu',0,NULL),('SW.3.3.7.3','Proteolysis during sporulation/ germination',NULL,'2018-06-18 08:07:43','Bzhu',0,NULL),('SW.3.3.7.4','Additional proteins involved in proteolysis','null','2018-08-22 12:38:14','Bzhu',2,NULL),('SW.3.4','Regulation of gene expression',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.3.4.1','Sigma factors and their control','{\"important Reviews\":[\"<pubmed>14527287,12073657,11121773,10322161,10200951,9891799,7708009,1538747,1935888,2127951,3095618,22381678,21639785,26901131,25002089<\\/pubmed>\"]}','2018-06-18 08:07:43','Jstuelk',0,NULL),('SW.3.4.1.1','Sigma factors','{\"Key publications\":[\"<pubmed>29343670<\\/pubmed>\"]}','2018-06-18 08:07:43','Jstuelk',0,'SW.3.2.1.2'),('SW.3.4.1.2','Control of sigma factors','{\"Key publications\":[\"<pubmed>29343670<\\/pubmed>\"]}','2018-06-18 08:07:43','Jstuelk',0,NULL),('SW.3.4.2','Transcription factors and their control','{\"reviews on transcription regulation\":[\"<pubmed>19721087,19632156,16772031,18599813,22210308,23504016,22728391<\\/pubmed>\"]}','2018-06-18 08:07:43','ghost',0,NULL),('SW.3.4.2.1','Two-component system response regulators',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.3.4.2.2','Control of two-component response regulators',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.3.4.2.2.1','Two-component sensor kinase',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.3.4.2.2.2','Response regulator aspartate phosphatase',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.3.4.2.2.3','Control of response regulators/ other',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.3.4.2.3','PRD-type regulators',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.3.4.2.4','Control of PRD-type regulators',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.3.4.2.5','Transcription factors/ other',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.3.4.2.6','Transcription factor/ other/ based on similarity',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.3.4.2.7','Control of transcription factor (other than two-component system)',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.3.4.3','Trigger enzyme','{\"reviews on trigger enzymes\":[\"<pubmed>18086213,22625175<\\/pubmed>\"]}','2018-06-18 08:07:43','ghost',0,NULL),('SW.3.4.3.1','Trigger enzymes of the PTS that control the activity of PRD-containing transcription factors',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.3.4.3.2','Trigger enzymes that control gene expression by protein-protein interaction with transcription factors',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.3.4.3.3','Trigger enzymes that act directly as transcription factors by binding DNA',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.3.4.3.4','Trigger enzyme that acts by binding of a specific RNA element',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.3.4.3.5','Trigger enzymes that control transcription in a yet unknown way',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.3.4.4','RNA binding regulators','{\"reviews\":[\"<pubmed>12029388<\\/pubmed>\"]}','2018-06-18 08:07:43','ghost',0,NULL),('SW.3.4.5','Regulators of core metabolism','{\"important original publications\":[\"<pubmed>18302748<\\/pubmed>\"],\"reviews\":[\"<pubmed>20408793 <\\/pubmed>\"]}','2018-06-18 08:07:43','ghost',0,NULL),('SW.3.4.6','Transition state regulators',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.3.4.7','phosphorelay',NULL,'2018-06-18 08:07:43','ghost',0,'SW.4.2.2'),('SW.3.4.7.1','The kinases',NULL,'2018-06-18 08:07:43','ghost',0,'SW.4.2.2.1'),('SW.3.4.7.2','Proteins controlling the activity of the kinases','[]','2018-06-18 08:07:43','Jstuelk',0,'SW.4.2.2.2'),('SW.3.4.7.3','The phosphotransferases',NULL,'2018-06-18 08:07:43','ghost',0,'SW.4.2.2.3'),('SW.3.4.7.4','The ultimate target','{\"References\":[\"<pubmed>28886686<\\/pubmed>\"]}','2018-06-18 08:07:43','Jstuelk',0,'SW.4.2.2.4'),('SW.3.4.7.5','Phosphatases controlling the phosphorelay',NULL,'2018-06-18 08:07:43','ghost',0,'SW.4.2.2.5'),('SW.3.4.7.6','Other protein controlling the activity of the phosphorelay',NULL,'2018-06-18 08:07:43','ghost',0,'SW.4.2.2.6'),('SW.3.4.8','Quorum sensing','{\"important reviews and original publications\":[\"<pubmed>20378052,24432140,24425772,25846138,26196509,26787913,26927849,29243493<\\/pubmed>\"]}','2018-06-18 08:07:43','Jstuelk',0,NULL),('SW.3.4.9','Other regulators',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.4','Lifestyles',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.4.1','Exponential and early post-exponential lifestyles',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.4.1.1','Motility and chemotaxis','{\"key original publications\":[\"<pubmed>26122431,24256735,25035996,25538299,28536199<\\/pubmed>\"],\"key reviews\":[\"<pubmed>15187186,18774298,8604438,22092493,25251856,26195616,26490009,26731482<\\/pubmed>\"]}','2018-06-18 08:07:43','Jstuelk',0,NULL),('SW.4.1.1.1','Signal transduction in motility and chemotaxis',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.4.1.1.1.1','Soluble signalling proteins','null','2018-06-18 08:07:43','Bzhu',0,NULL),('SW.4.1.1.1.2','Coupling proteins',NULL,'2018-06-18 08:07:43','Bzhu',0,NULL),('SW.4.1.1.1.3','Soluble chemoreceptors',NULL,'2018-06-18 08:07:43','Bzhu',0,NULL),('SW.4.1.1.1.4','Membrane-bound chemoreceptors',NULL,'2018-06-18 08:07:43','Bzhu',0,NULL),('SW.4.1.1.1.5','Additional chemotaxis signal transduction and regulatory proteins',NULL,'2018-06-18 08:07:43','Bzhu',0,NULL),('SW.4.1.1.2','Flagellar proteins',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.4.1.1.3','Flagellar proteins/ based on similarity',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.4.1.1.4','Motility and chemotaxis/ other',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.4.1.2','Biofilm formation','{\"Description\":[\"Biofilms are the result of the multicellular lifestyle of <i>B. subtilis<\\/i>. They are characterized by the formation of a matrix polysaccharide (poly-N-acetyl glucosamine as a major polysaccharide [pubmed|26078454]) and an amyloid-like protein, [[protein|BF97457E986656E4A9FE7A858F5BDF1759850D5C]]. Correction of [[gene|1627167E7E9DBE444161A94D9F76317612C6401C]], [[gene|5DB168A3D087AAEB003D7548A1A4356ABD07F712]], [[gene|5D479874B43F521DB52EDC2C27CDE4967F22DE47]], [[gene|0CEE58D799AF41634D161DBDF3D67EEFFF1C861E]], and [[gene|40C1E81BAB04BD1CC98FC57DF25D27DBDFEB5A59]] as well as introduction of rapP from a plasmid present in NCIB3610 results in biofilm formation in B. subtilis 168 [pubmed|21278284].\"],\"Labs working on biofilm formation\":[\"[SW|Roberto Grau]\",\"[SW|Daniel Kearns]\",\"[SW|Roberto Kolter]\",\"[SW|Akos T Kovacs]\",\"[SW|Oscar Kuipers]\",\"[SW|Beth Lazazzera]\",\"[SW|Richard Losick]\",\"[SW|Eric Raspaud]\",\"[SW|Nicola Stanley-Wall]\",\"[SW|J\\u00f6rg St\\u00fclke]\"],\"important original publications\":[\"<pubmed> 26122431, 26152584, 26078454, 25870300, 26060272, 25825426, 23271809, 23300252, 21267464, 21278284, 16091050, 22232655, 22371091, 23341623, 23406351, 25768534, 23012477,22934631, 23517761, 23569226, 23564171, 25035996, 23637960, 23645570, 24256735, 25422306 ,25680358, 25713360, 25894589, 26200335, 26873313,28386026 29163384<\\/pubmed>\"],\"key reviews\":[\"<pubmed>16787201,24771632, 9891794,19054118,20890834,21109420,20519345,18381896,22024380,20735481,23353768,23927648,24909922,26104716,24988880,24608334,25907113,28622518<\\/pubmed>\"]}','2018-06-18 08:07:43','Jstuelk',0,NULL),('SW.4.1.2.1','Matrix polysaccharide synthesis','{\"References\":[\"<pubmed>26078454<\\/pubmed>\"]}','2018-06-18 08:07:43','Bzhu',0,NULL),('SW.4.1.2.2','Amyloid protein synthesis, secretion and assembly',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.4.1.2.3','Repellent surface layer',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.4.1.2.4','Regulation',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.4.1.2.5','Other proteins required for biofilm formation',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.4.1.2.6','Other proteins required for efficient pellicle biofilm formation','{\"description\":[\"mutant is out-competed by wild type\"]}','2018-06-04 10:33:25','Bzhu',0,NULL),('SW.4.1.3','Genetic competence','{\"additional information\":[\"The wild type strain NCIB3610 is only poorly competent because the small protein ComI encoded on the endogenous plasmid pBS32 inhibits the competence DNA uptake machinery. [pubmed|23836866]\"],\"important original publications\":[\"<pubmed> 23836866,24012503 <\\/pubmed>\"],\"important reviews\":[\"<pubmed>15083159,19228200,10607621,9224890,1943994,19995980,12576575,23046409,22146301,23551850,23572583,23693123,23669271,25547840 <\\/pubmed>\"]}','2018-06-18 08:07:43','Bzhu',0,'SW.3.1.7'),('SW.4.1.4','Swarming','{\"Important original publications\":[\"<pubmed>18566286,19749039,23190039,24296669,23893111,19542270,16030230,15066026,12864845,16357223,26438858<\\/pubmed>\"],\"key reviews\":[\"<pubmed>20694026,22092493<\\/pubmed>\"]}','2018-06-18 08:07:43','Bzhu',0,NULL),('SW.4.1.5','Sliding','{\"important original publications\":[\"<pubmed> 26152584, 25894589,1784632,19659723,16545127,16321950,12949115<\\/pubmed>\"],\"key reviews\":[\"<pubmed>20694026, 22092493<\\/pubmed>\"]}','2018-06-18 08:07:43','ghost',0,NULL),('SW.4.2','Sporulation',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.4.2.1','Sporulation proteins','{\"important web site\":{\"description\":[\"[http:\\/\\/sporeweb.molgenrug.nl\\/ SporeWeb] is a dynamic web-based platform describing all stages of sporulation in B. subtilis from a gene regulatory point of view. The website is intended for all microbial researches interested in bacterial sporulation and\\/or gene regulatory networks and contains useful information for both sporulation experts and non-experts.\"],\"you can find\":[\"detailed review-like descriptions of all sporulation stages,\",\"schematic representations of regulatory events during every stage,\",\"links to specific regulators and their regulons,\",\"access to detailed Excel sheets of all sporulation-specific regulons,\",\"Cytoscape-generated layouts of regulon interactions during a specific stage,\",\"a tool to generate your own Cytoscape interaction figure for specific genes of interest,\",\"heat maps showing expression values of all sporulation genes during the sporulation cycle (derived from [pubmed|22383849]),\",\"direct links to other knowledge platforms, such as SubtiWiki,\",\"an extensive list of references for further detailed information.\"]},\"important reviews\":[\"<pubmed>22091839,20833318,16045607,15819616,15659154,15556029,15187183,15035041,2257467,22882546,24983526,25646759<\\/pubmed>\"],\"important original publications\":[\"<pubmed>21667307,21665972,21905219,23284278,25341802,25356555,25548246,26735940,29425492<\\/pubmed>\"]}','2018-06-18 08:07:43','Jstuelk',0,NULL),('SW.4.2.1.1','Spore coat proteins','{\"important publications\":[\"<pubmed>22171814,20451384,22192522,23202530,22262582,24283940,26512126<\\/pubmed>\"]}','2018-06-18 08:07:43','ghost',0,NULL),('SW.4.2.1.1.1','Class I','{\"description\":[\"these proteins completly cover the membrane around the forespore\",\"early localizing spore coat proteins\"]}','2018-06-18 08:07:43','ghost',0,NULL),('SW.4.2.1.1.2','Class II','{\"description\":[\"these proteins localize simultaneously with the class I proteins but begin to encase the spore only after engulfment is complete\",\"early localizing spore coat proteins\"]}','2018-06-18 08:07:43','ghost',0,NULL),('SW.4.2.1.1.3','Class III','{\"description\":[\"these proteins localize simultaneously with the class I and class II proteins but start to encase the spore only after the appearance of phase dark spores (hr 4.5)\",\"early localizing spore coat proteins\"]}','2018-06-18 08:07:43','ghost',0,NULL),('SW.4.2.1.1.4','Class IV','{\"description\":[\"late localizing spore coat proteins (only after completion of engulfment)\",\"intermediate between classes III and V\"]}','2018-06-18 08:07:43','ghost',0,NULL),('SW.4.2.1.1.5','Class V','{\"description\":[\"late localizing spore coat proteins (only after completion of engulfment)\",\"localize exclusively to phase dark spores\",\"localize simultaneously to both poles of the spore\"]}','2018-06-18 08:07:43','ghost',0,NULL),('SW.4.2.1.1.7','Class VI','{\"description\":[\"late localizing spore coat proteins (only after completion of engulfment)\",\"most delayed initial localization\",\"localize excluseively to phase bright spores\"]}','2018-06-18 08:07:43','Bzhu',0,NULL),('SW.4.2.1.1.8','Not yet assigned','[]','2018-06-04 10:33:31','Bzhu',0,NULL),('SW.4.2.1.2','Spore coat protein/ based on similarity','[]','2018-06-18 08:07:43','Jstuelk',0,NULL),('SW.4.2.1.3','Small acid-soluble spore proteins',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.4.2.1.4','Sporulation proteins/ other',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.4.2.1.5','Newly identified sporulation proteins (based on transcription profiling)',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.4.2.2','phosphorelay',NULL,'2018-06-18 08:07:43','ghost',0,'SW.3.4.7'),('SW.4.2.2.1','The kinases',NULL,'2018-06-18 08:07:43','ghost',0,'SW.3.4.7.1'),('SW.4.2.2.2','Proteins controlling the activity of the kinases','[]','2018-06-18 08:07:43','Jstuelk',0,'SW.3.4.7.2'),('SW.4.2.2.3','The phosphotransferases',NULL,'2018-06-18 08:07:43','ghost',0,'SW.3.4.7.3'),('SW.4.2.2.4','The ultimate target','{\"References\":[\"<pubmed>28886686<\\/pubmed>\"]}','2018-06-18 08:07:43','Jstuelk',0,'SW.3.4.7.4'),('SW.4.2.2.5','Phosphatases controlling the phosphorelay',NULL,'2018-06-18 08:07:43','ghost',0,'SW.3.4.7.5'),('SW.4.2.2.6','Other protein controlling the activity of the phosphorelay',NULL,'2018-06-18 08:07:43','ghost',0,'SW.3.4.7.6'),('SW.4.2.3','Sporulation/ other',NULL,'2018-08-22 10:03:35','ghost',1,NULL),('SW.4.2.4','Germination','{\"important original publications\":[\"<pubmed>22493018,23536843,23746146,23749970,24317076,24769693,25583976,25661487,25681191,25764471,26279233<\\/pubmed>\"],\"important reviews\":[\"<pubmed>16907803,14662349,11964118,20972452,28697670<\\/pubmed>\"]}','2018-06-18 08:07:43','Jstuelk',0,NULL),('SW.4.2.4.1','Germinant receptors',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.4.2.4.2','Additional germination proteins',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.4.2.5','Germination/ based on similarity',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.4.3','Coping with stress',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.4.3.1','General stress proteins (controlled by SigB)','{\"key reviews\":[\"<pubmed>9767581,11407115<\\/pubmed>\"]}','2018-06-18 08:07:43','ghost',0,NULL),('SW.4.3.10','Resistance against other toxic compounds (nitric oxide, phenolic acids, flavonoids, oxalate)',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.4.3.11','Resistance against toxic metals','{\"key reviews\":[\"<pubmed> 19774401<\\/pubmed>\"]}','2018-06-18 08:07:43','ghost',0,NULL),('SW.4.3.12','Resistance against toxic metals/ based on similarity',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.4.3.13','Resistance against toxins/ antibiotics','{\"important publications\":[\"<pubmed>19231985,21665979,20822442<\\/pubmed>\"]}','2018-06-18 08:07:43','ghost',0,NULL),('SW.4.3.14','Resistance against toxins/ antibiotics/ based on similarity',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.4.3.15','Biosynthesis of antibacterial compounds','{\"important publications\":[\"<pubmed>21464591,26648120<\\/pubmed>\"]}','2018-06-18 08:07:43','ghost',0,'SW.2.6.6.1'),('SW.4.3.16','Biosynthesis of antibacterial compounds/ based on similarity',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.4.3.17','Toxins, antitoxins and immunity against toxins','{\"key reviews\":[\"<pubmed>21041110,19493340,19325885,15864262,12970556,19052321,20156992,21315267,23059907,22434880,23289536,25808661<\\/pubmed>\"]}','2018-06-18 08:07:43','ghost',0,NULL),('SW.4.3.17.1','Type 1 TA systems',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.4.3.17.2','Type 2 TA systems',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.4.3.17.3','Toxins, antitoxins and immunity/ Additional genes','[]','2018-06-18 08:07:43','Jstuelk',0,NULL),('SW.4.3.18','Toxins, antitoxins and immunity against toxins/ based on similarity',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.4.3.2','Cell envelope stress proteins (controlled by SigM, V, W, X, Y)',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.4.3.3','Acid stress proteins (controlled by YvrI-YvrHa)',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.4.3.4','Heat shock proteins','{\"key reviews\":[\"<pubmed>14984053,11407115<\\/pubmed>\"]}','2018-06-18 08:07:43','ghost',0,NULL),('SW.4.3.5','Cold stress proteins','{\"key reviews\":[\"<pubmed>15199224,12171653,10943551,8929274<\\/pubmed>\"]}','2018-06-18 08:07:43','ghost',0,NULL),('SW.4.3.6','Coping with hyper-osmotic stress','{\"reviews\":[\"<pubmed>9818351,11913457,21663439,15519310,17047223,17875413,28965724<\\/pubmed>\"]}','2018-06-18 08:07:43','Jstuelk',0,NULL),('SW.4.3.7','Coping with hypo-osmotic stress',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.4.3.8','Resistance against oxidative and electrophile stress','{\"important original publications\":[\"<pubmed>22582280<\\/pubmed>\"],\"reviews\":[\"<pubmed>21352461,19575568,18282125,7851732,22797754,23899494,25852656<\\/pubmed>\"]}','2018-06-18 08:07:43','ghost',0,NULL),('SW.4.3.9','Resistance against oxidative and electrophile stress/ based on similarity',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.4.4','Lifestyles/ miscellaneous',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.5','Prophages and mobile genetic elements','{\"important note\":[\"A strain devoid of [SW|PBSX prophage] , [SW|SP-beta prophage] and the [SW|Skin element] (TF8A) as well as a strain devoid of all six prophages of the 168 genome (D6) is available in Jan Maarten van Dijl\'s lab [PubMed|12949151]\"],\"references\":[\"<pubmed>12949151<\\/pubmed>\"]}','2018-07-12 11:55:12','Bzhu',8,NULL),('SW.5.1','Prophages',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.5.1.1','PBSX prophage','{\"references\":[\"<pubmed>12949151<\\/pubmed>\"],\"key publications\":[\"<pubmed>8760915,9555893,8083174,7921239,2110147,3923209<\\/pubmed>\"]}','2018-06-18 08:07:43','ghost',0,NULL),('SW.5.1.2','SP-beta prophage','{\"important notes\":[\"In 168, SP-beta disrupts the [[gene|240CD6EA3793821F5109252BDEF69C0120E454EF]] gene\",\"A strain devoid of [[SW|PBSX prophage]] , [[SW|SP-beta prophage]] and the [[SW|Skin element]] (TF8A) as well as a strain devoid of all six prophages of the 168 genome (D6) is available in Jan Maarten van Dijl\'s lab [PubMed|12949151]\"],\"key publications\":[\"<pubmed>9781889,10376821,12949151<\\/pubmed>\"]}','2018-06-18 08:07:43','ghost',0,NULL),('SW.5.1.3','Skin element',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.5.1.4','Prophage 1','{\"important note\":[\"A strain devoid of [[SW|PBSX prophage]] , [[SW|SP-beta prophage]] and the [[SW|Skin element]] (TF8A) as well as a strain devoid of all six prophages of the 168 genome (D6) is available in Jan Maarten van Dijl\'s lab [PubMed|12949151]\"],\"references\":[\"<pubmed>12949151<\\/pubmed>\"]}','2018-06-18 08:07:43','ghost',0,NULL),('SW.5.1.5','Prophage 3','{\"important note\":[\"A strain devoid of [[SW|PBSX prophage]] , [[SW|SP-beta prophage]] and the [[SW|Skin element]] (TF8A) as well as a strain devoid of all six prophages of the 168 genome (D6) is available in Jan Maarten van Dijl\'s lab [PubMed|12949151]\"],\"references\":[\"<pubmed>12949151<\\/pubmed>\"]}','2018-06-18 08:07:43','ghost',0,NULL),('SW.5.1.6','Phage-related functions',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.5.2','Mobile genetic elements','{\"important publications\":[\"<pubmed>21406598,19854907,22505685,23326247,23874222,21239213,24995588,26013486,26440206,26104437<\\/pubmed>\"]}','2018-06-18 08:07:43','ghost',0,NULL),('SW.5.2.1','ICEBs1',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.5.2.2','Additional genes',NULL,'2018-07-19 11:18:13','ghost',9,NULL),('SW.6','Groups of genes',NULL,'2018-08-08 07:31:35','ghost',1,NULL),('SW.6.1','Essential genes','{\"important original publications\":[\"<pubmed>12682299,17114254,17005971,23420519,24178028,25092907<\\/pubmed>\"]}','2018-09-03 13:14:20','ghost',2,NULL),('SW.6.10','Pseudogenes',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.6.11','Efp-dependent proteins','{\"description\":[\"These 34 proteins contains three or more consecutive proline residues and are therefore likely to depend on the translation factor Efp for efficient expression\"]}','2018-06-18 08:07:43','Bzhu',0,NULL),('SW.6.12','Secreted proteins','[]','2018-06-04 10:33:51','Jstuelk',0,NULL),('SW.6.2','Membrane proteins','{\"Key publications\":[\"<pubmed>18763711,21266987,25135940<\\/pubmed>\"]}','2018-08-22 12:44:09','Jstuelk',3,NULL),('SW.6.3','GTP-binding proteins','{\"additional information\":[\"several GTP-binding proteins bind ppGpp resulting in inhibition of ther GTPase activity [PubMed|26951678]\"],\"important publications\":[\"<pubmed>12427945,19575570,21885683,26951678<\\/pubmed>\"]}','2018-06-18 08:07:43','ghost',0,NULL),('SW.6.4','Phosphoproteins','{\"description\":[\"These proteins are subject to a phosphorylation event. Most often, protein phosphorylation affects the conformation of the protein resulting in changes in biological activity, interaction properties and\\/ or localization.\"],\"original papers on the \'\'B. subtilis\'\' phosphoproteome\":[\"<pubmed>17218307,16493705,17726680,20509597,22517742,24263382,24390483<\\/pubmed>\"],\"reviews\":[\"<pubmed>21266190,19387796,19525115,19189200,18834307,18761471,17881301,17208443,16415592,16415586,14977554,14745484,11856347,11751048,10603474,8759835, 188320,19489734,19489734,20497498<\\/pubmed>\"]}','2018-06-18 08:07:43','ghost',0,NULL),('SW.6.4.1','Phosphorylation on an Arg residue',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.6.4.2','Phosphorylation on an Asp residue',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.6.4.3','Phosphorylation on a Cys residue',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.6.4.4','Phosphorylation on a His residue',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.6.4.5','Phosphorylation on a Ser residue',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.6.4.6','Phosphorylation on a Thr residue',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.6.4.7','Phosphorylation on a Tyr residue',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.6.4.8','Phosphorylation on either a Ser, Thr or Tyr residue',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.6.4.9','Phosphoproteins / Other','[]','2018-06-04 10:33:51','Bzhu',0,NULL),('SW.6.5','Universally conserved proteins','{\"description\":[\"These proteins are present in all genomes sequenced so far, from archaea and bacteria to man. So, one can say, that there is no life on earth without this small set of proteins. The genes encoding these proteins are usually essential.\"],\"important publications\":[\"<pubmed>15479782<\\/pubmed>\"]}','2018-08-22 10:16:00','Bzhu',NULL,NULL),('SW.6.6','Poorly characterized/ putative enzymes',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.6.7','Proteins of unknown function',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.6.8','Short peptides',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.6.9','NcRNA','{\"reviews\":[\"<pubmed>19239884,19207102,20525796,19859665,21646430,22827307,24576839,25808661,27784798<\\/pubmed>\"],\"important original publications\":[\"<pubmed>22383849,28732463<\\/pubmed>\"]}','2018-06-18 08:07:43','Jstuelk',0,NULL),('SW.6.9.1','6S RNA',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.6.9.2','tmRNA',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.6.9.3','Small cytoplasmatic RNA',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.6.9.4','RNA component of RNase P',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.6.9.5','Regulatory RNAs',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.6.9.6','Antisense RNAs of toxin/antitoxin systems',NULL,'2018-06-18 08:07:43','ghost',0,NULL),('SW.6.9.7','Small RNAs with unknown functions',NULL,'2018-06-18 08:07:43','ghost',0,NULL);
/*!40000 ALTER TABLE `Category` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Complex`
--

DROP TABLE IF EXISTS `Complex`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Complex` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `title` (`title`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ComplexMember`
--

DROP TABLE IF EXISTS `ComplexMember`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ComplexMember` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `complex` int(11) NOT NULL,
  `member` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `coefficient` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `complex` (`complex`),
  KEY `unique` (`complex`,`member`),
  CONSTRAINT `fk_ComplexMember_complex` FOREIGN KEY (`complex`) REFERENCES `Complex` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `DataSet`
--

DROP TABLE IF EXISTS `DataSet`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `DataSet` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `data` text NOT NULL,
  `pubmed` int(10) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `title` (`title`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Gene`
--

DROP TABLE IF EXISTS `Gene`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Gene` (
  `id` char(40) NOT NULL,
  `title` varchar(255) NOT NULL,
  `data` json DEFAULT NULL,
  `_locus` varchar(50) DEFAULT NULL,
  `_function` text,
  `_synonyms` text,
  `_mw` double DEFAULT NULL,
  `_pI` double DEFAULT NULL,
  `_description` text,
  `_essential` varchar(10) DEFAULT NULL,
  `_ec` varchar(30) DEFAULT NULL,
  `_geneLength` int(11) DEFAULT NULL,
  `_proteinLength` int(11) DEFAULT NULL,
  `_strain` varchar(100) NOT NULL,
  `count` int(11) DEFAULT '0',
  `lastUpdate` timestamp NOT NULL DEFAULT '2016-02-24 15:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `lastAuthor` varchar(255) NOT NULL DEFAULT 'ghost',
  PRIMARY KEY (`id`),
  KEY `_mw` (`_mw`),
  KEY `_essential` (`_essential`),
  KEY `_locus` (`_locus`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `GeneCategory`
--

DROP TABLE IF EXISTS `GeneCategory`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `GeneCategory` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gene` char(40) DEFAULT NULL,
  `category` varchar(255) DEFAULT NULL,
  `lastAuthor` varchar(255) DEFAULT 'ghost',
  `lastUpdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique` (`gene`,`category`) USING BTREE,
  KEY `gene` (`gene`),
  KEY `category` (`category`),
  CONSTRAINT `fk_GeneCategory_category` FOREIGN KEY (`category`) REFERENCES `Category` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_GeneCategory_gene` FOREIGN KEY (`gene`) REFERENCES `Gene` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `GenomicContext`
--

DROP TABLE IF EXISTS `GenomicContext`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `GenomicContext` (
  `start` int(11) DEFAULT NULL,
  `stop` int(11) DEFAULT NULL,
  `object` varchar(255) DEFAULT NULL,
  `strand` int(1) DEFAULT NULL,
  `strain` text NOT NULL,
  UNIQUE KEY `start` (`start`,`stop`,`strand`,`object`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `History`
--

DROP TABLE IF EXISTS `History`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `History` (
  `commit` char(16) NOT NULL,
  `origin` varchar(255) DEFAULT NULL,
  `identifier` varchar(255) DEFAULT NULL,
  `record` json DEFAULT NULL,
  `user` varchar(255) DEFAULT NULL,
  `lastOperation` varchar(255) DEFAULT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`commit`),
  KEY `origin` (`origin`),
  KEY `identifier` (`identifier`),
  KEY `user` (`user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Interaction`
--

DROP TABLE IF EXISTS `Interaction`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Interaction` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `prot1` varchar(255) NOT NULL,
  `prot2` varchar(255) NOT NULL,
  `data` text,
  `lastUpdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `lastAuthor` varchar(255) NOT NULL DEFAULT 'ghost',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique` (`prot1`,`prot2`) USING BTREE,
  KEY `prot2` (`prot2`) USING BTREE,
  KEY `prot1` (`prot1`),
  CONSTRAINT `fk_Interaction_prot1` FOREIGN KEY (`prot1`) REFERENCES `Gene` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_Interaction_prot2` FOREIGN KEY (`prot2`) REFERENCES `Gene` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `MaterialViewGeneRegulation`
--

DROP TABLE IF EXISTS `MaterialViewGeneRegulation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `MaterialViewGeneRegulation` (
  `gene` char(40) NOT NULL,
  `regulation` int(11) NOT NULL DEFAULT '0',
  UNIQUE KEY `gene` (`gene`,`regulation`),
  KEY `regulation` (`regulation`) USING BTREE,
  CONSTRAINT `fk_MaterialViewGeneRegulation_gene` FOREIGN KEY (`gene`) REFERENCES `Gene` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_MaterialViewGeneRegulation_regulation` FOREIGN KEY (`regulation`) REFERENCES `Regulation` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `MetaData`
--

DROP TABLE IF EXISTS `MetaData`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `MetaData` (
  `className` varchar(255) NOT NULL,
  `scheme` json NOT NULL,
  PRIMARY KEY (`className`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `MetaData`
--

LOCK TABLES `MetaData` WRITE;
/*!40000 ALTER TABLE `MetaData` DISABLE KEYS */;
INSERT INTO `MetaData` VALUES ('Gene','[{\"path\": [\"id\"], \"type\": \"a\"}, {\"path\": [\"title\"], \"type\": \"a\"}, {\"path\": [\"description\"], \"type\": \"a\"}, {\"path\": [\"locus\"], \"type\": \"a\"}, {\"path\": [\"pI\"], \"type\": \"a\"}, {\"path\": [\"mw\"], \"type\": \"a\"}, {\"path\": [\"proteinLength\"], \"type\": \"a\"}, {\"path\": [\"geneLength\"], \"type\": \"a\"}, {\"path\": [\"function\"], \"type\": \"a\"}, {\"path\": [\"product\"], \"type\": \"a\"}, {\"path\": [\"ec\"], \"type\": \"a\"}, {\"path\": [\"synonyms\"], \"type\": \"a\"}, {\"path\": [\"uniProt\"], \"type\": \"a\"}, {\"path\": [\"essential\"], \"type\": \"a\"}, {\"path\": [\"outlinks\", \"geneBank\"], \"type\": \"a\"}, {\"path\": [\"genomicContext\"], \"type\": \"a\", \"default\": \"[[this]]\"}, {\"path\": [\"categories\"], \"type\": \"a\", \"default\": \"[[this]]\"}, {\"path\": [\"regulons\"], \"type\": \"a\", \"default\": \"[[this]]\"}, {\"path\": [\"Gene\", \"Coordinates\"], \"type\": \"a\"}, {\"path\": [\"Gene\", \"Phenotypes of a mutant\"], \"type\": \"b\"}, {\"path\": [\"Gene\", \"additional information\"], \"type\": \"b\"}, {\"path\": [\"RNA\", \"Catalyzed reaction/ biological activity\"], \"type\": \"b\"}, {\"path\": [\"RNA\", \"additional information\"], \"type\": \"b\"}, {\"path\": [\"The protein\", \"Catalyzed reaction/ biological activity\"], \"type\": \"b\"}, {\"path\": [\"The protein\", \"Protein family\"], \"type\": \"b\"}, {\"path\": [\"The protein\", \"Paralogous protein(s)\"], \"type\": \"b\", \"default\": [\"[[this]]\"]}, {\"path\": [\"The protein\", \"Similar proteins in <i>B. subtilis</i> subsp. 168\"], \"type\": \"b\"}, {\"path\": [\"The protein\", \"Kinetic information\"], \"type\": \"b\"}, {\"path\": [\"The protein\", \"Structure\"], \"type\": \"b\"}, {\"path\": [\"The protein\", \"Domain\"], \"type\": \"b\"}, {\"path\": [\"The protein\", \"Modification\"], \"type\": \"b\"}, {\"path\": [\"The protein\", \"Cofactors\"], \"type\": \"b\"}, {\"path\": [\"The protein\", \"Effectors of protein activity\"], \"type\": \"b\"}, {\"path\": [\"The protein\", \"Localization\"], \"type\": \"b\"}, {\"path\": [\"The protein\", \"additional information\"], \"type\": \"b\"}, {\"path\": [\"Expression and Regulation\", \"Operons\"], \"type\": \"a\", \"default\": [\"[[this]]\"]}, {\"path\": [\"Expression and Regulation\", \"Other regulations\"], \"type\": \"a\", \"default\": [\"[[this]]\"]}, {\"path\": [\"Expression and Regulation\", \"additional information\"], \"type\": \"b\"}, {\"path\": [\"Biological materials\", \"Mutant\"], \"type\": \"b\"}, {\"path\": [\"Biological materials\", \"Expression vector\"], \"type\": \"b\"}, {\"path\": [\"Biological materials\", \"lacZ fusion\"], \"type\": \"b\"}, {\"path\": [\"Biological materials\", \"GFP fusion\"], \"type\": \"b\"}, {\"path\": [\"Biological materials\", \"two-hybrid system\"], \"type\": \"b\"}, {\"path\": [\"Biological materials\", \"FLAG-tag construct\"], \"type\": \"b\"}, {\"path\": [\"Biological materials\", \"Antibody\"], \"type\": \"b\"}, {\"path\": [\"strain\"], \"type\": \"b\"}, {\"path\": [\"Labs working on this gene/protein\"], \"type\": \"b\"}, {\"path\": [\"References\"], \"type\": \"b\", \"default\": [\"<pubmed></pubmed>\"]}, {\"path\": [\"References\", \"Reviews\"], \"type\": \"b\", \"default\": [\"<pubmed></pubmed>\"]}, {\"path\": [\"References\", \"Research papers\"], \"type\": \"b\", \"default\": [\"<pubmed></pubmed>\"]}, {\"path\": [\"count\"], \"type\": \"a\"}, {\"path\": [\"lastUpdate\"], \"type\": \"a\"}, {\"path\": [\"lastAuthor\"], \"type\": \"a\"}]'),('Operon','[{\"path\": [\"id\"], \"type\": \"a\"}, {\"path\": [\"title\"], \"type\": \"a\"}, {\"path\": [\"genes\"], \"type\": \"a\"}, {\"path\": [\"description\"], \"type\": \"a\"}, {\"path\": [\"regulation\"], \"type\": \"b\"}, {\"path\": [\"additional information\"], \"type\": \"b\"}, {\"path\": [\"count\"], \"type\": \"a\"}, {\"path\": [\"lastUpdate\"], \"type\": \"a\"}, {\"path\": [\"lastAuthor\"], \"type\": \"a\"}]');
/*!40000 ALTER TABLE `MetaData` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Metabolite`
--

DROP TABLE IF EXISTS `Metabolite`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Metabolite` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `synonym` varchar(255) DEFAULT NULL,
  `pubchem` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `title_UNIQUE` (`title`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `OmicsData_gene`
--

DROP TABLE IF EXISTS `OmicsData_gene`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `OmicsData_gene` (
  `gene` char(40) NOT NULL,
  `dataSet` int(11) NOT NULL,
  `value` double DEFAULT NULL,
  UNIQUE KEY `unique` (`gene`,`dataSet`) USING BTREE,
  KEY `gene` (`gene`),
  KEY `dataSet` (`dataSet`),
  CONSTRAINT `fk_omics_dataset` FOREIGN KEY (`dataSet`) REFERENCES `DataSet` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_omics_gene` FOREIGN KEY (`gene`) REFERENCES `Gene` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `OmicsData_position`
--

DROP TABLE IF EXISTS `OmicsData_position`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `OmicsData_position` (
  `position` int(11) NOT NULL,
  `strand` int(1) NOT NULL,
  `dataSet` int(11) NOT NULL,
  `value` double DEFAULT NULL,
  UNIQUE KEY `unique` (`position`,`dataSet`,`strand`) USING BTREE,
  KEY `strand` (`strand`),
  KEY `position` (`position`),
  KEY `dataSet` (`dataSet`),
  CONSTRAINT `fk_omics_pos_dataset` FOREIGN KEY (`dataSet`) REFERENCES `DataSet` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Operon`
--

DROP TABLE IF EXISTS `Operon`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Operon` (
  `id` char(40) NOT NULL,
  `title` text,
  `data` json DEFAULT NULL,
  `_genes` longtext,
  `lastUpdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `lastAuthor` varchar(255) NOT NULL DEFAULT 'ghost',
  `count` int(11) NOT NULL DEFAULT '0',
  `hash` char(40) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `hash` (`hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `Operon_after_update` AFTER UPDATE ON `Operon` FOR EACH ROW begin
	if @triggerEnabled then
		delete MaterialViewGeneRegulation from MaterialViewGeneRegulation join Regulation on Regulation.id = MaterialViewGeneRegulation.regulation where Regulation.regulated = concat("{operon|", new.id, "}");
		insert ignore into MaterialViewGeneRegulation (gene, regulation) select gene, id from ViewGeneOperon join Regulation on Regulation.regulated = concat("{operon|", ViewGeneOperon.operon, "}") where operon = new.id;
	end if;
end */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `Operon_after_delete` AFTER DELETE ON `Operon` FOR EACH ROW if @triggerEnabled THEN
delete MaterialViewGeneRegulation from MaterialViewGeneRegulation join Regulation on Regulation.id = MaterialViewGeneRegulation.regulation where Regulation.regulated like concat("{operon|", old.id, "}");
end IF */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `ParalogousProtein`
--

DROP TABLE IF EXISTS `ParalogousProtein`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ParalogousProtein` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `prot1` char(40) DEFAULT NULL,
  `prot2` char(40) DEFAULT NULL,
  `data` mediumtext,
  `strain` text,
  `lastUpdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `lastAuthor` varchar(255) NOT NULL DEFAULT 'ghost',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique` (`prot1`,`prot2`) USING BTREE,
  KEY `prot1` (`prot1`) USING BTREE,
  KEY `prot2` (`prot2`) USING BTREE,
  CONSTRAINT `fk_ParalogousProtein_prot1` FOREIGN KEY (`prot1`) REFERENCES `Gene` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_ParalogousProtein_prot2` FOREIGN KEY (`prot2`) REFERENCES `Gene` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Pathway`
--

DROP TABLE IF EXISTS `Pathway`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Pathway` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `map` longtext,
  PRIMARY KEY (`id`),
  UNIQUE KEY `title` (`title`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Pathway`
--

LOCK TABLES `Pathway` WRITE;
/*!40000 ALTER TABLE `Pathway` DISABLE KEYS */;
INSERT INTO `Pathway` VALUES (1, "untitled pathway", NULL);
/*!40000 ALTER TABLE `Pathway` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Pubmed`
--

DROP TABLE IF EXISTS `Pubmed`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Pubmed` (
  `id` int(11) NOT NULL DEFAULT '0',
  `report` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Reaction`
--

DROP TABLE IF EXISTS `Reaction`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Reaction` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reversible` int(1) DEFAULT NULL,
  `equation` text,
  `comment` text,
  `lastAuthor` varchar(255) NOT NULL DEFAULT 'ghost',
  `lastUpdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `KEGG` varchar(20) DEFAULT NULL,
  `EC` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `KEGG` (`KEGG`),
  KEY `EC` (`EC`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ReactionCatalyst`
--

DROP TABLE IF EXISTS `ReactionCatalyst`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ReactionCatalyst` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reaction` int(11) NOT NULL,
  `catalyst` varchar(255) DEFAULT NULL,
  `modification` varchar(45) DEFAULT NULL,
  `position` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique` (`reaction`,`catalyst`,`modification`,`position`) USING BTREE,
  KEY `reaction` (`reaction`) USING BTREE,
  KEY `catalyst` (`catalyst`),
  CONSTRAINT `fk_ReactionCatalyst_reaction` FOREIGN KEY (`reaction`) REFERENCES `Reaction` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ReactionMetabolite`
--

DROP TABLE IF EXISTS `ReactionMetabolite`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ReactionMetabolite` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reaction` int(11) NOT NULL,
  `coefficient` int(11) NOT NULL,
  `metabolite` int(11) NOT NULL,
  `side` varchar(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique` (`reaction`,`metabolite`,`side`) USING BTREE,
  KEY `reaction` (`reaction`) USING BTREE,
  KEY `metabolite` (`metabolite`) USING BTREE,
  CONSTRAINT `fk_ReactionMetabolite_1` FOREIGN KEY (`metabolite`) REFERENCES `Metabolite` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_ReactionMetabolite_2` FOREIGN KEY (`reaction`) REFERENCES `Reaction` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ReactionPathway`
--

DROP TABLE IF EXISTS `ReactionPathway`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ReactionPathway` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reaction` int(11) NOT NULL,
  `pathway` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique` (`reaction`,`pathway`),
  KEY `reaction` (`reaction`),
  KEY `pathway` (`pathway`),
  CONSTRAINT `fk_ReactionPathway_1` FOREIGN KEY (`pathway`) REFERENCES `Pathway` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_ReactionPathway_2` FOREIGN KEY (`reaction`) REFERENCES `Reaction` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ReactionPathway`
--

LOCK TABLES `ReactionPathway` WRITE;
/*!40000 ALTER TABLE `ReactionPathway` DISABLE KEYS */;
/*!40000 ALTER TABLE `ReactionPathway` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Regulation`
--

DROP TABLE IF EXISTS `Regulation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Regulation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `regulator` varchar(255) DEFAULT NULL,
  `regulated` varchar(255) DEFAULT NULL,
  `mode` varchar(255) DEFAULT NULL,
  `description` text,
  `lastAuthor` varchar(255) NOT NULL DEFAULT 'ghost',
  `lastUpdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique` (`regulator`,`regulated`) USING BTREE,
  KEY `regulator` (`regulator`),
  KEY `regulated` (`regulated`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `Regulation_after_insert` AFTER INSERT ON `Regulation` FOR EACH ROW begin
	if new.regulated like "{operon|%}" and @triggerEnabled then
		insert into MaterialViewGeneRegulation (gene, regulation) select gene, new.id from ViewGeneOperon where new.regulated like concat("{operon|", operon, "}");
	end if;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `Regulon`
--

DROP TABLE IF EXISTS `Regulon`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Regulon` (
  `id` varchar(255) NOT NULL,
  `data` text,
  `lastUpdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `lastAuthor` varchar(255) NOT NULL DEFAULT 'ghost',
  `count` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Sequence`
--

DROP TABLE IF EXISTS `Sequence`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Sequence` (
  `gene` char(40) NOT NULL,
  `dna` mediumtext,
  `aminos` mediumtext,
  `strain` text NOT NULL,
  PRIMARY KEY (`gene`),
  CONSTRAINT `fk_Sequence_gene` FOREIGN KEY (`gene`) REFERENCES `Gene` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Statistics`
--

DROP TABLE IF EXISTS `Statistics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Statistics` (
  `item` varchar(255) NOT NULL,
  `count` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`item`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Statistics`
--

LOCK TABLES `Statistics` WRITE;
/*!40000 ALTER TABLE `Statistics` DISABLE KEYS */;
INSERT INTO `Statistics` VALUES ('categoryExport',0),('categoryIndex',0),('expressionBrowser',0),('geneCategoryExport',0),('geneExport',0),('genomeBrowser',0),('index',0),('interactionBrowser',0),('interactionExport',0),('operonExport',0),('pathwayBrowser',0),('regulationBrowser',0),('regulationExport',0),('regulonIndex',0),('statistics',0);
/*!40000 ALTER TABLE `Statistics` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `User`
--

DROP TABLE IF EXISTS `User`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `User` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varbinary(255) NOT NULL,
  `realName` varchar(255) DEFAULT NULL,
  `password` varbinary(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `registration` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `privilege` int(11) NOT NULL DEFAULT '1',
  `token` binary(32) NOT NULL,
  `description` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `UserInvitation`
--

DROP TABLE IF EXISTS `UserInvitation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `UserInvitation` (
  `token` binary(32) NOT NULL,
  `email` varchar(255) NOT NULL,
  `expired` int(1) DEFAULT NULL,
  `type` enum('admin','normal') NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `fromWhom` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`email`),
  UNIQUE KEY `token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Temporary table structure for view `ViewGeneOperon`
--

DROP TABLE IF EXISTS `ViewGeneOperon`;
/*!50001 DROP VIEW IF EXISTS `ViewGeneOperon`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `ViewGeneOperon` AS SELECT 
 1 AS `operon`,
 1 AS `gene`*/;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `Wiki`
--

DROP TABLE IF EXISTS `Wiki`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Wiki` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `article` longtext,
  `count` int(11) NOT NULL DEFAULT '0',
  `lastAuthor` varchar(255) NOT NULL DEFAULT 'ghost',
  `lastUpdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `title_UNIQUE` (`title`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Final view structure for view `ViewGeneOperon`
--

/*!50001 DROP VIEW IF EXISTS `ViewGeneOperon`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=MERGE */
/*!50013 DEFINER=`ListiWikiAdmin`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `ViewGeneOperon` AS select `Operon`.`id` AS `operon`,`Gene`.`id` AS `gene` from (`Operon` join `Gene` on((`Operon`.`_genes` like concat('%',`Gene`.`id`,'%')))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2018-10-17 17:35:08

<?php
/*
    This file is part of EnvoiMoinsCher's shipping plugin for Prestashop.

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
/**
 * Boxtale_Envoimoinscher : installer.
 * 
 * @category    Boxtale
 * @package     Boxtale_Envoimoinscher
 * @copyright   Copyright (c) 2011 EnvoiMoinsCher.com
 * @author 	    EnvoiMoinsCher (http://www.envoimoinscher.com)
 * @license     http://www.gnu.org/licenses  GNU Lesser General Public License (LGPL)
 */
$installer = $this;

$installer->run("
DROP TABLE IF EXISTS {$installer->getTable('envoimoinscher/emc_documents')};
DROP TABLE IF EXISTS {$installer->getTable('envoimoinscher/emc_orders')};
DROP TABLE IF EXISTS {$installer->getTable('envoimoinscher/emc_orders_tmp')};
DROP TABLE IF EXISTS {$installer->getTable('envoimoinscher/emc_points')};
DROP TABLE IF EXISTS {$installer->getTable('envoimoinscher/emc_tracking')};
DROP TABLE IF EXISTS {$installer->getTable('envoimoinscher/emc_services_has_zones')};
DROP TABLE IF EXISTS {$installer->getTable('envoimoinscher/emc_services')};
DROP TABLE IF EXISTS {$installer->getTable('envoimoinscher/emc_zones')}; 
DROP TABLE IF EXISTS {$installer->getTable('envoimoinscher/emc_categories')};
DROP TABLE IF EXISTS {$installer->getTable('envoimoinscher/emc_dimensions')};
DROP TABLE IF EXISTS {$installer->getTable('envoimoinscher/emc_operators')};
DELETE FROM {$this->getTable('core/config_data')} WHERE path LIKE 'carriers/envoimoinscher/%';
DELETE FROM {$this->getTable('core/resource')} WHERE code = 'envoimoinscher_setup';
");

$query = "
CREATE TABLE IF NOT EXISTS {$installer->getTable('envoimoinscher/emc_categories')} (
  id_eca int(11) NOT NULL,
  emc_categories_id_eca int(11) NOT NULL,
  name_eca varchar(100) NOT NULL,
  PRIMARY KEY (`id_eca`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
 

INSERT INTO {$installer->getTable('envoimoinscher/emc_categories')} (id_eca, emc_categories_id_eca, name_eca) VALUES
(10000, 0, 'Livres et documents'),
(10100, 10000, 'Documents sans valeur commerciale'),
(10120, 10000, 'Journaux'),
(10130, 10000, 'Magazines, revues'),
(10140, 10000, 'Manuels techniques'),
(10150, 10000, 'Livres'),
(10160, 10000, 'Passeports'),
(10170, 10000, 'Billets d\'avion'),
(10180, 10000, 'Radiographies'),
(10190, 10000, 'Photographies'),
(10200, 10000, 'Courrier interne d\'entreprise'),
(10210, 10000, 'Propositions commerciales'),
(10220, 10000, 'Documents publicitaires'),
(10230, 10000, 'Catalogues, rapports annuels'),
(10240, 10000, 'Listings informatiques'),
(10250, 10000, 'Plans, dessins'),
(10260, 10000, 'Documents d\'impression'),
(10280, 10000, 'Patrons'),
(10290, 10000, 'Etiquettes, autocollants'),
(10300, 10000, 'Documents d\'appels d\'offres'),
(20000, 0, 'Alimentation et matières périssables'),
(20100, 20000, 'Denrées alimentaires non périssables'),
(20102, 20000, 'Produits frais et périssables'),
(20103, 20000, 'Produits réfrigérés'),
(20105, 20000, 'Produits surgelés'),
(20110, 20000, 'Boissons non alcoolisées'),
(20120, 20000, 'Boissons alcoolisées'),
(20130, 20000, 'Plantes, fleurs, semences'),
(30000, 0, 'Produits'),
(30100, 30000, 'Cosmétiques, bien-être'),
(30200, 30000, 'Pharmacie, médicaments'),
(30300, 30000, 'Chimie, droguerie, produits d\'entretien'),
(50190, 30000, 'Tabac'),
(50200, 30000, 'Parfums'),
(40000, 0, 'Habillement et accessoires'),
(40100, 40000, 'Chaussures'),
(40110, 40000, 'Tissus, vêtements neufs'),
(40120, 40000, 'Vêtements usagés'),
(40125, 40000, 'Accessoires vestimentaires, de mode'),
(40130, 40000, 'Cuirs, peaux, maroquinerie'),
(40150, 40000, 'Bijoux fantaisie'),
(50160, 40000, 'Bijoux, objets précieux'),
(50000, 0, 'Appareils et matériels'),
(50100, 50000, 'Matériel médical'),
(50110, 50000, 'Informatique, High tech, téléphonie fixe'),
(50113, 50000, 'Téléphonie mobile et accessoires'),
(50114, 50000, 'Téléviseurs, écrans d\'ordinateur'),
(50120, 50000, 'Autres appareils et matériels'),
(50130, 50000, 'Supports numériques, CD, DVD'),
(50140, 50000, 'Pièces de rechange et accessoires (auto)'),
(50150, 50000, 'Pièces de rechange et accessoires (autres)'),
(50170, 50000, 'Montres, horlogerie (hors bijoux)'),
(50330, 50000, 'Articles de camping, de pêche'),
(50350, 50000, 'Articles de sport (hors vêtement)'),
(50360, 50000, 'Instruments de musique et accessoires'),
(50380, 50000, 'Matériel de chauffage, chaudronnerie'),
(50390, 50000, 'Matériel de labo, optique, de mesure'),
(50395, 50000, 'Matériel électrique, transfo., câbles'),
(50400, 50000, 'Fournitures de bureau, papeterie, recharges'),
(50420, 50000, 'Moteurs'),
(50430, 50000, 'Motos, scooters'),
(50440, 50000, 'Vélos, cycles sans moteur'),
(50450, 50000, 'Outillage, outils, bricolage'),
(50490, 50000, 'Plomberie, tubes plastiques'),
(50500, 50000, 'Quincaillerie, robinetterie, serrurerie'),
(60000, 0, 'Mobilier et décoration'),
(60100, 60000, 'Mobilier d\'habitation'),
(60102, 60000, 'Mobilier de bureau'),
(60105, 60000, 'Mobilier démonté sous emballage'),
(60108, 60000, 'Mobilier ancien (antiquité)'),
(60110, 60000, 'Electroménager '),
(60112, 60000, 'Petit électroménager, petits appareils ménagers'),
(60120, 60000, 'Objets ou tableaux cotés, de collection, miroirs, vitres'),
(60122, 60000, 'Objets et tableaux courants '),
(60124, 60000, 'Lampes, luminaire'),
(60126, 60000, 'Tapis'),
(60128, 60000, 'Toiles, rideaux, draps'),
(60129, 60000, 'Sanitaires, verres, cristallerie, bibelots'),
(60130, 60000, 'Autres objets fragiles et sculptures'),
(70000, 0, 'Effets personnels, cadeaux'),
(50180, 70000, 'Cadeaux, cadeaux entreprise'),
(70100, 70000, 'Bagages, valises, malles'),
(70200, 70000, 'Petit déménagement, cartons, effets personnels');



CREATE TABLE IF NOT EXISTS {$installer->getTable('envoimoinscher/emc_dimensions')} (
  id_ed int(3) NOT NULL AUTO_INCREMENT,
  length_ed int(3) NOT NULL,
  width_ed int(3) NOT NULL,
  height_ed int(3) NOT NULL,
  weight_from_ed int(3) NOT NULL,
  weight_ed int(3) NOT NULL,
  PRIMARY KEY (`id_ed`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=14 ;

INSERT INTO {$installer->getTable('envoimoinscher/emc_dimensions')} (id_ed, length_ed, width_ed, height_ed, weight_from_ed, weight_ed) VALUES
(1, 18, 18, 18, 0, 1),
(2, 22, 22, 22, 1, 2),
(3, 26, 26, 26, 2, 3),
(4, 28, 28, 28, 3, 4),
(5, 31, 31, 31, 4, 5),
(6, 33, 33, 33, 5, 6),
(7, 34, 34, 34, 6, 7),
(8, 36, 36, 36, 7, 8),
(9, 37, 37, 37, 8, 9),
(10, 39, 39, 39, 9, 10),
(11, 44, 44, 44, 10, 15),
(12, 56, 56, 56, 15, 20),
(13, 57, 57, 57, 20, 50);


CREATE TABLE IF NOT EXISTS {$installer->getTable('envoimoinscher/emc_documents')} (
  id_ed int(11) NOT NULL AUTO_INCREMENT,
  sales_flat_order_entity_id int(10) unsigned NOT NULL,
  link_ed varchar(255) NOT NULL,
  type_ed enum('label','proforma') NOT NULL,
  state_ed int(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_ed`),
  KEY sales_flat_order_entity_id (sales_flat_order_entity_id)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS {$installer->getTable('envoimoinscher/emc_operators')} (
  id_eo int(2) NOT NULL AUTO_INCREMENT,
  name_eo varchar(100) NOT NULL,
  code_eo char(4) NOT NULL,
  mandatory_eo text NOT NULL,
  PRIMARY KEY (id_eo)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=15;


INSERT INTO {$installer->getTable('envoimoinscher/emc_operators')} (id_eo, name_eo, code_eo, mandatory_eo) VALUES
(1, 'DHL Freight', 'DHLF', ''),
(2, 'Distribike', 'DTBK', ''),
(3, 'FedEx', 'FEDX', 'a:2:{i:0;s:17:\"disponibilite.HDE\";i:1;s:17:\"disponibilite.HLE\";}'),
(4, 'Guisnel', 'GUIN', ''),
(5, 'Premier Air Courier', 'PACO', ''),
(6, 'Sernam', 'SERN', ''),
(7, 'Sodexi', 'SODX', ''),
(8, 'Relais Colis', 'SOGP', 'a:1:{i:0;s:17:\"depot.pointrelais\";}'),
(9, 'TNT', 'TNTE', 'a:2:{i:0;s:17:\"disponibilite.HDE\";i:1;s:17:\"disponibilite.HLE\";}'),
(10, 'UPS', 'UPSE', 'a:2:{i:0;s:17:\"disponibilite.HDE\";i:1;s:17:\"disponibilite.HLE\";}\"'),
(11, 'Chronopost', 'CHRP', 'a:1:{i:0;s:17:\"depot.pointrelais\";}'),
(12, 'Aramex', 'ARAM', ''),
(13, 'Saga Express', 'SAGA', ''),
(14, 'Adrexo', 'ADRX', 'a:2:{i:0;s:17:\"disponibilite.HDE\";i:1;s:17:\"disponibilite.HLE\";}'),
(15, 'Evertrans', 'EVER', ''),
(16, 'Kordex', 'KODX', ''),
(17, 'Top Chrono', 'TOPC', ''),
(18, 'SLS-GCI', 'SLSP', ''),
(19, 'Agediss', 'AGED', ''),
(20, 'Mondial Relay', 'MONR', ''),
(21, 'Low Cost Express', 'LOCO', ''),
(22, 'DHL Express', 'DHLE', ''),
(23, 'Coliposte', 'POFR', ''),
(24, 'Colis Privé', 'COPR', '');

CREATE TABLE IF NOT EXISTS {$installer->getTable('envoimoinscher/emc_orders')} (
  sales_flat_order_entity_id int(10) unsigned NOT NULL,
  emc_operators_code_eo char(4) NOT NULL,
  price_ht_eor float NOT NULL,
  price_ttc_eor float NOT NULL,
  ref_emc_eor char(20) NOT NULL,
  service_eor varchar(20) NOT NULL,
  date_order_eor datetime NOT NULL,
  ref_ope_eor varchar(20) NOT NULL,
  info_eor varchar(20) NOT NULL,
  date_collect_eor datetime NOT NULL,
  date_del_eor datetime NOT NULL,
  date_del_real_eor datetime NOT NULL,
  parcels_eor INT(3) NOT NULL,
  base_url_eor VARCHAR(255) NOT NULL,
  code_eor VARCHAR(255) NOT NULL,
  PRIMARY KEY (sales_flat_order_entity_id),
  KEY emc_operators_code_eo(emc_operators_code_eo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
 
CREATE TABLE IF NOT EXISTS {$installer->getTable('envoimoinscher/emc_orders_errors')} (
  sales_flat_order_entity_id int(10) unsigned NOT NULL,
  errors_eoe text NOT NULL,
  PRIMARY KEY (sales_flat_order_entity_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
 
CREATE TABLE IF NOT EXISTS {$installer->getTable('envoimoinscher/emc_orders_parcels')} (
  sales_flat_order_entity_id int(10) unsigned NOT NULL,
  number_eop INT(10) unsigned NOT NULL,
  weight_eop DECIMAL(5,2) NOT NULL,
  length_eop INT(3) NOT NULL,
  width_eop INT(3) NOT NULL,
  height_eop INT(3) NOT NULL,
  PRIMARY KEY (sales_flat_order_entity_id, number_eop)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
 
CREATE TABLE IF NOT EXISTS {$installer->getTable('envoimoinscher/emc_orders_tmp')} (
  emc_orders_entity_id int(10) unsigned NOT NULL,
  data_eot text NOT NULL,
  date_eot datetime NOT NULL,
  errors_eot text NOT NULL,
  PRIMARY KEY (emc_orders_entity_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS {$installer->getTable('envoimoinscher/emc_points')} (
  sales_flat_order_entity_id int(10) unsigned NOT NULL,
  point_ep varchar(10) NOT NULL,
  emc_operators_code_eo char(4) NOT NULL,
  PRIMARY KEY (sales_flat_order_entity_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS {$installer->getTable('envoimoinscher/emc_services')} (
  id_es int(3) NOT NULL AUTO_INCREMENT,
  code_es varchar(40) NOT NULL,
  emc_operators_code_eo char(4) NOT NULL,
  label_es varchar(100) NOT NULL,
  desc_es varchar(150) NOT NULL,
  desc_store_es varchar(150) NOT NULL,
  label_store_es varchar(100) NOT NULL,
  price_type_es int(1) NOT NULL,
  is_parcel_point_es int(1) NOT NULL,
  is_parcel_dropoff_point_es int(1) NOT NULL,
  zones_es int(1) NOT NULL COMMENT '1 - International, 2 - France,  3 - both',
  family_es int(1) NOT NULL,
  tracking_es VARCHAR(155) NOT NULL,
  PRIMARY KEY (id_es),
  KEY emc_operators_code_eo (emc_operators_code_eo),
  KEY code_es (code_es)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=46;

INSERT INTO {$installer->getTable('envoimoinscher/emc_services')} (`id_es`, `code_es`, `emc_operators_code_eo`, `label_es`, `desc_es`, `desc_store_es`, `label_store_es`, `price_type_es`, `is_parcel_point_es`, `is_parcel_dropoff_point_es`, `zones_es`, `family_es`, `tracking_es`) VALUES
(1, 'RelaisColis', 'SOGP', 'Relais Colis eco', 'Dépôt en Relais Colis - Livraison en Relais Colis en 10 jours, en France', 'Livraison en Relais Colis en 10 jours', 'Relais Colis®', 0, 1, 1, 2, 1, ''),
(2, 'Standard', 'UPSE', 'UPS Standard', 'Livraison à domicile en 24h à 72h (avant 19h), en France et dans les pays européens', 'Livraison à domicile en 24h à 72h (avant 19h)', 'UPS Standard', 0, 0, 0, 2, 2, ''),
(3, 'ExpressSaver', 'UPSE', 'UPS Express Saver', 'Livraison à domicile en 72h  (avant 19h), à l\'international  (hors délai de douanes)', 'Livraison à domicile en 72h (avant 19h, hors délai de douanes)', 'UPS Express Saver', 0, 0, 0, 1, 2, ''),
(4, 'InternationalEconomy', 'FEDX', 'FedEx International Economy', 'Livraison à domicile en 5 jours à l''international (hors délai de douanes)', 'Livraison à domicile en 5 jours (hors délai de douanes)', 'FedEx International Economy', 0, 0, 0, 1, 2, ''),
(5,	'InternationalPriorityCC',	'FEDX',	'FedEx International Priority',	'Livraison express à domicile, en 24h à 48h (hors délai de douanes)',	'Livraison express à domicile en 24h à 48h (hors délai de douanes)',	'FedEx International Priority'	, 0,	0, 0,	1,	2,	''),
(6, 'ExpressNational', 'TNTE', '13:00 Express', 'Livraison express à domicile le lendemain (avant 13h), en France', 'Livraison express à domicile le lendemain (avant 13h)', '13:00 Express', 1, 0, 0, 2, 2, ''),
(7, 'Chrono13', 'CHRP', 'Chrono13', 'Dépôt en bureau de poste - Livraison express à domicile, le lendemain (avant 13h), en France. Dépôt en bureau de poste si la livraison rate.', 'Livraison express à domicile, le lendemain (avant 13h). Si la livraison rate, dépôt en bureau de poste', 'Chrono13', 0, 0, 0, 2, 1, ''),
(8, 'ChronoInternationalClassic', 'CHRP', 'Chrono Classic', 'Dépôt en bureau de poste - Livraison à domicile en 2 à 4 jours, à l''international (hors délai de douanes)', 'Livraison à domicile en 2 à 4 jours (hors délai de douanes)', 'Chrono Classic', 0, 0, 0, 1, 1, ''),
(9, 'CpourToi', 'MONR', 'C.pourToi®', 'Dépôt en point relais - Livraison en point relais en 3 à 5 jours, en France', 'Livraison en point relais en 3 à 5 jours', 'C.pourToi®', 0, 1, 1, 2, 1, ''),
(10, 'CpourToiEurope', 'MONR', 'C.pourToi® - Europe', 'Dépôt en point relais - Livraison en point relais en 4 à 6 jours, dans certains pays d''Europe', 'Livraison en point relais en 4 à 6 jours', 'C.pourToi®', 0, 1, 1, 3, 1, ''),
(11, 'EconomyExpressInternational', 'TNTE', 'Economy Express', 'Livraison à domicile en 2 à 5 jours, à l''international (hors délai de douanes)', 'Livraison à domicile en 2 à 5 jours (hors délai de douanes)', 'Economy Express', 0, 0, 0, 1, 2, ''),
(12, 'ExpressWorldwide', 'DHLE', 'DHL Express Worldwide', 'Livraison express à domicile en 24h à 72h, à l''international (hors délai de douanes)', 'Livraison express à domicile en 24h à 72h (hors délai de douanes)', 'DHL Express Worldwide', 0, 0, 0, 1, 2, ''),
(13, 'EASY', 'COPR', 'Colis Privé EASY', 'Livraison à domicile en 2 à 3 jours. En cas d''absence, 2nde présentation ou dépôt en relais. Offre sous conditions de volume.', 'Livraison à domicile en 2 à 3 jours. En cas d''absence, 2nde présentation ou dépôt en relais Kiala', 'Colis Privé EASY', 0, 0, 0, 2, 1, ''),
(14, 'Chrono18', 'CHRP', 'Chrono18', 'Dépôt en bureau de poste - Livraison express à domicile, le lendemain (avant 18h), en France. Dépôt en bureau de poste si la livraison rate.', 'Livraison express à domicile, le lendemain (avant 18h). Si la livraison rate, dépôt en bureau de poste', 'Chrono18', 0, 0, 0, 2, 1, ''),
(15, 'ColissimoAccess', 'POFR', 'La Poste Colissimo Access France', 'Délai indicatif de 48h en jours ouvrables pour les envois en France métropolitaine. Remise sans signature.', 'Livraison à domicile en 48h', 'La Poste Colissimo Access France. Remise sans signature.', 0, 0, 0, 2, 1, ''),
(16, 'ColissimoExpert', 'POFR', 'La Poste Colissimo Expert France', 'Délai indicatif de 48h en jours ouvrables pour les envois en France métropolitaine. Remise contre signature.', 'Livraison à domicile en 48h', 'La Poste Colissimo Expert France. Remise contre signature.', 0, 0, 0, 2, 1, ''),
(17, 'ExpressStandard', 'SODX', 'Express Standard', 'Livraison à domicile en 2 à 3 jours, en France', 'Livraison à domicile en 2 à 3 jours', 'Express Standard', 0, 0, 0, 2, 2, ''),
(18, 'ExpressStandardInterColisMarch', 'SODX', 'Inter Express Standard', 'Livraison à domicile en 7 à 10 jours, à l''international (hors délai de douanes)', 'Livraison à domicile en 7 à 10 jours (hors délai de douanes)', 'Inter Express Standard', 0, 0, 0, 1, 2, ''),
(19, 'ExpressStandardInterPlisDSVC', 'SODX', 'Inter Express Standard doc', 'Livraison à domicile en 7 à 10 jours, à l''international (hors délai de douanes)', 'Livraison à domicile en 7 à 10 jours (hors délai de douanes)', 'Inter Express Standard doc', 0, 0, 0, 1, 2, ''),
(20, 'ChronoRelais', 'CHRP', 'Chrono Relais', 'Livraison en points relais Chronopost', 'Livraison en points relais Chronopost', 'Chrono Relais', 0, 1, 0, 2, 1, '');


CREATE TABLE IF NOT EXISTS {$installer->getTable('envoimoinscher/emc_services_has_zones')} (
  id_eshz int(7) NOT NULL AUTO_INCREMENT,
  emc_zones_id_ez int(1) NOT NULL,
  emc_services_id_es int(3) NOT NULL,
  value_from_eshz float NOT NULL,
  value_eshz float NOT NULL,
  price_eshz float NOT NULL,
  type_eshz int(1) NOT NULL,
  profitability_eshz int(1) NOT NULL,
  PRIMARY KEY (id_eshz),
  KEY emc_services_id_es (emc_services_id_es),
  KEY emc_zones_id_ez (emc_zones_id_ez)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS {$installer->getTable('envoimoinscher/emc_services_rules')} (
  id_esr int(7) NOT NULL AUTO_INCREMENT,
  emc_services_id_es int(3) NOT NULL,
  type_esr int(1) NOT NULL COMMENT '0 - amount, 1 - pourcent',
  from_esr decimal(7,2) NOT NULL,
  to_esr decimal(7,2) NOT NULL,
  value_esr decimal(7,2) NOT NULL,
  from_date_esr datetime NOT NULL,
  to_date_esr datetime NOT NULL,
  geo_esr int(1) NOT NULL COMMENT '0 - everywhere, 1 - only French orders, 2 - only internation orders',
  PRIMARY KEY (id_esr),
  KEY emc_services_has_zones_id_eshz (emc_services_id_es)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS {$installer->getTable('envoimoinscher/emc_tracking')} (
  id_et int(11) NOT NULL AUTO_INCREMENT,
  sales_flat_order_entity_id int(10) unsigned NOT NULL,
  state_et char(4) NOT NULL,
  date_et datetime NOT NULL,
  text_et text NOT NULL,
  localisation_et varchar(50) NOT NULL,
  PRIMARY KEY (id_et),
  KEY sales_flat_order_entity_id (sales_flat_order_entity_id)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS {$installer->getTable('envoimoinscher/emc_zones')} (
  id_ez int(1) NOT NULL AUTO_INCREMENT,
  code_ez char(4) NOT NULL,
  name_ez varchar(15) NOT NULL,
  PRIMARY KEY (id_ez)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

INSERT INTO {$installer->getTable('envoimoinscher/emc_zones')} (id_ez, code_ez, name_ez) VALUES
(1, 'INTE', 'International'),
(2, 'FRAN', 'France');

CREATE TABLE IF NOT EXISTS {$installer->getTable('envoimoinscher/emc_points_tmp')} (
sales_flat_quote_entity_id INT(10) UNSIGNED NOT NULL ,
point_ept VARCHAR(15) NOT NULL ,
PRIMARY KEY (sales_flat_quote_entity_id)
) ENGINE = InnoDB;

ALTER TABLE {$installer->getTable('envoimoinscher/emc_documents')}
  ADD CONSTRAINT emc_documents_ibfk_1 FOREIGN KEY (sales_flat_order_entity_id) REFERENCES {$installer->getTable('sales_flat_order')} (entity_id) ON DELETE CASCADE;
  
ALTER TABLE {$installer->getTable('envoimoinscher/emc_orders')}
  ADD CONSTRAINT emc_orders_ibfk_3 FOREIGN KEY (sales_flat_order_entity_id) REFERENCES {$installer->getTable('sales_flat_order')} (entity_id) ON DELETE CASCADE;

ALTER TABLE {$installer->getTable('envoimoinscher/emc_orders_tmp')}
  ADD CONSTRAINT emc_orders_tmp_ibfk_1 FOREIGN KEY (emc_orders_entity_id) REFERENCES {$installer->getTable('sales_flat_order')} (entity_id) ON DELETE CASCADE;

ALTER TABLE {$installer->getTable('envoimoinscher/emc_points')} ADD FOREIGN KEY ( sales_flat_order_entity_id ) REFERENCES {$installer->getTable('sales_flat_order')} (
entity_id) ON DELETE CASCADE;

ALTER TABLE {$installer->getTable('envoimoinscher/emc_tracking')} ADD FOREIGN KEY ( sales_flat_order_entity_id ) REFERENCES {$installer->getTable('sales_flat_order')} (
entity_id) ON DELETE CASCADE ;

ALTER TABLE {$installer->getTable('envoimoinscher/emc_services_has_zones')} ADD FOREIGN KEY ( emc_zones_id_ez ) REFERENCES {$installer->getTable('envoimoinscher/emc_zones')} (
id_ez) ON DELETE CASCADE ;

ALTER TABLE {$installer->getTable('envoimoinscher/emc_services_has_zones')} ADD FOREIGN KEY ( emc_services_id_es ) REFERENCES {$installer->getTable('envoimoinscher/emc_services')} (
id_es) ON DELETE CASCADE ;
";
if($installer->tableExists('sales/order_status'))
{
  $query = $query."  INSERT IGNORE INTO {$installer->getTable('sales/order_status')} (status, label) 
  VALUES ('traitement', 'commande en cours de traitement');";
} 
$installer->run($query);
$installer->endSetup();
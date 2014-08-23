<?php
/**
 * LaPoste_SoColissimoSimplicite
 *
 * @category  LaPoste
 * @package   LaPoste_SoColissimoSimplicite
 * @copyright Copyright (c) 2010 La Poste
 * @author    Smile (http://www.smile.fr)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
$installer = $this;
$installer->startSetup();

if (!$installer->tableExists($installer->getTable('laposte_socolissimosimplicite_transaction'))) {

    $installer->run(
        "-- DROP TABLE IF EXISTS {$this->getTable('laposte_socolissimosimplicite_transaction')};
        CREATE TABLE {$this->getTable('laposte_socolissimosimplicite_transaction')} (
            `transaction_id` bigint(16) unsigned NOT NULL auto_increment,
            `quote_id` int(10) unsigned NOT NULL default '0',
            `signature` varchar(40) NOT NULL default '',
            `created_at` datetime NOT NULL default '0000-00-00 00:00:00',
            PRIMARY KEY  (`transaction_id`),
            KEY `FK_laposte_socolissimosimplicite_transaction_QUOTE_ID` (`quote_id`),
            CONSTRAINT `FK_laposte_socolissimosimplicite_transaction_QUOTE_ID` FOREIGN KEY (`quote_id`) REFERENCES `{$this->getTable('sales_flat_quote')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT = 10000 COMMENT='colissimo transations';

        -- transaction_id doit contenir entre 5 et 16 caractères
        ALTER TABLE {$this->getTable('laposte_socolissimosimplicite_transaction')} AUTO_INCREMENT = 10000;"
    );
}

$installer->endSetup();
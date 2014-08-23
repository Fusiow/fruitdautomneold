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

if ($installer->tableExists($installer->getTable('laposte_socolissimosimplicite_transaction'))) {
    $installer->run(
        // transaction_id doit contenir entre 5 et 16 caractères
        "ALTER TABLE {$this->getTable('laposte_socolissimosimplicite_transaction')} AUTO_INCREMENT = 10000;"
    );
}

$installer->endSetup();

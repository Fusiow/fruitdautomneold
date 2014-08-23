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

$installer->run(
    "DELETE
    FROM {$installer->getTable('core_config_data')}
    WHERE path LIKE 'carriers/socolissimosimplicite/%';"
);

$installer->endSetup();

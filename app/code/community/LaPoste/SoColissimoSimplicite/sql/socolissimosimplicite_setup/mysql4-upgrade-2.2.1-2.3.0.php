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

$this->removeAttribute('order', 'soco_network_code');

$this->addAttribute(
    'order',
    'soco_network_code',
    array(
        'type'     => 'varchar',
        'label'    => 'Code réseau So Colissimo',
        'visible'  => true,
        'required' => false,
        'input'    => 'text',
    )
);

$installer->endSetup();

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

$this->removeAttribute('order', 'soco_product_code');
$this->removeAttribute('order', 'soco_shipping_instruction');
$this->removeAttribute('order', 'soco_door_code1');
$this->removeAttribute('order', 'soco_door_code2');
$this->removeAttribute('order', 'soco_interphone');
$this->removeAttribute('order', 'soco_relay_point_code');

$this->addAttribute(
    'order',
    'soco_product_code',
    array(
        'type'     => 'varchar',
        'label'    => 'Code produit So Colissimo',
        'visible'  => true,
        'required' => false,
        'input'    => 'text',
    )
);

$this->addAttribute(
    'order',
    'soco_shipping_instruction',
    array(
        'type'     => 'varchar',
        'label'    => 'Instructions de livraison So Colissimo',
        'visible'  => true,
        'required' => false,
        'input'    => 'text',
    )
);

$this->addAttribute(
    'order',
    'soco_door_code1',
    array(
        'type'     => 'varchar',
        'label'    => 'Code porte 1 So Colissimo',
        'visible'  => true,
        'required' => false,
        'input'    => 'text',
    )
);

$this->addAttribute(
    'order',
    'soco_door_code2',
    array(
        'type'     => 'varchar',
        'label'    => 'Code porte 2 So Colissimo',
        'visible'  => true,
        'required' => false,
        'input'    => 'text',
    )
);

$this->addAttribute(
    'order',
    'soco_interphone',
    array(
        'type'     => 'varchar',
        'label'    => 'Interphone So Colissimo',
        'visible'  => true,
        'required' => false,
        'input'    => 'text',
    )
);

$this->addAttribute(
    'order',
    'soco_relay_point_code',
    array(
        'type'     => 'varchar',
        'label'    => 'Code du point de retrait So Colissimo',
        'visible'  => true,
        'required' => false,
        'input'    => 'text',
    )
);

$this->addAttribute(
    'order',
    'soco_civility',
    array(
        'type'     => 'varchar',
        'label'    => 'Civilité',
        'visible'  => true,
        'required' => false,
        'input'    => 'text',
    )
);

$this->addAttribute(
    'order',
    'soco_phone_number',
    array(
        'type'     => 'varchar',
        'label'    => 'Numéro de portable',
        'visible'  => true,
        'required' => false,
        'input'    => 'text',
    )
);

$this->addAttribute(
    'order',
    'soco_email',
    array(
        'type'     => 'varchar',
        'label'    => 'E-mail du destinataire',
        'visible'  => true,
        'required' => false,
        'input'    => 'text',
    )
);

$installer->endSetup();

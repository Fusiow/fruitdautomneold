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
 * Boxtale_Envoimoinscher : uninstall fragment.
 * 
 * @category    Boxtale
 * @package     Boxtale_Envoimoinscher
 * @copyright   Copyright (c) 2011 EnvoiMoinsCher.com
 * @author 	    EnvoiMoinsCher (http://www.envoimoinscher.com)
 * @license     http://www.gnu.org/licenses  GNU Lesser General Public License (LGPL)
 */
$installer = $this;

$installer->startSetup();

$installer->run("
DROP TABLE  {$installer->getTable('envoimoinscher/emc_categories')};
DROP TABLE {$installer->getTable('envoimoinscher/emc_dimensions')};
DROP TABLE {$installer->getTable('envoimoinscher/emc_documents')};
DROP TABLE {$installer->getTable('envoimoinscher/emc_operators')};
DROP TABLE  {$installer->getTable('envoimoinscher/emc_orders')};
DROP TABLE {$installer->getTable('envoimoinscher/emc_orders_tmp')};
DROP TABLE {$installer->getTable('envoimoinscher/emc_points')};
DROP TABLE  {$installer->getTable('envoimoinscher/emc_services')};
DROP TABLE   {$installer->getTable('envoimoinscher/emc_services_has_zones')};
DROP TABLE  {$installer->getTable('envoimoinscher/emc_tracking')};
DROP TABLE {$installer->getTable('envoimoinscher/emc_zones')}; 
DELETE FROM {$this->getTable('core/config_data')} WHERE path LIKE 'carriers/envoimoinscher/%';
DELETE FROM {$this->getTable('core/resource')} WHERE code = 'envoimoinscher_setup';
");

$installer->endSetup();
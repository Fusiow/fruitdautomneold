<?php

class Magmi_DefaultAttributeItemProcessor extends Magmi_ItemProcessor
{
    protected $_basecols = array("store"=>"admin","type"=>"simple");
    protected $_baseattrs = array("status"=>1,"visibility"=>4,"page_layout"=>"","tax_class_id"=>"Taxable Goods");
    protected $_forcedefault = array("store"=>"admin");
    protected $_missingcols = array();
    protected $_missingattrs = array();

    /**
     * (non-PHPdoc)
     *
     * @see Magmi_Plugin::initialize()
     */
    public function initialize($params)
    {
        $this->registerAttributeHandler($this, array("attribute_code:.*"));
    }

    /**
     * (non-PHPdoc)
     *
     * @see Magmi_Plugin::getPluginInfo()
     */
    public function getPluginInfo()
    {
        return array("name"=>"Standard Attribute Import","author"=>"Dweeves","version"=>"1.0.6");
    }

    /**
     * callback for column list processing
     *
     * @param unknown $cols            
     */
    public function processColumnList(&$cols)
    {
        // This will not change the column list
        // this will only log the list of columns that will be added to newly created items
        $this->_missingcols = array_diff(array_keys($this->_basecols), $cols);
        $this->_missingattrs = array_diff(array_keys($this->_baseattrs), $cols);
        $m = $this->getMode();
        if ($m == "create" || $m == "xcreate")
        {
            $cols = array_merge($cols, $this->_missingcols, $this->_missingattrs);
            $this->log(
                "Newly created items will have default values for columns:" .
                     implode(",", array_merge($this->_missingcols, $this->_missingattrs)), "startup");
        }
    }

    /**
     * initializes extra columns if needed
     *
     * @param unknown $item            
     */
    public function initializeBaseCols(&$item)
    {
        foreach ($this->_missingcols as $missing)
        {
            $item[$missing] = $this->_basecols[$missing];
        }
    }

    /**
     * Initialized base attributes to retrieve from a given item description
     *
     * @param unknown $item            
     */
    public function initializeBaseAttrs(&$item)
    {
        foreach ($this->_missingattrs as $missing)
        {
            $item[$missing] = $this->_baseattrs[$missing];
        }
    }

    /**
     * (non-PHPdoc)
     *
     * @see Magmi_ItemProcessor::processItemAfterId()
     */
    public function processItemAfterId(&$item, $params = null)
    {
        if ($params["new"] == true)
        {
            $this->initializeBaseCols($item);
            $this->initializeBaseAttrs($item);
            //force url key for new items for magento > 1.7.x
            if($this->getMagentoVersion()>"1.7.x")
            {
               $item["url_key"]=Slugger::slug($item["name"]);
            } 
        
        }
        // forcing default values for mandatory processing columns
        foreach ($this->_forcedefault as $k => $v)
        {
            if (isset($item[$k]) && trim($item[$k]) == "")
            {
                $item[$k] = $v;
            }
        }
        return true;
    }

    /**
     * returns magento default value if applicable
     * - item should not exist yet
     * - default value should be set in magento
     * - ivalue should be empty
     *
     * @param array $attrdesc
     *            : attribute description table
     * @param mixed $ivalue
     *            : input value
     * @return magento default value or NULL if not applicable
     */
    public function getDefaultValue($attrdesc, $ivalue)
    {
        $exists = $this->currentItemExists();
        // check for new item default value in DB for new items
        if (!$exists && isset($attrdesc["default_value"]) && !empty($attrdesc["default_value"]) && empty($ivalue))
        {
            return $attrdesc["default_value"];
        }
        return null;
    }

    /**
     * attribute handler for Decimal attributes
     *
     * @param int $pid
     *            : product id
     * @param array $item
     *            : item to inges
     * @param int $storeid
     *            : store for attribute value storage
     * @param int $attrcode
     *            : attribute code
     * @param array $attrdesc
     *            : attribute metadata
     * @param mixed $ivalue
     *            : input value to import
     * @return new decimal value to set
     */
    public function handleDecimalAttribute($pid, &$item, $storeid, $attrcode, $attrdesc, $ivalue)
    {
        $dval = $this->getDefaultValue($attrdesc, $ivalue);
        if ($dval !== null)
        {
            return $dval;
        }
        // force convert decimal separator to dot
        $ivalue = str_replace(",", ".", $ivalue);
        $ovalue = deleteifempty($ivalue);
        return $ovalue;
    }

    /**
     * attribute handler for DateTime attributes
     *
     * @param int $pid
     *            : product id
     * @param array $item
     *            : item to inges
     * @param int $storeid
     *            : store for attribute value storage
     * @param int $attrcode
     *            : attribute code
     * @param array $attrdesc
     *            : attribute metadata
     * @param mixed $ivalue
     *            : input value to import
     * @return new datetime value to set
     */
    public function handleDatetimeAttribute($pid, &$item, $storeid, $attrcode, $attrdesc, $ivalue)
    {
        $dval = $this->getDefaultValue($attrdesc, $ivalue);
        if ($dval !== null)
        {
            return $dval;
        }
        $ovalue = deleteifempty(trim($ivalue));
        // Handle european date format or other common separators
        if (preg_match("|(\d{1,2})\D(\d{1,2})\D(\d{4})\s*(\d{2}:\d{2}:\d{2})?|", $ovalue, $matches))
        {
            $hms = count($matches) > 4 ? $matches[4] : "";
            $ovalue = trim(sprintf("%4d-%2d-%2d %s", $matches[3], $matches[2], $matches[1], $hms));
        }
        return $ovalue;
    }

    /**
     * attribute handler for Text attributes
     *
     * @param int $pid
     *            : product id
     * @param array $item
     *            : item to inges
     * @param int $storeid
     *            : store for attribute value storage
     * @param int $attrcode
     *            : attribute code
     * @param array $attrdesc
     *            : attribute metadata
     * @param mixed $ivalue
     *            : input value to import
     * @return new text value to set
     */
    public function handleTextAttribute($pid, &$item, $storeid, $attrcode, $attrdesc, $ivalue)
    {
        $dval = $this->getDefaultValue($attrdesc, $ivalue);
        if ($dval !== null)
        {
            return $dval;
        }
        $ovalue = deleteifempty($ivalue);
        return $ovalue;
    }

    /**
     * check if a value is integer
     *
     * @param mixed $value            
     * @return true if integer, false if not
     */
    public function checkInt($value)
    {
        return is_int($value) || (is_string($value) && is_numeric($value) && (int) $value == $value);
    }

    /**
     * attribute handler for Int typed attributes
     *
     * @param int $pid
     *            : product id
     * @param array $item
     *            : item to inges
     * @param int $storeid
     *            : store for attribute value storage
     * @param int $attrcode
     *            : attribute code
     * @param array $attrdesc
     *            : attribute metadata
     * @param mixed $ivalue
     *            : input value to import
     * @return new int value to set
     *        
     *         Many attributes are int typed, so we need to handle all cases like :
     *         - select
     *         - tax id
     *         - boolean
     *         - status
     *         - visibility
     */
    public function handleIntAttribute($pid, &$item, $storeid, $attrcode, $attrdesc, $ivalue)
    {
        $ovalue = $ivalue;
        // default value exists, return it
        $dval = $this->getDefaultValue($attrdesc, $ivalue);
        if ($dval !== null)
        {
            return intval($dval);
        }
        
        $attid = $attrdesc["attribute_id"];
        // if we've got a select type value
        if ($attrdesc["frontend_input"] == "select")
        {
            // we need to identify its type since some have no options
            switch ($attrdesc["source_model"])
            {
                // if its status, default to 1 (Enabled) if not correcly mapped
                case "catalog/product_status":
                    if (!$this->checkInt($ivalue))
                    {
                        $ovalue = 1;
                    }
                    break;
                // do not create options for boolean values tagged as select ,default to 0 if not correcly mapped
                case "eav/entity_attribute_source_boolean":
                    if (!$this->checkInt($ivalue))
                    {
                        $ovalue = 0;
                    }
                    break;
                // if visibility no options either,default to 4 if not correctly mapped
                case "catalog/product_visibility":
                    if (!$this->checkInt($ivalue))
                    {
                        $ovalue = 4;
                    }
                    
                    break;
                // if it's tax_class, get tax class id from item value
                case "tax/class_source_product":
                    $ovalue = $this->getTaxClassId($ivalue);
                    break;
                // otherwise, standard option behavior
                // get option id for value, create it if does not already exist
                // do not insert if empty
                default:
                    $exists = $this->currentItemExists();
                    if ($ivalue == "" && $exists)
                    {
                        return "__MAGMI_DELETE__";
                    }
                    $oids = $this->getOptionIds($attid, $storeid, array($ivalue));
                    $ovalue = $oids[0];
                    unset($oids);
                    break;
            }
        }
        return $ovalue;
    }

    /**
     * attribute handler for "url_key" attribute
     *
     * @param int $pid
     *            : product id
     * @param array $item
     *            : item to inges
     * @param int $storeid
     *            : store for attribute value storage
     * @param int $attrcode
     *            : attribute code
     * @param array $attrdesc
     *            : attribute metadata
     * @param mixed $ivalue
     *            : input value to import
     * @return new int value to set
     *        
     */
    public function handleUrl_keyAttribute($pid, &$item, $storeid, $attrcode, $attrdesc, $ivalue)
    {
        $cpev = $this->tablename("catalog_product_entity_varchar");
        // find conflicting url keys
        $urlk = trim($ivalue);
        $exists = $this->currentItemExists();
        $lst = array();
        if ($urlk == "" && $exists)
        {
            return "__MAGMI_DELETE__";
        }
        
        //in case of url key already has special regexp value in it
        $xurlk=preg_quote($urlk);
        // for existing product, check if we have already a value matching the current pattern
        if ($exists)
        {
            $sql = "SELECT value FROM $cpev WHERE attribute_id=? AND entity_id=? AND value REGEXP ?";
            $eurl = $this->selectone($sql, array($attrdesc["attribute_id"],$pid,$xurlk . "(-\d+)?"), "value");
            // we match wanted pattern, try finding conflicts with our current one
            if ($eurl)
            {
                $urlk = $eurl;
                $sql = "SELECT * FROM $cpev WHERE attribute_id=? AND entity_id!=?  AND value=?";
                $umatch = $urlk;
            }
            // no current value, so try inserting into target pattern list
            else
            {
                
                $sql = "SELECT * FROM $cpev WHERE attribute_id=? AND entity_id!=?  AND value REGEXP ?";
                $umatch = $xurlk . "(-\d+)?";
            }
            $lst = $this->selectAll($sql, array($attrdesc["attribute_id"],$pid,$umatch));
        }
        
        // all conflicting url keys
        if (count($lst) > 0)
        {
            $urlk = $urlk . "-" . count($lst);
        }
        return $urlk;
    }

    /**
     * attribute handler for Varchar typed attributes
     *
     * @param int $pid
     *            : product id
     * @param array $item
     *            : item to inges
     * @param int $storeid
     *            : store for attribute value storage
     * @param int $attrcode
     *            : attribute code
     * @param array $attrdesc
     *            : attribute metadata
     * @param mixed $ivalue
     *            : input value to import
     * @return new int value to set
     *        
     *         Special case for multiselect
     */
    public function handleVarcharAttribute($pid, &$item, $storeid, $attrcode, $attrdesc, $ivalue)
    {
        $exists = $this->currentItemExists();
        // Check store specific value & empty & new item => ignore
        if ($storeid !== 0 && empty($ivalue) && !$exists)
        {
            return false;
        }
        // item exists , empty value, remove value, back to admin
        if ($ivalue == "" && $exists)
        {
            return "__MAGMI_DELETE__";
        }
        // default value exists, return it
        $dval = $this->getDefaultValue($attrdesc, $ivalue);
        if ($dval !== null)
        {
            return $dval;
        }
        
        $ovalue = $ivalue;
        $attid = $attrdesc["attribute_id"];
        // --- Contribution From mennos , optimized by dweeves ----
        // Added to support multiple select attributes
        // (as far as i could figure out) always stored as varchars
        // if it's a multiselect value
        if ($attrdesc["frontend_input"] == "multiselect")
        {
            // if empty delete entry
            if ($ivalue == "")
            {
                return "__MAGMI_DELETE__";
            }
            // magento uses "," as separator for different multiselect values
            $sep = Magmi_Config::getInstance()->get("GLOBAL", "multiselect_sep", ",");
            $multiselectvalues = explode($sep, $ivalue);
            $oids = $this->getOptionIds($attid, $storeid, $multiselectvalues);
            $ovalue = implode(",", $oids);
            unset($oids);
        }
        
        return $ovalue;
    }
}

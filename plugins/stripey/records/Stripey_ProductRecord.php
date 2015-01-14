<?php

namespace Craft;


class Stripey_ProductRecord extends BaseRecord
{

    /**
     * @inheritDoc BaseRecord::getTableName()
     *
     * @return string
     */
    public function getTableName()
    {
        return 'stripey_products';
    }

    /**
     * @inheritDoc BaseRecord::defineRelations()
     *
     * @return array
     */
    public function defineRelations()
    {
        return array(
            'element' => array(static::BELONGS_TO, 'ElementRecord', 'id', 'required' => true, 'onDelete' => static::CASCADE),
            'type'    => array(static::BELONGS_TO, 'Stripey_ProductTypeRecord', 'onDelete' => static::CASCADE),
            'author'  => array(static::BELONGS_TO, 'UserRecord', 'onDelete' => static::CASCADE),
            'master' => array(static::HAS_ONE,'Stripey_VariantRecord','productId','condition'=>'master.isMaster = 1'),
            'variants' => array(static::HAS_MANY,'Stripey_VariantRecord','productId','condition'=>'master.isMaster = 0'),
            'variantsWithMaster' => array(static::HAS_MANY,'Stripey_VariantRecord','productId')
        );
    }

    /**
     * @inheritDoc BaseRecord::defineIndexes()
     *
     * @return array
     */
    public function defineIndexes()
    {
        return array(
            array('columns' => array('typeId')),
            array('columns' => array('availableOn')),
            array('columns' => array('expiresOn')),
        );
    }

    // Protected Methods
    // =========================================================================

    /**
     * @inheritDoc BaseRecord::defineAttributes()
     *
     * @return array
     */
    protected function defineAttributes()
    {
        return array(
            'availableOn' => AttributeType::DateTime,
            'expiresOn'   => AttributeType::DateTime,
        );
    }

}
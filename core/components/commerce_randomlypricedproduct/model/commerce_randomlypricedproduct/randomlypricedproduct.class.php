<?php
use modmore\Commerce\Admin\Widgets\Form\Field;

/**
 * Randomly Priced Product extension for Commerce
 *
 * Copyright 2017 by Mark Hamstra <mark@modmore.com>
 *
 * This file is meant to be used with Commerce by modmore. A valid Commerce license is required.
 *
 * @package commerce_randomlypricedproduct
 * @author Mark Hamstra <mark@modmore.com>
 * @license See core/components/commerce_randomlypricedproduct/docs/license.txt
 */
class RandomlyPricedProduct extends comProduct
{
    /**
     * The core function of this product type is to provide a random price. So we define our own getPrice method.
     *
     * @return \modmore\Commerce\Products\Price
     */
    public function getPrice()
    {
        // Create the price object
        $price = new \modmore\Commerce\Products\Price($this->commerce);

        // Pick a random price between the min and max
        $min = $this->getProperty('min_price', 0);
        $max = $this->getProperty('max_price', 100);
        $randomPrice = function_exists('mt_rand') ? mt_rand($min, $max) : rand($min, $max);

        // Set it on the product record.
        $this->set('price', $randomPrice);

        // Set it on the price object for the current currency
        $price->set($randomPrice, $this->commerce->currency);

        // Return the price
        return $price;
    }

    /**
     * Synchronise is called when a product is added to the cart or saved in the dashboard. We use it to update
     * the price on the product.
     *
     * @return bool
     */
    public function synchronise()
    {
        $this->getPrice();
        return $this->save();
    }

    /**
     * With the getModelFields method we define what fields are available when editing a product of this type.
     *
     * For the RandomlyPricedProduct, we remove the standard price field, and replace it with a min and max field.
     *
     * @return Field[]
     */
    public function getModelFields()
    {
        // This product will inherit all the standard fields
        /** @var Field[] $fields */
        $fields = parent::getModelFields();

        // First we find the original price field - we don't want that as the price is random!
        $originalPriceFieldIdx = 0;
        foreach ($fields as $idx => $field) {
            if ($field->getName() === 'price') {
                // Found it! Keep the index so we can add new fields in its place, and get rid of it.
                $originalPriceFieldIdx = $idx;
                unset($fields[$idx]);
                break;
            }
        }

        // Then we define two new price fields, min and max.
        $newFields = [];
        $newFields[] = new \modmore\Commerce\Admin\Widgets\Form\NumberField($this->commerce, [
            'name' => 'properties[min_price]',
            'label' => $this->adapter->lexicon('commerce_rpp.min_price'),
            'input_class' => 'commerce-field-currency',
            'value' => $this->getProperty('min_price', 0),
        ]);

        $newFields[] = new \modmore\Commerce\Admin\Widgets\Form\NumberField($this->commerce, [
            'name' => 'properties[max_price]',
            'label' => $this->adapter->lexicon('commerce_rpp.max_price'),
            'input_class' => 'commerce-field-currency',
            'value' => $this->getProperty('max_price', 0),
        ]);

        array_splice($fields, $originalPriceFieldIdx, 0, $newFields);

        return $fields;
    }

    /**
     * When using the Products TV, we can show additional information about the product in the right column
     * by returning a (HTML) formatted string.
     *
     * @return mixed|null|string
     */
    public function getTVExtraInformation()
    {
        $min = $this->formatValue($this->getProperty('min_price'), 'financial');
        $max = $this->formatValue($this->getProperty('max_price'), 'financial');
        return $this->adapter->lexicon('commerce_rpp.tv_description', [
            'min' => $min,
            'max' => $max
        ]);
    }
}

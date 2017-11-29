<?php
namespace modmore\Example\RandomlyPricedProduct\Modules;
use modmore\Commerce\Modules\BaseModule;
use Symfony\Component\EventDispatcher\EventDispatcher;

class RandomlyPricedProduct extends BaseModule {

    public function getName()
    {
        $this->adapter->loadLexicon('commerce_randomlypricedproduct:default');
        return $this->adapter->lexicon('commerce_rrp.randomlypricedproduct');
    }

    public function getAuthor()
    {
        return 'Mark Hamstra';
    }

    public function getDescription()
    {
        return $this->adapter->lexicon('commerce_rrp.randomlypricedproduct.description');
    }

    public function initialize(EventDispatcher $dispatcher)
    {
        // Load our lexicon
        $this->adapter->loadLexicon('commerce_randomlypricedproduct:default');

        // Add the xPDO package, so Commerce can detect the comProduct derivative class
        $path = dirname(dirname(__DIR__)) . '/model/';
        $this->adapter->loadPackage('commerce_randomlypricedproduct', $path);
    }
}
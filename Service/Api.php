<?php

namespace LaravelEnso\MagentoProductSync\Service;

use LaravelEnso\MagentoProductSync\Helper\Cache;

class Api // TODO :: REMOVE CACHE RESPONSIBILITY FROM THIS!
{
    private $content;
    private $products;
    private $cache;

    private $created;
    private $updated;
    private $removed;

    public function __construct()
    {
        $this->cache = new Cache();
        $this->fetch()->convert();
    }

    public function created()
    {
        if (! $this->created) {
            $this->created = array_filter($this->products, function ($product) {
                return $this->cache->get($this->key($product)) === null;
            });
        }

        return $this->created;
    }

    public function updated()
    {
        if (! $this->updated) {
            $this->updated = array_filter($this->products, function ($product) {
                return $this->cache->get($this->key($product)) !== null
                    && $this->checksum($product) !== $this->cache->get($this->key($product));
            });
        }

        return $this->updated;
    }

    public function removed()
    {
        if ($this->removed) {
            return $this->removed;
        }

        $removed = array_diff_key($this->cache->all(), $this->products);
        $this->removed = [];

        foreach ($removed as $sku => $checksum) {
            $this->removed[$sku] = $this->product($sku);
        }

        return $this->removed;
    }

    public function count()
    {
        return count($this->removed())
            + count($this->created())
            + count($this->updated());
    }

    public function synced($product)
    {
        isset($this->removed[$this->key($product)])
            ? $this->cache->delete($this->key($product))
            : $this->cache->put($this->key($product), $this->checksum($product));
    }

    public function keys()
    {
        $result =  array_map(function ($product) {
            return $this->key($product);
        }, $this->products + $this->removed());

        return array_values($result);
    }

    private function fetch()
    {
        $this->content = file_get_contents('https://gest.magicashop.ro/Feeds/feedCompletProduse.xml');

        return $this;
    }

    private function convert()
    {
        $xml = simplexml_load_string($this->content);
        $array = json_decode(json_encode($xml), true)['produs'] ?? [];
//        $array = array_splice($array, 400, 10);

        $this->products = [];

        foreach ($array as $product) {
            $product = $this->toEnglish($product);
            $this->products[$this->key($product)] = $product;
        }

        return $this->products;
    }

    private function key($product)
    {
        return $product['sku'];
    }

    private function checksum($product)
    {
        return md5(json_encode($product));
    }

    private function toEnglish($product)
    {
        return [
            'sku' => $product['CodProdus'],
            'manufacturer' => $product['Producator'],
            'name' => $product['NumeProdus'],
            'url' => $product['URL'],
            'category' => $product['NumeCategorie'],
            'price' => $product['PretEndUser'],
            'stock' => $product['Stoc'],
            'short_description' => $product['DescriereScurta'],
            'description' => $product['Descriere'],
            'image' => $product['URL_poza'],
        ];
    }

    private function product($sku): array
    {
        return ['sku' => $sku];
    }
}
<?php

namespace LaravelEnso\MagentoProductSync\Service;

use LaravelEnso\MagentoProductSync\Helper\Cache;

class Api // TODO :: REMOVE CACHE RESPONSIBLITY FROM THIS!
{
    private $content;
    private $products;
    private $cache;

    private $created;
    private $updated;
    private $removed;

    public function __construct()
    {
        $this->cache = (new Cache())->getCache();
        $this->fetch()->convert();
        $this->cache = []; //TODO::REMOVE IT!
    }

    public function created()
    {
        if (! $this->created) {
            $this->created = array_diff_key($this->products, $this->cache);
        }

        return $this->created;
    }

    public function updated()
    {
        if (! $this->updated) {
            $this->updated = array_filter($this->products, function ($product) {
                return array_key_exists($this->key($product), $this->cache)
                    && $this->cache[$this->key($product)] !== $this->checksum($product);
            });
        }

        return $this->updated;
    }

    public function removed()
    {
        if (! $this->removed) {
            $removed = array_diff_key($this->cache, $this->products);
            $this->removed = [];
            foreach ($removed as $sku => $checksum) {
                $this->removed[$sku] = ['sku' => $sku]; //TODO:: BAD LOGIC!
            }
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
        if (isset($this->created[$this->key($product)]) || isset($this->updated[$this->key($product)])) {
           (new Cache())->updated($product);

           return;
        }

        (new Cache())->deleted($product);
    }

    public function keys()
    {
        return array_map(function ($product) {
            return $this->key($product);
        }, $this->products + $this->removed());
    }

    private function fetch()
    {
        $this->content = '<produse>
  <produs id="6174">
    <CodProdus>BL400717345613212521231362</CodProdus>
    <Producator>Bullyl2a345n4211232d</Producator>
    <NumeProdus>101 Da2452346456lma5ti432eni Dipstick111111112</NumeProdus>
    <URL>https://www.magicashop.ro/101_Dalmatieni_Dipstick</URL>
    <NumeCategorie>F324iguri1ne&gt;Figurine D223isney&gt;101 123d3almatieni</NumeCategorie>
    <PretEndUser>2220.43</PretEndUser>
    <Stoc>24</Stoc>
    <DescriereScurta>Catelul Dipstick din "101 Dalmatieni"</DescriereScurta>
    <Descriere>&lt;p&gt;&lt;span style="font-family: arial, helvetica, sans-serif; font-size: 9pt;"&gt;Pongo este un dalmatian&amp;nbsp;care traieste alaturi de stapanul sau, Roger, un compozitor intr-o casa micuța din Londra. Plictisit de viata sa, Pongo decide ca este timpul sa aiba, atat el cat si Roger, o sotie. In timp ce se uita pe geam, vede femei si cateluse de toate felurile, insa niciuna nu e pe placul lui. Exact atunci apare o femeie, Anita, cu cateaua ei, un dalmatian. Pongo se hotaraste sa faca tot posibilul pentru ca Roger sa o intalneasca pe Anita si reuseste.&lt;/span&gt;&lt;/p&gt;
&lt;p&gt;&lt;span style="font-family: arial, helvetica, sans-serif; font-size: 9pt;"&gt;Dupa un timp, cei doi se casatoresc, iar Pongo impreuna cu cateaua Anitei, Perdita fac o serie de puisori. &lt;/span&gt;&lt;/p&gt;
&lt;p&gt;&lt;em&gt;&lt;span style="font-family: arial, helvetica, sans-serif; font-size: 9pt;"&gt;Dipstick este unul din cei 15 catelusi, fiind si cel mai deosebit.&lt;/span&gt;&lt;/em&gt;&lt;/p&gt;
&lt;p&gt;&lt;span style="font-family: arial, helvetica, sans-serif; font-size: 9pt;"&gt;Din pacate, apare Cruella de Vil care vrea sa ii cumpere pe toti pentru a-si face o noua blana. Cum era de asteptat, Anita si Roger nu accepta si Cruella trimite doi hoți, Jasper si Horace, sa fure dalmatienii. Catelusii sunt dusi intr-o casa unde mai sunt si alti pui de dalmatieni cumparati. In total 99.&lt;/span&gt;&lt;/p&gt;
&lt;p&gt;&lt;span style="font-family: arial, helvetica, sans-serif; font-size: 9pt;"&gt;Pongo si Perdita reusesc sa gaseasca puii si sa-i salveze pe toti. Dupa un drum lung ei ajung acasa. Desi erau in total 101 dalmatieni, Roger si Anita s-au gandit sa-si faca o crescatorie de dalmatieni cu banii facuti din hitul lui Roger.&lt;/span&gt;&lt;/p&gt;
&lt;p&gt;&lt;span style="font-family: arial, helvetica, sans-serif; font-size: 9pt;"&gt;Dimensiune figurina: 4 cm.&lt;/span&gt;&lt;/p&gt;
&lt;p&gt;&lt;span style="font-family: arial, helvetica, sans-serif; font-size: 9pt;"&gt;&amp;nbsp;&lt;/span&gt;&lt;/p&gt;
&lt;p&gt;&lt;span style="font-family: arial, helvetica, sans-serif; font-size: 9pt;"&gt;&lt;strong&gt;Figurina este pictata manual si este realizata dintr-un material plastic care nu contine PVC.&lt;/strong&gt;&lt;/span&gt;&lt;strong&gt;&amp;nbsp;&lt;/strong&gt;&lt;/p&gt;</Descriere>
    <URL_poza>https://www.magicashop.ro//Images/0012243_0.jpeg</URL_poza>
    <data>08.06.2020 17:21</data>
  </produs>
  <produs id="6175">
    <CodProdus>BL4007qwe231234761123252291</CodProdus>
    <Producator>Bu123lqwel2341212332yland</Producator>
    <NumeProdus>10112qwe142343423 2Dalmat11ieni Lucky3</NumeProdus>
    <URL>https://www.magicashop.ro/101_Dalmatieni_Lucky</URL>
    <NumeCategorie>F111111ig4351ur213ine&gt;Figuri1ne1222123 Disney&gt;101 d1a22222222lma4tieni</NumeCategorie>
    <PretEndUser>19.97</PretEndUser>
    <Stoc>96</Stoc>
    <DescriereScurta>Catelul Lucky din "101 Dalmatieni"</DescriereScurta>
    <Descriere>&lt;p&gt;&lt;span style="font-family: arial, helvetica, sans-serif; font-size: 9pt;"&gt;Pongo este un dalmatian&amp;nbsp;care traieste alaturi de stapanul sau, Roger, un compozitor intr-o casa micuța din Londra. Plictisit de viata sa, Pongo decide ca este timpul sa aiba, atat el cat si Roger, o sotie. In timp ce se uita pe geam, vede femei si cateluse de toate felurile, insa niciuna nu e pe placul lui. Exact atunci apare o femeie, Anita, cu cateaua ei, un dalmatian. Pongo se hotaraste sa faca tot posibilul pentru ca Roger sa o intalneasca pe Anita si reuseste.&lt;/span&gt;&lt;/p&gt;
&lt;p&gt;&lt;span style="font-family: arial, helvetica, sans-serif; font-size: 9pt;"&gt;Dupa un timp, cei doi se casatoresc, iar Pongo impreuna cu cateaua Anitei, Perdita fac o serie de puisori. &lt;/span&gt;&lt;/p&gt;
&lt;p&gt;&lt;em&gt;&lt;span style="font-family: arial, helvetica, sans-serif; font-size: 9pt;"&gt;Lucky este cel mai norocos&amp;nbsp;din cei 15 catelusi, deoarece scapa teafar ca prin minune din orice situatie.&lt;/span&gt;&lt;/em&gt;&lt;/p&gt;
&lt;p&gt;&lt;span style="font-family: arial, helvetica, sans-serif; font-size: 9pt;"&gt;Din pacate, apare Cruella de Vil care vrea sa ii cumpere pe toti pentru a-si face o noua blana. Cum era de asteptat, Anita si Roger nu accepta si Cruella trimite doi hoți, Jasper si Horace, sa fure dalmatienii. Catelusii sunt dusi intr-o casa unde mai sunt si alti pui de dalmatieni cumparati. In total 99.&lt;/span&gt;&lt;/p&gt;
&lt;p&gt;&lt;span style="font-family: arial, helvetica, sans-serif; font-size: 9pt;"&gt;Pongo si Perdita reusesc sa gaseasca puii si sa-i salveze pe toti. Dupa un drum lung ei ajung acasa. Desi erau in total 101 dalmatieni, Roger si Anita s-au gandit sa-si faca o crescatorie de dalmatieni cu banii facuti din hitul lui Roger.&lt;/span&gt;&lt;/p&gt;
&lt;p&gt;&lt;span style="font-family: arial, helvetica, sans-serif; font-size: 9pt;"&gt;Dimensiune figurina: 5 cm.&lt;/span&gt;&lt;/p&gt;
&lt;p&gt;&lt;span style="font-family: arial, helvetica, sans-serif; font-size: 9pt;"&gt;&amp;nbsp;&lt;/span&gt;&lt;/p&gt;
&lt;p&gt;&lt;span style="font-family: arial, helvetica, sans-serif; font-size: 9pt;"&gt;&lt;strong&gt;Figurina este pictata manual si este realizata dintr-un material plastic care nu contine PVC.&lt;/strong&gt;&lt;strong&gt;&amp;nbsp;&lt;/strong&gt;&lt;/span&gt;&lt;/p&gt;</Descriere>
    <URL_poza>https://www.magicashop.ro//Images/0012244_0.jpeg</URL_poza>
    <data>08.06.2020 17:21</data>
  </produs>
  </produse>';

//        $content = file_get_contents('https://gest.magicashop.ro/Feeds/feedCompletProduse.xml');
        $this->content = file_get_contents('http://localhost/tmp/feedCompletProduse.xml');

        return $this;
    }

    private function convert()
    {
        $xml = simplexml_load_string($this->content);

        $array = json_decode(json_encode($xml), true);

        $this->products = [];

        $result= array_splice($array['produs'], 200, 10);
        foreach ($result as $product) {
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
            'manufacture' => $product['Producator'],
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
}
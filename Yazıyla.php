<?php

/*
 * This file is part of Twig.
 *
 * (c) 2011 Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Twig_Extension_Yaziyla extends Twig_Extension
{
  var $sayi=0;
  var $kurus=0;
  var $eksi="";
  var $birim="TL";
  var $kurus_birim = "KR";
  var $bolukler;
  var $birler;
  var $onlar;

    public function getFunctions()
    {


        return array(
            new Twig_SimpleFunction('yaziylayaz', array($this,'yazi')),
        );
    }

    public function getName()
    {
        return 'yaziyla';
    }

    function yazi($context)
    {

      $this->yaziyla();
      return $this->yaz($context);
    }


    function yaziyla($birim="TL", $kurus_birim="KR") {

        $this->birim          = $birim;
        $this->kurus_birim    = $kurus_birim;
        $this->bolukler       = array("","BİN","Milyon","Milyar","Trilyon","Katrilyon","Trilyar","Kentrilyon","Kentrilyar","Zontrilyar");
        $this->birler         = array("SIFIR","BİR","İKİ","ÜÇ","DÖRT","BEŞ","ALTI","YEDİ","SEKİZ","DOKUZ");
        $this->onlar          = array("","ON","YİRMİ","OTUZ","KIRK","ELLİ","ALTMIŞ","YETMİŞ","SEKSEN","DOKSAN","YÜZ");

    }
    function yaz($sayi) {

      $sayi = str_replace(' TL','',$sayi);
      $sayi = str_replace('.','',$sayi);
      $sayi = str_replace(',','.',$sayi);


        $tam="";
        $kurus="";

        if($this->sayi_cozumle($sayi)) {

        //return "Hatalı Sayı Formatı!";
        return "";
        }

        if(($this->sayi+$this->kurus) == 0) return $this->birler[0].' '.$this->birim;

        if($this->sayi>0) $tam = $this->oku($this->sayi);
        if($this->kurus>0) $kurus = $this->oku($this->kurus);

        if( $this->sayi == 0 ) return $this->eksi.' '.$kurus.' '.$this->kurus_birim;
        if( $this->kurus == 0 ) return $this->eksi.' '.$tam.' '.$this->birim;
        return $this->eksi.' '.$tam.' '.$this->birim.' '.$kurus.' '.$this->kurus_birim;
    }
    function oku($sayi) {

    if($sayi == 0) return $this->birler[0];
        $ubb = sizeof($this->bolukler);
        $kac_sifir = 3 - (strlen($sayi) % 3);
        if($kac_sifir!=3) for($i=0;$i<$kac_sifir;++$i) { $sayi = "0$sayi"; }

        $k = 0; $sonuc = "";
        for($i = strlen($sayi); $i>0; $i-=3,++$k) {

           $boluk = $this->boluk_oku(substr($sayi, $i-3, 3));
           if($boluk) {
           if(($k == 1) && ($boluk == $this->birler[1])) $boluk = "";
           if(  $k > $ubb) $sonuc = $boluk ."Tanımsız(".($k*3).".Basamak) $sonuc";
           else $sonuc = $boluk . $this->bolukler[$k]." $sonuc";
           }
        }
        return $sonuc;
    }
    function boluk_oku($sayi) {

         $sayi = ((int)($sayi)) % 1000; $sonuc = "";
         $bir = $sayi % 10;
         $on_ = (int)($sayi / 10) % 10;
         $yuz = (int)($sayi / 100) % 10;

         if($yuz) { if($yuz == 1) $sonuc = $this->onlar[10];
         else $sonuc = $this->birler[$yuz].$this->onlar[10]; }

         if($on_) $sonuc = $sonuc.$this->onlar[$on_];
         if($bir) $sonuc = $sonuc.$this->birler[$bir];
         return $sonuc;
    }
    function sayi_cozumle($sayi) {

        $sayi = trim($sayi);
        if($sayi[0] == "-") { $this->eksi="Eksi"; $sayi = substr($sayi, 1); }
        if(preg_match("/^(0*\.0+|0*|\.0+)$/", $sayi)) { $this->sayi = $this->kurus = 0; return 0; }
        if(preg_match("/^(\d+)\.(\d+)$/", $sayi, $m))
        {
            $sayi = $m[1]; $this->sayi = (int)preg_replace("/^0+/","",$sayi);
            if(!preg_match("/^0+$/",$m[2])) $this->kurus = (int)$m[2];
        }
        else if(preg_match("/^0*(\d+)$/", $sayi, $m) || preg_match("/^0*(\d+)\.0+$/", $sayi, $m)) { $this->sayi = (int)$m[1]; }
        else if(preg_match("/^0*\.(\d+)$/", $sayi, $m)) { $this->sayi = 0; $this->kurus = (int)$m[1]; }
        else return 1;
        if($this->kurus>0) {

          $this->kurus= number_format('0.'.$this->kurus, 2);
          if( (int)$this->kurus == 1 ) { ++$this->sayi; $this->kurus = 0; }
          else $this->kurus = (int)str_replace("0.", "", $this->kurus);
        }
        return 0;
    }
}

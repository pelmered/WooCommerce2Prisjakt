<?php
/*
Name: Pricelist for prisjakt
Description: A simple script to make a prisjakt.nu (price hunt) readable file for WooComemrce.
Version: 1.0
Author: Peter
Author URI: http://elmered.com

Copyright: © 2012-2013 Peter Elmered.
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

require 'wp-config.php';

// Include the wp-loader
include('wp-load.php');

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

//$r = $mysqli->query("SELECT * FROM wp_posts JOIN wp_postmeta ON wp_postmeta.post_id = wp_posts.ID WHERE post_type = 'product' AND post_status = 'publish' LIMIT 5 ");

$r = $mysqli->query("SELECT * FROM wp_posts 
    WHERE post_type = 'product' AND post_status = 'publish' ");

echo 'Produktnamn;Art.nr.;Kategori;Pris inkl.moms;Frakt;Produkt-URL;Bild-URL;Lagerstatus;Tillverkare;Tillverkar-SKU';

//
//
//;

while ($p = $r->fetch_assoc()) 
{    
    $r2 = $mysqli->query("SELECT * FROM wp_postmeta WHERE post_id = ".$p['ID']." ");
    while ($pm = $r2->fetch_assoc() )
    {
        $p_meta[$pm['meta_key']] = $pm['meta_value'];
    }
    
    $r3 = $mysqli->query("SELECT wp_terms.* FROM wp_term_relationships 
        JOIN wp_terms ON wp_term_relationships.term_taxonomy_id = wp_terms.term_id
        JOIN wp_term_taxonomy ON wp_term_relationships.term_taxonomy_id = wp_term_taxonomy.term_id AND wp_term_taxonomy.taxonomy = 'product_cat'
        WHERE wp_term_relationships.object_id = ".$p['ID']."");

    $cat = array();

    while ($pc = $r3->fetch_assoc() )
    {
        $cat[] = $pc['name'];
    }

    $cat = implode(',', $cat);
    
    //Produktnamn
    echo $p['post_title'].';';

    //art.id
    echo $p_meta['_sku'].';';

    //Kategori
    echo $cat.';';

    //Pris
    echo $p_meta['_price'].';';

    //Fraktpris
    echo '0;'; //@TODO Hur räkna ut?

    //Produktlänk
    echo (get_permalink($p['ID'])).';';

    //Bild-URL
    if ( has_post_thumbnail($p['ID']) )
    {
        echo wp_get_attachment_url( get_post_thumbnail_id($p['ID']) );
    }
    echo ';';

    //Lagerstatus
    if($p_meta['_stock_status'] == 'instock')
        echo 'Ja';
    else 
        echo 'Nej';
       
    //Tillverkare
    //@TODO Finns en stöd i WC hämta från custom meta-fält om ni behöver
	echo ';';

    //Tillverkar-SKU
    //@TODO Finns en stöd i WC hämta från custom meta-fält om ni behöver   
	echo ';'; 

    //radbrytning
    echo "\n";
    
    //Free results to samve memory
    $r2->free();
    $r3->free();
}

$r->free();

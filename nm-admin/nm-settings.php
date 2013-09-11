<?php
/**
 * Database Details
 */
define("DB_HOST","localhost"); //Your database host
define("DB_USER","root"); //Your username to the database
define("DB_PASS",""); //Your password for the account
define("DB_NAME","testinstall"); //The database name

/**
 * Cache Control
 * (set to 'never' if you never want that type of cache emptied)
 */
define("CACHE_LIFE","30"); //how long the network DB cache lasts in days  
define("UNMANAGED_LIFE","1"); //how long unmanaged networks last before being deleted
define("META_CACHE_LIFE","10"); //how long in days the meta cache lasts


/**
 * CACHE_LIFE - This is the cache that stores the outputs of the last 
 * 				time the network was run (including after training). It also
 * 				caches the Tree View HTML.
 * 
 * UNMANAGED_LIFE - This is the lifespan of unmanaged networks (networks that are
 * 					created without the intention of being permanent).
 * 
 * META_CACHE_LIFE - This is the template caching and the caching of exporting sets.
 */

define("ROUND_DECIMAL_PLACE",6); //round to the nth decimal place
?>
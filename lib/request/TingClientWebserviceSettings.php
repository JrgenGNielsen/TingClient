<?php

/**
 * @file Class TingClientWebserviceSettings
 *
 * Configuration. *
 * Holds inline webservice definitions - that is webservice-classes in the
 * request folder.
 */
class TingClientWebserviceSettings {
  public static function getInlineServices() {
    return array(
      'agency' => array(
        'url' => 'agency_search_url',
        'class' => 'TingClientAgencyRequest'
      ),
      'search' => array(
        'url' => 'ting_search_url',
        'class' => 'TingClientSearchRequest'
      ),
      'object' => array(
        'url' => 'ting_search_url',
        'class' => 'TingClientObjectRequest'
      ),
      'collection' => array(
        'url' => 'ting_search_url',
        'class' => 'TingCollectionRequest'
      ),
      'adhl' => array(
        'class' => 'OpenAdhlRequest',
        'url' => 'ting_recommendation_url',
        'xsd_url' => 'ting_recommendation_xsd',
      ),
      'forsrights' => array(
        'class' => 'bibdk_forsrights',
        'url' => 'bibdk_forsrights_url',
        'xsdNamespace' => array(0 => 'http://oss.dbc.dk/ns/forsrights'),
      ),
    );
  }

  public static function getDefaultUrls(){
    return array(
      'adhl' => array(
        'ting_recommendation_url' => 'http://lakiseks.dbc.dk/openadhl/3.0/',
        'ting_recommendation_xsd' => 'http://openadhl.addi.dk/1.1/adhl.xsd',
      ),
      'forsrights' => array(
        'bibdk_forsrights_url' => 'http://forsrights.addi.dk/1.2/',
      )
    );
  }
}
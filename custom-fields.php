<?php
$prefix = 'wpcf-';
$customterm = array(
  'fields' =>array(
    'first-name' => array(
      'name' => __( 'First Name*', 'cmb2' ),
      'desc' => __( '', 'cmb2' ),
      'id'   => $prefix . 'first-name',
      'type' => 'text_medium',
      'attributes'  => array(
        'required'    => 'required',
      ),
    ) ,
    'last-name' =>array(
      'name' => __( 'Last Name*', 'cmb2' ),
      'desc' => __( '', 'cmb2' ),
      'id'   => $prefix . 'last-name',
      'type' => 'text_medium',
      'attributes'  => array(
        'required'    => 'required',
      ),
    ),
    'display-name' => array(
      'name' => __( 'Display Name', 'cmb2' ),
      'desc' => __( '', 'cmb2' ),
      'id'   => $prefix . 'display-name',
      'type' => 'text_medium',
    ) ,
    'email' => array(
      'name' => __( 'Email*', 'cmb2' ),
      'desc' => __( '', 'cmb2' ),
      'id'   => $prefix . 'email',
      'type' => 'text_email',
      'attributes'  => array(
        'required'    => 'required',
      ),
    ),
    'phone' => array(
      'name' => __( 'Phone', 'cmb2' ),
      'desc' => __( '', 'cmb2' ),
      'id'   => $prefix . 'phone',
      'type' => 'text_medium',
    ) ,
    'mobile' => array(
      'name' => __( 'Mobile', 'cmb2' ),
      'desc' => __( '', 'cmb2' ),
      'id'   => $prefix . 'mobile',
      'type' => 'text_medium',
    ) ,
    'website' =>array(
      'name' => __( 'Website', 'cmb2' ),
      'desc' => __( '', 'cmb2' ),
      'id'   => $prefix . 'website',
      'type' => 'text_url',
    ) ,
    'contactimage' => array(
      'name' => __( 'Contact Image', 'cmb2' ),
      'desc' => __( '', 'cmb2' ),
      'id'   => $prefix . 'contactimage',
      'type' => 'file',
    ) ,
    'ukuu-job-title' => array(
      'name' => __( 'Job Title', 'cmb2' ),
      'desc' => __( '', 'cmb2' ),
      'id'   => $prefix . 'ukuu-job-title',
      'type' => 'text_medium',
    ) ,
    'ukuu-twitter-handle' => array(
      'name' => __( 'Twitter Handle', 'cmb2' ),
      'desc' => __( '', 'cmb2' ),
      'id'   => $prefix . 'ukuu-twitter-handle',
      'type' => 'text_medium',
    ) ,
    'ukuu-facebook-url' => array(
      'name' => __( 'Facebook URL', 'cmb2' ),
      'desc' => __( '', 'cmb2' ),
      'id'   => $prefix . 'ukuu-facebook-url',
      'type' => 'text_url',
    ),
    'ukuu-date-of-birth' => array(
      'name' => __( 'Date Of Birth', 'cmb2' ),
      'desc' => __( '', 'cmb2' ),
      'id'   => $prefix . 'ukuu-date-of-birth',
      'type' => 'text_date',
    ),
    'streetaddress' => array(
      'name' => __( 'Street Address', 'cmb2' ),
      'desc' => __( '', 'cmb2' ),
      'id'   => $prefix . 'streetaddress',
      'type' => 'text_medium',
    ) ,
    'streetaddress2' => array(
      'name' => __( 'Street Address Line 2', 'cmb2' ),
      'desc' => __( '', 'cmb2' ),
      'id'   => $prefix . 'streetaddress2',
      'type' => 'text_medium',
    ),
    'city' => array(
      'name' => __( 'City', 'cmb2' ),
      'desc' => __( '', 'cmb2' ),
      'id'   => $prefix . 'city',
      'type' => 'text_medium',
    ) ,
    'postalcode' => array(
      'name' => __( 'Postal code', 'cmb2' ),
      'desc' => __( '', 'cmb2' ),
      'id'   => $prefix . 'postalcode',
      'type' => 'text_medium',
    ) ,
    'country' => array(
      'name' => __( 'Country', 'cmb2' ),
      'desc' => __( '', 'cmb2' ),
      'id'   => $prefix . 'country',
      'type' => 'text_medium',
    ) ,
    'state' => array(
      'name' => __( 'State', 'cmb2' ),
      'desc' => __( '', 'cmb2' ),
      'id'   => $prefix . 'state',
      'type' => 'text_medium',
    ) ,
    'privacy-settings' => array(
      'name'    => __( 'Preferred Contact Method', 'cmb2' ),
      'desc'    => __( '', 'cmb2' ),
      'id'      => $prefix . 'privacy-settings',
      'type'    => 'multicheck',
      // 'multiple' => true, // Store values in individual rows
      'options' => array(
        'do_not_phone' => __( 'Phone', 'cmb2' ),
        'do_not_email' => __( 'Email', 'cmb2' ),
        'do_not_sms' => __( 'SMS', 'cmb2' ),
      ),
      // 'inline'  => true, // Toggles display to inline
    ),
    'bulk-mailings' => array(
      'name'    => __( 'Bulk Mailings', 'cmb2' ),
      'desc'    => __( '', 'cmb2' ),
      'id'      => $prefix . 'bulk-mailings',
      'type'    => 'multicheck',
      // 'multiple' => true, // Store values in individual rows
      'options' => array(
        'opt_out' => __( 'Opt Out', 'cmb2' ),
      ),
      // 'inline'  => true, // Toggles display to inline
    ) ,
    'startdate' => array(
      'name' => __( 'Start Date*', 'cmb2' ),
      'desc' => __( 'Enter the Start Date', 'cmb2' ),
      'id'   => $prefix . 'startdate',
      'type' => 'text_datetime_timestamp',
      'attributes'  => array(
        'required'    => 'required',
      ),
    ) ,
    'enddate' => array(
      'name' => __( 'End Date*', 'cmb2' ),
      'desc' => __( 'Enter the End Date', 'cmb2' ),
      'id'   => $prefix . 'enddate',
      'type' => 'text_datetime_timestamp',
      'attributes'  => array(
        'required'    => 'required',
      ),
    ),
    'status' => array(
      'name'             => __( 'Status*', 'cmb2' ),
      'desc'             => __( 'Status of Touchpoint', 'cmb2' ),
      'id'               => $prefix . 'status',
      'type'             => 'select',
      'attributes'  => array(
        'required'    => 'required',
      ),
      'options'          => array(
        'scheduled' => __( 'Scheduled', 'cmb2' ),
        'completed'   => __( 'Completed', 'cmb2' ),
        'cancel'     => __( 'Cancel', 'cmb2' ),
      ),
    ) ,
    'details' => array(
      'name' => __( 'Details', 'cmb2' ),
      'desc' => __( '', 'cmb2' ),
      'id'   => $prefix . 'details',
      'type' => 'textarea_small',
    ) ,
    'attachments' => array(
      'name' => __( 'Attachments', 'cmb2' ),
      'desc' => __( 'Upload a file or enter a URL.', 'cmb2' ),
      'id'   => $prefix . 'attachments',
      'type' => 'file',
    ),
  )
);

<?php

namespace App\Constants;

use Carbon\Carbon;

class AppConstants
{
    public static $pagination = 10;
    public static $product_duration = 15;
    public static $bid_duration = 30;
    public static $time_format = 'Y-m-d H:i:s';
    public static $time_zone = 'Africa/Nairobi';
    public static $buyerStatus = array(
        'pending' => 'PENDING',
        'approved' => 'APPROVED',
        'rejected' => 'REJECTED',
    );

    public static function file_name($key){
        $file_name = 'check_'.$key.'_status_'.Carbon::now().'.log';
        return str_replace(array(' ',':','-'),'_',$file_name);
    }
}

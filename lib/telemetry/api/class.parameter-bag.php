<?php

namespace NinjaShop\Telemetry\API;

use ITE_Parameter_Bag;
use ITE_Array_Parameter_Bag;

/**
 * Flattens nested parameter bags parameters for the request.
 */
class Parameter_Bag extends ITE_Array_Parameter_Bag {

  public function get_params() {
    $params = [];
    foreach( $this->params as $key => $value ) {
      if( $value instanceof ITE_Parameter_Bag ){
        $params = array_merge( $params, $value->get_params() );
      } else {
        $params[ $key ] = $value;
      }
    }
    return $params;
  }
}

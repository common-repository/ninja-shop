<?php

namespace NinjaShop\Telemetry\API;

use ITE_Array_Parameter_Bag;

/**
 * A parameter bag class for the specific formatting requirement of the request.
 *
 * Example: ?metrics[a]=a&metrics[b]=b
 */
class Metrics_Bag extends ITE_Array_Parameter_Bag {

	public function get_params() {
    $params = [];
    foreach( $this->params as $key => $value ) {
      $params[ "metrics[$key]" ] = $value;
    }
    return $params;
	}
}
